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
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // If not super admin and cannot read dashboard, redirect to their first permitted module
        if (!$user->isSuperAdmin() && !$user->hasPermission('dashboard', 'read')) {
            $firstRoute = $user->getFirstPermittedRoute();
            if ($firstRoute && $firstRoute !== 'dashboard' && \Illuminate\Support\Facades\Route::has($firstRoute)) {
                return redirect()->route($firstRoute);
            }
            abort(403, 'You do not have permission to view the Dashboard.');
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $userId = $user->id;

        // Date Period Filtering: today, 7_days, month (default), 6_months, year
        $period = $request->input('period', 'month');
        if (!in_array($period, ['today', '7_days', 'month', '6_months', 'year'])) {
            $period = 'month';
        }

        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Today (' . Carbon::today()->format('d M Y') . ')';
                break;
            case '7_days':
                $startDate = Carbon::today()->subDays(6)->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Last 7 Days (' . $startDate->format('d M') . ' – ' . $endDate->format('d M Y') . ')';
                break;
            case '6_months':
                $startDate = Carbon::today()->subMonths(6)->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Last 6 Months (' . $startDate->format('M Y') . ' – ' . $endDate->format('M Y') . ')';
                break;
            case 'year':
                $startDate = Carbon::today()->startOfYear()->startOfDay();
                $endDate = Carbon::today()->endOfYear()->endOfDay();
                $periodLabel = 'This Year (' . Carbon::today()->format('Y') . ')';
                break;
            case 'month':
            default:
                $period = 'month';
                $startDate = Carbon::today()->startOfMonth()->startOfDay();
                $endDate = Carbon::today()->endOfMonth()->endOfDay();
                $periodLabel = 'This Month (' . Carbon::today()->format('F Y') . ')';
                break;
        }

        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();

        $totalProducts = Product::count();
        $totalStockQuantity = (int) Product::sum('stock_quantity');
        $zeroStockCount = Product::where('stock_quantity', 0)->count();
        $negativeStockCount = Product::where('stock_quantity', '<', 0)->count();

        $totalCustomers = Customer::count();

        $salesBaseQuery = Sale::whereBetween('invoice_date', [$startDateStr, $endDateStr]);
        $returnsBaseQuery = ReturnRecord::whereBetween('return_date', [$startDateStr, $endDateStr]);

        if (!$isSuperAdmin) {
            $salesBaseQuery->where('user_id', $userId);
            $returnsBaseQuery->where('user_id', $userId);
        }

        $totalSalesCount = (clone $salesBaseQuery)->count();
        $totalReturnsCount = (clone $returnsBaseQuery)->count();
        $totalSalesAmount = (float) (clone $salesBaseQuery)->sum('total_amount');
        $totalReturnAmount = (float) (clone $returnsBaseQuery)->sum('total_amount');

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

        // Recent sales and returns (scoped by user if not super admin)
        $recentSalesQuery = Sale::query();
        $recentReturnsQuery = ReturnRecord::query();
        if (!$isSuperAdmin) {
            $recentSalesQuery->where('user_id', $userId);
            $recentReturnsQuery->where('user_id', $userId);
        }
        $recentSales = $recentSalesQuery->with(['customer', 'details'])
            ->latest()
            ->take(5)
            ->get();

        $recentReturns = $recentReturnsQuery->with(['customer', 'sale', 'details'])
            ->latest()
            ->take(5)
            ->get();

        // Chart Data: dynamic based on period
        $chartDates = [];
        $salesTrend = [];
        $returnsTrend = [];

        if ($period === 'today') {
            $chartDates[] = Carbon::today()->format('d M (Today)');
            $sQ = Sale::whereDate('invoice_date', Carbon::today()->toDateString());
            $rQ = ReturnRecord::whereDate('return_date', Carbon::today()->toDateString());
            if (!$isSuperAdmin) {
                $sQ->where('user_id', $userId);
                $rQ->where('user_id', $userId);
            }
            $salesTrend[] = (float) $sQ->sum('total_amount');
            $returnsTrend[] = (float) $rQ->sum('total_amount');
        } elseif ($period === '7_days') {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $chartDates[] = $date->format('M d');

                $daySalesQ = Sale::whereDate('invoice_date', $dateStr);
                $dayReturnsQ = ReturnRecord::whereDate('return_date', $dateStr);
                if (!$isSuperAdmin) {
                    $daySalesQ->where('user_id', $userId);
                    $dayReturnsQ->where('user_id', $userId);
                }

                $salesTrend[] = (float) $daySalesQ->sum('total_amount');
                $returnsTrend[] = (float) $dayReturnsQ->sum('total_amount');
            }
        } elseif ($period === 'month') {
            $daysInMonth = Carbon::today()->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::create(Carbon::today()->year, Carbon::today()->month, $d);
                $dateStr = $date->format('Y-m-d');
                $chartDates[] = $date->format('d M');

                $daySalesQ = Sale::whereDate('invoice_date', $dateStr);
                $dayReturnsQ = ReturnRecord::whereDate('return_date', $dateStr);
                if (!$isSuperAdmin) {
                    $daySalesQ->where('user_id', $userId);
                    $dayReturnsQ->where('user_id', $userId);
                }

                $salesTrend[] = (float) $daySalesQ->sum('total_amount');
                $returnsTrend[] = (float) $dayReturnsQ->sum('total_amount');
            }
        } elseif ($period === '6_months') {
            for ($m = 5; $m >= 0; $m--) {
                $monthDate = Carbon::today()->subMonths($m);
                $chartDates[] = $monthDate->format('M Y');
                $mStart = $monthDate->copy()->startOfMonth()->toDateString();
                $mEnd = $monthDate->copy()->endOfMonth()->toDateString();

                $mSalesQ = Sale::whereBetween('invoice_date', [$mStart, $mEnd]);
                $mReturnsQ = ReturnRecord::whereBetween('return_date', [$mStart, $mEnd]);
                if (!$isSuperAdmin) {
                    $mSalesQ->where('user_id', $userId);
                    $mReturnsQ->where('user_id', $userId);
                }

                $salesTrend[] = (float) $mSalesQ->sum('total_amount');
                $returnsTrend[] = (float) $mReturnsQ->sum('total_amount');
            }
        } elseif ($period === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create(Carbon::today()->year, $m, 1);
                $chartDates[] = $monthDate->format('M');
                $mStart = $monthDate->copy()->startOfMonth()->toDateString();
                $mEnd = $monthDate->copy()->endOfMonth()->toDateString();

                $mSalesQ = Sale::whereBetween('invoice_date', [$mStart, $mEnd]);
                $mReturnsQ = ReturnRecord::whereBetween('return_date', [$mStart, $mEnd]);
                if (!$isSuperAdmin) {
                    $mSalesQ->where('user_id', $userId);
                    $mReturnsQ->where('user_id', $userId);
                }

                $salesTrend[] = (float) $mSalesQ->sum('total_amount');
                $returnsTrend[] = (float) $mReturnsQ->sum('total_amount');
            }
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

        // 1. Top 10 Selling Products in period (Bar Chart Data)
        $topProductsQuery = \App\Models\SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->whereBetween('sales.invoice_date', [$startDateStr, $endDateStr])
            ->select(
                'sale_details.product_title',
                DB::raw('SUM(sale_details.quantity) as total_qty'),
                DB::raw('SUM(sale_details.total_amount) as total_revenue')
            );
        if (!$isSuperAdmin) {
            $topProductsQuery->where('sales.user_id', $userId);
        }
        $topProducts = $topProductsQuery->groupBy('sale_details.product_title')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $topProductsLabels = $topProducts->pluck('product_title')->toArray();
        $topProductsQtys = $topProducts->pluck('total_qty')->map(fn($q) => (int)$q)->toArray();
        $topProductsRevenues = $topProducts->pluck('total_revenue')->map(fn($r) => (float)$r)->toArray();

        // 2. Top Customers in period (Purchases Breakdown)
        $topCustomers = Customer::withSum(['sales as total_purchased' => function($q) use ($startDateStr, $endDateStr, $isSuperAdmin, $userId) {
                $q->whereBetween('invoice_date', [$startDateStr, $endDateStr]);
                if (!$isSuperAdmin) $q->where('user_id', $userId);
            }], 'total_amount')
            ->withCount(['sales' => function($q) use ($startDateStr, $endDateStr, $isSuperAdmin, $userId) {
                $q->whereBetween('invoice_date', [$startDateStr, $endDateStr]);
                if (!$isSuperAdmin) $q->where('user_id', $userId);
            }])
            ->orderByDesc('total_purchased')
            ->take(10)
            ->get();

        $topCustomersLabels = $topCustomers->pluck('name')->toArray();
        $topCustomersPurchases = $topCustomers->pluck('total_purchased')->map(fn($p) => (float)($p ?? 0))->toArray();

        // 3. Customer Receivables & Cash Receipts in period
        $allCustomers = Customer::with(['sales', 'cashReceipts', 'returns', 'journalVouchers'])->get();
        $pendingCustomers = $allCustomers->filter(fn($c) => $c->balance_due > 0)->sortByDesc('balance_due')->values();
        $totalReceivables = (float) $pendingCustomers->sum('balance_due');
        
        $receiptsQuery = \App\Models\CashReceipt::whereBetween('payment_date', [$startDateStr, $endDateStr]);
        if (!$isSuperAdmin) {
            $receiptsQuery->where('user_id', $userId);
        }
        $totalCashReceived = (float) $receiptsQuery->sum('amount');

        // 4. Gross Profit & Actual Price vs Selling Price Analysis for period
        $saleDetailsQuery = \App\Models\SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->whereBetween('sales.invoice_date', [$startDateStr, $endDateStr])
            ->select(
                'sale_details.quantity',
                'sale_details.unit_price',
                'sale_details.total_amount',
                'products.actual_price',
                'products.parent_category'
            );
        if (!$isSuperAdmin) {
            $saleDetailsQuery->where('sales.user_id', $userId);
        }
        $saleDetailsWithCost = $saleDetailsQuery->get();

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

        // Factor in returns to ensure accurate net profit for period
        $returnDetailsQuery = \App\Models\ReturnDetail::join('products', 'return_details.product_id', '=', 'products.id')
            ->join('returns', 'return_details.return_id', '=', 'returns.id')
            ->whereBetween('returns.return_date', [$startDateStr, $endDateStr])
            ->select(
                'return_details.quantity',
                'return_details.unit_price',
                'return_details.total_amount',
                'products.actual_price',
                'products.parent_category'
            );
        if (!$isSuperAdmin) {
            $returnDetailsQuery->where('returns.user_id', $userId);
        }
        $returnDetailsWithCost = $returnDetailsQuery->get();

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

        // Product-wise comparison: Top 5 Bee Comfort vs Top 5 China in period
        $topBeeComfortProducts = \App\Models\SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->whereBetween('sales.invoice_date', [$startDateStr, $endDateStr])
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
            ->take(5);
        if (!$isSuperAdmin) {
            $topBeeComfortProducts->where('sales.user_id', $userId);
        }
        $topBeeComfortProducts = $topBeeComfortProducts->get();

        $topChinaProducts = \App\Models\SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->whereBetween('sales.invoice_date', [$startDateStr, $endDateStr])
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
            ->take(5);
        if (!$isSuperAdmin) {
            $topChinaProducts->where('sales.user_id', $userId);
        }
        $topChinaProducts = $topChinaProducts->get();

        return view('dashboard.index', compact(
            'period',
            'periodLabel',
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
