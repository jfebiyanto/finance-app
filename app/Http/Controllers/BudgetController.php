<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->format('Y-m'));

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
                $budget->spent = (float) $spent;
                $budget->percentage = $budget->amount > 0 ? min(100, round(($spent / $budget->amount) * 100, 2)) : 0;
                $budget->remaining = max(0, $budget->amount - $spent);
                return $budget;
            });

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');

        // Budget vs Actual report data
        $reportBudgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->get()
            ->map(function ($budget) use ($user, $month) {
                $actual = Transaction::where('user_id', $user->id)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', substr($month, 0, 4))
                    ->whereMonth('transaction_date', substr($month, 5, 2))
                    ->sum('amount');
                $budget->actual = (float) $actual;
                $budget->variance = $budget->amount - $actual;
                $budget->onTrack = $actual <= $budget->amount;
                return $budget;
            });

        $totalReportBudget = $reportBudgets->sum('amount');
        $totalReportActual = $reportBudgets->sum('actual');

        return view('budgets.index', compact(
            'budgets', 'month', 'totalBudget', 'totalSpent',
            'reportBudgets', 'totalReportBudget', 'totalReportActual'
        ));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        $exists = Budget::where('user_id', auth()->id())
            ->where('category_id', $validated['category_id'])
            ->where('month', $validated['month'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['category_id' => 'A budget for this category and month already exists.'])
                ->withInput();
        }

        Budget::create($validated);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget set successfully.');
    }

    public function edit(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) abort(403);
        $categories = Category::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
        ]);

        $budget->update($validated);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) abort(403);
        $budget->delete();
        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }

    public function copyNextMonth(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->format('Y-m'));

        // Calculate next month
        $nextMonth = \Carbon\Carbon::parse($month.'-01')->addMonth()->format('Y-m');

        $currentBudgets = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->get();

        if ($currentBudgets->isEmpty()) {
            return back()->with('error', 'No budgets found for this month to copy.');
        }

        $copied = 0;
        $skipped = 0;

        foreach ($currentBudgets as $budget) {
            $exists = Budget::where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->where('month', $nextMonth)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Budget::create([
                'user_id' => $user->id,
                'category_id' => $budget->category_id,
                'month' => $nextMonth,
                'amount' => $budget->amount,
            ]);
            $copied++;
        }

        return redirect()->route('budgets.index', ['month' => $nextMonth])
            ->with('success', "Copied {$copied} budget(s) to {$nextMonth}." . ($skipped > 0 ? " {$skipped} already existed." : ''));
    }
}
