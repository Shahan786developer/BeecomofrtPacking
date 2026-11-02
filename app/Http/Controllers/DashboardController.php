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

        // 4. Gross Profit & Actual Price vs Selling Price Analysis
        // Profit = Sold Quantity * (Selling Price - Actual Price) net of returns
        $saleDetailsWithCost = \App\Models\SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
            ->select(
                'sale_details.quantity',
                'sale_details.unit_price',
                'sale_details.total_amount',
                'products.actual_price',
                'products.parent_category'
            )
            ->get();

        $grossSalesRevenue = 0.0;
        $grossSalesCost = 0.0;

        $bcRevenue = 0.0;
        $bcCost = 0.0;
        $bcQty = 0;

        $chRevenue = 0.0;
        $chCost = 0.0;
        $chQty = 0;

        foreach ($saleDetailsWithCost as $sd) {
            $rev = (float) $sd->total_amount;
            $cost = (float) ($sd->quantity * $sd->actual_price);
            $qty = (int) $sd->quantity;

            $grossSalesRevenue += $rev;
            $grossSalesCost += $cost;

            $isChina = strtolower(str_replace(' ', '', $sd->parent_category ?? '')) === 'china';
            if ($isChina) {
                $chRevenue += $rev;
                $chCost += $cost;
                $chQty += $qty;
            } else {
                $bcRevenue += $rev;
                $bcCost += $cost;
                $bcQty += $qty;
            }
        }

        // Factor in returns to ensure accurate net profit
        $returnDetailsWithCost = \App\Models\ReturnDetail::join('products', 'return_details.product_id', '=', 'products.id')
            ->select(
                'return_details.quantity',
                'return_details.unit_price',
                'return_details.total_amount',
                'products.actual_price',
                'products.parent_category'
            )
            ->get();

        $refundRevenue = 0.0;
        $refundCost = 0.0;

        foreach ($returnDetailsWithCost as $rd) {
            $ref = (float) $rd->total_amount;
            $c = (float) ($rd->quantity * $rd->actual_price);
            $refundRevenue += $ref;
            $refundCost += $c;

            $isChina = strtolower(str_replace(' ', '', $rd->parent_category ?? '')) === 'china';
            if ($isChina) {
                $chRevenue -= $ref;
                $chCost -= $c;
                $chQty -= (int) $rd->quantity;
            } else {
                $bcRevenue -= $ref;
                $bcCost -= $c;
                $bcQty -= (int) $rd->quantity;
            }
        }

        $totalRealizedRevenue = max(0, $grossSalesRevenue - $refundRevenue);
        $totalCostOfGoodsSold = max(0, $grossSalesCost - $refundCost);
        $totalGrossProfit = max(0, $totalRealizedRevenue - $totalCostOfGoodsSold);
        $profitMarginPercent = $totalRealizedRevenue > 0 ? (($totalGrossProfit / $totalRealizedRevenue) * 100) : 0;

        $bcRevenue = max(0, $bcRevenue);
        $bcCost = max(0, $bcCost);
        $bcProfit = max(0, $bcRevenue - $bcCost);
        $bcQty = max(0, $bcQty);

        $chRevenue = max(0, $chRevenue);
        $chCost = max(0, $chCost);
        $chProfit = max(0, $chRevenue - $chCost);
        $chQty = max(0, $chQty);

        // Product-wise comparison: Top 5 Bee Comfort vs Top 5 China
        $topBeeComfortProducts = \App\Models\SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
            ->where(function ($q) {
                $q->where('products.parent_category', 'Bee Comfort')
                  ->orWhere('products.parent_category', 'bee_comfort')
                  ->orWhereNull('products.parent_category');
            })
            ->select(
                'sale_details.product_title',
                DB::raw('SUM(sale_details.quantity) as total_qty'),
                DB::raw('SUM(sale_details.total_amount) as total_revenue')
            )
            ->groupBy('sale_details.product_title')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $topChinaProducts = \App\Models\SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
            ->where(function ($q) {
                $q->where('products.parent_category', 'China')
                  ->orWhere('products.parent_category', 'china');
            })
            ->select(
                'sale_details.product_title',
                DB::raw('SUM(sale_details.quantity) as total_qty'),
                DB::raw('SUM(sale_details.total_amount) as total_revenue')
            )
            ->groupBy('sale_details.product_title')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

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
            'totalGrossProfit',
            'totalCostOfGoodsSold',
            'profitMarginPercent',
            'totalRealizedRevenue',
            'bcRevenue',
            'bcCost',
            'bcProfit',
            'bcQty',
            'chRevenue',
            'chCost',
            'chProfit',
            'chQty',
            'topBeeComfortProducts',
            'topChinaProducts',
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
