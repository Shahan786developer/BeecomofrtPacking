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

    public function report(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id') ?: ($customers->first()->id ?? null);

        if (!$selectedCustomerId) {
            return redirect()->route('customers.index')->with('info', 'Please register a customer first.');
        }

        $customer = Customer::findOrFail($selectedCustomerId);
        return $this->renderLedger($request, $customer, $customers);
    }

    public function ledger(Request $request, Customer $customer)
    {
        $customers = Customer::orderBy('name')->get();
        return $this->renderLedger($request, $customer, $customers);
    }

    public function printStatement(Request $request, Customer $customer)
    {
        $company = \App\Models\CompanyProfile::getActive();
        return $this->renderLedger($request, $customer, collect(), true, $company);
    }

    protected function renderLedger(Request $request, Customer $customer, $allCustomers, bool $isPrint = false, $company = null)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Opening balance calculation before $startDate
        $openingBalance = 0.0;
        if ($startDate) {
            $invoicedBefore = (float) $customer->sales()->whereDate('invoice_date', '<', $startDate)->sum('total_amount');
            $returnedBefore = (float) $customer->returns()->whereDate('return_date', '<', $startDate)->sum('total_amount');
            $paidBefore = (float) $customer->cashReceipts()->whereDate('payment_date', '<', $startDate)->sum('amount');
            $openingBalance = max(0, $invoicedBefore - ($returnedBefore + $paidBefore));
        }

        // Queries within date range
        $salesQuery = $customer->sales()->with(['details.product.category', 'details.product.dozen']);
        $returnsQuery = $customer->returns()->with(['details.product.category', 'details.product.dozen', 'sale']);
        $receiptsQuery = $customer->cashReceipts();

        if ($startDate) {
            $salesQuery->whereDate('invoice_date', '>=', $startDate);
            $returnsQuery->whereDate('return_date', '>=', $startDate);
            $receiptsQuery->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate) {
            $salesQuery->whereDate('invoice_date', '<=', $endDate);
            $returnsQuery->whereDate('return_date', '<=', $endDate);
            $receiptsQuery->whereDate('payment_date', '<=', $endDate);
        }

        $sales = $salesQuery->get();
        $returns = $returnsQuery->get();
        $receipts = $receiptsQuery->get();

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
                'date' => $sale->invoice_date ? $sale->invoice_date->format('Y-m-d') : $dateTime->format('Y-m-d'),
                'date_time' => $dateTime,
                'date_formatted' => $sale->invoice_date ? $sale->invoice_date->format('d M Y') : $dateTime->format('d M Y'),
                'items_count' => $sale->details->count(),
                'quantity' => (int) $sale->details->sum('quantity'),
                'quantity_formatted' => '+' . $sale->details->sum('quantity'),
                'debit' => (float) $sale->total_amount,
                'credit' => 0.0,
                'amount' => (float) $sale->total_amount,
                'view_url' => route('sales.show', $sale),
                'items' => $sale->details->map(fn($d) => [
                    'name' => $d->product ? $d->product->title : 'Product #' . $d->product_id,
                    'sku' => $d->product ? $d->product->sku_code : '—',
                    'quantity' => $d->quantity,
                    'unit_price' => (float) $d->unit_price,
                    'subtotal' => (float) $d->total_amount,
                ]),
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
                'date' => $ret->return_date ? $ret->return_date->format('Y-m-d') : $dateTime->format('Y-m-d'),
                'date_time' => $dateTime,
                'date_formatted' => $ret->return_date ? $ret->return_date->format('d M Y') : $dateTime->format('d M Y'),
                'items_count' => $ret->details->count(),
                'quantity' => (int) $ret->details->sum('quantity'),
                'quantity_formatted' => '-' . $ret->details->sum('quantity'),
                'debit' => 0.0,
                'credit' => (float) $ret->total_amount,
                'amount' => (float) $ret->total_amount,
                'view_url' => route('returns.show', $ret),
                'items' => $ret->details->map(fn($d) => [
                    'name' => $d->product ? $d->product->title : 'Product #' . $d->product_id,
                    'sku' => $d->product ? $d->product->sku_code : '—',
                    'quantity' => $d->quantity,
                    'unit_price' => (float) $d->unit_price,
                    'subtotal' => (float) $d->total_amount,
                ]),
                'notes' => $ret->reason ?: '',
            ]);
        }

        foreach ($receipts as $r) {
            $dateTime = $r->created_at ?: Carbon::parse($r->payment_date);
            $transactions->push([
                'id' => 'receipt_' . $r->id,
                'model_id' => $r->id,
                'type' => 'receipt',
                'type_label' => 'Cash Receipt (CRV)',
                'badge_class' => 'bg-success-subtle text-success border border-success-subtle',
                'doc_no' => $r->voucher_no,
                'related_doc_no' => null,
                'date' => $r->payment_date ? $r->payment_date->format('Y-m-d') : $dateTime->format('Y-m-d'),
                'date_time' => $dateTime,
                'date_formatted' => $r->payment_date ? $r->payment_date->format('d M Y') : $dateTime->format('d M Y'),
                'items_count' => 1,
                'quantity' => 0,
                'quantity_formatted' => '—',
                'debit' => 0.0,
                'credit' => (float) $r->amount,
                'amount' => (float) $r->amount,
                'view_url' => route('receipts.show', $r),
                'items' => collect(),
                'notes' => ($r->payment_method ? '[' . ucfirst(str_replace('_', ' ', $r->payment_method)) . '] ' : '') . ($r->notes ?: ''),
            ]);
        }

        // Sort chronologically ascending to compute running balances correctly
        $sorted = $transactions->sortBy(fn($t) => $t['date_time']->timestamp)->values();

        $running = $openingBalance;
        $withBalance = $sorted->map(function ($tx) use (&$running) {
            $running += ($tx['debit'] - $tx['credit']);
            $tx['running_balance'] = $running;
            return $tx;
        });

        // Totals in current range
        $rangeDebits = (float) $withBalance->sum('debit');
        $rangeCredits = (float) $withBalance->sum('credit');
        $closingBalance = $running;

        // Customer lifetime totals
        $lifetimeInvoiced = $customer->total_invoiced;
        $lifetimePaid = $customer->total_paid;
        $lifetimeReturned = $customer->total_returned;
        $lifetimeBalanceDue = $customer->balance_due;

        if ($isPrint) {
            return view('customers.statement', compact(
                'customer',
                'company',
                'startDate',
                'endDate',
                'openingBalance',
                'closingBalance',
                'rangeDebits',
                'rangeCredits',
                'lifetimeInvoiced',
                'lifetimePaid',
                'lifetimeReturned',
                'lifetimeBalanceDue',
                'withBalance'
            ));
        }

        // Default display on screen: latest first
        $allTransactions = $withBalance->reverse()->values();

        // Search & Type filter on screen
        $search = $request->input('search');
        $typeFilter = $request->input('type');

        $filteredTransactions = $allTransactions;
        if ($typeFilter && in_array($typeFilter, ['sale', 'return', 'receipt'])) {
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
            'allCustomers',
            'allTransactions',
            'filteredTransactions',
            'startDate',
            'endDate',
            'openingBalance',
            'closingBalance',
            'rangeDebits',
            'rangeCredits',
            'lifetimeInvoiced',
            'lifetimePaid',
            'lifetimeReturned',
            'lifetimeBalanceDue',
            'search',
            'typeFilter'
        ));
    }
}
