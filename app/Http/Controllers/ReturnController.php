<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ReturnDetail;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $returnType = $request->input('return_type'); // null, 'all', 'bee_comfort', or 'china'
        $query = ReturnRecord::with(['customer', 'sale', 'details', 'user']);
        $countsQuery = ReturnRecord::query();

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

        if ($returnType && in_array($returnType, ['bee_comfort', 'china'])) {
            $query->where('return_type', $returnType);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhereHas('sale', function ($sq) use ($search) {
                      $sq->where('invoice_no', 'like', "%{$search}%");
                  })
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('owner_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('return_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('return_date', '<=', $toDate);
        }

        $returns = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $beeComfortCount = (clone $countsQuery)->where('return_type', 'bee_comfort')->count();
        $chinaCount = (clone $countsQuery)->where('return_type', 'china')->count();
        $allCount = (clone $countsQuery)->count();
        $users = $isSuperAdmin ? User::orderBy('name')->get() : collect();

        return view('returns.index', compact('returns', 'returnType', 'beeComfortCount', 'chinaCount', 'allCount', 'users'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');

        $returnType = $request->input('return_type');
        $saleId = $request->input('sale_id');
        $selectedSale = null;

        if ($saleId) {
            $selectedSale = Sale::with(['customer', 'details.product.category', 'details.product.dozen'])->find($saleId);
            if ($selectedSale) {
                $selectedCustomerId = $selectedSale->customer_id;
                if (!$returnType) {
                    $returnType = $selectedSale->bill_type;
                }
            }
        }

        $nextReturnNo = ReturnRecord::generateNextReturnNo($returnType);
        $today = Carbon::today()->format('Y-m-d');

        // All products from all categories (Bee Comfort and China unified)
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

        return view('returns.create', compact('customers', 'selectedCustomerId', 'nextReturnNo', 'today', 'products', 'returnType', 'selectedSale'));
    }

    public function store(Request $request)
    {
        // Auto-fill customer_id from sale if sale_id is provided but customer_id is not
        if (!$request->filled('customer_id') && $request->filled('sale_id')) {
            $sale = Sale::find($request->input('sale_id'));
            if ($sale) {
                $request->merge(['customer_id' => $sale->customer_id]);
                if (!$request->filled('return_type')) {
                    $request->merge(['return_type' => $sale->bill_type]);
                }
            }
        }

        // If items are submitted with sale_detail_id without product_id, auto-populate product_id & validate eligible qty
        $items = $request->input('items', []);
        if (is_array($items)) {
            $modifiedItems = [];
            foreach ($items as $idx => $it) {
                if (isset($it['sale_detail_id']) && (!isset($it['product_id']) || empty($it['product_id']))) {
                    $sDetail = SaleDetail::find($it['sale_detail_id']);
                    if ($sDetail) {
                        $it['product_id'] = $sDetail->product_id;
                        if (!isset($it['unit_price'])) {
                            $it['unit_price'] = $sDetail->unit_price;
                        }
                        if (isset($it['quantity']) && (int)$it['quantity'] > $sDetail->eligible_return_quantity) {
                            throw ValidationException::withMessages([
                                'items' => ["Return quantity for '{$sDetail->product_title}' exceeds eligible quantity of {$sDetail->eligible_return_quantity}."],
                            ]);
                        }
                    }
                }
                $modifiedItems[$idx] = $it;
            }
            $request->merge(['items' => $modifiedItems]);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'return_no' => 'required|string|unique:returns,return_no',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'return_type' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.sale_detail_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'customer_id.required' => 'Please select a customer for this return.',
            'items.required' => 'At least one product item must be added to the return.',
            'items.min' => 'At least one product item must be added to the return.',
            'items.*.quantity.min' => 'Return quantity must be at least 1.',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $return = DB::transaction(function () use ($validated, $customer, $request) {
            $returnType = !empty($validated['return_type']) ? $validated['return_type'] : 'bee_comfort';

            $return = ReturnRecord::create([
                'user_id' => Auth::id(),
                'return_no' => trim($validated['return_no']),
                'return_type' => $returnType,
                'sale_id' => $request->input('sale_id') ?: null,
                'customer_id' => $customer->id,
                'return_date' => $validated['return_date'],
                'total_amount' => 0,
                'reason' => $validated['reason'] ?? null,
            ]);

            $totalAmount = 0.0;

            foreach ($validated['items'] as $itemData) {
                $product = Product::with(['category', 'dozen'])->lockForUpdate()->findOrFail($itemData['product_id']);
                $returnQty = (int) $itemData['quantity'];
                $unitPrice = isset($itemData['unit_price']) && is_numeric($itemData['unit_price'])
                    ? (float) $itemData['unit_price']
                    : (float) $product->selling_price;
                $lineTotal = (float) ($returnQty * $unitPrice);
                $totalAmount += $lineTotal;

                ReturnDetail::create([
                    'return_id' => $return->id,
                    'sale_detail_id' => $itemData['sale_detail_id'] ?? null,
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'sku_code' => $product->sku_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $returnQty,
                    'total_amount' => $lineTotal,
                ]);

                if (!empty($itemData['sale_detail_id'])) {
                    $sDetail = SaleDetail::find($itemData['sale_detail_id']);
                    if ($sDetail) {
                        $sDetail->increment('returned_quantity', $returnQty);
                    }
                }

                // On successful return submission, automatically restore warehouse stock
                $product->recordStockChange(
                    quantityChange: +$returnQty,
                    type: 'return',
                    referenceId: $return->id,
                    referenceType: ReturnRecord::class,
                    notes: "Customer return #{$return->return_no} for {$customer->name}"
                );
            }

            $return->update(['total_amount' => $totalAmount]);

            return $return;
        });

        $redirectParams = [];
        if (!empty($validated['return_type']) && in_array($validated['return_type'], ['bee_comfort', 'china'])) {
            $redirectParams['return_type'] = $validated['return_type'];
        }

        return redirect()->route('returns.index', $redirectParams)
            ->with('success', "Return record #{$return->return_no} processed successfully. Stock restored & credited to {$customer->name}'s ledger.");
    }

    public function show(ReturnRecord $return)
    {
        $this->checkReturnOwnership($return);
        $return->load(['customer', 'sale', 'details.product.category', 'details.product.dozen', 'user']);
        return view('returns.show', compact('return'));
    }

    public function edit(ReturnRecord $return)
    {
        $this->checkReturnOwnership($return);
        $customers = Customer::orderBy('name')->get();
        $return->load(['customer', 'details.product']);

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

        return view('returns.edit', compact('return', 'customers', 'products'));
    }

    public function update(Request $request, ReturnRecord $return)
    {
        $this->checkReturnOwnership($return);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.sale_detail_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'customer_id.required' => 'Please select a customer for this return.',
            'items.required' => 'At least one return item must be added.',
            'items.min' => 'At least one return item must be added.',
            'items.*.quantity.min' => 'Return quantity must be at least 1.',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        DB::transaction(function () use ($return, $validated, $customer) {
            // Revert previous returned stock changes
            foreach ($return->details as $oldDetail) {
                if ($oldDetail->sale_detail_id) {
                    $sDetail = SaleDetail::find($oldDetail->sale_detail_id);
                    if ($sDetail) {
                        $sDetail->decrement('returned_quantity', min($sDetail->returned_quantity, $oldDetail->quantity));
                    }
                }

                $product = Product::lockForUpdate()->find($oldDetail->product_id);
                if ($product) {
                    $product->recordStockChange(
                        quantityChange: -$oldDetail->quantity,
                        type: 'adjustment',
                        referenceId: $return->id,
                        referenceType: ReturnRecord::class,
                        notes: "Reverting stock for return #{$return->return_no} update"
                    );
                }
            }

            // Remove old details
            $return->details()->delete();

            $totalAmount = 0.0;
            foreach ($validated['items'] as $itemData) {
                $product = Product::with(['category', 'dozen'])->lockForUpdate()->findOrFail($itemData['product_id']);
                $returnQty = (int) $itemData['quantity'];
                $unitPrice = isset($itemData['unit_price']) && is_numeric($itemData['unit_price'])
                    ? (float) $itemData['unit_price']
                    : (float) $product->selling_price;
                $lineTotal = (float) ($returnQty * $unitPrice);
                $totalAmount += $lineTotal;

                ReturnDetail::create([
                    'return_id' => $return->id,
                    'sale_detail_id' => $itemData['sale_detail_id'] ?? null,
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'sku_code' => $product->sku_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $returnQty,
                    'total_amount' => $lineTotal,
                ]);

                if (!empty($itemData['sale_detail_id'])) {
                    $sDetail = SaleDetail::find($itemData['sale_detail_id']);
                    if ($sDetail) {
                        $sDetail->increment('returned_quantity', $returnQty);
                    }
                }

                // Restore warehouse stock
                $product->recordStockChange(
                    quantityChange: +$returnQty,
                    type: 'return',
                    referenceId: $return->id,
                    referenceType: ReturnRecord::class,
                    notes: "Updated customer return #{$return->return_no} for {$customer->name}"
                );
            }

            $return->update([
                'customer_id' => $customer->id,
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'] ?? null,
                'total_amount' => $totalAmount,
            ]);
        });

        $redirectParams = [];
        if (!empty($return->return_type) && in_array($return->return_type, ['bee_comfort', 'china'])) {
            $redirectParams['return_type'] = $return->return_type;
        }

        return redirect()->route('returns.index', $redirectParams)
            ->with('success', "Return record #{$return->return_no} updated successfully.");
    }

    /**
     * Enforce data ownership: regular users can only access their own return records.
     */
    protected function checkReturnOwnership(ReturnRecord $return): void
    {
        if (!Auth::user()->isSuperAdmin() && $return->user_id && $return->user_id != Auth::id()) {
            abort(403, 'Unauthorized access: You are only allowed to view and manage your own bill return records.');
        }
    }
}
