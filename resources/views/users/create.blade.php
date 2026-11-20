@extends('layouts.app')

@section('title', 'Register New User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-secondary">Users & Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Register User</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- HEADER -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Register New User</h1>
            <p class="text-secondary small mb-0">Create a staff account with IC card, 3 contact phone numbers, and module access permissions.</p>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users List
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="row g-3">

            <!-- LEFT COLUMN: USER DETAILS -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h2 class="h6 fw-bold text-dark mb-0">
                            <i class="bi bi-person-badge text-primary me-2"></i> User Personal & Login Details
                        </h2>
                    </div>
                    <div class="card-body p-3 d-flex flex-column gap-3">

                        <!-- Full Name -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" placeholder="e.g. Muhammad Waqas" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- IC Card / CNIC -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                IC Card / CNIC Number
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="ic_card" class="form-control font-monospace @error('ic_card') is-invalid @enderror" 
                                    value="{{ old('ic_card') }}" placeholder="e.g. 35201-1234567-1">
                            </div>
                            @error('ic_card') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone 1 (Primary & Login) -->
                        <div class="p-2.5 rounded-3 bg-primary-subtle border border-primary-subtle">
                            <label class="form-label small fw-bold text-primary mb-1">
                                <i class="bi bi-telephone-fill me-1"></i> Phone 1 (Primary Login Phone) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone1" class="form-control font-monospace fw-bold @error('phone1') is-invalid @enderror" 
                                value="{{ old('phone1') }}" placeholder="e.g. 03001234567" required>
                            <div class="form-text fs-7 text-primary-emphasis">
                                User will log into the system using this phone number and password.
                            </div>
                            @error('phone1') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone 2 (Optional) -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Phone 2 (Secondary Contact)
                            </label>
                            <input type="text" name="phone2" class="form-control font-monospace @error('phone2') is-invalid @enderror" 
                                value="{{ old('phone2') }}" placeholder="e.g. 03219876543">
                            @error('phone2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone 3 (Optional) -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Phone 3 (Alternative Contact)
                            </label>
                            <input type="text" name="phone3" class="form-control font-monospace @error('phone3') is-invalid @enderror" 
                                value="{{ old('phone3') }}" placeholder="e.g. 03334567890">
                            @error('phone3') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Residential / Office Address -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Address
                            </label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" 
                                placeholder="Complete street address, shop or city">{{ old('address') }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="form-label small fw-bold text-secondary mb-1">
                                System Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-lock"></i></span>
                                <input type="text" name="password" id="userPassword" class="form-control font-monospace fw-bold @error('password') is-invalid @enderror" 
                                    value="{{ old('password', '123456') }}" placeholder="Assign a secure password" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPass()">
                                    <i class="bi bi-dice-5"></i> Generate
                                </button>
                            </div>
                            <div class="form-text fs-7 text-muted">
                                Provide this password to the user to enable them to sign in.
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <!-- Account Status -->
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                            <label class="form-check-label fw-bold text-dark small" for="is_active">
                                Account Active (Can log in immediately)
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: MODULE PERMISSIONS MATRIX -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h2 class="h6 fw-bold text-dark mb-0">
                            <i class="bi bi-shield-check text-primary me-2"></i> Assign Module Permissions
                        </h2>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fs-7" onclick="toggleAllCheckboxes(true)">
                                Check All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fs-7" onclick="toggleAllCheckboxes(false)">
                                Clear All
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 bg-light border-bottom">
                            <p class="small text-secondary mb-0">
                                <i class="bi bi-info-circle text-primary me-1"></i>
                                Check the allowed actions for each module. If all 5 boxes for a module remain unchecked, that module will be <strong>completely hidden</strong> from the user.
                            </p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Module Name</th>
                                        <th class="text-center" style="width: 70px;">Create</th>
                                        <th class="text-center" style="width: 70px;">Read</th>
                                        <th class="text-center" style="width: 70px;">Update</th>
                                        <th class="text-center" style="width: 70px;">Delete</th>
                                        <th class="text-center" style="width: 70px;">Print</th>
                                        <th class="text-end pe-3" style="width: 80px;">Row</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modules as $modKey => $modInfo)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded p-1.5 bg-primary-subtle text-primary">
                                                        <i class="bi {{ $modInfo['icon'] }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark small">{{ $modInfo['name'] }}</div>
                                                        <div class="text-secondary fs-8">{{ $modInfo['description'] }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Create Checkbox -->
                                            <td class="text-center">
                                                @if(in_array('create', $modInfo['actions']))
                                                    <input type="checkbox" name="permissions[{{ $modKey }}][create]" value="1" 
                                                        class="form-check-input perm-box row-{{ $modKey }} col-create"
                                                        {{ old("permissions.{$modKey}.create") ? 'checked' : '' }}>
                                                @else
                                                    <span class="text-muted fs-7">&mdash;</span>
                                                @endif
                                            </td>

                                            <!-- Read Checkbox -->
                                            <td class="text-center">
                                                @if(in_array('read', $modInfo['actions']))
                                                    <input type="checkbox" name="permissions[{{ $modKey }}][read]" value="1" 
                                                        class="form-check-input perm-box row-{{ $modKey }} col-read"
                                                        {{ old("permissions.{$modKey}.read") ? 'checked' : '' }}>
                                                @else
                                                    <span class="text-muted fs-7">&mdash;</span>
                                                @endif
                                            </td>

                                            <!-- Update Checkbox -->
                                            <td class="text-center">
                                                @if(in_array('update', $modInfo['actions']))
                                                    <input type="checkbox" name="permissions[{{ $modKey }}][update]" value="1" 
                                                        class="form-check-input perm-box row-{{ $modKey }} col-update"
                                                        {{ old("permissions.{$modKey}.update") ? 'checked' : '' }}>
                                                @else
                                                    <span class="text-muted fs-7">&mdash;</span>
                                                @endif
                                            </td>

                                            <!-- Delete Checkbox -->
                                            <td class="text-center">
                                                @if(in_array('delete', $modInfo['actions']))
                                                    <input type="checkbox" name="permissions[{{ $modKey }}][delete]" value="1" 
                                                        class="form-check-input perm-box row-{{ $modKey }} col-delete"
                                                        {{ old("permissions.{$modKey}.delete") ? 'checked' : '' }}>
                                                @else
                                                    <span class="text-muted fs-7">&mdash;</span>
                                                @endif
                                            </td>

                                            <!-- Print Checkbox -->
                                            <td class="text-center">
                                                @if(in_array('print', $modInfo['actions']))
                                                    <input type="checkbox" name="permissions[{{ $modKey }}][print]" value="1" 
                                                        class="form-check-input perm-box row-{{ $modKey }} col-print"
                                                        {{ old("permissions.{$modKey}.print") ? 'checked' : '' }}>
                                                @else
                                                    <span class="text-muted fs-7">&mdash;</span>
                                                @endif
                                            </td>

                                            <!-- Row toggle -->
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fs-8" 
                                                    onclick="toggleRow('{{ $modKey }}')">
                                                    Toggle
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Permissions can be modified anytime later.</span>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save & Register User
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </form>

</div>

<script>
    function generateRandomPass() {
        const chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        let pass = '';
        for (let i = 0; i < 8; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('userPassword').value = pass;
    }

    function toggleAllCheckboxes(state) {
        document.querySelectorAll('.perm-box').forEach(cb => cb.checked = state);
    }

    function toggleRow(rowKey) {
        const boxes = document.querySelectorAll('.row-' + rowKey);
        const anyUnchecked = Array.from(boxes).some(cb => !cb.checked);
        boxes.forEach(cb => cb.checked = anyUnchecked);
    }
</script>
@endsection
