<?php

namespace App\Http\Controllers;

use App\Models\FinancialTarget;
use App\Models\Investment;
use App\Models\Saving;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    public function index()
    {
        $savings = Saving::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        $totalPrincipal = $savings->sum('amount_invested');
        $totalCurrent = $savings->sum('current_value');
        $totalInterest = $totalCurrent - $totalPrincipal;

        return view('savings.index', compact('savings', 'totalPrincipal', 'totalCurrent', 'totalInterest'));
    }

    public function create()
    {
        $targets = FinancialTarget::where('user_id', auth()->id())->active()->orderBy('name')->get();
        return view('savings.create', compact('targets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'amount_invested' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0',
            'amount_invested_foreign' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'target_id' => 'nullable|exists:financial_targets,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['current_value'] = 0;

        // Auto-calculate IDR amount from foreign currency
        if (!empty($validated['amount_invested_foreign']) && !empty($validated['exchange_rate']) && empty($validated['amount_invested'])) {
            $validated['amount_invested'] = round((float) $validated['amount_invested_foreign'] * (float) $validated['exchange_rate'], 2);
        }

        if (!empty($validated['purchase_date']) && !empty($validated['interest_rate']) && !empty($validated['amount_invested'])) {
            $principal = (float) $validated['amount_invested'];
            $rate = (float) $validated['interest_rate'];
            $days = now()->diffInDays(\Carbon\Carbon::parse($validated['purchase_date']));
            $grossInterest = $principal * ($rate / 100) * ($days / 365);
            $validated['current_value'] = round($principal + ($grossInterest * 0.8), 2);
        }

        $saving = Saving::create($validated);
        $this->syncTargetAmount($saving);

        return redirect()->route('savings.index')
            ->with('success', 'Savings recorded successfully.');
    }

    public function edit(Saving $saving)
    {
        if ($saving->user_id !== auth()->id()) abort(403);
        $targets = FinancialTarget::where('user_id', auth()->id())->active()->orderBy('name')->get();
        return view('savings.edit', compact('saving', 'targets'));
    }

    public function update(Request $request, Saving $saving)
    {
        if ($saving->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'amount_invested' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0',
            'amount_invested_foreign' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'target_id' => 'nullable|exists:financial_targets,id',
        ]);

        $purchaseDate = $validated['purchase_date'] ?? $saving->purchase_date;
        $rate = $validated['interest_rate'] ?? $saving->interest_rate;

        // Auto-calculate IDR amount from foreign currency
        if (!empty($validated['amount_invested_foreign']) && !empty($validated['exchange_rate']) && empty($validated['amount_invested'])) {
            $validated['amount_invested'] = round((float) $validated['amount_invested_foreign'] * (float) $validated['exchange_rate'], 2);
        }

        if ($purchaseDate && $rate && !empty($validated['amount_invested'] ?? $saving->amount_invested)) {
            $principal = (float) ($validated['amount_invested'] ?? $saving->amount_invested);
            $days = now()->diffInDays(\Carbon\Carbon::parse($purchaseDate));
            $grossInterest = $principal * ((float) $rate / 100) * ($days / 365);
            $validated['current_value'] = round($principal + ($grossInterest * 0.8), 2);
        }

        $saving->update($validated);
        $this->syncTargetAmount($saving);

        return redirect()->route('savings.index')
            ->with('success', 'Savings updated successfully.');
    }

    public function destroy(Saving $saving)
    {
        if ($saving->user_id !== auth()->id()) abort(403);
        $targetId = $saving->target_id;
        $saving->delete();
        if ($targetId) {
            $target = FinancialTarget::find($targetId);
            if ($target) {
                $total = Saving::where('user_id', auth()->id())->where('target_id', $targetId)->sum('current_value');
                $totalInvestments = Investment::where('user_id', auth()->id())->where('target_id', $targetId)->where('status', 'active')->sum('current_value');
                $target->update(['current_amount' => $total + $totalInvestments]);
            }
        }
        return redirect()->route('savings.index')
            ->with('success', 'Savings deleted successfully.');
    }

    private function syncTargetAmount(Saving $saving): void
    {
        if ($saving->target) {
            $total = Saving::where('user_id', auth()->id())->where('target_id', $saving->target_id)->sum('current_value');
            $totalInvestments = Investment::where('user_id', auth()->id())->where('target_id', $saving->target_id)->where('status', 'active')->sum('current_value');
            $saving->target->update(['current_amount' => $total + $totalInvestments]);
        }
    }
}
