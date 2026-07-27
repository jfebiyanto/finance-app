<?php

namespace App\Http\Controllers;

use App\Models\FinancialTarget;
use App\Models\Investment;
use App\Models\Saving;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function index(Request $request)
    {
        $showHistory = $request->boolean('history');

        $query = Investment::where('user_id', auth()->id());

        if (!$showHistory) {
            $query->active();
        }

        $investments = $query->orderByRaw("FIELD(status, 'active', 'sold')")
            ->orderBy('name')
            ->get();

        $totalInvested = $investments->sum('amount_invested');
        $totalCurrentValue = $investments->sum('current_value');
        $totalProfitLoss = $totalCurrentValue - $totalInvested;
        return view('investments.index', compact('investments', 'totalInvested', 'totalCurrentValue', 'totalProfitLoss', 'showHistory'));
    }

    public function create()
    {
        $targets = FinancialTarget::where('user_id', auth()->id())->active()->orderBy('name')->get();
        return view('investments.create', compact('targets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
            'bank_name' => 'nullable|string|max:200',
            'term_months' => 'nullable|integer|in:1,3,6,12',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'shares' => 'nullable|numeric|min:0',
            'avg_cost' => 'nullable|numeric|min:0',
            'amount_invested' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'maturity_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'target_id' => 'nullable|exists:financial_targets,id',
        ]);

        $validated['user_id'] = auth()->id();

        // Auto-calculate amount_invested from shares × avg_cost
        if (!empty($validated['shares']) && !empty($validated['avg_cost'])) {
            $validated['amount_invested'] = round((float) $validated['shares'] * (float) $validated['avg_cost'], 2);
        }

        // Auto-calculate maturity_date for term deposits
        if (!empty($validated['purchase_date']) && !empty($validated['term_months'])) {
            $validated['maturity_date'] = \Carbon\Carbon::parse($validated['purchase_date'])
                ->addMonths((int) $validated['term_months'])
                ->format('Y-m-d');
        }

        // Auto-calculate current_value if left empty
        if (!$request->filled('current_value')) {
            $isTermDeposit = ($validated['type'] ?? null) === 'term_deposit';
            if ($isTermDeposit) {
                $principal = (float) ($validated['amount_invested'] ?? 0);
                $rate = (float) ($validated['interest_rate'] ?? 0);
                $months = (int) ($validated['term_months'] ?? 0);
                if ($principal > 0 && $rate > 0 && $months > 0) {
                    $interest = $principal * ($rate / 100) * ($months / 12);
                    $validated['current_value'] = round($principal + $interest, 2);
                } else {
                    $validated['current_value'] = $principal;
                }
            } elseif (!empty($validated['amount_invested'])) {
                // For other investments, default current_value = amount_invested (cost basis)
                $validated['current_value'] = (float) $validated['amount_invested'];
            } else {
                $validated['current_value'] = 0;
            }
        }

        $investment = Investment::create($validated);

        // Update linked target current_amount
        if (!empty($validated['target_id']) && $investment->target) {
            $totalForTarget = Investment::where('user_id', auth()->id())
                ->where('target_id', $validated['target_id'])
                ->where('status', 'active')
                ->sum('current_value');
            $investment->target->update(['current_amount' => $totalForTarget]);
        }

        return redirect()->route('investments.index')
            ->with('success', 'Investment recorded successfully.');
    }

    public function edit(Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) abort(403);
        $targets = FinancialTarget::where('user_id', auth()->id())->active()->orderBy('name')->get();
        return view('investments.edit', compact('investment', 'targets'));
    }

    public function update(Request $request, Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
            'bank_name' => 'nullable|string|max:200',
            'term_months' => 'nullable|integer|in:1,3,6,12',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'shares' => 'nullable|numeric|min:0',
            'avg_cost' => 'nullable|numeric|min:0',
            'amount_invested' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'maturity_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'target_id' => 'nullable|exists:financial_targets,id',
        ]);

        // Auto-calculate amount_invested from shares × avg_cost
        if (!empty($validated['shares']) && !empty($validated['avg_cost'])) {
            $validated['amount_invested'] = round((float) $validated['shares'] * (float) $validated['avg_cost'], 2);
        }

        // Auto-calculate maturity_date for term deposits
        if (!empty($validated['purchase_date']) && !empty($validated['term_months'])) {
            $validated['maturity_date'] = \Carbon\Carbon::parse($validated['purchase_date'])
                ->addMonths((int) $validated['term_months'])
                ->format('Y-m-d');
        }

        // Auto-calculate current_value if left empty
        if (!$request->filled('current_value')) {
            $isTermDeposit = ($validated['type'] ?? $investment->type) === 'term_deposit';
            if ($isTermDeposit) {
                $principal = (float) ($validated['amount_invested'] ?? $investment->amount_invested);
                $rate = (float) ($validated['interest_rate'] ?? $investment->interest_rate);
                $months = (int) ($validated['term_months'] ?? $investment->term_months);
                if ($principal > 0 && $rate > 0 && $months > 0) {
                    $interest = $principal * ($rate / 100) * ($months / 12);
                    $validated['current_value'] = round($principal + $interest, 2);
                } else {
                    $validated['current_value'] = $principal;
                }
            } elseif (!empty($validated['amount_invested'])) {
                $validated['current_value'] = (float) $validated['amount_invested'];
            } else {
                $validated['current_value'] = $investment->current_value ?? 0;
            }
        }

        $investment->update($validated);

        // Update linked target current_amount
        if ($investment->target) {
            $totalForTarget = Investment::where('user_id', auth()->id())
                ->where('target_id', $investment->target_id)
                ->where('status', 'active')
                ->sum('current_value');
            $investment->target->update(['current_amount' => $totalForTarget]);
        }

        return redirect()->route('investments.index')
            ->with('success', 'Investment updated successfully.');
    }

    public function updateValue(Request $request, Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);
        $investment->update($validated);

        // Update linked target
        $this->syncTargetAmount($investment);

        $redirect = $request->query('history') ? route('investments.index', ['history' => 1]) : route('investments.index');
        return redirect($redirect)
            ->with('success', 'Investment value updated.');
    }

    public function topUp(Request $request, Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'additional_shares' => 'nullable|numeric|min:0',
            'additional_amount' => 'required|numeric|min:0',
            'new_current_value' => 'nullable|numeric|min:0',
        ]);

        $additionalAmount = (float) $validated['additional_amount'];
        $additionalShares = (float) ($validated['additional_shares'] ?? 0);

        // Update amount_invested
        $investment->amount_invested = round((float) $investment->amount_invested + $additionalAmount, 2);

        // Update shares and recalculate avg_cost
        if ($additionalShares > 0 && (float) $investment->shares > 0) {
            $oldShares = (float) $investment->shares;
            $newShares = $oldShares + $additionalShares;
            // Recalculate avg_cost: total cost / total shares
            $investment->avg_cost = round($investment->amount_invested / $newShares, 2);
            $investment->shares = $newShares;
        } elseif ($additionalShares > 0) {
            // First time shares were set
            $investment->shares = $additionalShares;
            $investment->avg_cost = round($additionalAmount / $additionalShares, 2);
        }

        // Update current_value if provided
        if (!empty($validated['new_current_value'])) {
            $investment->current_value = (float) $validated['new_current_value'];
        } elseif ($investment->current_price && $investment->shares > 0) {
            $investment->current_value = round((float) $investment->shares * (float) $investment->current_price, 2);
        } else {
            $investment->current_value = round((float) $investment->current_value + $additionalAmount, 2);
        }

        $investment->save();

        // Update linked target
        $this->syncTargetAmount($investment);

        return redirect()->route('investments.index')
            ->with('success', 'Investment topped up successfully. Avg cost recalculated.');
    }

    public function markAsSold(Request $request, Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
            'sold_date' => 'required|date',
        ]);
        $validated['status'] = 'sold';
        $investment->update($validated);

        // Update linked target
        $this->syncTargetAmount($investment);

        return redirect()->route('investments.index')
            ->with('success', 'Investment marked as sold.');
    }

    public function destroy(Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) abort(403);
        $targetId = $investment->target_id;
        $investment->delete();

        // Update linked target after deletion
        if ($targetId) {
            $target = FinancialTarget::find($targetId);
            if ($target) {
                $total = Investment::where('user_id', auth()->id())
                    ->where('target_id', $targetId)
                    ->where('status', 'active')
                    ->sum('current_value');
                $target->update(['current_amount' => $total ?: 0]);
            }
        }

        return redirect()->route('investments.index')
            ->with('success', 'Investment deleted successfully.');
    }

    private function syncTargetAmount(Investment $investment): void
    {
        if ($investment->target) {
            $totalInvestments = Investment::where('user_id', auth()->id())
                ->where('target_id', $investment->target_id)
                ->where('status', 'active')
                ->sum('current_value');
            $totalSavings = Saving::where('user_id', auth()->id())
                ->where('target_id', $investment->target_id)
                ->sum('current_value');
            $investment->target->update(['current_amount' => ($totalInvestments ?: 0) + ($totalSavings ?: 0)]);
        }
    }
}
