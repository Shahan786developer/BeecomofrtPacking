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

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $defaultCategory = Category::where('is_default', true)->first();
        $dozens = Dozen::orderBy('quantity')->get();
        $defaultDozen = Dozen::where('is_default', true)->first() ?? Dozen::where('quantity', 12)->first();

        // All pricing rules (Percentage & Category-based)
        $pricingRules = PricingRule::with('category')->orderBy('name')->get();
        $defaultPricingRule = PricingRule::where('is_default', true)->first() ?? $pricingRules->first();

        // Generate next preview 7-digit SKU
        $previewSku = Sku::generateNextUniqueCode();

        return view('products.create', compact(
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
        $validated = $request->validate([
            'bill_no' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'dozen_id' => 'required|exists:dozens,id',
            'product_code' => 'required|string|max:50',
            'actual_price' => 'required|numeric|min:0',
            'pricing_rule_id' => 'required|exists:pricing_rules,id',
            'quantity' => 'required|integer',
        ], [
            'bill_no.required' => 'Bill Number is required.',
            'category_id.required' => 'Please select a product category.',
            'dozen_id.required' => 'Please select a dozen unit.',
            'product_code.required' => 'Product Code is required (e.g. AA-01).',
            'actual_price.required' => 'Actual Price is required.',
            'pricing_rule_id.required' => 'Please select a pricing rule.',
            'quantity.required' => 'Initial stock quantity is required.',
        ]);

        $product = DB::transaction(function () use ($validated) {
            // Allocate atomic, unique 7-digit SKU
            $sku = Sku::allocate();

            // Fetch pricing rule percentage and calculate selling price:
            // Formula: Selling Price = CEILING((Actual Price * (1 + Percentage / 100)) / 10) * 10
            $pricingRule = PricingRule::findOrFail($validated['pricing_rule_id']);
            $sellingPrice = $pricingRule->calculateSellingPrice((float) $validated['actual_price']);

            $initialQty = (int) $validated['quantity'];
            $zeroDate = null;
            if ($initialQty <= 0) {
                $zeroDate = Carbon::now();
            }

            $product = Product::create([
                'bill_no' => trim($validated['bill_no']),
                'category_id' => $validated['category_id'],
                'dozen_id' => $validated['dozen_id'],
                'product_code' => strtoupper(trim($validated['product_code'])),
                'sku_id' => $sku->id,
                'sku_code' => $sku->sku_code,
                'actual_price' => (float) $validated['actual_price'],
                'pricing_rule_id' => $pricingRule->id,
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
                'notes' => 'Registered with initial stock from Bill #' . $product->bill_no,
            ]);

            return $product;
        });

        return redirect()->route('products.index')
            ->with('success', "Product '{$product->title}' (SKU: {$product->sku_code}) registered successfully with selling price $" . number_format($product->selling_price, 2) . ".");
    }

    public function show(Product $product)
    {
        $product->load(['category', 'dozen', 'pricingRule', 'sku', 'stockMovements' => function ($q) {
            $q->latest()->take(25);
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $dozens = Dozen::orderBy('quantity')->get();
        $pricingRules = PricingRule::with('category')->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'dozens', 'pricingRules'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'bill_no' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'dozen_id' => 'required|exists:dozens,id',
            'product_code' => 'required|string|max:50',
            'actual_price' => 'required|numeric|min:0',
            'pricing_rule_id' => 'required|exists:pricing_rules,id',
        ]);

        $pricingRule = PricingRule::findOrFail($validated['pricing_rule_id']);
        $sellingPrice = $pricingRule->calculateSellingPrice((float) $validated['actual_price']);

        $product->update([
            'bill_no' => trim($validated['bill_no']),
            'category_id' => $validated['category_id'],
            'dozen_id' => $validated['dozen_id'],
            'product_code' => strtoupper(trim($validated['product_code'])),
            'actual_price' => (float) $validated['actual_price'],
            'pricing_rule_id' => $pricingRule->id,
            'selling_price' => $sellingPrice,
        ]);

        return redirect()->route('products.index')
            ->with('success', "Product '{$product->title}' updated successfully.");
    }

    public function movements(Product $product)
    {
        $movements = $product->stockMovements()->latest()->paginate(20);
        return view('products.movements', compact('product', 'movements'));
    }
}
