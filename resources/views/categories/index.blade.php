@extends('layouts.app')

@section('title', 'Product Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Product Categories</h1>
            <p class="text-secondary small mb-0">Manage master product lines (the first prefix segment of the unified product title).</p>
        </div>
        <div>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Register Product with Category
            </a>
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="row g-4 align-items-start">

        <!-- Add Category Form (Col 4) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-folder-plus text-primary me-2"></i> New Category
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('categories.store') }}" class="d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" required placeholder="e.g. Silk Touch, Active Sport" class="form-control fw-bold">
                            <div class="form-text small text-secondary">Appears as prefix in combined Product Title</div>
                        </div>

                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Description (Optional)
                            </label>
                            <textarea name="description" rows="2" placeholder="Fabric, material, or line notes" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold mt-2">
                            Save Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories Table (Col 8) -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Registered Categories</h5>
                        <span class="text-secondary small">Categories selectable during product registration</span>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1">
                        {{ $categories->total() }} Categories
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Category Name</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Description</th>
                                <th class="py-3 text-center text-secondary text-uppercase fs-7">Assigned Products</th>
                                <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($categories as $cat)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark d-block fs-6">{{ $cat->name }}</span>
                                </td>
                                <td class="py-3 text-secondary small">
                                    {{ $cat->description ?? '—' }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                        {{ $cat->products_count }} products
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    @if($cat->products_count == 0)
                                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-light text-secondary border small">In Use</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-secondary">No categories registered yet.</td>
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

    </div>

</div>
@endsection
