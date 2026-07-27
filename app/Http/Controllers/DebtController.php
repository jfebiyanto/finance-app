<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function index()
    {
        $debts = Debt::where('user_id', auth()->id())
            ->orderBy('status')
            ->orderBy('due_date')
            ->get();
        $totalActiveDebt = Debt::where('user_id', auth()->id())
            ->where('status', 'active')
            ->sum('remaining_amount');
        return view('debts.index', compact('debts', 'totalActiveDebt'));
    }

    public function create()
    {
        return view('debts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'payment_term' => 'nullable|in:weekly,biweekly,monthly,yearly',
            'term_count' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        // Calculate total amount (principal + interest)
        $principal = (float) $validated['principal_amount'];
        $interestRate = (float) ($validated['interest_rate'] ?? 0);
        $totalAmount = $principal + ($principal * $interestRate / 100);
        $validated['total_amount'] = round($totalAmount, 2);
        $validated['remaining_amount'] = round($totalAmount, 2);
        $validated['principal_amount'] = $principal;

        // Calculate term amount if payment schedule is set
        if (!empty($validated['payment_term']) && !empty($validated['term_count'])) {
            $validated['term_amount'] = round($totalAmount / (int) $validated['term_count'], 2);
        }

        Debt::create($validated);

        return redirect()->route('debts.index')
            ->with('success', 'Debt recorded successfully.');
    }

    public function show(Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) abort(403);
        $payments = DebtPayment::where('debt_id', $debt->id)
            ->orderBy('payment_date', 'desc')
            ->get();
        $schedule = $debt->generateSchedule();
        return view('debts.show', compact('debt', 'payments', 'schedule'));
    }

    public function edit(Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) abort(403);
        return view('debts.edit', compact('debt'));
    }

    public function update(Request $request, Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'payment_term' => 'nullable|in:weekly,biweekly,monthly,yearly',
            'term_count' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'status' => 'required|in:active,paid',
            'notes' => 'nullable|string',
        ]);

        // Recalculate total amount
        $principal = (float) $validated['principal_amount'];
        $interestRate = (float) ($validated['interest_rate'] ?? 0);
        $totalAmount = $principal + ($principal * $interestRate / 100);
        $validated['total_amount'] = round($totalAmount, 2);
        $validated['principal_amount'] = $principal;

        // Keep remaining_amount synced if it was the same as total before
        if ((float) $debt->total_amount === (float) $debt->remaining_amount) {
            $validated['remaining_amount'] = round($totalAmount, 2);
        }

        // Calculate term amount
        if (!empty($validated['payment_term']) && !empty($validated['term_count'])) {
            $validated['term_amount'] = round($totalAmount / (int) $validated['term_count'], 2);
        } else {
            $validated['term_amount'] = null;
        }

        $debt->update($validated);

        return redirect()->route('debts.index')
            ->with('success', 'Debt updated successfully.');
    }

    public function destroy(Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) abort(403);
        $debt->delete();
        return redirect()->route('debts.index')
            ->with('success', 'Debt deleted successfully.');
    }

    public function addPayment(Request $request, Debt $debt)
    {
        if ($debt->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['debt_id'] = $debt->id;
        DebtPayment::create($validated);

        // Update remaining amount
        $debt->remaining_amount = max(0, $debt->remaining_amount - $validated['amount']);
        if ($debt->remaining_amount == 0) {
            $debt->status = 'paid';
        }
        $debt->save();

        return redirect()->route('debts.show', $debt)
            ->with('success', 'Payment recorded successfully.');
    }
}
