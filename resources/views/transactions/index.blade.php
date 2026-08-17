<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="md-page-title">{{ __('Transactions') }}</h2>
            <a href="{{ route('transactions.create') }}" class="md-btn md-btn-primary">
                + Add Transaction
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="md-alert md-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="md-card p-4 mb-6">
                <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="md-label">Month</label>
                        <input type="month" name="month" value="{{ $month }}" class="md-input text-sm">
                    </div>
                    <div>
                        <label class="md-label">Type</label>
                        <select name="type" class="md-select text-sm">
                            <option value="">All</option>
                            <option value="expense" {{ $type == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="income" {{ $type == 'income' ? 'selected' : '' }}>Income</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="md-btn md-btn-tonal">Filter</button>
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="md-card p-4">
                    <p class="text-sm text-[var(--md-sys-color-on-surface-variant)]">Total Income</p>
                    <p class="text-xl font-bold text-[var(--md-status-positive)]">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>
                <div class="md-card p-4">
                    <p class="text-sm text-[var(--md-sys-color-on-surface-variant)]">Total Expenses</p>
                    <p class="text-xl font-bold text-[var(--md-status-negative)]">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="md-table-card">
                @if($transactions->count() > 0)
                    <table class="min-w-full divide-y divide-[var(--md-sys-color-outline-variant)]">
                        <thead>
                            <tr>
                                <th class="md-th">Date</th>
                                <th class="md-th">Category</th>
                                <th class="md-th">Payee</th>
                                <th class="md-th">Description</th>
                                <th class="md-th text-right">Amount</th>
                                <th class="md-th text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--md-sys-color-outline-variant)]">
                            @foreach($transactions as $transaction)
                                <tr class="md-row-hover">
                                    <td class="md-td text-[var(--md-sys-color-on-surface-variant)]">{{ $transaction->transaction_date->format('d M Y') }}</td>
                                    <td class="md-td">
                                        <span class="md-chip {{ $transaction->type == 'income' ? 'md-chip-positive' : 'md-chip-negative' }}">
                                            {{ $transaction->category->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="md-td text-[var(--md-sys-color-on-surface-variant)]">{{ $transaction->payee ?: '-' }}</td>
                                    <td class="md-td text-[var(--md-sys-color-on-surface-variant)]">{{ $transaction->description ?: '-' }}</td>
                                    <td class="md-td text-sm font-semibold text-right {{ $transaction->type == 'income' ? 'text-[var(--md-status-positive)]' : 'text-[var(--md-status-negative)]' }}">
                                        {{ $transaction->type == 'income' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="md-td text-right text-sm whitespace-nowrap">
                                        <a href="{{ route('transactions.edit', $transaction) }}" class="md-link mr-3">Edit</a>
                                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Delete this transaction?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="md-link-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-4">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-6 text-center text-[var(--md-sys-color-on-surface-variant)]">
                        No transactions found. <a href="{{ route('transactions.create') }}" class="md-link">Add your first transaction</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
