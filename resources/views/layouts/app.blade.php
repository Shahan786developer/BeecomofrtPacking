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
    <!-- Google Font: Public Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

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
            background-color: #f8fafc;
            color: #1e293b;
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

        /* Clean Minimal Sidebar */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            height: 68px;
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
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .menu-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            padding: 0.75rem 0.75rem 0.25rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #334155;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .sidebar-link i {
            font-size: 1.25rem;
            color: #64748b;
            transition: color 0.15s ease;
        }

        .sidebar-link:hover {
            color: #0d6efd;
            background-color: #f1f5f9;
        }

        .sidebar-link:hover i {
            color: #0d6efd;
        }

        .sidebar-link.active {
            color: #ffffff;
            background-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.28);
        }

        .sidebar-link.active i {
            color: #ffffff;
        }

        .main-wrapper {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.2s ease;
        }

        .top-navbar {
            height: 68px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .content-body {
            padding: 1.75rem;
            flex-grow: 1;
        }

        /* Large Simple Form Controls */
        .form-control, .form-select {
            padding: 0.65rem 0.95rem;
            font-size: 0.95rem;
            border-radius: 8px;
            border-color: #cbd5e1;
        }

        .form-control:focus, .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        /* Clean Card */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            font-weight: 700;
        }

        /* Clean Tables */
        .table > :not(caption) > * > * {
            padding: 0.85rem 1rem;
        }

        .table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
        }

        /* Buttons */
        .btn {
            font-weight: 600;
            padding: 0.6rem 1.15rem;
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
                margin-left: -250px;
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

    <!-- CLEAN LEFT SIDEBAR: 3 PRIMARY PAGES -->
    <aside class="sidebar no-print" id="appSidebar">
        <!-- Brand -->
        <a href="{{ route('products.index') }}" class="sidebar-brand">
            <i class="bi bi-box-seam me-2 fs-4"></i>
            <span>BEE COMFORT</span>
        </a>

        <!-- Main 3 Navigation Links -->
        <div class="sidebar-menu">
            
            <div class="menu-label">Main System</div>

            <!-- Page 1: Products & Stock -->
            <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*', 'stock.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i>
                <span>Products & Stock</span>
            </a>

            <!-- Page 2: Sales Invoices -->
            <a href="{{ route('sales.index') }}" class="sidebar-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Sales Invoices</span>
            </a>

            <!-- Page 3: Bill Returns -->
            <a href="{{ route('returns.index') }}" class="sidebar-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Bill Returns</span>
            </a>

            <!-- Secondary Links (Dashboard & Tools) -->
            <div class="menu-label mt-4">Overview & Tools</div>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard', 'home') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard Overview</span>
            </a>

            <a href="{{ route('barcode.index') }}" class="sidebar-link {{ request()->routeIs('barcode.*') ? 'active' : '' }}">
                <i class="bi bi-printer"></i>
                <span>Print Barcode Roll</span>
            </a>

            <!-- Settings Accordion / Modal Dropdown -->
            <div class="dropdown mt-2">
                <button class="sidebar-link w-100 border-0 bg-transparent text-start dropdown-toggle {{ request()->routeIs('pricing.*', 'categories.*', 'customers.*') ? 'text-primary' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i>
                    <span>Settings & Config</span>
                </button>
                <ul class="dropdown-menu shadow border-0 p-2">
                    <li><a class="dropdown-item rounded py-2 small fw-semibold" href="{{ route('pricing.index') }}"><i class="bi bi-tag me-2 text-primary"></i> Pricing Rules</a></li>
                    <li><a class="dropdown-item rounded py-2 small fw-semibold" href="{{ route('categories.index') }}"><i class="bi bi-folder me-2 text-primary"></i> Product Categories</a></li>
                    <li><a class="dropdown-item rounded py-2 small fw-semibold" href="{{ route('customers.index') }}"><i class="bi bi-people me-2 text-primary"></i> Customers</a></li>
                    <li><a class="dropdown-item rounded py-2 small fw-semibold" href="{{ route('barcode.settings') }}"><i class="bi bi-sliders me-2 text-primary"></i> Printer Settings</a></li>
                </ul>
            </div>

        </div>

        <!-- Sidebar Footer Status -->
        <div class="p-3 border-top bg-light no-print text-center small text-secondary">
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="bi bi-check-circle me-1"></i> 32&times;22mm Thermal Active
            </span>
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
                    <a href="{{ route('products.index') }}" class="text-decoration-none text-secondary">
                        <i class="bi bi-house-door"></i>
                    </a>
                    <span>/</span>
                    @yield('breadcrumb')
                </div>
            </div>

            <!-- Header Quick Action Buttons: Create & Print -->
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add Product
                </a>
                <a href="{{ route('sales.create') }}" class="btn btn-outline-primary btn-sm px-3">
                    <i class="bi bi-cart-plus me-1"></i> New Sale
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
                        <strong>Please check the following errors:</strong>
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
            <strong>Bee Comfort</strong> &bull; Simple Inventory, Barcode, Sales and Returns Software
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
