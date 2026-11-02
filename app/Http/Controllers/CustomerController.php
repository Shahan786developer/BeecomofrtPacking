<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount(['sales', 'returns']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('phone_2', 'like', "%{$search}%")
                  ->orWhere('phone_3', 'like', "%{$search}%")
                  ->orWhere('phone_4', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'owner_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'phone_3' => 'nullable|string|max:50',
            'phone_4' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer registered successfully.',
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' added successfully.");
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'owner_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'phone_3' => 'nullable|string|max:50',
            'phone_4' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' updated successfully.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete customer who has existing sales records.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function ledger(Request $request, Customer $customer)
    {
        // Load sales and returns with their detail relationships
        $sales = $customer->sales()
            ->with(['details.product.category', 'details.product.dozen'])
            ->orderBy('id', 'asc')
            ->get();

        $returns = $customer->returns()
            ->with(['details.product.category', 'details.product.dozen', 'sale'])
            ->orderBy('id', 'asc')
            ->get();

        // High-level summary metrics for cards
        $totalInvoicesCount = $sales->count();
        $totalPurchasedQty = (int) $sales->sum(fn($s) => $s->details->sum('quantity'));
        $totalInvoicedAmount = (float) $sales->sum('total_amount');

        $totalReturnsCount = $returns->count();
        $totalReturnedQty = (int) $returns->sum(fn($r) => $r->details->sum('quantity'));
        $totalRefundedAmount = (float) $returns->sum('total_amount');

        $netUnitsKept = $totalPurchasedQty - $totalReturnedQty;
        $netBalance = $totalInvoicedAmount - $totalRefundedAmount;

        // Build unified chronological transactions
        $transactions = collect();

        foreach ($sales as $sale) {
            $dateTime = $sale->created_at ?: Carbon::parse($sale->invoice_date);
            $transactions->push([
                'id' => 'sale_' . $sale->id,
                'model_id' => $sale->id,
                'type' => 'sale',
                'type_label' => 'Sale Invoice',
                'badge_class' => 'bg-primary-subtle text-primary border border-primary-subtle',
                'doc_no' => $sale->invoice_no,
                'related_doc_no' => null,
                'date_time' => $dateTime,
                'date_formatted' => $dateTime->format('d M Y, h:i A'),
                'items_count' => $sale->details->count(),
                'quantity' => (int) $sale->details->sum('quantity'),
                'quantity_formatted' => '+' . $sale->details->sum('quantity'),
                'debit' => (float) $sale->total_amount,
                'credit' => 0.0,
                'amount' => (float) $sale->total_amount,
                'view_url' => route('sales.show', $sale),
                'items' => $sale->details->map(function ($d) {
                    return [
                        'name' => $d->product ? $d->product->title : 'Product #' . $d->product_id,
                        'sku' => $d->product ? $d->product->sku_code : '—',
                        'quantity' => $d->quantity,
                        'unit_price' => (float) $d->unit_price,
                        'subtotal' => (float) $d->subtotal,
                    ];
                }),
                'notes' => $sale->notes ?: '',
            ]);
        }

        foreach ($returns as $ret) {
            $dateTime = $ret->created_at ?: Carbon::parse($ret->return_date);
            $transactions->push([
                'id' => 'return_' . $ret->id,
                'model_id' => $ret->id,
                'type' => 'return',
                'type_label' => 'Bill Return',
                'badge_class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'doc_no' => $ret->return_no,
                'related_doc_no' => $ret->sale ? $ret->sale->invoice_no : null,
                'date_time' => $dateTime,
                'date_formatted' => $dateTime->format('d M Y, h:i A'),
                'items_count' => $ret->details->count(),
                'quantity' => (int) $ret->details->sum('quantity'),
                'quantity_formatted' => '-' . $ret->details->sum('quantity'),
                'debit' => 0.0,
                'credit' => (float) $ret->total_amount,
                'amount' => (float) $ret->total_amount,
                'view_url' => route('returns.show', $ret),
                'items' => $ret->details->map(function ($d) {
                    return [
                        'name' => $d->product ? $d->product->title : 'Product #' . $d->product_id,
                        'sku' => $d->product ? $d->product->sku_code : '—',
                        'quantity' => $d->quantity,
                        'unit_price' => (float) $d->unit_price,
                        'subtotal' => (float) $d->subtotal,
                    ];
                }),
                'notes' => $ret->reason ?: '',
            ]);
        }

        // Sort chronologically ascending to compute running balances accurately
        $sorted = $transactions->sortBy(fn($t) => $t['date_time']->timestamp)->values();

        $running = 0.0;
        $withBalance = $sorted->map(function ($tx) use (&$running) {
            $running += ($tx['debit'] - $tx['credit']);
            $tx['running_balance'] = $running;
            return $tx;
        });

        // Default display: latest activity first
        $allTransactions = $withBalance->reverse()->values();

        // Optional filtering by search and type
        $search = $request->input('search');
        $typeFilter = $request->input('type');

        $filteredTransactions = $allTransactions;
        if ($typeFilter && in_array($typeFilter, ['sale', 'return'])) {
            $filteredTransactions = $filteredTransactions->where('type', $typeFilter);
        }

        if ($search) {
            $s = strtolower($search);
            $filteredTransactions = $filteredTransactions->filter(function ($t) use ($s) {
                if (str_contains(strtolower($t['doc_no']), $s)) return true;
                if (!empty($t['related_doc_no']) && str_contains(strtolower($t['related_doc_no']), $s)) return true;
                if (str_contains(strtolower($t['date_formatted']), $s)) return true;
                if (str_contains(strtolower($t['notes']), $s)) return true;
                foreach ($t['items'] as $it) {
                    if (str_contains(strtolower($it['name']), $s)) return true;
                    if (str_contains(strtolower($it['sku']), $s)) return true;
                }
                return false;
            });
        }

        return view('customers.ledger', compact(
            'customer',
            'allTransactions',
            'filteredTransactions',
            'totalInvoicesCount',
            'totalPurchasedQty',
            'totalInvoicedAmount',
            'totalReturnsCount',
            'totalReturnedQty',
            'totalRefundedAmount',
            'netUnitsKept',
            'netBalance',
            'search',
            'typeFilter'
        ));
    }
}
