@extends('layouts.app')

@section('title', 'Edit User &bull; ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-secondary">Users & Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $user->name }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- HEADER -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Edit User &bull; {{ $user->name }}</h1>
            <p class="text-secondary small mb-0">Update contact phone numbers, IC Card, and password for this user.</p>
        </div>
        <div class="d-flex gap-2">
            @if(!$user->isSuperAdmin())
                <a href="{{ route('users.permissions', $user) }}" class="btn btn-outline-primary fw-semibold">
                    <i class="bi bi-shield-lock me-1"></i> Manage Permissions
                </a>
            @endif
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users List
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h2 class="h6 fw-bold text-dark mb-0">
                        <i class="bi bi-pencil-square text-primary me-2"></i> User Information
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('users.update', $user) }}" class="d-flex flex-column gap-3">
                        @csrf
                        @method('PUT')

                        <!-- Full Name -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- IC Card -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                IC Card / CNIC Number
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="ic_card" class="form-control font-monospace @error('ic_card') is-invalid @enderror" 
                                    value="{{ old('ic_card', $user->ic_card) }}" placeholder="e.g. 35201-1234567-1">
                            </div>
                            @error('ic_card') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone 1 (Primary & Login) -->
                        <div class="p-2.5 rounded-3 bg-primary-subtle border border-primary-subtle">
                            <label class="form-label small fw-bold text-primary mb-1">
                                <i class="bi bi-telephone-fill me-1"></i> Phone 1 (Primary Login Phone) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone1" class="form-control font-monospace fw-bold @error('phone1') is-invalid @enderror" 
                                value="{{ old('phone1', $user->phone1) }}" required>
                            <div class="form-text fs-7 text-primary-emphasis">
                                User uses this phone number to sign into the system.
                            </div>
                            @error('phone1') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone 2 -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Phone 2 (Secondary Contact)
                            </label>
                            <input type="text" name="phone2" class="form-control font-monospace @error('phone2') is-invalid @enderror" 
                                value="{{ old('phone2', $user->phone2) }}">
                            @error('phone2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone 3 -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Phone 3 (Alternative Contact)
                            </label>
                            <input type="text" name="phone3" class="form-control font-monospace @error('phone3') is-invalid @enderror" 
                                value="{{ old('phone3', $user->phone3) }}">
                            @error('phone3') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Address
                            </label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Reset Password -->
                        <div class="p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Reset Password <span class="text-secondary fw-normal">(Leave blank to keep existing password)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-secondary"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control font-monospace @error('password') is-invalid @enderror" 
                                    placeholder="Enter new password to change">
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        @if(!$user->isSuperAdmin())
                            <!-- Active Status -->
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ $user->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="is_active">
                                    Account Active
                                </label>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-2">
                            <a href="{{ route('users.index') }}" class="btn btn-light text-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-check2 me-1"></i> Update User Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
