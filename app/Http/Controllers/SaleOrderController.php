<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleOrderController extends Controller
{
    /**
     * Display a listing of orders with filters & clean grouping table.
     */
    public function index(Request $request): View
    {
        $query = SaleOrder::with(['customer', 'items'])->latest('order_date')->latest('id');

        // Filter: Order No / Voucher No
        if ($request->filled('order_no')) {
            $query->where('order_no', 'like', '%' . trim($request->order_no) . '%');
        }

        // Filter: Customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter: Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter: Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        // Summary metrics
        $allOrdersQuery = SaleOrder::query();
        $totalOrdersCount = $allOrdersQuery->count();
        $totalPiecesBooked = (int) $allOrdersQuery->sum('total_quantity');
        $pendingCount = (clone $allOrdersQuery)->where('status', 'Pending')->count();
        $completedCount = (clone $allOrdersQuery)->where('status', 'Completed')->count();

        $orders = $query->paginate(20)->withQueryString();

        $customers = Customer::orderBy('name')->get();
        $recentOrderNos = SaleOrder::orderByDesc('id')->take(30)->pluck('order_no');

        return view('orders.index', compact(
            'orders',
            'customers',
            'recentOrderNos',
            'totalOrdersCount',
            'totalPiecesBooked',
            'pendingCount',
            'completedCount'
        ));
    }

    /**
     * Show the form for creating a new order booking.
     */
    public function create(): View
    {
        $nextOrderNo = SaleOrder::generateNextOrderNo();
        $customers = Customer::orderBy('name')->get();

        // Registered products from backend product registration
        $registeredProducts = Product::with(['category', 'dozen'])->orderBy('id', 'desc')->get();
        $productArticles = [];
        foreach ($registeredProducts as $p) {
            if (!empty($p->product_code)) {
                $productArticles[] = $p->product_code;
            }
            if (!empty($p->title)) {
                $productArticles[] = $p->title;
            }
        }

        // Standard predefined articles
        $predefinedArticles = [
            'COMFORT FIT 32-42',
            'COMFORT FIT C & D CUP 32-42',
            'COMFORT PLUS 32-42',
            'COMFORT DELUXE 32-42',
            'SOFT TOUCH 32-42',
            'COTTON SOFT 32-42',
            'FANCY EMBROIDERY 32-42',
        ];

        // Also fetch product categories
        $categoryNames = Category::orderBy('name')->pluck('name')->toArray();
        $availableArticles = array_values(array_unique(array_filter(array_merge(
            $productArticles,
            $predefinedArticles,
            $categoryNames
        ))));

        // Color options (HTML default dropdown)
        $availableColors = [
            'Black',
            'Skin',
            'White',
            'Mehroon',
            'Baby Pink',
            'Grey',
            'Pink',
            'Red',
            'Royal Blue',
            'Champagne',
            'Charcoal Grey',
            'Dark Pink',
            'Dusty Rose',
            'Ivory',
            'Navy',
            'Nude Beige',
            'Purple',
            'Sage Green',
        ];

        // Cup options
        $cupOptions = ['B', 'C', 'D', 'DD'];

        return view('orders.create', compact(
            'nextOrderNo',
            'customers',
            'availableArticles',
            'availableColors',
            'cupOptions'
        ));
    }

    /**
     * Store a newly created order with articles & size matrix items.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'order_no' => 'required|string|max:64|unique:sale_orders,order_no',
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:Pending,Completed,Rejected',
            'notes' => 'nullable|string|max:1000',
            'articles' => 'required|array|min:1',
            'articles.*.article_name' => 'required|string|max:255',
            'articles.*.items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $order = SaleOrder::create([
                'order_no' => trim($request->order_no),
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'total_quantity' => 0,
                'total_amount' => 0.00,
            ]);

            $totalQty = 0;
            $totalAmount = 0.0;

            foreach ($request->articles as $artData) {
                $articleName = trim($artData['article_name'] ?? '');
                if (empty($articleName) || empty($artData['items'])) {
                    continue;
                }

                foreach ($artData['items'] as $itemData) {
                    $s32 = max(0, (int) ($itemData['size_32'] ?? 0));
                    $s34 = max(0, (int) ($itemData['size_34'] ?? 0));
                    $s36 = max(0, (int) ($itemData['size_36'] ?? 0));
                    $s38 = max(0, (int) ($itemData['size_38'] ?? 0));
                    $s40 = max(0, (int) ($itemData['size_40'] ?? 0));
                    $s42 = max(0, (int) ($itemData['size_42'] ?? 0));
                    $s44 = max(0, (int) ($itemData['size_44'] ?? 0));
                    $s46 = max(0, (int) ($itemData['size_46'] ?? 0));
                    $s48 = max(0, (int) ($itemData['size_48'] ?? 0));
                    $s50 = max(0, (int) ($itemData['size_50'] ?? 0));

                    $lineQty = $s32 + $s34 + $s36 + $s38 + $s40 + $s42 + $s44 + $s46 + $s48 + $s50;

                    $color = trim($itemData['color'] ?? '');
                    $cup = trim($itemData['cup'] ?? '');

                    // Skip row if completely empty (no qty and no color)
                    if ($lineQty === 0 && empty($color) && empty($cup)) {
                        continue;
                    }

                    $rateStd = !empty($itemData['rate_std']) ? (float) $itemData['rate_std'] : null;
                    $ratePlus = !empty($itemData['rate_plus']) ? (float) $itemData['rate_plus'] : null;

                    $stdQty = $s32 + $s34 + $s36 + $s38 + $s40 + $s42;
                    $plusQty = $s44 + $s46 + $s48 + $s50;
                    $lineTotal = ($stdQty * ($rateStd ?? 0)) + ($plusQty * ($ratePlus ?? 0));

                    $orderItem = new SaleOrderItem([
                        'sale_order_id' => $order->id,
                        'article_name' => $articleName,
                        'cup' => $cup ?: 'B',
                        'color' => $color ?: 'STANDARD',
                        'size_32' => $s32,
                        'size_34' => $s34,
                        'size_36' => $s36,
                        'size_38' => $s38,
                        'size_40' => $s40,
                        'size_42' => $s42,
                        'size_44' => $s44,
                        'size_46' => $s46,
                        'size_48' => $s48,
                        'size_50' => $s50,
                        'rate_std' => $rateStd,
                        'rate_plus' => $ratePlus,
                        'total_qty' => $lineQty,
                        'line_total' => $lineTotal,
                    ]);

                    $order->items()->save($orderItem);

                    $totalQty += $lineQty;
                    $totalAmount += $lineTotal;
                }
            }

            $order->update([
                'total_quantity' => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('orders.index')->with('success', "Sale Order {$order->order_no} booked successfully with {$totalQty} pieces!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating sale order: ' . $e->getMessage());
        }
    }

    /**
     * Display order details.
     */
    public function show(SaleOrder $order): View
    {
        $order->load(['customer', 'items']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show edit form for an existing order.
     */
    public function edit(SaleOrder $order): View
    {
        $order->load(['customer', 'items']);
        $customers = Customer::orderBy('name')->get();

        // Registered products from backend product registration
        $registeredProducts = Product::with(['category', 'dozen'])->orderBy('id', 'desc')->get();
        $productArticles = [];
        foreach ($registeredProducts as $p) {
            if (!empty($p->product_code)) {
                $productArticles[] = $p->product_code;
            }
            if (!empty($p->title)) {
                $productArticles[] = $p->title;
            }
        }

        $predefinedArticles = [
            'COMFORT FIT 32-42',
            'COMFORT FIT C & D CUP 32-42',
            'COMFORT PLUS 32-42',
            'COMFORT DELUXE 32-42',
            'SOFT TOUCH 32-42',
            'COTTON SOFT 32-42',
            'FANCY EMBROIDERY 32-42',
        ];

        $categoryNames = Category::orderBy('name')->pluck('name')->toArray();
        $availableArticles = array_values(array_unique(array_filter(array_merge(
            $productArticles,
            $predefinedArticles,
            $categoryNames
        ))));

        $availableColors = [
            'Black',
            'Skin',
            'White',
            'Mehroon',
            'Baby Pink',
            'Grey',
            'Pink',
            'Red',
            'Royal Blue',
            'Champagne',
            'Charcoal Grey',
            'Dark Pink',
            'Dusty Rose',
            'Ivory',
            'Navy',
            'Nude Beige',
            'Purple',
            'Sage Green',
        ];

        $cupOptions = ['B', 'C', 'D', 'DD'];

        // Group existing items by article name
        $groupedArticles = [];
        foreach ($order->items as $item) {
            $groupedArticles[$item->article_name][] = $item;
        }

        return view('orders.edit', compact(
            'order',
            'customers',
            'availableArticles',
            'availableColors',
            'cupOptions',
            'groupedArticles'
        ));
    }

    /**
     * Update existing order.
     */
    public function update(Request $request, SaleOrder $order): RedirectResponse
    {
        $request->validate([
            'order_no' => 'required|string|max:64|unique:sale_orders,order_no,' . $order->id,
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:Pending,Completed,Rejected',
            'notes' => 'nullable|string|max:1000',
            'articles' => 'required|array|min:1',
            'articles.*.article_name' => 'required|string|max:255',
            'articles.*.items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $order->update([
                'order_no' => trim($request->order_no),
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Remove existing items and rebuild
            $order->items()->delete();

            $totalQty = 0;
            $totalAmount = 0.0;

            foreach ($request->articles as $artData) {
                $articleName = trim($artData['article_name'] ?? '');
                if (empty($articleName) || empty($artData['items'])) {
                    continue;
                }

                foreach ($artData['items'] as $itemData) {
                    $s32 = max(0, (int) ($itemData['size_32'] ?? 0));
                    $s34 = max(0, (int) ($itemData['size_34'] ?? 0));
                    $s36 = max(0, (int) ($itemData['size_36'] ?? 0));
                    $s38 = max(0, (int) ($itemData['size_38'] ?? 0));
                    $s40 = max(0, (int) ($itemData['size_40'] ?? 0));
                    $s42 = max(0, (int) ($itemData['size_42'] ?? 0));
                    $s44 = max(0, (int) ($itemData['size_44'] ?? 0));
                    $s46 = max(0, (int) ($itemData['size_46'] ?? 0));
                    $s48 = max(0, (int) ($itemData['size_48'] ?? 0));
                    $s50 = max(0, (int) ($itemData['size_50'] ?? 0));

                    $lineQty = $s32 + $s34 + $s36 + $s38 + $s40 + $s42 + $s44 + $s46 + $s48 + $s50;

                    $color = trim($itemData['color'] ?? '');
                    $cup = trim($itemData['cup'] ?? '');

                    if ($lineQty === 0 && empty($color) && empty($cup)) {
                        continue;
                    }

                    $rateStd = !empty($itemData['rate_std']) ? (float) $itemData['rate_std'] : null;
                    $ratePlus = !empty($itemData['rate_plus']) ? (float) $itemData['rate_plus'] : null;

                    $stdQty = $s32 + $s34 + $s36 + $s38 + $s40 + $s42;
                    $plusQty = $s44 + $s46 + $s48 + $s50;
                    $lineTotal = ($stdQty * ($rateStd ?? 0)) + ($plusQty * ($ratePlus ?? 0));

                    $orderItem = new SaleOrderItem([
                        'sale_order_id' => $order->id,
                        'article_name' => $articleName,
                        'cup' => $cup ?: 'B',
                        'color' => $color ?: 'STANDARD',
                        'size_32' => $s32,
                        'size_34' => $s34,
                        'size_36' => $s36,
                        'size_38' => $s38,
                        'size_40' => $s40,
                        'size_42' => $s42,
                        'size_44' => $s44,
                        'size_46' => $s46,
                        'size_48' => $s48,
                        'size_50' => $s50,
                        'rate_std' => $rateStd,
                        'rate_plus' => $ratePlus,
                        'total_qty' => $lineQty,
                        'line_total' => $lineTotal,
                    ]);

                    $order->items()->save($orderItem);

                    $totalQty += $lineQty;
                    $totalAmount += $lineTotal;
                }
            }

            $order->update([
                'total_quantity' => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('orders.index')->with('success', "Order {$order->order_no} updated successfully!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Delete an order.
     */
    public function destroy(SaleOrder $order): RedirectResponse
    {
        $orderNo = $order->order_no;
        $order->delete();

        return redirect()->route('orders.index')->with('success', "Order {$orderNo} has been deleted.");
    }

    /**
     * Clean printable A4 booking sheet.
     */
    public function print(SaleOrder $order): View
    {
        $order->load(['customer', 'items']);
        $companyProfile = \App\Models\CompanyProfile::getSettings();

        // Group items by article
        $groupedArticles = [];
        foreach ($order->items as $item) {
            $groupedArticles[$item->article_name][] = $item;
        }

        return view('orders.print', compact('order', 'companyProfile', 'groupedArticles'));
    }
}
