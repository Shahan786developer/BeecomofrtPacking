@extends('layouts.app')

@section('title', 'Stock Movements — ' . $product->title)

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-amber-600">Products</a>
    <span>/</span>
    <a href="{{ route('products.show', $product) }}" class="hover:text-amber-600">{{ $product->title }}</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">Stock Movements</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                    SKU: {{ $product->sku_code }}
                </span>
                <span class="text-xs text-slate-500 font-medium">Current Stock: <strong>{{ $product->stock_quantity }} units</strong></span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Stock Movement History</h1>
            <p class="text-xs text-slate-500 mt-0.5">Complete audit log for {{ $product->title }}</p>
        </div>
        <a href="{{ route('products.show', $product) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Back to Product
        </a>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-4 pl-6">Timestamp</th>
                        <th class="p-4">Movement Type</th>
                        <th class="p-4 text-center">Change</th>
                        <th class="p-4 text-center">Previous Stock</th>
                        <th class="p-4 text-center">New Stock</th>
                        <th class="p-4 pr-6">Notes / Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $move)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 pl-6 font-mono text-slate-500">{{ $move->created_at->format('d M Y, H:i:s') }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                {{ $move->type === 'sale' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($move->type === 'return' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                {{ $move->type }}
                            </span>
                        </td>
                        <td class="p-4 text-center font-extrabold text-sm {{ $move->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $move->quantity_change > 0 ? '+' : '' }}{{ $move->quantity_change }}
                        </td>
                        <td class="p-4 text-center text-slate-600 font-medium">{{ $move->previous_stock }}</td>
                        <td class="p-4 text-center font-black text-slate-900">{{ $move->new_stock }}</td>
                        <td class="p-4 pr-6 text-slate-600">{{ $move->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No movements recorded for this product yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
