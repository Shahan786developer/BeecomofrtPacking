<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Sku;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Preview next unique 7-digit SKU
     */
    public function generateSku()
    {
        $code = Sku::generateNextUniqueCode();
        return response()->json([
            'sku' => $code,
            'length' => strlen($code),
        ]);
    }

    /**
     * Calculate selling price dynamically
     * Formula: Selling Price = CEILING((Actual Price * (1 + Percentage / 100)) / 10) * 10
     */
    public function calculateSellingPrice(Request $request)
    {
        $actualPrice = (float) $request->input('actual_price', 0);
        $pricingRuleId = $request->input('pricing_rule_id');

        if (!$pricingRuleId) {
            return response()->json([
                'selling_price' => 0.0,
                'percentage' => 0,
            ]);
        }

        $rule = PricingRule::find($pricingRuleId);
        if (!$rule) {
            return response()->json([
                'selling_price' => 0.0,
                'percentage' => 0,
            ]);
        }

        $sellingPrice = $rule->calculateSellingPrice($actualPrice);

        return response()->json([
            'actual_price' => $actualPrice,
            'percentage' => (float) $rule->percentage,
            'rule_name' => $rule->name,
            'selling_price' => $sellingPrice,
            'formula' => "CEIL(({$actualPrice} * (1 + {$rule->percentage}/100)) / 10) * 10 = {$sellingPrice}",
        ]);
    }

    /**
     * Scan / search product by 7-digit SKU
     */
    public function getProductBySku(Request $request, string $sku)
    {
        $product = Product::with(['category', 'dozen', 'sku'])
            ->where('sku_code', trim($sku))
            ->first();

        if (!$product) {
            return response()->json([
                'found' => false,
                'message' => "No product found with SKU '{$sku}'.",
            ], 404);
        }

        $parentCat = $product->parent_category ?? 'Bee Comfort';
        $normalizedParent = strtolower(str_replace(' ', '', $parentCat)) === 'china' ? 'china' : 'bee_comfort';

        $expectedBillType = $request->input('bill_type');
        if ($expectedBillType && in_array($expectedBillType, ['bee_comfort', 'china'])) {
            if ($expectedBillType !== $normalizedParent) {
                return response()->json([
                    'found' => true,
                    'mismatch' => true,
                    'product_bill_type' => $normalizedParent,
                    'parent_category' => $parentCat,
                    'title' => $product->title,
                    'message' => "Product '{$product->title}' belongs to {$parentCat} catalog and cannot be added to a " . ($expectedBillType === 'china' ? 'China' : 'Bee Comfort') . " bill.",
                ]);
            }
        }

        return response()->json([
            'found' => true,
            'mismatch' => false,
            'id' => $product->id,
            'sku_code' => $product->sku_code,
            'product_code' => $product->product_code,
            'category_name' => $product->category->name,
            'dozen_label' => $product->dozen->label,
            'parent_category' => $parentCat,
            'title' => $product->title, // Dynamic combined title e.g. "Comfort Fit (12 pieces) AA-01"
            'selling_price' => (float) $product->selling_price,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }

    /**
     * Fetch items for an invoice to populate Return form
     */
    public function getSaleItems(int $saleId)
    {
        $sale = Sale::with(['customer', 'details' => function ($q) {
            $q->with(['product.category', 'product.dozen']);
        }])->find($saleId);

        if (!$sale) {
            return response()->json(['found' => false, 'message' => 'Sale invoice not found.'], 404);
        }

        $items = $sale->details->map(function ($detail) {
            return [
                'sale_detail_id' => $detail->id,
                'product_id' => $detail->product_id,
                'sku_code' => $detail->sku_code,
                'product_title' => $detail->product_title,
                'unit_price' => (float) $detail->unit_price,
                'quantity_sold' => (int) $detail->quantity,
                'returned_quantity' => (int) $detail->returned_quantity,
                'eligible_quantity' => $detail->eligible_return_quantity,
            ];
        });

        return response()->json([
            'found' => true,
            'sale_id' => $sale->id,
            'invoice_no' => $sale->invoice_no,
            'customer_name' => $sale->customer->name,
            'invoice_date' => $sale->invoice_date->format('Y-m-d'),
            'items' => $items,
        ]);
    }
}
