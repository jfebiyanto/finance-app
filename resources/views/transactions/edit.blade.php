<x-app-layout>
    <x-slot name="header">
        <h2 class="md-page-title">{{ __('Edit Transaction') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="md-card p-6">
                <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="md-label">Type</label>
                        <select name="type" id="edit-type" class="md-select" required onchange="updateEditCategories()">
                            <option value="expense" {{ $transaction->type == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="income" {{ $transaction->type == 'income' ? 'selected' : '' }}>Income</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Category</label>
                        <select name="category_id" class="md-select" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-type="{{ $category->type }}" {{ $transaction->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <script>
                        function updateEditCategories() {
                            const type = document.getElementById('edit-type').value;
                            document.querySelectorAll('select[name="category_id"] [data-type]').forEach(opt => {
                                opt.style.display = opt.dataset.type === type ? '' : 'none';
                            });
                        }
                        updateEditCategories();
                    </script>

                    <div class="mb-4">
                        <label class="md-label">Amount (Rp)</label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $transaction->amount) }}" class="md-input" required>
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Payee <span class="md-hint">(optional)</span></label>
                        <input type="text" name="payee" value="{{ old('payee', $transaction->payee) }}" placeholder="e.g. Starbucks, Alfamart, Gojek" class="md-input">
                        @error('payee') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Description</label>
                        <input type="text" name="description" value="{{ old('description', $transaction->description) }}" class="md-input">
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Date</label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" class="md-input" required>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('transactions.index') }}" class="md-btn md-btn-outlined">Cancel</a>
                        <button type="submit" class="md-btn md-btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
