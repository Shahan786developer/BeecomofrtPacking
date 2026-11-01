<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login &bull; Bee Comfort</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            padding: 1.5rem;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .brand-header {
            background: #0d6efd;
            color: #ffffff;
            padding: 2.25rem 2rem;
            text-align: center;
        }

        .brand-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 0.25rem;
        }

        .brand-header p {
            font-size: 0.85rem;
            opacity: 0.88;
            margin-bottom: 0;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .credentials-hint {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            font-size: 0.82rem;
            color: #475569;
        }
    </style>
</head>
<body>

    <div class="login-card">
        
        <!-- Header -->
        <div class="brand-header">
            <div class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle mb-3" style="width: 52px; height: 52px;">
                <i class="bi bi-box-seam fs-3"></i>
            </div>
            <h1>BEE COMFORT</h1>
            <p>Super Admin Portal &bull; System Authentication</p>
        </div>

        <!-- Body -->
        <div class="p-4 p-sm-4">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="d-flex flex-column gap-3">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="form-control border-start-0 ps-0 fw-semibold @error('email') is-invalid @enderror"
                            placeholder="admin@domain.com">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                        Password <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="form-control border-start-0 border-end-0 ps-0 font-monospace fw-bold @error('password') is-invalid @enderror"
                            placeholder="Enter password">
                        <button class="btn btn-outline-secondary bg-white border-start-0 text-secondary" type="button" id="togglePasswordBtn">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small fw-semibold text-secondary" for="remember">
                            Remember session
                        </label>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
                        Protected System
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100 shadow-sm mt-1">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Dashboard
                </button>
            </form>

        </div>

        <div class="card-footer bg-light border-top py-3 text-center text-secondary small">
            &copy; {{ date('Y') }} <strong>Bee Comfort</strong> &bull; All Rights Reserved
        </div>

    </div>

    <!-- Toggle Password Visibility Script -->
    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
