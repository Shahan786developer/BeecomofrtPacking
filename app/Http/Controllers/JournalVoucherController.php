<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Customer;
use App\Models\JournalVoucher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalVoucherController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $query = JournalVoucher::with(['customer', 'user']);
        $totalQuery = JournalVoucher::query();

        // Scope to user if not Super Admin
        if (!$isSuperAdmin) {
            $query->where('user_id', $currentUser->id);
            $totalQuery->where('user_id', $currentUser->id);
        } else {
            if ($request->filled('user_id') && $request->user_id !== 'all') {
                $query->where('user_id', $request->user_id);
                $totalQuery->where('user_id', $request->user_id);
            }
        }

        // Search Filter
        if ($search = $request->input('search')) {
            $searchFilter = function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('owner_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('city', 'like', "%{$search}%");
                  });
            };
            $query->where($searchFilter);
            $totalQuery->where($searchFilter);
        }

        // Customer filter
        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
            $totalQuery->where('customer_id', $customerId);
        }

        // Type filter (debit / credit)
        if ($type = $request->input('type')) {
            if (in_array($type, ['debit', 'credit'])) {
                $query->where('type', $type);
                $totalQuery->where('type', $type);
            }
        }

        // Date range filter
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('voucher_date', '>=', $fromDate);
            $totalQuery->whereDate('voucher_date', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('voucher_date', '<=', $toDate);
            $totalQuery->whereDate('voucher_date', '<=', $toDate);
        }

        $vouchers = $query->orderBy('voucher_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Summary metrics
        $totalDebits = (float) (clone $totalQuery)->where('type', 'debit')->sum('amount');
        $totalCredits = (float) (clone $totalQuery)->where('type', 'credit')->sum('amount');
        $netAdjustment = $totalDebits - $totalCredits;
        $allCount = (clone $totalQuery)->count();

        $customers = Customer::orderBy('name')->get();
        $users = $isSuperAdmin ? User::orderBy('name')->get() : collect();

        return view('vouchers.index', compact(
            'vouchers',
            'customers',
            'users',
            'totalDebits',
            'totalCredits',
            'netAdjustment',
            'allCount',
            'search',
            'type',
            'fromDate',
            'toDate'
        ));
    }

    public function create(Request $request)
    {
        $voucherNo = JournalVoucher::generateNextVoucherNo();
        $customers = Customer::orderBy('name')->get();
        $preselectedCustomerId = $request->input('customer_id');
        $preselectedCustomer = $preselectedCustomerId ? Customer::find($preselectedCustomerId) : null;
        $preselectedType = $request->input('type', 'debit');
        $redirectTo = $request->input('redirect_to');

        return view('vouchers.create', compact(
            'voucherNo',
            'customers',
            'preselectedCustomer',
            'preselectedCustomerId',
            'preselectedType',
            'redirectTo'
        ));
    }

    public function store(Request $request)
    {
        if (!$request->filled('voucher_no')) {
            $request->merge(['voucher_no' => JournalVoucher::generateNextVoucherNo()]);
        }

        $validated = $request->validate([
            'voucher_no' => 'required|string|max:50|unique:journal_vouchers,voucher_no',
            'customer_id' => 'nullable|exists:customers,id',
            'voucher_date' => 'required|date',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $voucher = JournalVoucher::create([
            'voucher_no' => $validated['voucher_no'],
            'customer_id' => $validated['customer_id'] ?? null,
            'user_id' => Auth::id(),
            'voucher_date' => $validated['voucher_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->input('redirect_to') === 'customer_ledger' && $voucher->customer_id) {
            return redirect()->route('customers.ledger', $voucher->customer_id)
                ->with('success', "Journal Voucher '{$voucher->voucher_no}' recorded successfully.");
        }

        return redirect()->route('vouchers.index')
            ->with('success', "Journal Voucher '{$voucher->voucher_no}' created successfully.");
    }

    public function show(JournalVoucher $voucher)
    {
        $voucher->load(['customer', 'user']);
        $company = CompanyProfile::getActive();

        return view('vouchers.show', compact('voucher', 'company'));
    }

    public function edit(JournalVoucher $voucher)
    {
        $customers = Customer::orderBy('name')->get();
        return view('vouchers.edit', compact('voucher', 'customers'));
    }

    public function update(Request $request, JournalVoucher $voucher)
    {
        if (!$request->filled('voucher_no')) {
            $request->merge(['voucher_no' => $voucher->voucher_no]);
        }

        $validated = $request->validate([
            'voucher_no' => 'required|string|max:50|unique:journal_vouchers,voucher_no,' . $voucher->id,
            'customer_id' => 'nullable|exists:customers,id',
            'voucher_date' => 'required|date',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $voucher->update($validated);

        return redirect()->route('vouchers.index')
            ->with('success', "Journal Voucher '{$voucher->voucher_no}' updated successfully.");
    }

    public function destroy(JournalVoucher $voucher)
    {
        $no = $voucher->voucher_no;
        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success', "Journal Voucher '{$no}' deleted successfully.");
    }

    public function print(JournalVoucher $voucher)
    {
        $voucher->load(['customer', 'user']);
        $company = CompanyProfile::getActive();

        return view('vouchers.print', compact('voucher', 'company'));
    }
}
