@extends('layouts.app')

@section('title', 'Thermal Barcode Configuration')

@section('breadcrumb')
    <a href="{{ route('barcode.index') }}" class="hover:text-amber-600">Barcode Generation</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">Configuration</span>
@endsection

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Thermal Barcode Configuration</h1>
            <p class="text-sm text-slate-500 mt-1">Manage label dimensions, brand typography, and second-line origin descriptions.</p>
        </div>
        <a href="{{ route('barcode.index') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-barcode"></i> Go to Barcode Studio
        </a>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- 1. Thermal Printer Hardware Parameters -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Thermal Label Specifications</h2>
                    <p class="text-xs text-slate-500">Hardware parameters for thermal roll output</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-200">
                    32 mm × 22 mm
                </span>
            </div>

            <form method="POST" action="{{ route('barcode.settings.update') }}" class="space-y-4">
                @csrf

                <!-- Brand Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        1. First Line: Brand Name
                    </label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', $config->brand_name) }}" required
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-bold">
                    <p class="text-[11px] text-slate-400 mt-0.5">Prints on the first line (e.g. BEE COMFORT)</p>
                </div>

                <!-- Label Dimensions -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Width (mm)
                        </label>
                        <input type="number" step="0.1" name="label_width_mm" value="{{ old('label_width_mm', $config->label_width_mm) }}" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Height (mm)
                        </label>
                        <input type="number" step="0.1" name="label_height_mm" value="{{ old('label_height_mm', $config->label_height_mm) }}" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-mono font-bold">
                    </div>
                </div>

                <!-- Barcode Type & Font Family -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Barcode Standard
                        </label>
                        <input type="text" name="barcode_type" value="{{ old('barcode_type', $config->barcode_type) }}" readonly
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-100 border border-slate-200 font-mono font-bold text-slate-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Font Family
                        </label>
                        <input type="text" name="font_family" value="{{ old('font_family', $config->font_family) }}" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>
                </div>

                <!-- Font Size, Alignment, Rotation -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Font Size
                        </label>
                        <input type="number" name="font_size" value="{{ old('font_size', $config->font_size) }}" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Text Alignment
                        </label>
                        <select name="text_align" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                            <option value="center" {{ $config->text_align === 'center' ? 'selected' : '' }}>Center</option>
                            <option value="left" {{ $config->text_align === 'left' ? 'selected' : '' }}>Left</option>
                            <option value="right" {{ $config->text_align === 'right' ? 'selected' : '' }}>Right</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Rotation
                        </label>
                        <select name="rotation" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono">
                            <option value="0" {{ $config->rotation == 0 ? 'selected' : '' }}>0°</option>
                            <option value="90" {{ $config->rotation == 90 ? 'selected' : '' }}>90°</option>
                            <option value="180" {{ $config->rotation == 180 ? 'selected' : '' }}>180°</option>
                        </select>
                    </div>
                </div>

                <!-- Auto-resize checkbox -->
                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                        <input type="checkbox" name="auto_resize" value="1" {{ $config->auto_resize ? 'checked' : '' }}
                            class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Auto-Resize Enabled (High Readability)</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-extrabold text-xs hover:bg-slate-800 transition-colors">
                        Save Printer Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Second-Line Description Options (Origins / Descriptors) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Second-Line Description Options</h2>
                    <p class="text-xs text-slate-500">Configurable origins combined with Product Code (e.g. INDIA AA-01)</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                    {{ $descriptions->count() }} Available
                </span>
            </div>

            <!-- Add Description Form -->
            <form method="POST" action="{{ route('barcode.descriptions.store') }}" class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                @csrf
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Add Origin / Description</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div class="sm:col-span-2">
                        <input type="text" name="name" placeholder="e.g. PAKISTAN, CHINA" required style="text-transform: uppercase;"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-400 font-bold uppercase">
                    </div>
                    <div>
                        <button type="submit" class="w-full py-2 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i> Add
                        </button>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700 pt-1">
                    <input type="checkbox" name="is_default" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                    <span>Set as Default Second-Line Selection</span>
                </label>
            </form>

            <!-- Descriptions Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-3">Description Name</th>
                            <th class="p-3 text-center">Default Status</th>
                            <th class="p-3 pr-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($descriptions as $desc)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-extrabold text-slate-900 tracking-wide font-mono">
                                {{ $desc->name }}
                            </td>
                            <td class="p-3 text-center">
                                @if($desc->is_default)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-star text-amber-500"></i> Default
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('barcode.descriptions.default', $desc) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-amber-600 hover:underline">
                                            Make Default
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="p-3 pr-4 text-right">
                                @if(!$desc->is_default)
                                <form method="POST" action="{{ route('barcode.descriptions.destroy', $desc) }}" class="inline" onsubmit="return confirm('Remove this description?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-slate-400 italic">Locked</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>
@endsection
