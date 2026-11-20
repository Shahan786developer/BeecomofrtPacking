@extends('layouts.app')

@section('title', 'Permissions Matrix &bull; ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-secondary">Users & Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Permissions: {{ $user->name }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- HEADER -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Module Permissions Matrix</h1>
            <p class="text-secondary small mb-0">Configure granular feature permissions (Create, Read, Update, Delete, Print) for <strong>{{ $user->name }}</strong>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit User Info
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users List
            </a>
        </div>
    </div>

    <!-- USER SUMMARY CARD -->
    <div class="card border-0 shadow-sm bg-primary-subtle border-primary-subtle">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.95rem;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                        <div>
                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                            <span class="badge bg-primary text-white">Staff User</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-secondary small fw-bold">Primary Phone (Login)</div>
                    <div class="fw-bold font-monospace text-primary">{{ $user->phone1 ?: 'Not set' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-secondary small fw-bold">IC Card / CNIC</div>
                    <div class="fw-semibold font-monospace text-dark">{{ $user->ic_card ?: 'Not set' }}</div>
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    @if($user->is_active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1.5">
                            <i class="bi bi-check-circle me-1"></i> Account Active
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1.5">
                            <i class="bi bi-x-circle me-1"></i> Deactivated
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- PERMISSION MATRIX FORM -->
    <form method="POST" action="{{ route('users.permissions.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap text-primary fs-5"></i>
                    <h2 class="h6 fw-bold text-dark mb-0">System Modules & Action Access Boxes</h2>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllCheckboxes(true)">
                        <i class="bi bi-check-all me-1"></i> Check All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCheckboxes(false)">
                        <i class="bi bi-dash me-1"></i> Clear All
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-shield-check text-primary fs-5 mt-0.5"></i>
                        <div class="small text-secondary">
                            <strong>How permissions work:</strong>
                            Mark the checkboxes to allow specific functions. If all boxes for a module are unchecked, that module is <strong>completely hidden</strong> from the user's sidebar and route access is blocked with 403 Forbidden. Regular users will also <strong>only see their own created entries</strong> for Sale Orders, Invoices, CRV Receipts, and Returns.
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="min-width: 220px;">Module Name</th>
                                <th class="text-center" style="width: 95px;">
                                    <div class="fw-bold">Create</div>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fs-8 text-secondary" onclick="toggleColumn('create')">all</button>
                                </th>
                                <th class="text-center" style="width: 95px;">
                                    <div class="fw-bold">Read</div>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fs-8 text-secondary" onclick="toggleColumn('read')">all</button>
                                </th>
                                <th class="text-center" style="width: 95px;">
                                    <div class="fw-bold">Update</div>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fs-8 text-secondary" onclick="toggleColumn('update')">all</button>
                                </th>
                                <th class="text-center" style="width: 95px;">
                                    <div class="fw-bold">Delete</div>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fs-8 text-secondary" onclick="toggleColumn('delete')">all</button>
                                </th>
                                <th class="text-center" style="width: 95px;">
                                    <div class="fw-bold">Print</div>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fs-8 text-secondary" onclick="toggleColumn('print')">all</button>
                                </th>
                                <th class="text-end pe-3" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $modKey => $modInfo)
                                @php
                                    $perm = $userPermissions->get($modKey);
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded p-2 bg-primary-subtle text-primary">
                                                <i class="bi {{ $modInfo['icon'] }} fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $modInfo['name'] }}</div>
                                                <div class="text-secondary small">{{ $modInfo['description'] }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Create -->
                                    <td class="text-center">
                                        @if(in_array('create', $modInfo['actions']))
                                            <input type="checkbox" name="permissions[{{ $modKey }}][create]" value="1" 
                                                class="form-check-input perm-box row-{{ $modKey }} col-create" style="width: 20px; height: 20px;"
                                                {{ ($perm && $perm->can_create) ? 'checked' : '' }}>
                                        @else
                                            <span class="text-muted fs-7">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- Read -->
                                    <td class="text-center">
                                        @if(in_array('read', $modInfo['actions']))
                                            <input type="checkbox" name="permissions[{{ $modKey }}][read]" value="1" 
                                                class="form-check-input perm-box row-{{ $modKey }} col-read" style="width: 20px; height: 20px;"
                                                {{ ($perm && $perm->can_read) ? 'checked' : '' }}>
                                        @else
                                            <span class="text-muted fs-7">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- Update -->
                                    <td class="text-center">
                                        @if(in_array('update', $modInfo['actions']))
                                            <input type="checkbox" name="permissions[{{ $modKey }}][update]" value="1" 
                                                class="form-check-input perm-box row-{{ $modKey }} col-update" style="width: 20px; height: 20px;"
                                                {{ ($perm && $perm->can_update) ? 'checked' : '' }}>
                                        @else
                                            <span class="text-muted fs-7">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- Delete -->
                                    <td class="text-center">
                                        @if(in_array('delete', $modInfo['actions']))
                                            <input type="checkbox" name="permissions[{{ $modKey }}][delete]" value="1" 
                                                class="form-check-input perm-box row-{{ $modKey }} col-delete" style="width: 20px; height: 20px;"
                                                {{ ($perm && $perm->can_delete) ? 'checked' : '' }}>
                                        @else
                                            <span class="text-muted fs-7">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- Print -->
                                    <td class="text-center">
                                        @if(in_array('print', $modInfo['actions']))
                                            <input type="checkbox" name="permissions[{{ $modKey }}][print]" value="1" 
                                                class="form-check-input perm-box row-{{ $modKey }} col-print" style="width: 20px; height: 20px;"
                                                {{ ($perm && $perm->can_print) ? 'checked' : '' }}>
                                        @else
                                            <span class="text-muted fs-7">&mdash;</span>
                                        @endif
                                    </td>

                                    <!-- Row toggle -->
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fs-8" 
                                            onclick="toggleRow('{{ $modKey }}')">
                                            Row
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('users.index') }}" class="btn btn-light text-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save Permissions Changes
                </button>
            </div>
        </div>

    </form>

</div>

<script>
    function toggleAllCheckboxes(state) {
        document.querySelectorAll('.perm-box').forEach(cb => cb.checked = state);
    }

    function toggleRow(rowKey) {
        const boxes = document.querySelectorAll('.row-' + rowKey);
        const anyUnchecked = Array.from(boxes).some(cb => !cb.checked);
        boxes.forEach(cb => cb.checked = anyUnchecked);
    }

    function toggleColumn(actionName) {
        const boxes = document.querySelectorAll('.col-' + actionName);
        const anyUnchecked = Array.from(boxes).some(cb => !cb.checked);
        boxes.forEach(cb => cb.checked = anyUnchecked);
    }
</script>
@endsection
