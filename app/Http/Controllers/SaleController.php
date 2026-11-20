<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $billType = $request->input('bill_type'); // null, 'bee_comfort', or 'china'
        $query = Sale::with(['customer', 'details', 'user']);
        $countsQuery = Sale::query();

        // Scope to user if not Super Admin
        if (!$isSuperAdmin) {
            $query->where('user_id', $currentUser->id);
            $countsQuery->where('user_id', $currentUser->id);
        } else {
            // Super Admin user filter
            if ($request->filled('user_id') && $request->user_id !== 'all') {
                $query->where('user_id', $request->user_id);
                $countsQuery->where('user_id', $request->user_id);
            }
        }

        if ($billType && in_array($billType, ['bee_comfort', 'china'])) {
            $query->where('bill_type', $billType);
        }

        if ($search = $request->input('search')) {
            $searchFilter = function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('owner_name', 'like', "%{$search}%");
                  });
            };
            $query->where($searchFilter);
            $countsQuery->where($searchFilter);
        }

        $today = Carbon::today();
        $isAllDates = $request->has('all_dates') && $request->all_dates == '1';
        $hasCustomDate = $request->filled('start_date') || $request->filled('end_date') || $request->filled('from_date') || $request->filled('to_date');
        $isDefaultMonth = !$hasCustomDate && !$isAllDates;

        $fromDate = $request->input('start_date') ?: $request->input('from_date');
        $toDate = $request->input('end_date') ?: $request->input('to_date');

        if ($isDefaultMonth) {
            $fromDate = $today->copy()->startOfMonth()->toDateString();
            $toDate = $today->copy()->endOfMonth()->toDateString();
        }

        if (!$isAllDates) {
            if ($fromDate) {
                $query->whereDate('invoice_date', '>=', $fromDate);
                $countsQuery->whereDate('invoice_date', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('invoice_date', '<=', $toDate);
                $countsQuery->whereDate('invoice_date', '<=', $toDate);
            }
        }

        $sales = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $beeComfortCount = (clone $countsQuery)->where('bill_type', 'bee_comfort')->count();
        $chinaCount = (clone $countsQuery)->where('bill_type', 'china')->count();
        $allCount = (clone $countsQuery)->count();
        $users = $isSuperAdmin ? User::orderBy('name')->get() : collect();

        return view('sales.index', compact(
            'sales',
            'billType',
            'beeComfortCount',
            'chinaCount',
            'allCount',
            'users',
            'fromDate',
            'toDate',
            'isDefaultMonth',
            'isAllDates'
        ));
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
                    'title' => $p->clean_title ?: ($p->category ? $p->category->name : $p->title),
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
            'show_previous_balance' => 'nullable',
            'previous_balance' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
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

        $sale = DB::transaction(function () use ($validated, $request) {
            $discount = (float) ($validated['discount'] ?? 0);
            $showPreviousBalance = $request->boolean('show_previous_balance');
            $customer = Customer::find($validated['customer_id']);
            $previousBalance = (float) ($request->input('previous_balance') ?? ($customer ? $customer->getPreviousBalance() : 0));

            $sale = Sale::create([
                'user_id' => Auth::id(),
                'invoice_no' => trim($validated['invoice_no']),
                'bill_type' => $validated['bill_type'],
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'subtotal' => 0,
                'discount' => $discount,
                'total_amount' => 0,
                'show_previous_balance' => $showPreviousBalance,
                'previous_balance' => $previousBalance,
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotal = 0.0;
            $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
            $canEditRate = ($validated['bill_type'] === 'bee_comfort' && $isSuperAdmin);

            foreach ($validated['items'] as $itemData) {
                // Lock product record to maintain accurate stock
                $product = Product::with(['category', 'dozen'])->lockForUpdate()->findOrFail($itemData['product_id']);

                $quantity = (int) $itemData['quantity'];

                // Rate editable only by super admin on bee comfort bill
                if ($canEditRate && isset($itemData['unit_price']) && is_numeric($itemData['unit_price'])) {
                    $unitPrice = max(0, (float) $itemData['unit_price']);
                } else {
                    $unitPrice = (float) $product->selling_price;
                }

                $lineTotal = (float) ($quantity * $unitPrice);
                $subtotal += $lineTotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_title' => $product->clean_title ?: ($product->category ? $product->category->name : $product->title),
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
                    notes: "Invoice #{$sale->invoice_no} sale to customer ID {$sale->customer_id}",
                    customerId: $sale->customer_id
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
        $this->checkSaleOwnership($sale);

        $sale->load([
            'customer',
            'user',
            'details' => fn($q) => $q->orderBy('id', 'asc'),
            'details.product.category',
            'details.product.dozen'
        ]);
        $company = \App\Models\CompanyProfile::getActive();
        return view('sales.print', compact('sale', 'company'));
    }

    public function show(Sale $sale)
    {
        $this->checkSaleOwnership($sale);

        $sale->load([
            'customer',
            'user',
            'details' => fn($q) => $q->orderBy('id', 'asc'),
            'details.product.category',
            'details.product.dozen',
            'returns.details'
        ]);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $this->checkSaleOwnership($sale);

        $customers = Customer::orderBy('name')->get();
        $sale->load([
            'customer',
            'details' => fn($q) => $q->orderBy('id', 'asc'),
        ]);

        $productsQuery = Product::with(['category', 'dozen', 'sku']);

        if ($sale->bill_type === 'china') {
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
                    'title' => $p->clean_title ?: ($p->category ? $p->category->name : $p->title),
                    'parent_category' => $p->parent_category ?? 'Bee Comfort',
                    'stock_quantity' => $p->stock_quantity,
                    'selling_price' => (float) $p->selling_price,
                ];
            });

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->checkSaleOwnership($sale);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'show_previous_balance' => 'nullable',
            'previous_balance' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'items.required' => 'At least one product item must be added to the invoice.',
            'items.min' => 'At least one product item must be added to the invoice.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
        ]);

        DB::transaction(function () use ($sale, $validated, $request) {
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
            $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
            $canEditRate = ($sale->bill_type === 'bee_comfort' && $isSuperAdmin);

            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                $quantity = (int) $itemData['quantity'];

                // Rate editable only by super admin on bee comfort bill
                if ($canEditRate && isset($itemData['unit_price']) && is_numeric($itemData['unit_price'])) {
                    $unitPrice = max(0, (float) $itemData['unit_price']);
                } else {
                    $unitPrice = (float) $product->selling_price;
                }

                $lineTotal = (float) ($quantity * $unitPrice);
                $subtotal += $lineTotal;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_title' => $product->clean_title ?: ($product->category ? $product->category->name : $product->title),
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
                    notes: "Updated invoice #{$sale->invoice_no} sale to customer ID {$validated['customer_id']}",
                    customerId: (int) $validated['customer_id']
                );
            }

            $showPreviousBalance = $request->has('show_previous_balance') 
                ? $request->boolean('show_previous_balance') 
                : $sale->show_previous_balance;
            $previousBalance = $request->filled('previous_balance') 
                ? (float) $request->input('previous_balance') 
                : $sale->previous_balance;

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'show_previous_balance' => $showPreviousBalance,
                'previous_balance' => $previousBalance,
            ]);
        });

        return redirect()->route('sales.index', ['bill_type' => $sale->bill_type])
            ->with('success', "Invoice #{$sale->invoice_no} updated successfully.");
    }

    /**
     * Enforce data ownership: regular users can only access their own sales invoices.
     */
    protected function checkSaleOwnership(Sale $sale): void
    {
        if (!Auth::user()->isSuperAdmin() && $sale->user_id && $sale->user_id != Auth::id()) {
            abort(403, 'Unauthorized access: You are only allowed to view and manage your own sales invoices.');
        }
    }
}
