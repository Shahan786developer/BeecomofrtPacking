@extends('layouts.app')

@section('title', 'Pricing Configurations')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pricing Rules</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Pricing Configurations</h1>
            <p class="text-secondary small mb-0">Configure profit markup percentage rules and category rates for automatic selling price calculation.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createPercentageModal">
                <i class="bi bi-plus-lg me-1"></i> Add Percentage Rule
            </button>
            <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryPricingModal">
                <i class="bi bi-folder-plus me-1"></i> Add Category Rate
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('pricing.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Search pricing rules by name, percentage, or category...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-3">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('pricing.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION 1: Percentage Pricing Rules -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">Percentage Pricing Tiers</h5>
                <span class="text-secondary small">Markup options applied to actual purchase prices. The default rule is pre-selected on product registration.</span>
            </div>
            <span class="badge bg-light text-dark border px-2 py-1">
                {{ $percentageRules->count() }} Tiers
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 30%;">Rule Name</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 15%;">Markup %</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 20%;">Default Status</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 15%;">Assigned Items</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 20%;">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($percentageRules as $pRule)
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="fw-bold text-dark d-block fs-6">{{ $pRule->name }}</span>
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
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Set as default pricing rule">
                                        Set as Default
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                {{ $pRule->products->count() }} products
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPricingModal{{ $pRule->id }}" title="Edit Rule">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>

                                @if(!$pRule->is_default && $pRule->products->count() == 0)
                                    <form method="POST" action="{{ route('pricing.destroy', $pRule) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Rule">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Edit Modal for Percentage Rule -->
                            <div class="modal fade text-start" id="editPricingModal{{ $pRule->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('pricing.update', $pRule) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-dark">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Percentage Rule
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body d-flex flex-column gap-3">
                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Rule Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name" value="{{ old('name', $pRule->name) }}" required class="form-control fw-bold">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Markup Percentage (%) <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" step="0.01" min="0" max="1000" name="percentage" value="{{ old('percentage', $pRule->percentage) }}" required class="form-control font-monospace fw-bold">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description (Optional)</label>
                                                    <input type="text" name="description" value="{{ old('description', $pRule->description) }}" class="form-control">
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editDef{{ $pRule->id }}" {{ $pRule->is_default ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-semibold text-dark" for="editDef{{ $pRule->id }}">
                                                        Set as Default Pricing Rule
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
                        <td colspan="5" class="text-center py-4 text-secondary">No percentage pricing tiers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2: Category Pricing Configurations -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">Category Specific Rates</h5>
                <span class="text-secondary small">Markup percentages bound to specific product categories.</span>
            </div>
            <span class="badge bg-light text-dark border px-2 py-1">
                {{ $categoryRules->count() }} Rates
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 25%;">Target Category</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 30%;">Rule Name</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 15%;">Markup %</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 15%;">Assigned Items</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($categoryRules as $cRule)
                    <tr>
                        <td class="ps-4 py-3 fw-bold text-dark">
                            <i class="bi bi-folder2 text-primary me-1"></i> {{ $cRule->category->name ?? 'Unassigned' }}
                        </td>
                        <td class="py-3 text-secondary">
                            {{ $cRule->name }}
                        </td>
                        <td class="py-3 text-center font-monospace fw-bold text-primary fs-6">
                            +{{ number_format($cRule->percentage, 2) }}%
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                {{ $cRule->products->count() }} products
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryRuleModal{{ $cRule->id }}" title="Edit Rule">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>

                                @if($cRule->products->count() == 0)
                                    <form method="POST" action="{{ route('pricing.destroy', $cRule) }}" class="d-inline" onsubmit="return confirm('Delete this category rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Rule">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Edit Modal for Category Rule -->
                            <div class="modal fade text-start" id="editCategoryRuleModal{{ $cRule->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('pricing.update', $cRule) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-dark">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Category Rate
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body d-flex flex-column gap-3">
                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Category <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="category_id" required class="form-select">
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}" {{ $cRule->category_id == $cat->id ? 'selected' : '' }}>
                                                                {{ $cat->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Markup Percentage (%) <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" step="0.01" min="0" max="1000" name="percentage" value="{{ old('percentage', $cRule->percentage) }}" required class="form-control font-monospace fw-bold">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Rule Name</label>
                                                    <input type="text" name="name" value="{{ old('name', $cRule->name) }}" required class="form-control">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description (Optional)</label>
                                                    <input type="text" name="description" value="{{ old('description', $cRule->description) }}" class="form-control">
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
                        <td colspan="5" class="text-center py-4 text-secondary">No category-specific pricing rules found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal 1: Create Percentage Rule -->
<div class="modal fade" id="createPercentageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('pricing.percentage.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-percent text-primary me-2"></i> Add Percentage Rule
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Rule Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" placeholder="e.g. Retail 22%, Wholesale 15%" required class="form-control fw-bold">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Markup Percentage (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="1000" name="percentage" placeholder="e.g. 22" required class="form-control font-monospace fw-bold">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description (Optional)</label>
                        <input type="text" name="description" placeholder="Notes or remarks" class="form-control">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="newPercDefault">
                        <label class="form-check-label small fw-semibold text-dark" for="newPercDefault">
                            Set as Default Rule (pre-selected on product registration)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Add Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Create Category Rule -->
<div class="modal fade" id="createCategoryPricingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('pricing.category.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-folder-plus text-primary me-2"></i> Add Category Rate
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" required class="form-select">
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
                        <input type="number" step="0.01" min="0" max="1000" name="percentage" placeholder="e.g. 40" required class="form-control font-monospace fw-bold">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Rule Name (Optional)</label>
                        <input type="text" name="name" placeholder="Leave blank for auto-generated name" class="form-control">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description (Optional)</label>
                        <input type="text" name="description" placeholder="Notes" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Add Category Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
