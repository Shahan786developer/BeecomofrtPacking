<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dozen;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Sku;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'dozen', 'pricingRule', 'sku']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('sku_code', 'like', "%{$search}%")
                  ->orWhere('bill_no', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Parent Category filter
        if ($parentCategory = $request->input('parent_category')) {
            $query->where('parent_category', $parentCategory);
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Stock status filter
        $stockStatus = $request->input('stock_status');
        if ($stockStatus === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        } elseif ($stockStatus === 'zero') {
            $query->where('stock_quantity', 0);
        } elseif ($stockStatus === 'negative') {
            $query->where('stock_quantity', '<', 0);
        } elseif ($stockStatus === 'depleted_month') {
            $query->where('stock_quantity', '<=', 0)
                  ->whereNotNull('zero_stock_date')
                  ->where('zero_stock_date', '<=', Carbon::now()->subMonth());
        }

        $products = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(Request $request)
    {
        $rawType = $request->query('type', $request->query('parent_category', 'bee_comfort'));
        $activeType = (strtolower(str_replace([' ', '_'], '', $rawType)) === 'china') ? 'China' : 'Bee Comfort';

        $parentCategories = ['Bee Comfort', 'China'];
        $categories = Category::orderBy('name')->get();
        $defaultCategory = Category::where('is_default', true)->first();
        $dozens = Dozen::orderBy('quantity')->get();
        $defaultDozen = Dozen::where('is_default', true)->first() ?? Dozen::where('quantity', 12)->first();

        // All pricing rules (Percentage & Category-based)
        $pricingRules = PricingRule::with('category')->orderBy('name')->get();
        $defaultPricingRule = PricingRule::where('is_default', true)->first() ?? $pricingRules->first();

        // Generate next preview 7-digit SKU for China form
        $previewSku = Sku::generateNextUniqueCode();

        return view('products.create', compact(
            'activeType',
            'parentCategories',
            'categories',
            'defaultCategory',
            'dozens',
            'defaultDozen',
            'pricingRules',
            'defaultPricingRule',
            'previewSku'
        ));
    }

    public function store(Request $request)
    {
        $parentCat = $request->input('parent_category', 'Bee Comfort');
        $isChina = ($parentCat === 'China');

        $rules = [
            'bill_no' => 'nullable|string|max:100',
            'parent_category' => 'required|string|in:Bee Comfort,China',
            'category_id' => 'required|exists:categories,id',
            'dozen_id' => 'required|exists:dozens,id',
            'product_code' => 'required|string|max:50',
            'sku_code' => 'nullable|string|max:50|unique:products,sku_code',
            'quantity' => 'required|integer',
        ];

        $messages = [
            'parent_category.required' => 'Please select a parent category (Bee Comfort or China).',
            'category_id.required' => 'Please select a product category.',
            'dozen_id.required' => 'Please select a dozen unit.',
            'product_code.required' => 'Product Code is required (e.g. AA-01).',
            'sku_code.unique' => 'This SKU Code is already assigned to another product.',
            'quantity.required' => 'Initial stock quantity is required.',
        ];

        if ($isChina) {
            $rules['actual_price'] = 'required|numeric|min:0';
            $rules['pricing_rule_id'] = 'required|exists:pricing_rules,id';
            $rules['selling_price'] = 'nullable|numeric|min:0';
            $messages['actual_price.required'] = 'Actual Purchase Price is required for China products.';
            $messages['pricing_rule_id.required'] = 'Please select a pricing rule for China products.';
        } else {
            $rules['actual_price'] = 'nullable|numeric|min:0';
            $rules['pricing_rule_id'] = 'nullable|exists:pricing_rules,id';
            $rules['selling_price'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules, $messages);

        $product = DB::transaction(function () use ($validated, $request, $isChina) {
            // SKU handling: Allocate unique 7-digit SKU or use custom entered SKU
            if (!empty($validated['sku_code'])) {
                $customSku = trim($validated['sku_code']);
                $sku = Sku::firstOrCreate(['sku_code' => $customSku]);
            } else {
                $sku = Sku::allocate();
            }

            // Selling price and pricing rule handling
            $actualPrice = null;
            $pricingRuleId = null;
            $sellingPrice = null;

            if ($isChina) {
                $pricingRule = PricingRule::findOrFail($validated['pricing_rule_id']);
                $actualPrice = (float) $validated['actual_price'];
                $pricingRuleId = $pricingRule->id;

                if ($request->filled('selling_price') && is_numeric($request->input('selling_price'))) {
                    $sellingPrice = (float) $request->input('selling_price');
                } else {
                    $sellingPrice = $pricingRule->calculateSellingPrice($actualPrice);
                }
            } elseif ($request->filled('actual_price') || $request->filled('selling_price')) {
                $actualPrice = $request->filled('actual_price') ? (float) $request->input('actual_price') : null;
                $pricingRuleId = !empty($validated['pricing_rule_id']) ? $validated['pricing_rule_id'] : null;
                if ($request->filled('selling_price') && is_numeric($request->input('selling_price'))) {
                    $sellingPrice = (float) $request->input('selling_price');
                } elseif ($pricingRuleId && $actualPrice) {
                    $rule = PricingRule::find($pricingRuleId);
                    $sellingPrice = $rule ? $rule->calculateSellingPrice($actualPrice) : null;
                }
            }

            $initialQty = (int) $validated['quantity'];
            $zeroDate = null;
            if ($initialQty <= 0) {
                $zeroDate = Carbon::now();
            }

            $product = Product::create([
                'bill_no' => !empty($validated['bill_no']) ? trim($validated['bill_no']) : null,
                'parent_category' => $validated['parent_category'],
                'category_id' => $validated['category_id'],
                'dozen_id' => $validated['dozen_id'],
                'product_code' => strtoupper(trim($validated['product_code'])),
                'sku_id' => $sku->id,
                'sku_code' => $sku->sku_code,
                'actual_price' => $actualPrice,
                'pricing_rule_id' => $pricingRuleId,
                'selling_price' => $sellingPrice,
                'stock_quantity' => $initialQty,
                'zero_stock_date' => $zeroDate,
            ]);

            // Record initial stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'initial',
                'quantity_change' => $initialQty,
                'previous_stock' => 0,
                'new_stock' => $initialQty,
                'reference_id' => $product->id,
                'reference_type' => Product::class,
                'notes' => 'Registered with initial stock' . ($product->bill_no ? ' from Bill #' . $product->bill_no : ''),
            ]);

            return $product;
        });

        $successMsg = "Product '{$product->title}' (SKU: {$product->sku_code}) registered successfully.";
        if ($isChina && $product->selling_price) {
            $successMsg .= " Selling price: Rs. " . number_format($product->selling_price, 2) . ".";
        }

        return redirect()->route('products.index')
            ->with('success', $successMsg);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'dozen', 'pricingRule', 'sku', 'stockMovements' => function ($q) {
            $q->with('customer')->latest()->take(25);
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $parentCategories = ['Bee Comfort', 'China'];
        $categories = Category::orderBy('name')->get();
        $dozens = Dozen::orderBy('quantity')->get();
        $pricingRules = PricingRule::with('category')->orderBy('name')->get();

        return view('products.edit', compact('product', 'parentCategories', 'categories', 'dozens', 'pricingRules'));
    }

    public function update(Request $request, Product $product)
    {
        $parentCat = $request->input('parent_category', $product->parent_category);
        $isChina = ($parentCat === 'China');

        $rules = [
            'bill_no' => 'nullable|string|max:100',
            'parent_category' => 'required|string|in:Bee Comfort,China',
            'category_id' => 'required|exists:categories,id',
            'dozen_id' => 'required|exists:dozens,id',
            'product_code' => 'required|string|max:50',
            'sku_code' => 'required|string|max:50|unique:products,sku_code,' . $product->id,
            'stock_quantity' => 'nullable|integer',
        ];

        $messages = [
            'parent_category.required' => 'Please select a parent category.',
            'category_id.required' => 'Please select a category.',
            'dozen_id.required' => 'Please select a dozen unit.',
            'product_code.required' => 'Product Code is required.',
            'sku_code.required' => 'SKU Code is required.',
        ];

        if ($isChina) {
            $rules['actual_price'] = 'required|numeric|min:0';
            $rules['pricing_rule_id'] = 'required|exists:pricing_rules,id';
            $rules['selling_price'] = 'nullable|numeric|min:0';
            $messages['actual_price.required'] = 'Actual Purchase Price is required for China products.';
            $messages['pricing_rule_id.required'] = 'Please select a pricing rule for China products.';
        } else {
            $rules['actual_price'] = 'nullable|numeric|min:0';
            $rules['pricing_rule_id'] = 'nullable|exists:pricing_rules,id';
            $rules['selling_price'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($validated, $request, $product, $isChina) {
            $actualPrice = null;
            $pricingRuleId = null;
            $sellingPrice = null;

            if ($isChina) {
                $pricingRule = PricingRule::findOrFail($validated['pricing_rule_id']);
                $actualPrice = (float) $validated['actual_price'];
                $pricingRuleId = $pricingRule->id;

                if ($request->filled('selling_price') && is_numeric($request->input('selling_price'))) {
                    $sellingPrice = (float) $request->input('selling_price');
                } else {
                    $sellingPrice = $pricingRule->calculateSellingPrice($actualPrice);
                }
            } elseif ($request->filled('actual_price') || $request->filled('selling_price')) {
                $actualPrice = $request->filled('actual_price') ? (float) $request->input('actual_price') : null;
                $pricingRuleId = !empty($validated['pricing_rule_id']) ? $validated['pricing_rule_id'] : null;
                if ($request->filled('selling_price') && is_numeric($request->input('selling_price'))) {
                    $sellingPrice = (float) $request->input('selling_price');
                } elseif ($pricingRuleId && $actualPrice) {
                    $rule = PricingRule::find($pricingRuleId);
                    $sellingPrice = $rule ? $rule->calculateSellingPrice($actualPrice) : null;
                }
            }

            $newSku = trim($validated['sku_code']);
            $skuId = $product->sku_id;
            if ($newSku !== $product->sku_code) {
                $sku = Sku::firstOrCreate(['sku_code' => $newSku]);
                $skuId = $sku->id;
            }

            $oldStock = (int) $product->stock_quantity;
            $newStock = array_key_exists('stock_quantity', $validated) && !is_null($validated['stock_quantity'])
                ? (int) $validated['stock_quantity']
                : $oldStock;
            $stockDiff = $newStock - $oldStock;

            // Zero stock tracking
            $zeroDate = $product->zero_stock_date;
            if ($newStock <= 0) {
                if (is_null($zeroDate)) {
                    $zeroDate = Carbon::now();
                }
            } else {
                $zeroDate = null;
            }

            $product->update([
                'bill_no' => !empty($validated['bill_no']) ? trim($validated['bill_no']) : null,
                'parent_category' => $validated['parent_category'],
                'category_id' => $validated['category_id'],
                'dozen_id' => $validated['dozen_id'],
                'product_code' => strtoupper(trim($validated['product_code'])),
                'sku_id' => $skuId,
                'sku_code' => $newSku,
                'actual_price' => $actualPrice,
                'pricing_rule_id' => $pricingRuleId,
                'selling_price' => $sellingPrice,
                'stock_quantity' => $newStock,
                'zero_stock_date' => $zeroDate,
            ]);

            // If stock quantity was changed, log an adjustment movement
            if ($stockDiff !== 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity_change' => $stockDiff,
                    'previous_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'reference_id' => $product->id,
                    'reference_type' => Product::class,
                    'notes' => "Manual stock edit from {$oldStock} to {$newStock} units",
                ]);
            }
        });

        return redirect()->route('products.index')
            ->with('success', "Product '{$product->title}' updated successfully.");
    }

    public function movements(Product $product)
    {
        $movements = $product->stockMovements()->with('customer')->latest()->paginate(20);
        return view('products.movements', compact('product', 'movements'));
    }
}
