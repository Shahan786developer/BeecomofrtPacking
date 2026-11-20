<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CashReceiptController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DozenController;
use App\Http\Controllers\JournalVoucherController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bee Comfort — Inventory, Barcode, Sales & Returns Management System
|--------------------------------------------------------------------------
*/

// Authentication (Super Admin & Staff Login & Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Protected System Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Main Entry & Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management (Super Admin ONLY)
    Route::middleware('superadmin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::get('/{user}/permissions', [UserController::class, 'editPermissions'])->name('permissions');
        Route::put('/{user}/permissions', [UserController::class, 'updatePermissions'])->name('permissions.update');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Products & Stock (Permission Protected)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/{product}/movements', [ProductController::class, 'movements'])->middleware('permission:products,read')->name('movements');
        Route::get('/', [ProductController::class, 'index'])->middleware('permission:products,read')->name('index');
        Route::get('/create', [ProductController::class, 'create'])->middleware('permission:products,create')->name('create');
        Route::post('/', [ProductController::class, 'store'])->middleware('permission:products,create')->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->middleware('permission:products,read')->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->middleware('permission:products,update')->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware('permission:products,update')->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware('permission:products,delete')->name('destroy');
    });

    // Stock Management
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->middleware('permission:products,read')->name('index');
        Route::get('/movements', [StockController::class, 'movements'])->middleware('permission:products,read')->name('movements');
        Route::post('/{product}/adjust', [StockController::class, 'adjust'])->middleware('permission:products,update')->name('adjust');
    });

    // Pricing Configuration (Settings Module)
    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('/', [PricingController::class, 'index'])->middleware('permission:settings,read')->name('index');
        Route::post('/percentage', [PricingController::class, 'storePercentage'])->middleware('permission:settings,update')->name('percentage.store');
        Route::post('/category', [PricingController::class, 'storeCategory'])->middleware('permission:settings,update')->name('category.store');
        Route::put('/{pricingRule}', [PricingController::class, 'update'])->middleware('permission:settings,update')->name('update');
        Route::post('/{pricingRule}/default', [PricingController::class, 'setDefault'])->middleware('permission:settings,update')->name('default');
        Route::delete('/{pricingRule}', [PricingController::class, 'destroy'])->middleware('permission:settings,update')->name('destroy');
    });

    // Barcode Generation & Thermal Label Printing
    Route::prefix('barcode')->name('barcode.')->group(function () {
        Route::get('/', [BarcodeController::class, 'index'])->middleware('permission:barcode,read')->name('index');
        Route::post('/print', [BarcodeController::class, 'print'])->middleware('permission:barcode,print')->name('print');
        Route::get('/customer', [BarcodeController::class, 'customerIndex'])->middleware('permission:barcode,read')->name('customer');
        Route::post('/customer/print', [BarcodeController::class, 'customerPrint'])->middleware('permission:barcode,print')->name('customer.print');
        Route::get('/settings', [BarcodeController::class, 'settings'])->middleware('permission:barcode,read')->name('settings');
        Route::post('/settings', [BarcodeController::class, 'updateSettings'])->middleware('permission:barcode,read')->name('settings.update');
        Route::post('/descriptions', [BarcodeController::class, 'storeDescription'])->middleware('permission:barcode,read')->name('descriptions.store');
        Route::post('/descriptions/{description}/default', [BarcodeController::class, 'setDefaultDescription'])->middleware('permission:barcode,read')->name('descriptions.default');
        Route::delete('/descriptions/{description}', [BarcodeController::class, 'destroyDescription'])->middleware('permission:barcode,read')->name('descriptions.destroy');
    });

    // Sale Orders / Order Matrix Booking (Hashmi Brothers System)
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [SaleOrderController::class, 'index'])->middleware('permission:orders,read')->name('index');
        Route::get('/create', [SaleOrderController::class, 'create'])->middleware('permission:orders,create')->name('create');
        Route::post('/', [SaleOrderController::class, 'store'])->middleware('permission:orders,create')->name('store');
        Route::get('/{order}/print', [SaleOrderController::class, 'print'])->middleware('permission:orders,print')->name('print');
        Route::get('/{order}', [SaleOrderController::class, 'show'])->middleware('permission:orders,read')->name('show');
        Route::get('/{order}/edit', [SaleOrderController::class, 'edit'])->middleware('permission:orders,update')->name('edit');
        Route::put('/{order}', [SaleOrderController::class, 'update'])->middleware('permission:orders,update')->name('update');
        Route::delete('/{order}', [SaleOrderController::class, 'destroy'])->middleware('permission:orders,delete')->name('destroy');
    });

    // Sales / Customer Invoices
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->middleware('permission:sales,read')->name('index');
        Route::get('/create', [SaleController::class, 'create'])->middleware('permission:sales,create')->name('create');
        Route::post('/', [SaleController::class, 'store'])->middleware('permission:sales,create')->name('store');
        Route::get('/{sale}/print', [SaleController::class, 'print'])->middleware('permission:sales,print')->name('print');
        Route::get('/{sale}', [SaleController::class, 'show'])->middleware('permission:sales,read')->name('show');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->middleware('permission:sales,update')->name('edit');
        Route::put('/{sale}', [SaleController::class, 'update'])->middleware('permission:sales,update')->name('update');
    });

    // Bill Returns
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->middleware('permission:returns,read')->name('index');
        Route::get('/create', [ReturnController::class, 'create'])->middleware('permission:returns,create')->name('create');
        Route::post('/', [ReturnController::class, 'store'])->middleware('permission:returns,create')->name('store');
        Route::get('/{return}', [ReturnController::class, 'show'])->middleware('permission:returns,read')->name('show');
        Route::get('/{return}/edit', [ReturnController::class, 'edit'])->middleware('permission:returns,update')->name('edit');
        Route::put('/{return}', [ReturnController::class, 'update'])->middleware('permission:returns,update')->name('update');
    });

    // Cash Receipts Voucher (CRV)
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [CashReceiptController::class, 'index'])->middleware('permission:receipts,read')->name('index');
        Route::get('/create', [CashReceiptController::class, 'create'])->middleware('permission:receipts,create')->name('create');
        Route::post('/', [CashReceiptController::class, 'store'])->middleware('permission:receipts,create')->name('store');
        Route::get('/{receipt}/print', [CashReceiptController::class, 'print'])->middleware('permission:receipts,print')->name('print');
        Route::get('/{receipt}', [CashReceiptController::class, 'show'])->middleware('permission:receipts,read')->name('show');
        Route::get('/{receipt}/edit', [CashReceiptController::class, 'edit'])->middleware('permission:receipts,update')->name('edit');
        Route::put('/{receipt}', [CashReceiptController::class, 'update'])->middleware('permission:receipts,update')->name('update');
        Route::delete('/{receipt}', [CashReceiptController::class, 'destroy'])->middleware('permission:receipts,delete')->name('destroy');
    });

    // Journal Vouchers (JV)
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/', [JournalVoucherController::class, 'index'])->middleware('permission:customers,read')->name('index');
        Route::get('/create', [JournalVoucherController::class, 'create'])->middleware('permission:customers,create')->name('create');
        Route::post('/', [JournalVoucherController::class, 'store'])->middleware('permission:customers,create')->name('store');
        Route::get('/{voucher}/print', [JournalVoucherController::class, 'print'])->middleware('permission:customers,print')->name('print');
        Route::get('/{voucher}', [JournalVoucherController::class, 'show'])->middleware('permission:customers,read')->name('show');
        Route::get('/{voucher}/edit', [JournalVoucherController::class, 'edit'])->middleware('permission:customers,update')->name('edit');
        Route::put('/{voucher}', [JournalVoucherController::class, 'update'])->middleware('permission:customers,update')->name('update');
        Route::delete('/{voucher}', [JournalVoucherController::class, 'destroy'])->middleware('permission:customers,delete')->name('destroy');
    });

    // Company Profile & Invoice Branding Settings
    Route::get('/settings/company', [CompanyProfileController::class, 'edit'])->middleware('permission:settings,read')->name('settings.company');
    Route::post('/settings/company', [CompanyProfileController::class, 'update'])->middleware('permission:settings,update')->name('settings.company.update');

    // Customers & Customer Ledger History
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->middleware('permission:customers,read')->name('index');
        Route::get('/default-list', [CustomerController::class, 'defaultList'])->middleware('permission:customers,read')->name('default-list');
        Route::post('/', [CustomerController::class, 'store'])->middleware('permission:customers,create')->name('store');
        Route::get('/{customer}/ledger', [CustomerController::class, 'ledger'])->middleware('permission:customers,read')->name('ledger');
        Route::get('/{customer}/print-statement', [CustomerController::class, 'printStatement'])->middleware('permission:customers,print')->name('print-statement');
        Route::get('/{customer}', [CustomerController::class, 'ledger'])->middleware('permission:customers,read')->name('show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers,update')->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers,delete')->name('destroy');
    });
    Route::get('/reports/customer-ledger', [CustomerController::class, 'report'])->middleware('permission:customers,read')->name('customers.report');

    // Product Categories (Settings Module)
    Route::post('/categories/{category}/default', [CategoryController::class, 'setDefault'])->middleware('permission:settings,update')->name('categories.default');
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:settings,read')->name('index');
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:settings,update')->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('permission:settings,update')->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('permission:settings,update')->name('destroy');
    });

    // Dozens Units Management (Settings Module)
    Route::post('/dozens/{dozen}/default', [DozenController::class, 'setDefault'])->middleware('permission:settings,update')->name('dozens.default');
    Route::prefix('dozens')->name('dozens.')->group(function () {
        Route::get('/', [DozenController::class, 'index'])->middleware('permission:settings,read')->name('index');
        Route::post('/', [DozenController::class, 'store'])->middleware('permission:settings,update')->name('store');
        Route::put('/{dozen}', [DozenController::class, 'update'])->middleware('permission:settings,update')->name('update');
        Route::delete('/{dozen}', [DozenController::class, 'destroy'])->middleware('permission:settings,update')->name('destroy');
    });

    // AJAX & Scanning APIs
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/skus/generate', [ApiController::class, 'generateSku'])->name('skus.generate');
        Route::post('/pricing/calculate', [ApiController::class, 'calculateSellingPrice'])->name('pricing.calculate');
        Route::get('/products/by-sku/{sku}', [ApiController::class, 'getProductBySku'])->name('products.by-sku');
        Route::get('/sales/{sale}/items', [ApiController::class, 'getSaleItems'])->name('sales.items');
        Route::get('/customers/{customer}/balance', [ApiController::class, 'getCustomerBalance'])->name('customers.balance');
    });
});
