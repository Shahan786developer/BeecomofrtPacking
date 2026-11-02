<?php

namespace App\Http\Controllers;

use App\Models\CashReceipt;
use App\Models\CompanyProfile;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = CashReceipt::with('customer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('owner_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('payment_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('payment_date', '<=', $toDate);
        }

        $receipts = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $totalCollected = (float) CashReceipt::sum('amount');
        $customers = Customer::orderBy('name')->get();

        return view('receipts.index', compact('receipts', 'totalCollected', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $nextVoucherNo = CashReceipt::generateNextVoucherNo();
        $today = Carbon::today()->format('Y-m-d');
        $selectedCustomerId = $request->input('customer_id');

        return view('receipts.create', compact('customers', 'nextVoucherNo', 'today', 'selectedCustomerId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'voucher_no' => 'required|string|unique:cash_receipts,voucher_no',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'amount.min' => 'Payment amount must be greater than zero.',
        ]);

        $receipt = CashReceipt::create($validated);
        $customer = $receipt->customer;

        $remainingBalance = number_format($customer->balance_due, 2);

        // Redirect back to customer's ledger or receipt index with clear remaining balance notification
        if ($request->input('redirect_to') === 'customer_ledger') {
            return redirect()->route('customers.ledger', $customer)
                ->with('success', "Cash Receipt {$receipt->voucher_no} of Rs. " . number_format($receipt->amount, 2) . " saved successfully. Remaining Customer Balance: Rs. {$remainingBalance}");
        }

        return redirect()->route('receipts.index')
            ->with('success', "Payment {$receipt->voucher_no} (Rs. " . number_format($receipt->amount, 2) . ") recorded for {$customer->name}. Remaining Balance: Rs. {$remainingBalance}");
    }

    public function show(CashReceipt $receipt)
    {
        $receipt->load('customer');
        $company = CompanyProfile::getActive();

        return view('receipts.show', compact('receipt', 'company'));
    }

    public function edit(CashReceipt $receipt)
    {
        $customers = Customer::orderBy('name')->get();
        return view('receipts.edit', compact('receipt', 'customers'));
    }

    public function update(Request $request, CashReceipt $receipt)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $receipt->update($validated);

        return redirect()->route('receipts.index')
            ->with('success', "Cash Receipt {$receipt->voucher_no} updated successfully.");
    }

    public function destroy(CashReceipt $receipt)
    {
        $voucherNo = $receipt->voucher_no;
        $receipt->delete();

        return redirect()->route('receipts.index')
            ->with('success', "Cash Receipt {$voucherNo} removed successfully.");
    }
}
