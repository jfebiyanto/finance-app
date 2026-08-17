<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
     *
     * Minimal fields are enough: only `amount` is required. Receipt scans can
     * send `merchant` (alias for payee), `category_name` (auto find-or-create),
     * `type` (defaults to expense) and `transaction_date` (defaults to today).
     */
    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $category = $this->resolveCategory($user, $validated['type'] ?? 'expense', $validated);
        $transaction = $this->createTransaction($user, $validated, $category->id);

        return response()->json([
            'message' => 'Transaction recorded successfully.',
            'data' => $this->resource($transaction->load('category')),
            'category_created' => $category->wasRecentlyCreated,
        ], 201);
    }

    /**
     * Record several transactions in a single request (receipt batch import).
     * Items that fail validation are skipped and reported individually.
     */
    public function bulk(Request $request)
    {
        $request->validate(['transactions' => ['required', 'array', 'min:1', 'max:1000']]);
        $user = $request->user();

        $created = [];
        $errors = [];

        foreach ($request->input('transactions', []) as $index => $item) {
            $item = (array) $item;

            $validator = Validator::make($item, [
                'amount' => ['required', 'numeric', 'min:0'],
                'type' => ['nullable', Rule::in(['expense', 'income'])],
                'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('user_id', $user->id)],
                'category_name' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:255'],
                'payee' => ['nullable', 'string', 'max:255'],
                'merchant' => ['nullable', 'string', 'max:255'],
                'transaction_date' => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                $errors[] = ['index' => $index, 'error' => $validator->errors()->first()];
                continue;
            }

            if (! empty($item['category_id']) && ! empty($item['category_name'])) {
                $errors[] = ['index' => $index, 'error' => 'Provide either category_id or category_name, not both.'];
                continue;
            }

            try {
                $category = $this->resolveCategory($user, $item['type'] ?? 'expense', $item);
                $transaction = $this->createTransaction($user, $item, $category->id);
                $created[] = $this->resource($transaction->load('category')) + ['category_created' => $category->wasRecentlyCreated];
            } catch (\Throwable $e) {
                $errors[] = ['index' => $index, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'message' => count($created).' transaction(s) recorded, '.count($errors).' failed.',
            'created' => $created,
            'errors' => $errors,
        ], count($created) > 0 ? 201 : 422);
    }

    /**
     * Resolve a category from category_id or category_name, creating it on the
     * fly (as "Uncategorized" when neither is provided).
     */
    private function resolveCategory(User $user, string $type, array $input): Category
    {
        if (! empty($input['category_id'])) {
            return Category::findOrFail($input['category_id']);
        }

        $name = trim((string) ($input['category_name'] ?? 'Uncategorized'));

        return Category::findOrCreateForUser($user, $type, $name !== '' ? $name : 'Uncategorized');
    }

    /**
     * Persist a transaction with friendly defaults for receipt scans.
     */
    private function createTransaction(User $user, array $input, int $categoryId): Transaction
    {
        return Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'type' => $input['type'] ?? 'expense',
            'amount' => $input['amount'],
            'description' => $input['description'] ?? null,
            'payee' => $input['payee'] ?? $input['merchant'] ?? null,
            'transaction_date' => $input['transaction_date'] ?? now()->format('Y-m-d'),
        ]);
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
