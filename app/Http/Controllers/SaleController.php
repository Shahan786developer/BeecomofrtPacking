<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $billType = $request->input('bill_type'); // null, 'bee_comfort', or 'china'
        $query = Sale::with(['customer', 'details']);

        if ($billType && in_array($billType, ['bee_comfort', 'china'])) {
            $query->where('bill_type', $billType);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('owner_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('invoice_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('invoice_date', '<=', $toDate);
        }

        $sales = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $beeComfortCount = Sale::where('bill_type', 'bee_comfort')->count();
        $chinaCount = Sale::where('bill_type', 'china')->count();
        $allCount = Sale::count();

        return view('sales.index', compact('sales', 'billType', 'beeComfortCount', 'chinaCount', 'allCount'));
    }

    public function create(Request $request)
    {
        $billType = $request->input('bill_type', 'bee_comfort');
        if (!in_array($billType, ['bee_comfort', 'china'])) {
            $billType = 'bee_comfort';
        }

        $customers = Customer::orderBy('name')->get();
        $nextInvoiceNo = Sale::generateNextInvoiceNo($billType);
        $today = Carbon::today()->format('Y-m-d');

        // Products strictly filtered to match the selected bill type (Bee Comfort vs China)
        $productsQuery = Product::with(['category', 'dozen', 'sku']);

        if ($billType === 'china') {
            $productsQuery->where(function ($q) {
                $q->where('parent_category', 'China')
                  ->orWhere('parent_category', 'china');
            });
        } else {
            $productsQuery->where(function ($q) {
                $q->where('parent_category', 'Bee Comfort')
                  ->orWhere('parent_category', 'bee_comfort')
                  ->orWhereNull('parent_category');
            });
        }

        $products = $productsQuery
            ->orderBy('product_code')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sku_code' => $p->sku_code,
                    'title' => $p->title,
                    'parent_category' => $p->parent_category ?? 'Bee Comfort',
                    'stock_quantity' => $p->stock_quantity,
                    'selling_price' => (float) $p->selling_price,
                ];
            });

        return view('sales.create', compact('customers', 'nextInvoiceNo', 'today', 'products', 'billType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bill_type' => 'required|in:bee_comfort,china',
            'invoice_no' => 'required|string|unique:sales,invoice_no',
            'invoice_date' => 'required|date',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'items.required' => 'At least one product item must be added to the invoice.',
            'items.min' => 'At least one product item must be added to the invoice.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
        ]);

        // Validate that all items strictly belong to the specified bill type
        foreach ($validated['items'] as $itemData) {
            $prod = Product::find($itemData['product_id']);
            if ($prod) {
                $isChina = strtolower(str_replace(' ', '', $prod->parent_category ?? '')) === 'china';
                if ($validated['bill_type'] === 'china' && !$isChina) {
                    return back()->withInput()->withErrors([
                        'items' => "Product '{$prod->title}' is a Bee Comfort product and cannot be added to a China Bill."
                    ]);
                }
                if ($validated['bill_type'] === 'bee_comfort' && $isChina) {
                    return back()->withInput()->withErrors([
                        'items' => "Product '{$prod->title}' is a China product and cannot be added to a Bee Comfort Bill."
                    ]);
                }
            }
        }

        $sale = DB::transaction(function () use ($validated) {
            $discount = (float) ($validated['discount'] ?? 0);

            $sale = Sale::create([
                'invoice_no' => trim($validated['invoice_no']),
                'bill_type' => $validated['bill_type'],
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'subtotal' => 0,
                'discount' => $discount,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotal = 0.0;

            foreach ($validated['items'] as $itemData) {
                // Lock product record to maintain accurate stock
                $product = Product::with(['category', 'dozen'])->lockForUpdate()->findOrFail($itemData['product_id']);

                $quantity = (int) $itemData['quantity'];

                // Selling price and title are fetched from saved product record (read-only)
                $unitPrice = (float) $product->selling_price;
                $lineTotal = (float) ($quantity * $unitPrice);
                $subtotal += $lineTotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'sku_code' => $product->sku_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'returned_quantity' => 0,
                    'total_amount' => $lineTotal,
                ]);

                // On successful invoice submission, automatically deduct sold quantity from product stock.
                // Maintain accurate negative stock quantities if sold beyond current inventory.
                $product->recordStockChange(
                    quantityChange: -$quantity,
                    type: 'sale',
                    referenceId: $sale->id,
                    referenceType: Sale::class,
                    notes: "Invoice #{$sale->invoice_no} sale to customer ID {$sale->customer_id}"
                );
            }

            $totalAmount = max(0, $subtotal - $discount);

            $sale->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            return $sale;
        });

        return redirect()->route('sales.index', ['bill_type' => $sale->bill_type])
            ->with('success', "Invoice #{$sale->invoice_no} created successfully. Stock quantities deducted.");
    }

    public function print(Sale $sale)
    {
        $sale->load(['customer', 'details.product.category', 'details.product.dozen']);
        $company = \App\Models\CompanyProfile::getActive();
        return view('sales.print', compact('sale', 'company'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'details.product.category', 'details.product.dozen', 'returns.details']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $customers = Customer::orderBy('name')->get();
        $sale->load(['customer', 'details']);

        $products = Product::with(['category', 'dozen', 'sku'])
            ->orderBy('product_code')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sku_code' => $p->sku_code,
                    'title' => $p->title,
                    'parent_category' => $p->parent_category ?? 'Bee Comfort',
                    'stock_quantity' => $p->stock_quantity,
                    'selling_price' => (float) $p->selling_price,
                ];
            });

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'items.required' => 'At least one product item must be added to the invoice.',
            'items.min' => 'At least one product item must be added to the invoice.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
        ]);

        DB::transaction(function () use ($sale, $validated) {
            // Restore original stock from old details
            foreach ($sale->details as $oldDetail) {
                $product = Product::lockForUpdate()->find($oldDetail->product_id);
                if ($product) {
                    $product->recordStockChange(
                        quantityChange: +$oldDetail->quantity,
                        type: 'adjustment',
                        referenceId: $sale->id,
                        referenceType: Sale::class,
                        notes: "Restoring stock for invoice #{$sale->invoice_no} update"
                    );
                }
            }

            // Remove old details
            $sale->details()->delete();

            $subtotal = 0.0;
            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                $quantity = (int) $itemData['quantity'];
                $unitPrice = (float) $product->selling_price;
                $lineTotal = (float) ($quantity * $unitPrice);
                $subtotal += $lineTotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'sku_code' => $product->sku_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'returned_quantity' => 0,
                    'total_amount' => $lineTotal,
                ]);

                // Deduct updated stock
                $product->recordStockChange(
                    quantityChange: -$quantity,
                    type: 'sale',
                    referenceId: $sale->id,
                    referenceType: Sale::class,
                    notes: "Updated invoice #{$sale->invoice_no} sale to customer ID {$sale->customer_id}"
                );
            }

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
            ]);
        });

        return redirect()->route('sales.index', ['bill_type' => $sale->bill_type])
            ->with('success', "Invoice #{$sale->invoice_no} updated successfully.");
    }
}
