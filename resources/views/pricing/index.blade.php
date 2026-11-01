@extends('layouts.app')

@section('title', 'Pricing Configurations')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pricing Rules</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Pricing Configurations</h1>
            <p class="text-secondary small mb-0">Configure percentage tiers and category-based pricing used during product registration.</p>
        </div>
    </div>

    <!-- SECTION 1: PERCENTAGE PRICING RULES -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">Percentage Pricing Tiers</h5>
                <span class="text-secondary small">Markup percentage options. The designated default is pre-selected on product registration.</span>
            </div>
            <span class="badge bg-light text-dark border px-2 py-1">
                {{ $percentageRules->count() }} Tiers
            </span>
        </div>

        <div class="card-body p-4">
            <div class="row g-4 align-items-start">

                <!-- New Percentage Form (Col 4) -->
                <div class="col-12 col-lg-4">
                    <div class="p-4 bg-light rounded-3 border">
                        <h6 class="fw-bold text-secondary text-uppercase mb-3">Add Percentage Rule</h6>
                        <form method="POST" action="{{ route('pricing.percentage.store') }}" class="d-flex flex-column gap-3">
                            @csrf
                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Rule Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" placeholder="e.g. Retail 22%" required class="form-control fw-bold bg-white">
                            </div>

                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Markup Percentage (%) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" max="1000" name="percentage" placeholder="e.g. 22" required class="form-control font-monospace fw-bold bg-white">
                            </div>

                            <!-- Boolean Yes/No Option (Requirement #8) -->
                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Set as Default Rule?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_default" id="defaultYes" value="1">
                                        <label class="form-check-label small fw-semibold" for="defaultYes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_default" id="defaultNo" value="0" checked>
                                        <label class="form-check-label small fw-semibold" for="defaultNo">No</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description (Optional)</label>
                                <input type="text" name="description" placeholder="Notes or remarks" class="form-control bg-white">
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold mt-2">
                                Add Percentage Rule
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Existing Percentage Records List (Col 8) -->
                <div class="col-12 col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Rule Name</th>
                                    <th class="py-3 text-center text-secondary text-uppercase fs-7">Markup</th>
                                    <th class="py-3 text-center text-secondary text-uppercase fs-7">Status</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7">Products</th>
                                    <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Action</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($percentageRules as $pRule)
                                <tr>
                                    <td class="ps-3 py-3">
                                        <span class="fw-bold text-dark d-block">{{ $pRule->name }}</span>
                                        @if($pRule->description)
                                            <span class="text-secondary small">{{ $pRule->description }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center font-monospace fw-bold text-primary fs-6">
                                        +{{ number_format($pRule->percentage, 2) }}%
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($pRule->is_default)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-star-fill text-warning me-1"></i> Default Rule
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('pricing.default', $pRule) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-link btn-sm text-secondary text-decoration-none p-0 small">
                                                    Make Default
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="py-3 font-monospace text-secondary small">
                                        {{ $pRule->products->count() }} items
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        @if(!$pRule->is_default)
                                        <form method="POST" action="{{ route('pricing.destroy', $pRule) }}" class="d-inline" onsubmit="return confirm('Delete this pricing rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-secondary small fst-italic">Default</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">No percentage pricing rules found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SECTION 2: CATEGORY PRICING RULES -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">Category Pricing Configurations</h5>
                <span class="text-secondary small">Category-specific pricing rules linked through searchable foreign-key category selection.</span>
            </div>
            <span class="badge bg-light text-dark border px-2 py-1">
                {{ $categoryRules->count() }} Rules
            </span>
        </div>

        <div class="card-body p-4">
            <div class="row g-4 align-items-start">

                <!-- New Category Pricing Form (Col 4) -->
                <div class="col-12 col-lg-4">
                    <div class="p-4 bg-light rounded-3 border">
                        <h6 class="fw-bold text-secondary text-uppercase mb-3">Add Category Rule</h6>
                        <form method="POST" action="{{ route('pricing.category.store') }}" class="d-flex flex-column gap-3">
                            @csrf
                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select name="category_id" required class="searchable-select form-select">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Markup Percentage (%) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" max="1000" name="percentage" placeholder="e.g. 40" required class="form-control font-monospace fw-bold bg-white">
                            </div>

                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Rule Name (Optional)</label>
                                <input type="text" name="name" placeholder="Leave empty for auto-name" class="form-control bg-white">
                            </div>

                            <div>
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description (Optional)</label>
                                <input type="text" name="description" placeholder="Notes" class="form-control bg-white">
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold mt-2">
                                Add Category Rule
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Existing Category Rules Table (Col 8) -->
                <div class="col-12 col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Category</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7">Rule Name</th>
                                    <th class="py-3 text-center text-secondary text-uppercase fs-7">Markup</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7">Products</th>
                                    <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Action</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($categoryRules as $cRule)
                                <tr>
                                    <td class="ps-3 py-3 fw-bold text-dark">
                                        {{ $cRule->category->name ?? 'Unassigned' }}
                                    </td>
                                    <td class="py-3 text-secondary">
                                        {{ $cRule->name }}
                                    </td>
                                    <td class="py-3 text-center font-monospace fw-bold text-primary fs-6">
                                        +{{ number_format($cRule->percentage, 2) }}%
                                    </td>
                                    <td class="py-3 font-monospace text-secondary small">
                                        {{ $cRule->products->count() }} items
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        <form method="POST" action="{{ route('pricing.destroy', $cRule) }}" class="d-inline" onsubmit="return confirm('Delete this category rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">No category pricing rules configured yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
