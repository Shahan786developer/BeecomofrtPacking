@extends('layouts.app')

@section('title', 'Thermal Barcode Configuration')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('barcode.index') }}" class="text-decoration-none">Barcode Labels</a></li>
    <li class="breadcrumb-item active" aria-current="page">Configuration</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Thermal Barcode Configuration</h1>
            <p class="text-secondary small mb-0">Manage 32mm &times; 22mm roll label dimensions, brand typography, and second-line origin descriptions.</p>
        </div>
        <div>
            <a href="{{ route('barcode.index') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-printer me-1"></i> Go to Barcode Studio
            </a>
        </div>
    </div>

    <!-- Configuration Grid -->
    <div class="row g-4">

        <!-- 1. Thermal Printer Hardware Parameters -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Thermal Label Specifications</h5>
                        <span class="text-secondary small">Hardware parameters for thermal roll output</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                        32 mm &times; 22 mm
                    </span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('barcode.settings.update') }}" class="d-flex flex-column gap-3">
                        @csrf

                        <!-- Brand Name -->
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                1. First Line: Brand Name
                            </label>
                            <input type="text" name="brand_name" value="{{ old('brand_name', $config->brand_name) }}" required class="form-control fw-bold">
                            <div class="form-text small text-secondary">Prints as the prominent header on the first line (e.g., BEE COMFORT)</div>
                        </div>

                        <!-- Dimensions -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Width (mm)</label>
                                <input type="number" step="0.1" name="label_width_mm" value="{{ old('label_width_mm', $config->label_width_mm) }}" required class="form-control font-monospace fw-bold">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Height (mm)</label>
                                <input type="number" step="0.1" name="label_height_mm" value="{{ old('label_height_mm', $config->label_height_mm) }}" required class="form-control font-monospace fw-bold">
                            </div>
                        </div>

                        <!-- Barcode Standard & Font -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Barcode Standard</label>
                                <input type="text" name="barcode_type" value="{{ old('barcode_type', $config->barcode_type) }}" readonly class="form-control bg-light font-monospace fw-bold text-secondary">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Font Family</label>
                                <input type="text" name="font_family" value="{{ old('font_family', $config->font_family) }}" required class="form-control">
                            </div>
                        </div>

                        <!-- Font Size, Align, Rotation -->
                        <div class="row g-3">
                            <div class="col-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Font Size (pt)</label>
                                <input type="number" name="font_size" value="{{ old('font_size', $config->font_size) }}" required class="form-control font-monospace">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Alignment</label>
                                <select name="text_align" class="form-select">
                                    <option value="center" {{ $config->text_align === 'center' ? 'selected' : '' }}>Center</option>
                                    <option value="left" {{ $config->text_align === 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="right" {{ $config->text_align === 'right' ? 'selected' : '' }}>Right</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Rotation</label>
                                <select name="rotation" class="form-select font-monospace">
                                    <option value="0" {{ $config->rotation == 0 ? 'selected' : '' }}>0&deg;</option>
                                    <option value="90" {{ $config->rotation == 90 ? 'selected' : '' }}>90&deg;</option>
                                    <option value="180" {{ $config->rotation == 180 ? 'selected' : '' }}>180&deg;</option>
                                </select>
                            </div>
                        </div>

                        <!-- Auto-resize checkbox -->
                        <div class="form-check pt-2">
                            <input class="form-check-input" type="checkbox" name="auto_resize" id="autoResizeCheck" value="1" {{ $config->auto_resize ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold text-dark" for="autoResizeCheck">
                                Auto-Resize Enabled (High Readability for Thermal Roll Printers)
                            </label>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold">
                                Save Printer Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Second-Line Description Options -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Second-Line Description Options</h5>
                        <span class="text-secondary small">Origin descriptors combined with Product Code (e.g. INDIA AA-01)</span>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1">
                        {{ $descriptions->count() }} Available
                    </span>
                </div>

                <div class="card-body p-4 d-flex flex-column gap-3">
                    <!-- Add Description Form -->
                    <form method="POST" action="{{ route('barcode.descriptions.store') }}" class="p-3 bg-light rounded-3 border">
                        @csrf
                        <span class="form-label small fw-bold text-secondary text-uppercase d-block mb-2">Add New Origin / Description</span>
                        <div class="row g-2 align-items-center">
                            <div class="col-sm-8">
                                <input type="text" name="name" placeholder="e.g. PAKISTAN, CHINA" required style="text-transform: uppercase;" class="form-control text-uppercase fw-bold">
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-primary w-100 fw-bold">
                                    <i class="bi bi-plus-lg me-1"></i> Add
                                </button>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_default" id="isDefaultCheck" value="1">
                            <label class="form-check-label small text-secondary" for="isDefaultCheck">
                                Set as default second-line selection
                            </label>
                        </div>
                    </form>

                    <!-- Descriptions Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 text-secondary text-uppercase fs-7">Description Name</th>
                                    <th class="py-2 text-center text-secondary text-uppercase fs-7">Status</th>
                                    <th class="pe-3 py-2 text-end text-secondary text-uppercase fs-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($descriptions as $desc)
                                <tr>
                                    <td class="py-3 font-monospace fw-bold text-dark">
                                        {{ $desc->name }}
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($desc->is_default)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-star-fill me-1 text-warning"></i> Default
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('barcode.descriptions.default', $desc) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-link btn-sm text-secondary text-decoration-none p-0 small">
                                                    Make Default
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        @if(!$desc->is_default)
                                        <form method="POST" action="{{ route('barcode.descriptions.destroy', $desc) }}" class="d-inline" onsubmit="return confirm('Remove this description?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-secondary small fst-italic">Locked</span>
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

    </div>

</div>
@endsection
