<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalStockQuantity = (int) Product::sum('stock_quantity');
        $zeroStockCount = Product::where('stock_quantity', 0)->count();
        $negativeStockCount = Product::where('stock_quantity', '<', 0)->count();

        $totalCustomers = Customer::count();
        $totalSalesCount = Sale::count();
        $totalReturnsCount = ReturnRecord::count();
        $totalSalesAmount = (float) Sale::sum('total_amount');
        $totalReturnAmount = (float) ReturnRecord::sum('total_amount');

        // Requirement: "If a product remains at zero or negative stock for one month, display a dashboard notification. Do not delete the product after one month."
        $oneMonthDepletedProducts = Product::with(['category', 'dozen', 'sku'])
            ->where('stock_quantity', '<=', 0)
            ->whereNotNull('zero_stock_date')
            ->where('zero_stock_date', '<=', Carbon::now()->subMonth())
            ->orderBy('zero_stock_date', 'asc')
            ->get();

        // All zero and negative products list for quick view
        $allDepletedProducts = Product::with(['category', 'dozen', 'sku'])
            ->where('stock_quantity', '<=', 0)
            ->orderBy('stock_quantity', 'asc')
            ->take(10)
            ->get();

        // Recent sales and returns
        $recentSales = Sale::with(['customer', 'details'])
            ->latest()
            ->take(5)
            ->get();

        $recentReturns = ReturnRecord::with(['customer', 'sale', 'details'])
            ->latest()
            ->take(5)
            ->get();

        // Chart Data: Last 7 days Sales vs Returns
        $chartDates = [];
        $salesTrend = [];
        $returnsTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dateLabel = $date->format('M d');
            $chartDates[] = $dateLabel;

            $daySales = (float) Sale::whereDate('invoice_date', $dateStr)->sum('total_amount');
            $dayReturns = (float) ReturnRecord::whereDate('return_date', $dateStr)->sum('total_amount');

            $salesTrend[] = $daySales;
            $returnsTrend[] = $dayReturns;
        }

        // Category Stock Distribution for Doughnut chart
        $categoriesStock = Category::withSum('products as total_stock', 'stock_quantity')
            ->get()
            ->map(function ($cat) {
                return [
                    'name' => $cat->name,
                    'stock' => max(0, (int) ($cat->total_stock ?? 0)),
                ];
            });

        // 1. Top 10 Selling Products (Bar Chart Data)
        $topProducts = \App\Models\SaleDetail::select(
            'product_title',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
        ->groupBy('product_title')
        ->orderByDesc('total_qty')
        ->take(10)
        ->get();

        $topProductsLabels = $topProducts->pluck('product_title')->toArray();
        $topProductsQtys = $topProducts->pluck('total_qty')->map(fn($q) => (int)$q)->toArray();
        $topProductsRevenues = $topProducts->pluck('total_revenue')->map(fn($r) => (float)$r)->toArray();

        // 2. Top Customers (Purchases Breakdown)
        $topCustomers = Customer::withSum('sales as total_purchased', 'total_amount')
            ->withSum('cashReceipts as total_paid', 'amount')
            ->withCount('sales')
            ->orderByDesc('total_purchased')
            ->take(10)
            ->get();

        $topCustomersLabels = $topCustomers->pluck('name')->toArray();
        $topCustomersPurchases = $topCustomers->pluck('total_purchased')->map(fn($p) => (float)($p ?? 0))->toArray();

        // 3. Customer Receivables & Aging Breakdown
        $allCustomers = Customer::with(['sales', 'cashReceipts', 'returns'])->get();
        $pendingCustomers = $allCustomers->filter(fn($c) => $c->balance_due > 0)->sortByDesc('balance_due')->values();
        $totalReceivables = (float) $pendingCustomers->sum('balance_due');
        $totalCashReceived = (float) \App\Models\CashReceipt::sum('amount');

        return view('dashboard.index', compact(
            'totalProducts',
            'totalStockQuantity',
            'zeroStockCount',
            'negativeStockCount',
            'totalCustomers',
            'totalSalesCount',
            'totalReturnsCount',
            'totalSalesAmount',
            'totalReturnAmount',
            'totalReceivables',
            'totalCashReceived',
            'oneMonthDepletedProducts',
            'allDepletedProducts',
            'recentSales',
            'recentReturns',
            'chartDates',
            'salesTrend',
            'returnsTrend',
            'categoriesStock',
            'topProducts',
            'topProductsLabels',
            'topProductsQtys',
            'topProductsRevenues',
            'topCustomers',
            'topCustomersLabels',
            'topCustomersPurchases',
            'pendingCustomers'
        ));
    }
}
