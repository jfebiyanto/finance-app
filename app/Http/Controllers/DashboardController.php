<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\FinancialTarget;
use App\Models\Investment;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->format('Y-m'));

        // Monthly summary
        $monthlyExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->sum('amount');

        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->sum('amount');

        // Recent transactions (last 25)
        $recentTransactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        // Expenses by category for current month (top 10)
        $expensesByCategory = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // Budget progress
        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->get()
            ->map(function ($budget) use ($user, $month) {
                $spent = Transaction::where('user_id', $user->id)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', substr($month, 0, 4))
                    ->whereMonth('transaction_date', substr($month, 5, 2))
                    ->sum('amount');
                $budget->spent = $spent;
                $budget->percentage = $budget->amount > 0 ? min(100, round(($spent / $budget->amount) * 100, 2)) : 0;
                return $budget;
            });

        // Debt summary
        $totalDebt = Debt::where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('remaining_amount');

        // Investment summary
        $totalInvested = Investment::where('user_id', $user->id)->sum('amount_invested');
        $totalCurrentValue = Investment::where('user_id', $user->id)->sum('current_value');

        // Savings summary
        $savingsAll = Saving::where('user_id', $user->id)->get();
        $totalSavingsPrincipal = $savingsAll->sum('amount_invested');
        $totalSavingsCurrent = $savingsAll->sum('current_value');
        $totalSavingsInterest = $totalSavingsCurrent - $totalSavingsPrincipal;

        // Term Deposit summary
        $termDeposits = Investment::where('user_id', $user->id)
            ->where('type', 'term_deposit')
            ->where('status', 'active')
            ->get();
        $totalTermPrincipal = $termDeposits->sum('amount_invested');
        $totalTermCurrent = $termDeposits->sum('current_value');

        // Active financial targets
        $targets = FinancialTarget::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        // Daily expenses for current month (chart data)
        $dailyExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyData = $this->getMonthlyData($user, 3, $month);

        return view('dashboard', compact(
            'month', 'monthlyExpenses', 'monthlyIncome',
            'recentTransactions', 'expensesByCategory',
            'budgets', 'totalDebt', 'totalInvested',
            'totalCurrentValue', 'targets', 'dailyExpenses',
            'monthlyData', 'totalSavingsPrincipal', 'totalSavingsCurrent', 'totalSavingsInterest',
            'totalTermPrincipal', 'totalTermCurrent', 'termDeposits'
        ));
    }

    private function getMonthlyData($user, $monthsBack = 6, $referenceMonth = null)
    {
        $data = [];
        $reference = $referenceMonth ? \Carbon\Carbon::parse($referenceMonth.'-01') : now();
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $date = $reference->copy()->subMonths($i);
            $m = $date->format('Y-m');

            $expenses = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('amount');

            $income = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('amount');

            $data[] = [
                'month' => $m,
                'label' => $date->format('M Y'),
                'expenses' => (float) $expenses,
                'income' => (float) $income,
            ];
        }
        return $data;
    }
}
