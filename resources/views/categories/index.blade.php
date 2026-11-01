@extends('layouts.app')

@section('title', 'Product Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Product Categories</h1>
            <p class="text-secondary small mb-0">Manage product categories. The default category is automatically pre-selected when registering products.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-lg me-1"></i> Create Category
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('categories.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Search categories by name or description...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-3">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 25%;">Category Name</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 35%;">Description</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 15%;">Default</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 10%;">Products</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 15%;">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($categories as $cat)
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="fw-bold text-dark fs-6">{{ $cat->name }}</span>
                        </td>
                        <td class="py-3 text-secondary small">
                            {{ $cat->description ?: '—' }}
                        </td>
                        <td class="py-3 text-center">
                            @if($cat->is_default)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Default
                                </span>
                            @else
                                <form method="POST" action="{{ route('categories.default', $cat) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Set as default category">
                                        Set as Default
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                {{ $cat->products_count }} items
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}" title="Edit Category">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>

                                @if($cat->products_count == 0)
                                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Category">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Edit Modal for this category -->
                            <div class="modal fade text-start" id="editCategoryModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('categories.update', $cat) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-dark">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Category
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body d-flex flex-column gap-3">
                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Category Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name" value="{{ old('name', $cat->name) }}" required class="form-control fw-bold">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Description
                                                    </label>
                                                    <textarea name="description" rows="3" class="form-control" placeholder="Fabric, material, or line notes">{{ old('description', $cat->description) }}</textarea>
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
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-muted"></i>
                            No categories found matching your query.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="createCategoryModalLabel">
                        <i class="bi bi-folder-plus text-primary me-2"></i> Create New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="e.g. Silk Touch, Active Sport" class="form-control fw-bold">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Description (Optional)
                        </label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Fabric, material, or line notes"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="createCatDefault">
                        <label class="form-check-label small fw-semibold text-dark" for="createCatDefault">
                            Set as Default Category (automatically pre-selected on product registration)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
