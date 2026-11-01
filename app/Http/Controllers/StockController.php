<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'dozen', 'sku']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('sku_code', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($category = $request->input('category_id')) {
            $query->where('category_id', $category);
        }

        $filter = $request->input('status');
        if ($filter === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        } elseif ($filter === 'zero') {
            $query->where('stock_quantity', 0);
        } elseif ($filter === 'negative') {
            $query->where('stock_quantity', '<', 0);
        } elseif ($filter === 'depleted_month') {
            $query->where('stock_quantity', '<=', 0)
                  ->whereNotNull('zero_stock_date')
                  ->where('zero_stock_date', '<=', Carbon::now()->subMonth());
        }

        $products = $query->orderBy('stock_quantity', 'asc')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $stats = [
            'total_products' => Product::count(),
            'total_stock' => (int) Product::sum('stock_quantity'),
            'zero_stock' => Product::where('stock_quantity', 0)->count(),
            'negative_stock' => Product::where('stock_quantity', '<', 0)->count(),
            'depleted_month' => Product::where('stock_quantity', '<=', 0)
                ->whereNotNull('zero_stock_date')
                ->where('zero_stock_date', '<=', Carbon::now()->subMonth())
                ->count(),
        ];

        return view('stock.index', compact('products', 'categories', 'stats'));
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product.category', 'product.dozen']);

        if ($search = $request->input('search')) {
            $query->whereHas('product', function ($pq) use ($search) {
                $pq->where('product_code', 'like', "%{$search}%")
                   ->orWhere('sku_code', 'like', "%{$search}%")
                   ->orWhereHas('category', function ($cq) use ($search) {
                       $cq->where('name', 'like', "%{$search}%");
                   });
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $movements = $query->latest()->paginate(25)->withQueryString();

        return view('stock.movements', compact('movements'));
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer',
            'notes' => 'required|string|max:255',
        ]);

        $qty = (int) $validated['quantity'];
        $change = 0;

        if ($validated['adjustment_type'] === 'add') {
            $change = abs($qty);
        } elseif ($validated['adjustment_type'] === 'subtract') {
            $change = -abs($qty);
        } elseif ($validated['adjustment_type'] === 'set') {
            $change = $qty - $product->stock_quantity;
        }

        $product->recordStockChange(
            quantityChange: $change,
            type: 'adjustment',
            notes: "Manual adjustment: " . $validated['notes']
        );

        return back()->with('success', "Stock for '{$product->title}' updated. New stock: {$product->stock_quantity}.");
    }
}
