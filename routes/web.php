<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DozenController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bee Comfort — Inventory, Barcode, Sales & Returns Management System
|--------------------------------------------------------------------------
*/

// Authentication (Super Admin Login & Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Protected System Routes (Requires Active Super Admin Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Main Entry & Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/products/{product}/movements', [ProductController::class, 'movements'])->name('products.movements');
    Route::resource('products', ProductController::class);

    // Pricing Configuration (Percentage & Category-based)
    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('/', [PricingController::class, 'index'])->name('index');
        Route::post('/percentage', [PricingController::class, 'storePercentage'])->name('percentage.store');
        Route::post('/category', [PricingController::class, 'storeCategory'])->name('category.store');
        Route::put('/{pricingRule}', [PricingController::class, 'update'])->name('update');
        Route::post('/{pricingRule}/default', [PricingController::class, 'setDefault'])->name('default');
        Route::delete('/{pricingRule}', [PricingController::class, 'destroy'])->name('destroy');
    });

    // Barcode Generation & Thermal Label Printing
    Route::prefix('barcode')->name('barcode.')->group(function () {
        Route::get('/', [BarcodeController::class, 'index'])->name('index');
        Route::post('/print', [BarcodeController::class, 'print'])->name('print');
        Route::get('/settings', [BarcodeController::class, 'settings'])->name('settings');
        Route::post('/settings', [BarcodeController::class, 'updateSettings'])->name('settings.update');
        Route::post('/descriptions', [BarcodeController::class, 'storeDescription'])->name('descriptions.store');
        Route::post('/descriptions/{description}/default', [BarcodeController::class, 'setDefaultDescription'])->name('descriptions.default');
        Route::delete('/descriptions/{description}', [BarcodeController::class, 'destroyDescription'])->name('descriptions.destroy');
    });

    // Sales / Customer Invoices
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}/print', [SaleController::class, 'print'])->name('print');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
        Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
    });

    // Bill Returns
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::get('/create', [ReturnController::class, 'create'])->name('create');
        Route::post('/', [ReturnController::class, 'store'])->name('store');
        Route::get('/{return}', [ReturnController::class, 'show'])->name('show');
        Route::get('/{return}/edit', [ReturnController::class, 'edit'])->name('edit');
        Route::put('/{return}', [ReturnController::class, 'update'])->name('update');
    });

    // Stock Management
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/movements', [StockController::class, 'movements'])->name('movements');
        Route::post('/{product}/adjust', [StockController::class, 'adjust'])->name('adjust');
    });

    // Customers & Customer Ledger History
    Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
    Route::get('/customers/{customer}', [CustomerController::class, 'ledger'])->name('customers.show');
    Route::resource('customers', CustomerController::class)->except(['create', 'show', 'edit']);

    // Product Categories
    Route::post('/categories/{category}/default', [CategoryController::class, 'setDefault'])->name('categories.default');
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    // Dozens Units Management
    Route::post('/dozens/{dozen}/default', [DozenController::class, 'setDefault'])->name('dozens.default');
    Route::resource('dozens', DozenController::class)->except(['create', 'show', 'edit']);

    // AJAX & Scanning APIs
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/skus/generate', [ApiController::class, 'generateSku'])->name('skus.generate');
        Route::post('/pricing/calculate', [ApiController::class, 'calculateSellingPrice'])->name('pricing.calculate');
        Route::get('/products/by-sku/{sku}', [ApiController::class, 'getProductBySku'])->name('products.by-sku');
        Route::get('/sales/{sale}/items', [ApiController::class, 'getSaleItems'])->name('sales.items');
    });
});
