<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * List the authenticated user's transactions.
     *
     * Query params (all optional):
     * - type: "expense" | "income"
     * - from: Y-m-d start date
     * - to: Y-m-d end date
     * - limit: max records (default 100, capped at 500)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->when($request->filled('type') && in_array($request->input('type'), ['expense', 'income']), function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('transaction_date', '>=', $request->input('from'));
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('transaction_date', '<=', $request->input('to'));
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(min((int) $request->input('limit', 100), 500))
            ->get();

        return response()->json([
            'data' => $transactions->map(fn (Transaction $transaction) => $this->resource($transaction)),
        ]);
    }

    /**
     * Record a new daily transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $transaction = Transaction::create($validated);

        return response()->json([
            'message' => 'Transaction recorded successfully.',
            'data' => $this->resource($transaction->load('category')),
        ], 201);
    }

    private function resource(Transaction $transaction): array
    {
        $date = $transaction->transaction_date
            ? \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('Y-m-d')
            : null;

        return [
            'id' => $transaction->id,
            'category_id' => $transaction->category_id,
            'category' => $transaction->category?->name,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'description' => $transaction->description,
            'payee' => $transaction->payee,
            'transaction_date' => $date,
            'created_at' => $transaction->created_at?->toIso8601String(),
        ];
    }
}
