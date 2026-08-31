<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CashRegisterController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InstallmentController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\ManualController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\SaleReturnController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Authentication
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Admin area (auth required)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update');

    Route::get('categories/children', [CategoryController::class, 'children'])->name('categories.children');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');

    // Custom product tools — must precede the resource so they aren't treated as {product}.
    Route::get('products/bulk-pricing', [ProductController::class, 'bulkPricing'])->name('products.bulk-pricing');
    Route::post('products/bulk-pricing', [ProductController::class, 'bulkPricingUpdate'])->name('products.bulk-pricing.update');
    Route::get('products/labels', [ProductController::class, 'labels'])->name('products.labels');
    Route::get('products/{product}/batch-labels', [ProductController::class, 'batchLabels'])->name('products.batch-labels');
    Route::get('products/bulk-import', [ProductController::class, 'bulkImport'])->name('products.bulk-import');
    Route::post('products/bulk-import', [ProductController::class, 'bulkImportStore'])->name('products.bulk-import.store');
    Route::get('products/import-template', [ProductController::class, 'importTemplate'])->name('products.import-template');
    Route::resource('products', ProductController::class);

    // Contacts
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);

    // Purchases (create/view/delete only — no edit to keep stock consistent)
    Route::get('purchases/bulk', [PurchaseController::class, 'bulkCreate'])->name('purchases.bulk');
    Route::post('purchases/bulk', [PurchaseController::class, 'bulkStore'])->name('purchases.bulk.store');
    Route::get('purchases/import-template', [PurchaseController::class, 'importTemplate'])->name('purchases.import-template');
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Sales (created via POS; managed here)
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::resource('sales', SaleController::class)->only(['index', 'show', 'destroy']);

    // POS
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos', [PosController::class, 'store'])->name('pos.store');
    Route::post('pos/customers', [PosController::class, 'storeCustomer'])->name('pos.customers.store');
    Route::get('pos2', [PosController::class, 'pos2'])->name('pos.pos2');
    Route::get('pos/search', [PosController::class, 'searchProducts'])->name('pos.search');

    // Quotations
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Sale Returns
    Route::get('returns', [SaleReturnController::class, 'index'])->name('returns.index');
    Route::get('returns/{sale}/create', [SaleReturnController::class, 'create'])->name('returns.create');
    Route::post('returns/{sale}', [SaleReturnController::class, 'store'])->name('returns.store');
    Route::get('returns/{return}/show', [SaleReturnController::class, 'show'])->name('returns.show');

    // Payments (due collection)
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('sales/{sale}/payments', [PaymentController::class, 'storeSalePayment'])->name('sales.payments.store');
    Route::post('purchases/{purchase}/payments', [PaymentController::class, 'storePurchasePayment'])->name('purchases.payments.store');
    Route::post('customers/{customer}/payments', [PaymentController::class, 'storeCustomerPayment'])->name('customers.payments.store');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'destroy']);

    // Stock
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/movements', [StockController::class, 'movements'])->name('stock.movements');
    Route::get('stock/{product}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::post('stock/{product}/adjust', [StockController::class, 'storeAdjust'])->name('stock.adjust.store');

    // Batches
    Route::get('batches', [BatchController::class, 'index'])->name('batches.index');

    // Reports
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
    Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');

    // Accounting
    Route::get('accounting/day-book', [AccountingController::class, 'dayBook'])->name('accounting.day-book');
    Route::get('accounting/trial-balance', [AccountingController::class, 'trialBalance'])->name('accounting.trial-balance');

    // Access control
    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('permissions', PermissionController::class)->except('show');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('settings/backup', [SettingController::class, 'backup'])->name('settings.backup');

    // Cash Register
    Route::get('cash-register', [CashRegisterController::class, 'index'])->name('cash-register.index');
    Route::post('cash-register/open', [CashRegisterController::class, 'open'])->name('cash-register.open');
    Route::post('cash-register/{register}/close', [CashRegisterController::class, 'close'])->name('cash-register.close');

    // Installments
    Route::get('installments', [InstallmentController::class, 'index'])->name('installments.index');
    Route::get('installments/{sale}/create', [InstallmentController::class, 'create'])->name('installments.create');
    Route::post('installments/{sale}', [InstallmentController::class, 'store'])->name('installments.store');
    Route::get('installments/{installment}', [InstallmentController::class, 'show'])->name('installments.show');
    Route::post('installments/payments/{payment}/pay', [InstallmentController::class, 'markPaid'])->name('installments.pay');

    // Branches
    Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
    Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Activity Log & Login History
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('login-history', [LoginHistoryController::class, 'index'])->name('login-history.index');

    // User Manual
    Route::get('manual', [ManualController::class, 'index'])->name('manual.index');
    Route::get('manual/bangla', [ManualController::class, 'bangla'])->name('manual.bangla');
});
