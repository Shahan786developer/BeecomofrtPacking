<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Bee Comfort') — Inventory & Sales Management</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font: Inter / Public Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- TomSelect Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --bs-font-sans-serif: 'Public Sans', system-ui, -apple-system, sans-serif;
            --bs-primary: #0d6efd;
            --bs-primary-rgb: 13, 110, 253;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f4f6f9;
            color: #212529;
            font-size: 0.95rem;
        }

        /* 32mm x 22mm Thermal Label */
        .thermal-label-container {
            width: 32mm;
            height: 22mm;
            box-sizing: border-box;
            background: #ffffff;
            color: #000000;
            padding: 0.8mm 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            font-family: Arial, sans-serif;
            text-align: center;
            line-height: 1.1;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e3e6ec;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            background: #0d6efd;
            color: #ffffff;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .sidebar-menu {
            padding: 1.25rem 0.85rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-header {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #8c98a4;
            padding: 0.75rem 0.85rem 0.35rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.9rem;
            font-weight: 600;
            font-size: 0.92rem;
            color: #495057;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 3px;
            transition: all 0.15s ease;
        }

        .sidebar-link i {
            font-size: 1.15rem;
            width: 28px;
            color: #6c757d;
            transition: color 0.15s ease;
        }

        .sidebar-link:hover {
            color: #0d6efd;
            background-color: #eef4ff;
        }

        .sidebar-link:hover i {
            color: #0d6efd;
        }

        .sidebar-link.active {
            color: #ffffff;
            background-color: #0d6efd;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
        }

        .sidebar-link.active i {
            color: #ffffff;
        }

        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease;
        }

        .top-navbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid #e3e6ec;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .content-body {
            padding: 2rem;
            flex-grow: 1;
        }

        /* Large Readable Form Controls */
        .form-control, .form-select {
            padding: 0.65rem 0.95rem;
            font-size: 0.95rem;
            border-radius: 8px;
            border-color: #ced4da;
        }

        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: #344054;
            margin-bottom: 0.4rem;
        }

        /* Card Styling */
        .card {
            border: 1px solid #e3e6ec;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #eef0f4;
            padding: 1.1rem 1.4rem;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            font-weight: 700;
        }

        .card-body {
            padding: 1.4rem;
        }

        /* Table Styling */
        .table > :not(caption) > * > * {
            padding: 0.9rem 1rem;
        }

        .table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #6c757d;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 700;
        }

        /* Buttons */
        .btn {
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: -260px;
            }
            .sidebar.show {
                margin-left: 0;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }

        @media print {
            .no-print { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            body { background: #ffffff !important; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar no-print" id="appSidebar">
        <!-- Brand Header -->
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <i class="bi bi-box-seam me-2 fs-4"></i>
            <span>BEE COMFORT</span>
        </a>

        <!-- Sidebar Navigation Menu -->
        <div class="sidebar-menu">
            
            <div class="menu-header">Overview</div>
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard', 'home') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-header">Sales & Billing</div>
            <a href="{{ route('sales.create') }}" class="sidebar-link {{ request()->routeIs('sales.create') ? 'active' : '' }}">
                <i class="bi bi-cart-plus"></i>
                <span>New Sales Invoice</span>
            </a>
            <a href="{{ route('sales.index') }}" class="sidebar-link {{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Invoices History</span>
            </a>
            <a href="{{ route('returns.create') }}" class="sidebar-link {{ request()->routeIs('returns.create') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i>
                <span>Process Return</span>
            </a>
            <a href="{{ route('returns.index') }}" class="sidebar-link {{ request()->routeIs('returns.index', 'returns.show') ? 'active' : '' }}">
                <i class="bi bi-card-checklist"></i>
                <span>Returns History</span>
            </a>

            <div class="menu-header">Barcodes</div>
            <a href="{{ route('barcode.index') }}" class="sidebar-link {{ request()->routeIs('barcode.index') ? 'active' : '' }}">
                <i class="bi bi-upc-scan"></i>
                <span>Print Barcode Stickers</span>
            </a>

            <div class="menu-header">Inventory</div>
            <a href="{{ route('products.create') }}" class="sidebar-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i>
                <span>Register Product</span>
            </a>
            <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.index', 'products.show', 'products.edit') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i>
                <span>Product Catalog</span>
            </a>
            <a href="{{ route('stock.index') }}" class="sidebar-link {{ request()->routeIs('stock.index') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-steps"></i>
                <span>Stock Levels</span>
            </a>
            <a href="{{ route('stock.movements') }}" class="sidebar-link {{ request()->routeIs('stock.movements') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Movement Ledger</span>
            </a>

            <div class="menu-header">Configuration</div>
            <a href="{{ route('pricing.index') }}" class="sidebar-link {{ request()->routeIs('pricing.*') ? 'active' : '' }}">
                <i class="bi bi-tag"></i>
                <span>Pricing Rules</span>
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-folder2"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Customers</span>
            </a>
            <a href="{{ route('barcode.settings') }}" class="sidebar-link {{ request()->routeIs('barcode.settings') ? 'active' : '' }}">
                <i class="bi bi-printer"></i>
                <span>Printer Setup</span>
            </a>

        </div>

        <!-- Sidebar Footer Status -->
        <div class="p-3 border-top bg-light no-print text-center small text-muted">
            <i class="bi bi-check-circle-fill text-success me-1"></i> System Online • 32×22mm Roll
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-wrapper">
        
        <!-- TOP NAVBAR -->
        <header class="top-navbar no-print">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary btn-sm d-lg-none me-3" type="button" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center gap-2 text-secondary small">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">
                        <i class="bi bi-house-door"></i>
                    </a>
                    <span>/</span>
                    @yield('breadcrumb')
                </div>
            </div>

            <!-- Header Quick Action Buttons -->
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-cart-plus me-1"></i> New Sale
                </a>
                <a href="{{ route('products.create') }}" class="btn btn-outline-primary btn-sm px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Product
                </a>
            </div>
        </header>

        <!-- MAIN CONTENT BODY -->
        <main class="content-body">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                        <strong>Please check the following form errors:</strong>
                    </div>
                    <ul class="mb-0 ps-4 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small mt-auto no-print">
            <strong>Bee Comfort</strong> — Inventory, Barcode, Sales and Returns Management System
        </footer>

    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('appSidebar').classList.toggle('show');
        });

        // Initialize TomSelect on searchable dropdowns
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.searchable-select').forEach(function(el) {
                if (!el.tomselect) {
                    new TomSelect(el, {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        placeholder: el.getAttribute('placeholder') || 'Search...',
                    });
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
