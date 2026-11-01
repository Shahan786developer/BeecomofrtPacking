@extends('layouts.app')

@section('title', 'Dozens Units')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dozens Units</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Dozens Packaging Units</h1>
            <p class="text-secondary small mb-0">Manage packaging pack quantities (e.g. 12 pieces, 6 pieces) and configure the system default pre-selected unit.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createDozenModal">
                <i class="bi bi-plus-lg me-1"></i> Add Dozen Unit
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('dozens.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Search dozen units by label or quantity..." autocomplete="off">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-3">Search</button>
                    <a href="{{ route('dozens.index') }}" class="btn btn-outline-secondary btn-clear-search {{ !empty($search) ? '' : 'd-none' }}">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Dozens List Table -->
    <div id="table-container">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 30%;">Unit Label</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 20%;">Pieces / Pack</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 20%;">Default Status</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 15%;">Products</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($dozens as $dozen)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-dark fs-6">{{ $dozen->label }}</span>
                            </td>
                            <td class="py-3 text-center font-monospace fw-bold fs-6 text-primary">
                                {{ $dozen->quantity }} pcs
                            </td>
                            <td class="py-3 text-center">
                                @if($dozen->is_default)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Default Unit
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('dozens.default', $dozen) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2 fs-7" title="Set as default pre-selected option">
                                            Set as Default
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                    {{ $dozen->products_count }}
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <!-- Edit Button (Modal) -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDozenModal{{ $dozen->id }}" title="Edit Dozen Unit">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>

                                    <!-- Delete Button (Only if 0 products) -->
                                    @if($dozen->products_count == 0)
                                        <form method="POST" action="{{ route('dozens.destroy', $dozen) }}" class="d-inline" onsubmit="return confirm('Delete this dozen unit?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Dozen Unit">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade text-start" id="editDozenModal{{ $dozen->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('dozens.update', $dozen) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="bi bi-pencil-square text-primary me-2"></i> Edit Dozen Unit
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body d-flex flex-column gap-3">
                                                    <div>
                                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                            Unit Label <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" name="label" value="{{ old('label', $dozen->label) }}" required class="form-control fw-bold" placeholder="e.g. 12 pieces, 6 pieces">
                                                    </div>

                                                    <div>
                                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                            Pieces / Quantity <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="number" min="1" max="1000" name="quantity" value="{{ old('quantity', $dozen->quantity) }}" required class="form-control font-monospace fw-bold">
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editDef{{ $dozen->id }}" {{ $dozen->is_default ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-semibold text-dark" for="editDef{{ $dozen->id }}">
                                                            Set as Default Pre-selected Dozen Unit
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">
                                <i class="bi bi-boxes fs-1 d-block mb-2 text-muted"></i>
                                No dozen units found matching your search.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($dozens->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $dozens->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Create Dozen Modal -->
<div class="modal fade" id="createDozenModal" tabindex="-1" aria-labelledby="createDozenModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dozens.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="createDozenModalLabel">
                        <i class="bi bi-plus-circle text-primary me-2"></i> Add Dozen Unit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Unit Label <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="label" required placeholder="e.g. 12 pieces, 6 pieces, 24 pieces" class="form-control fw-bold">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Pieces / Quantity in Pack <span class="text-danger">*</span>
                        </label>
                        <input type="number" min="1" max="1000" name="quantity" value="12" required class="form-control font-monospace fw-bold">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="createDef">
                        <label class="form-check-label small fw-semibold text-dark" for="createDef">
                            Set as Default Pre-selected Unit
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Dozen Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
