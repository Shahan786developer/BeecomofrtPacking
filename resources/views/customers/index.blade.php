@extends('layouts.app')

@section('title', 'Customers Directory')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Customers</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Customer Accounts Directory</h1>
            <p class="text-sm text-slate-500 mt-1">Manage client records, contact numbers, and purchasing histories.</p>
        </div>
        <button type="button" onclick="document.getElementById('new-customer-section').scrollIntoView({behavior: 'smooth'})" 
            class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Add New Customer
        </button>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Add Customer Form (1 Col) -->
        <div id="new-customer-section" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-amber-500"></i> Register New Customer
            </h3>
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name / Business <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="e.g. Royal Garments Ltd."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Phone Number
                    </label>
                    <input type="text" name="phone" placeholder="+92 300 1234567"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" placeholder="customer@domain.com"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Address / City
                    </label>
                    <textarea name="address" rows="2" placeholder="Physical shop or billing location"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-sm transition-colors">
                    Save Customer Account
                </button>
            </form>
        </div>

        <!-- Customers Table (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 sm:px-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Registered Customers</h3>
                    <p class="text-xs text-slate-500">Foreign-key customer accounts available during invoice creation</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                    {{ $customers->total() }} Customers
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-4 pl-6">Customer Name</th>
                            <th class="p-4">Contact</th>
                            <th class="p-4">Location</th>
                            <th class="p-4 text-center">Invoices</th>
                            <th class="p-4 text-center">Returns</th>
                            <th class="p-4 pr-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6 font-extrabold text-slate-900 text-sm">
                                {{ $c->name }}
                            </td>
                            <td class="p-4 text-slate-600 font-mono">
                                {{ $c->phone ?? ($c->email ?? '—') }}
                            </td>
                            <td class="p-4 text-slate-500 max-w-xs truncate">
                                {{ $c->address ?? '—' }}
                            </td>
                            <td class="p-4 text-center font-bold text-indigo-700 font-mono">
                                {{ $c->sales_count }}
                            </td>
                            <td class="p-4 text-center font-bold text-orange-600 font-mono">
                                {{ $c->returns_count }}
                            </td>
                            <td class="p-4 pr-6 text-right">
                                @if($c->sales_count == 0)
                                <form method="POST" action="{{ route('customers.destroy', $c) }}" class="inline" onsubmit="return confirm('Delete customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-1" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-slate-400 italic">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No customers registered yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
