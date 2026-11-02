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
            <p class="text-secondary small mb-0">Hardware specifications for standard 32mm &times; 22mm thermal roll sticker printing.</p>
        </div>
        <div>
            <a href="{{ route('barcode.index') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-printer me-1"></i> Go to Barcode Studio
            </a>
        </div>
    </div>

    <!-- Configuration Card -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Thermal Label Specifications</h5>
                        <span class="text-secondary small">Hardware parameters for 32 mm &times; 22 mm thermal roll output</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                        32 mm &times; 22 mm
                    </span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('barcode.settings.update') }}" class="d-flex flex-column gap-3">
                        @csrf

                        <!-- Brand Name (Line 1) -->
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Default Brand Name (Line 1)
                            </label>
                            <input type="text" name="brand_name" value="{{ old('brand_name', $config->brand_name) }}" required class="form-control fw-bold">
                            <div class="form-text small text-secondary">
                                Prints as the prominent first header on every sticker (e.g. <strong>BEE COMFORT</strong>). Line 2 is auto-fetched as <em>[Category] [Product Code]</em>.
                            </div>
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
                                Auto-Resize Enabled (Crisp 1D lines for thermal roll printers)
                            </label>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                Save Printer Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
