<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Bee Comfort') — Inventory & Packing</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                            950: '#451a03',
                        }
                    },
                    fontFamily: {
                        sans: ['"Inter"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                        arabic: ['"Arabic Transparent"', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Tom Select (for clean searchable dropdowns) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <!-- JsBarcode (for Code39 Barcode rendering) -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        @font-face {
            font-family: 'Arabic Transparent';
            src: local('Arabic Transparent'), local('Arial');
            font-weight: normal;
            font-style: normal;
        }

        /* 32mm x 22mm Thermal Label Box */
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
            font-family: 'Arabic Transparent', Arial, sans-serif;
            text-align: center;
            line-height: 1.1;
        }

        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; }
        }

        /* Modern TomSelect Styling */
        .ts-control {
            border-radius: 0.75rem !important;
            border-color: #e2e8f0 !important;
            padding: 0.55rem 0.85rem !important;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03) !important;
            font-size: 0.875rem !important;
            min-height: 42px !important;
        }
        .ts-control:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
        }
        .ts-dropdown {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1) !important;
            border-color: #e2e8f0 !important;
            z-index: 9999 !important;
        }

        /* Custom subtle scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex antialiased text-slate-800 bg-slate-100 font-sans">

    <!-- LEFT SIDEBAR -->
    <aside id="sidebar" class="no-print w-64 lg:w-72 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 border-r border-slate-800 transition-all duration-300 z-40 fixed inset-y-0 left-0 lg:static -translate-x-full lg:translate-x-0 shadow-2xl lg:shadow-none">
        
        <!-- Brand / Logo Area -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/40">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 flex items-center justify-center text-slate-950 shadow-lg shadow-amber-500/25 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-cube text-lg"></i>
                </div>
                <div>
                    <span class="text-lg font-black text-white tracking-tight flex items-center gap-1.5">
                        BEE COMFORT
                    </span>
                    <span class="text-[11px] font-semibold text-amber-400 tracking-wider uppercase block -mt-0.5">
                        Packing & Inventory
                    </span>
                </div>
            </a>
            <button type="button" id="close-sidebar-btn" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-6 text-xs font-semibold">
            
            <!-- OVERVIEW SECTION -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Overview</p>
                
                <a href="{{ route('dashboard') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('dashboard', 'home') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-sm {{ request()->routeIs('dashboard', 'home') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                    <span class="ml-2.5 text-sm">Dashboard</span>
                </a>
            </div>

            <!-- SALES & OPERATIONS SECTION -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Sales & Barcode</p>
                
                <a href="{{ route('sales.create') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('sales.create') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-cash-register w-5 text-sm {{ request()->routeIs('sales.create') ? 'text-slate-950' : 'text-emerald-400' }}"></i>
                    <span class="ml-2.5 text-sm">New Sales Invoice</span>
                </a>

                <a href="{{ route('sales.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('sales.index', 'sales.show') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-sm {{ request()->routeIs('sales.index', 'sales.show') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Sales Invoices</span>
                </a>

                <a href="{{ route('returns.create') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('returns.create') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-rotate-left w-5 text-sm {{ request()->routeIs('returns.create') ? 'text-slate-950' : 'text-orange-400' }}"></i>
                    <span class="ml-2.5 text-sm">Process Bill Return</span>
                </a>

                <a href="{{ route('returns.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('returns.index', 'returns.show') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-receipt w-5 text-sm {{ request()->routeIs('returns.index', 'returns.show') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Returns History</span>
                </a>

                <a href="{{ route('barcode.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('barcode.index') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-barcode w-5 text-sm {{ request()->routeIs('barcode.index') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                    <span class="ml-2.5 text-sm">Print Barcode Stickers</span>
                </a>
            </div>

            <!-- INVENTORY & PRODUCTS SECTION -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Inventory Master</p>

                <a href="{{ route('products.create') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('products.create') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-plus-circle w-5 text-sm {{ request()->routeIs('products.create') ? 'text-slate-950' : 'text-amber-400' }}"></i>
                    <span class="ml-2.5 text-sm">Register Product</span>
                </a>

                <a href="{{ route('products.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('products.index', 'products.show', 'products.edit') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-sm {{ request()->routeIs('products.index', 'products.show', 'products.edit') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Product Catalog</span>
                </a>

                <a href="{{ route('stock.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('stock.index') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-warehouse w-5 text-sm {{ request()->routeIs('stock.index') ? 'text-slate-950' : 'text-cyan-400' }}"></i>
                    <span class="ml-2.5 text-sm">Stock Levels</span>
                </a>

                <a href="{{ route('stock.movements') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('stock.movements') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-sm {{ request()->routeIs('stock.movements') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Movement Ledger</span>
                </a>
            </div>

            <!-- CONFIGURATION SECTION -->
            <div class="space-y-1">
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Management</p>

                <a href="{{ route('pricing.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('pricing.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-tags w-5 text-sm {{ request()->routeIs('pricing.*') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Pricing Rules</span>
                </a>

                <a href="{{ route('categories.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('categories.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-folder-tree w-5 text-sm {{ request()->routeIs('categories.*') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Categories</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('customers.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-users w-5 text-sm {{ request()->routeIs('customers.*') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Customers</span>
                </a>

                <a href="{{ route('barcode.settings') }}" class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('barcode.settings') ? 'bg-amber-400 text-slate-950 font-bold shadow-md shadow-amber-400/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-gear w-5 text-sm {{ request()->routeIs('barcode.settings') ? 'text-slate-950' : 'text-slate-400' }}"></i>
                    <span class="ml-2.5 text-sm">Printer Setup</span>
                </a>
            </div>

        </nav>

        <!-- Sidebar Footer / Status Badge -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40 text-xs text-slate-400 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-semibold text-slate-300">Live POS Engine</span>
            </div>
            <span class="text-[10px] font-mono bg-slate-800 px-2 py-0.5 rounded text-amber-400">32×22mm</span>
        </div>

    </aside>

    <!-- MAIN RIGHT CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- TOP BAR -->
        <header class="no-print h-16 bg-white border-b border-slate-200/90 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30 shadow-xs">
            <div class="flex items-center space-x-3">
                <button type="button" id="open-sidebar-btn" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center space-x-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-600 transition-colors">
                        <i class="fa-solid fa-house"></i>
                    </a>
                    <span>/</span>
                    @yield('breadcrumb')
                </div>
            </div>

            <!-- Top Right Quick Actions -->
            <div class="flex items-center space-x-2.5">
                <a href="{{ route('sales.create') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 transition-colors shadow-sm">
                    <i class="fa-solid fa-cash-register mr-1.5"></i> New Sale
                </a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    <i class="fa-solid fa-plus mr-1.5"></i> Add Product
                </a>
            </div>
        </header>

        <!-- PAGE CONTENT CONTAINER -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="no-print mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 shadow-xs flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 text-sm">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-emerald-950">{{ session('success') }}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="no-print mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-900 shadow-xs flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 text-sm">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-rose-950">{{ session('error') }}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="no-print mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-900 shadow-xs">
                    <div class="flex items-center space-x-2.5 mb-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                        <h4 class="font-bold text-xs sm:text-sm text-rose-950">Action could not be completed:</h4>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-800 pl-2 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Clean Compact Footer -->
        <footer class="no-print bg-white border-t border-slate-200 px-6 py-4 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <span class="font-medium">Bee Comfort — Inventory & Packing Management</span>
            <span class="text-slate-400">Professional Edition • Thermal Engine 32×22 mm</span>
        </footer>

    </div>

    <!-- Mobile Sidebar Backdrop & Toggle Scripts -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-30 hidden lg:hidden"></div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openBtn = document.getElementById('open-sidebar-btn');
        const closeBtn = document.getElementById('close-sidebar-btn');

        function toggleSidebar(show) {
            if (show) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        openBtn?.addEventListener('click', () => toggleSidebar(true));
        closeBtn?.addEventListener('click', () => toggleSidebar(false));
        backdrop?.addEventListener('click', () => toggleSidebar(false));

        // Initialize TomSelect on searchable dropdowns
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.searchable-select').forEach(function(el) {
                if (!el.tomselect) {
                    new TomSelect(el, {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        placeholder: el.getAttribute('placeholder') || 'Type to search...',
                    });
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
