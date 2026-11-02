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

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $returnType = $request->input('return_type'); // null, 'bee_comfort', or 'china'
        $query = ReturnRecord::with(['customer', 'sale', 'details']);

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

        $beeComfortCount = ReturnRecord::where('return_type', 'bee_comfort')->count();
        $chinaCount = ReturnRecord::where('return_type', 'china')->count();
        $allCount = ReturnRecord::count();

        return view('returns.index', compact('returns', 'returnType', 'beeComfortCount', 'chinaCount', 'allCount'));
    }

    public function create(Request $request)
    {
        $returnType = $request->input('return_type', 'bee_comfort');
        if (!in_array($returnType, ['bee_comfort', 'china'])) {
            $returnType = 'bee_comfort';
        }

        $nextReturnNo = ReturnRecord::generateNextReturnNo($returnType);
        $today = Carbon::today()->format('Y-m-d');

        // Only sales matching the return type that have at least one returnable item
        $sales = Sale::with(['customer', 'details' => function ($q) {
            $q->whereColumn('quantity', '>', 'returned_quantity');
        }])
        ->where('bill_type', $returnType)
        ->whereHas('details', function ($q) {
            $q->whereColumn('quantity', '>', 'returned_quantity');
        })
        ->orderBy('id', 'desc')
        ->get();

        $selectedSaleId = $request->input('sale_id');

        return view('returns.create', compact('nextReturnNo', 'today', 'sales', 'selectedSaleId', 'returnType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_type' => 'required|in:bee_comfort,china',
            'sale_id' => 'required|exists:sales,id',
            'return_no' => 'required|string|unique:returns,return_no',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sale_detail_id' => 'required|exists:sale_details,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'sale_id.required' => 'Please select the original sales invoice.',
            'items.required' => 'At least one return item must be added.',
            'items.min' => 'At least one return item must be added.',
            'items.*.quantity.min' => 'Return quantity must be at least 1.',
        ]);

        $return = DB::transaction(function () use ($validated) {
            $sale = Sale::with('customer')->findOrFail($validated['sale_id']);

            $return = ReturnRecord::create([
                'return_no' => trim($validated['return_no']),
                'return_type' => $validated['return_type'],
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'return_date' => $validated['return_date'],
                'total_amount' => 0,
                'reason' => $validated['reason'] ?? null,
            ]);

            $totalAmount = 0.0;

            foreach ($validated['items'] as $itemData) {
                $saleDetail = SaleDetail::lockForUpdate()->findOrFail($itemData['sale_detail_id']);

                if ($saleDetail->sale_id !== $sale->id) {
                    throw ValidationException::withMessages([
                        'items' => "Item '{$saleDetail->product_title}' does not belong to Invoice #{$sale->invoice_no}.",
                    ]);
                }

                $returnQty = (int) $itemData['quantity'];
                $eligibleQty = $saleDetail->eligible_return_quantity;

                // Validation: Do not allow returning more than the eligible quantity sold
                if ($returnQty > $eligibleQty) {
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$returnQty} units of '{$saleDetail->product_title}'. Maximum returnable quantity is {$eligibleQty}.",
                    ]);
                }

                $unitPrice = (float) $saleDetail->unit_price;
                $lineTotal = (float) ($returnQty * $unitPrice);
                $totalAmount += $lineTotal;

                // Save complete return details
                ReturnDetail::create([
                    'return_id' => $return->id,
                    'sale_detail_id' => $saleDetail->id,
                    'product_id' => $saleDetail->product_id,
                    'product_title' => $saleDetail->product_title,
                    'sku_code' => $saleDetail->sku_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $returnQty,
                    'total_amount' => $lineTotal,
                ]);

                // Update returned quantity on sale detail
                $saleDetail->increment('returned_quantity', $returnQty);

                // On successful return submission, add the returned quantity to the same product's stock
                $product = Product::lockForUpdate()->findOrFail($saleDetail->product_id);
                $product->recordStockChange(
                    quantityChange: +$returnQty,
                    type: 'return',
                    referenceId: $return->id,
                    referenceType: ReturnRecord::class,
                    notes: "Customer return #{$return->return_no} from Invoice #{$sale->invoice_no}"
                );
            }

            $return->update(['total_amount' => $totalAmount]);

            return $return;
        });

        return redirect()->route('returns.index', ['return_type' => $return->return_type])
            ->with('success', "Return record #{$return->return_no} processed successfully. Stock restored.");
    }

    public function show(ReturnRecord $return)
    {
        $return->load(['customer', 'sale', 'details.product.category', 'details.product.dozen']);
        return view('returns.show', compact('return'));
    }

    public function edit(ReturnRecord $return)
    {
        $return->load(['customer', 'sale.details', 'details']);
        return view('returns.edit', compact('return'));
    }

    public function update(Request $request, ReturnRecord $return)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sale_detail_id' => 'required|exists:sale_details,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'items.required' => 'At least one return item must be added.',
            'items.min' => 'At least one return item must be added.',
            'items.*.quantity.min' => 'Return quantity must be at least 1.',
        ]);

        DB::transaction(function () use ($return, $validated) {
            // Revert previously returned quantities on sale_details and product stock
            foreach ($return->details as $oldDetail) {
                $saleDetail = SaleDetail::lockForUpdate()->find($oldDetail->sale_detail_id);
                if ($saleDetail) {
                    $saleDetail->decrement('returned_quantity', $oldDetail->quantity);
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
                $saleDetail = SaleDetail::lockForUpdate()->findOrFail($itemData['sale_detail_id']);
                $returnQty = (int) $itemData['quantity'];
                $eligibleQty = $saleDetail->eligible_return_quantity;

                if ($returnQty > $eligibleQty) {
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$returnQty} units of '{$saleDetail->product_title}'. Maximum returnable quantity is {$eligibleQty}.",
                    ]);
                }

                $unitPrice = (float) $saleDetail->unit_price;
                $lineTotal = (float) ($returnQty * $unitPrice);
                $totalAmount += $lineTotal;

                ReturnDetail::create([
                    'return_id' => $return->id,
                    'sale_detail_id' => $saleDetail->id,
                    'product_id' => $saleDetail->product_id,
                    'product_title' => $saleDetail->product_title,
                    'sku_code' => $saleDetail->sku_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $returnQty,
                    'total_amount' => $lineTotal,
                ]);

                $saleDetail->increment('returned_quantity', $returnQty);

                $product = Product::lockForUpdate()->findOrFail($saleDetail->product_id);
                $product->recordStockChange(
                    quantityChange: +$returnQty,
                    type: 'return',
                    referenceId: $return->id,
                    referenceType: ReturnRecord::class,
                    notes: "Updated customer return #{$return->return_no}"
                );
            }

            $return->update([
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'] ?? null,
                'total_amount' => $totalAmount,
            ]);
        });

        return redirect()->route('returns.index', ['return_type' => $return->return_type])
            ->with('success', "Return record #{$return->return_no} updated successfully.");
    }
}
