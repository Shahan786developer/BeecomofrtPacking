@extends('layouts.app')

@section('title', 'User Management & Permissions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Users & Permissions</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">User Management & Permissions</h1>
            <p class="text-secondary small mb-0">Register staff users, assign Phone 1 login credentials, and configure granular module permissions.</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-person-plus me-1"></i> + Register New User
            </a>
        </div>
    </div>

    <!-- STATS SUMMARY CARDS -->
    <div class="row g-3">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Total Registered Users</span>
                        <div class="h3 fw-black text-dark mb-0 font-monospace">{{ $totalUsers }}</div>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Active Accounts</span>
                        <div class="h3 fw-black text-success mb-0 font-monospace">{{ $activeUsers }}</div>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-3">
                        <i class="bi bi-person-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Deactivated Accounts</span>
                        <div class="h3 fw-black text-danger mb-0 font-monospace">{{ $inactiveUsers }}</div>
                    </div>
                    <div class="rounded-3 bg-danger-subtle text-danger p-3">
                        <i class="bi bi-person-x fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-7 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search by name, IC Card, Phone 1, 2, 3, or email..." 
                            class="form-control border-start-0 ps-0" autocomplete="off">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Clear Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- USERS TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>User & Role</th>
                        <th>IC Card</th>
                        <th>Phone Numbers</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Permitted Modules</th>
                        <th class="text-end pe-3" style="min-width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3 fw-bold text-secondary font-monospace">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle {{ $user->isSuperAdmin() ? 'bg-primary' : 'bg-secondary' }} text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <span class="fw-bold text-dark d-block">{{ $user->name }}</span>
                                        @if($user->isSuperAdmin())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="bi bi-shield-check me-1"></i> Super Admin
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border">
                                                Staff User
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->ic_card)
                                    <span class="font-monospace text-dark fw-semibold">{{ $user->ic_card }}</span>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-0.5">
                                    @if($user->phone1)
                                        <div class="small fw-bold text-primary font-monospace">
                                            <i class="bi bi-telephone-fill me-1 fs-7"></i> {{ $user->phone1 }}
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0 px-1 fs-8">Login</span>
                                        </div>
                                    @endif
                                    @if($user->phone2)
                                        <div class="small text-secondary font-monospace">
                                            <i class="bi bi-telephone me-1 fs-7"></i> {{ $user->phone2 }}
                                        </div>
                                    @endif
                                    @if($user->phone3)
                                        <div class="small text-secondary font-monospace">
                                            <i class="bi bi-telephone me-1 fs-7"></i> {{ $user->phone3 }}
                                        </div>
                                    @endif
                                    @if(!$user->phone1 && !$user->phone2 && !$user->phone3)
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="small text-secondary text-truncate d-inline-block" style="max-width: 180px;" title="{{ $user->address }}">
                                    {{ $user->address ?: '—' }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-x-circle me-1"></i> Deactivated
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge bg-dark text-white">Full System Access</span>
                                @else
                                    @php
                                        $allowedCount = $user->permissions->filter(function($p) {
                                            return $p->can_read || $p->can_create || $p->can_update || $p->can_delete || $p->can_print;
                                        })->count();
                                    @endphp
                                    @if($allowedCount > 0)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                            {{ $allowedCount }} Active Module(s)
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            No Access Assigned
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    @if(!$user->isSuperAdmin())
                                        <!-- Permissions Matrix Button -->
                                        <a href="{{ route('users.permissions', $user) }}" class="btn btn-sm btn-outline-primary" title="Configure Module Permissions">
                                            <i class="bi bi-shield-lock me-1"></i> Permissions
                                        </a>
                                    @endif

                                    <!-- Edit User Info -->
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Edit User & Password">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if(!$user->isSuperAdmin())
                                        <!-- Toggle Status (Activate / Deactivate) -->
                                        <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                title="{{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                                <i class="bi {{ $user->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Delete User -->
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete user {{ $user->name }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-1 text-secondary"></i>
                                No users found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
