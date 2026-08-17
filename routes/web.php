<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FinancialTargetController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Transactions
    Route::get('/transactions/import', [TransactionController::class, 'importForm'])->name('transactions.import');
    Route::get('/transactions/template', [TransactionController::class, 'template'])->name('transactions.template');
    Route::post('/transactions/import', [TransactionController::class, 'import'])->name('transactions.import.store');
    Route::resource('transactions', TransactionController::class);

    // Debts
    Route::resource('debts', DebtController::class);
    Route::post('/debts/{debt}/payments', [DebtController::class, 'addPayment'])->name('debts.payments.store');

    // Investments
    Route::resource('investments', InvestmentController::class);
    Route::patch('/investments/{investment}/value', [InvestmentController::class, 'updateValue'])->name('investments.updateValue');
    Route::post('/investments/{investment}/mark-sold', [InvestmentController::class, 'markAsSold'])->name('investments.markSold');
    Route::post('/investments/{investment}/top-up', [InvestmentController::class, 'topUp'])->name('investments.topUp');

    // Savings
    Route::resource('savings', SavingsController::class)->parameters(['savings' => 'saving']);
    Route::post('/savings/{saving}/top-up', [SavingsController::class, 'topUp'])->name('savings.topUp');
    Route::post('/savings/{saving}/withdraw', [SavingsController::class, 'withdraw'])->name('savings.withdraw');

    // Budgets
    Route::resource('budgets', BudgetController::class);
    Route::post('/budgets/copy-next-month', [BudgetController::class, 'copyNextMonth'])->name('budgets.copyNextMonth');

    // Financial Targets
    Route::resource('financial-targets', FinancialTargetController::class);

    // API Tokens
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

    // Database Backup
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/download', [BackupController::class, 'download'])->name('backup.download');
    Route::post('/backup/email', [BackupController::class, 'email'])->name('backup.email');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Favicon route
Route::get('/favicon.ico', function () {
    $path = storage_path('banking.ico');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, [
        'Content-Type' => 'image/x-icon',
    ]);
});



require __DIR__.'/auth.php';
