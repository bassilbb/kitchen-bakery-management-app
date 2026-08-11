<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    // Shared modules (dashboard, profile) for every logged-in user.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Sales modules - cashiers and admins only.
    Route::middleware('sales')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/add', [PosController::class, 'add'])->name('pos.add');
        Route::post('/pos/update-qty', [PosController::class, 'updateQty'])->name('pos.update-qty');
        Route::post('/pos/remove', [PosController::class, 'remove'])->name('pos.remove');
        Route::post('/pos/clear', [PosController::class, 'clear'])->name('pos.clear');
        Route::post('/pos/hold', [PosController::class, 'hold'])->name('pos.hold');
        Route::post('/pos/resume/{key}', [PosController::class, 'resume'])->name('pos.resume');
        Route::post('/pos/discard/{key}', [PosController::class, 'discard'])->name('pos.discard');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/{order}', [PosController::class, 'show'])->name('pos.show');

        Route::get('/paystack/callback', [PosController::class, 'paystackCallback'])->name('paystack.callback');

        Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::resource('customers', CustomerController::class);

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    });

    // Kitchen department modules
    Route::middleware('department:kitchen')->group(function () {
        Route::resource('ingredients', IngredientController::class);
        Route::post('/ingredients/{ingredient}/purchase', [IngredientController::class, 'addPurchase'])->name('ingredients.purchase');
        Route::post('/ingredients/{ingredient}/adjust-stock', [IngredientController::class, 'adjustStock'])->name('ingredients.adjust-stock');

        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    // Bakery department modules
    Route::middleware('department:bakery')->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('/products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
        Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
        Route::post('/products/{product}/recipe', [ProductController::class, 'saveRecipe'])->name('products.recipe');

        Route::get('/productions', [ProductionController::class, 'index'])->name('productions.index');
        Route::get('/productions/create', [ProductionController::class, 'create'])->name('productions.create');
        Route::post('/productions', [ProductionController::class, 'store'])->name('productions.store');
        Route::get('/productions/{production}', [ProductionController::class, 'show'])->name('productions.show');
        Route::delete('/productions/{production}', [ProductionController::class, 'destroy'])->name('productions.destroy');

        Route::resource('categories', CategoryController::class)->only(['index', 'update', 'destroy']);
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });

    // Admin-only modules
    Route::middleware('admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-orders', [ReportController::class, 'exportOrders'])->name('reports.export-orders');

        Route::resource('expenses', ExpenseController::class)->except(['show']);

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
