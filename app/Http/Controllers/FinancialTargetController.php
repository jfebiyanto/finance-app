<?php

namespace App\Http\Controllers;

use App\Models\FinancialTarget;
use Illuminate\Http\Request;

class FinancialTargetController extends Controller
{
    public function index()
    {
        $targets = FinancialTarget::where('user_id', auth()->id())
            ->orderBy('status')
            ->orderBy('target_date')
            ->get();
        return view('financial-targets.index', compact('targets'));
    }

    public function create()
    {
        return view('financial-targets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'target_date' => 'nullable|date',
            'type' => 'required|in:savings,debt_payment,investment,other',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        FinancialTarget::create($validated);

        return redirect()->route('financial-targets.index')
            ->with('success', 'Financial target created successfully.');
    }

    public function edit(FinancialTarget $financialTarget)
    {
        if ($financialTarget->user_id !== auth()->id()) abort(403);
        $financialTarget->load('investments', 'savings');
        return view('financial-targets.edit', compact('financialTarget'));
    }

    public function update(Request $request, FinancialTarget $financialTarget)
    {
        if ($financialTarget->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'target_date' => 'nullable|date',
            'type' => 'required|in:savings,debt_payment,investment,other',
            'status' => 'required|in:active,achieved,cancelled',
            'notes' => 'nullable|string',
        ]);

        $financialTarget->update($validated);

        return redirect()->route('financial-targets.index')
            ->with('success', 'Financial target updated successfully.');
    }

    public function destroy(FinancialTarget $financialTarget)
    {
        if ($financialTarget->user_id !== auth()->id()) abort(403);
        $financialTarget->delete();
        return redirect()->route('financial-targets.index')
            ->with('success', 'Financial target deleted successfully.');
    }
}
