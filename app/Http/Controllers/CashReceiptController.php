<?php

namespace App\Http\Controllers;

use App\Models\CashReceipt;
use App\Models\CompanyProfile;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashReceiptController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $query = CashReceipt::with(['customer', 'user']);
        $totalQuery = CashReceipt::query();

        // Scope to user if not Super Admin
        if (!$isSuperAdmin) {
            $query->where('user_id', $currentUser->id);
            $totalQuery->where('user_id', $currentUser->id);
        } else {
            // Super Admin user filter
            if ($request->filled('user_id') && $request->user_id !== 'all') {
                $query->where('user_id', $request->user_id);
                $totalQuery->where('user_id', $request->user_id);
            }
        }

        // Default to Today if no specific date range or search is supplied
        $todayStr = Carbon::today()->toDateString();
        $hasCustomDate = $request->filled('from_date') || $request->filled('to_date');
        $hasExplicitAll = $request->has('all_dates') && $request->all_dates == '1';

        $isDefaultToday = !$hasCustomDate && !$hasExplicitAll;
        $fromDate = $request->filled('from_date') ? $request->input('from_date') : ($isDefaultToday ? $todayStr : null);
        $toDate = $request->filled('to_date') ? $request->input('to_date') : ($isDefaultToday ? $todayStr : null);

        if ($search = $request->input('search')) {
            $searchFilter = function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('owner_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            };
            $query->where($searchFilter);
            $totalQuery->where($searchFilter);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
            $totalQuery->where('customer_id', $customerId);
        }

        if ($fromDate) {
            $query->whereDate('payment_date', '>=', $fromDate);
            $totalQuery->whereDate('payment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('payment_date', '<=', $toDate);
            $totalQuery->whereDate('payment_date', '<=', $toDate);
        }

        $receipts = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $totalCollected = (float) $totalQuery->sum('amount');
        $customers = Customer::orderBy('name')->get();
        $users = $isSuperAdmin ? User::orderBy('name')->get() : collect();

        return view('receipts.index', compact('receipts', 'totalCollected', 'customers', 'users', 'fromDate', 'toDate', 'isDefaultToday'));
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

        $validated['user_id'] = Auth::id();

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
        $this->checkReceiptOwnership($receipt);

        $receipt->load(['customer', 'user']);
        $company = CompanyProfile::getActive();

        return view('receipts.show', compact('receipt', 'company'));
    }

    public function print(CashReceipt $receipt)
    {
        $this->checkReceiptOwnership($receipt);

        $receipt->load(['customer', 'user']);
        $company = CompanyProfile::getActive();

        return view('receipts.print', compact('receipt', 'company'));
    }

    public function edit(CashReceipt $receipt)
    {
        $this->checkReceiptOwnership($receipt);

        $customers = Customer::orderBy('name')->get();
        return view('receipts.edit', compact('receipt', 'customers'));
    }

    public function update(Request $request, CashReceipt $receipt)
    {
        $this->checkReceiptOwnership($receipt);

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
        $this->checkReceiptOwnership($receipt);

        $voucherNo = $receipt->voucher_no;
        $receipt->delete();

        return redirect()->route('receipts.index')
            ->with('success', "Cash Receipt {$voucherNo} removed successfully.");
    }

    /**
     * Enforce ownership check: users only access their own vouchers.
     */
    protected function checkReceiptOwnership(CashReceipt $receipt): void
    {
        if (!Auth::user()->isSuperAdmin() && $receipt->user_id && $receipt->user_id != Auth::id()) {
            abort(403, 'Unauthorized access: You are only allowed to view and manage your own cash receipt vouchers.');
        }
    }
}
