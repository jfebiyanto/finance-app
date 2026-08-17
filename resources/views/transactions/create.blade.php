<x-app-layout>
    <x-slot name="header">
        <h2 class="md-page-title">{{ __('Add Transaction') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="md-card p-6">
                <form method="POST" action="{{ route('transactions.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="md-label">Type</label>
                        <select name="type" id="type" class="md-select" required onchange="updateCategories()">
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                        @error('type') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Category</label>
                        <select name="category_id" class="md-select" required>
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-type="{{ $category->type }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Amount (Rp)</label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="md-input" required>
                        @error('amount') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Payee <span class="md-hint">(optional)</span></label>
                        <input type="text" name="payee" value="{{ old('payee') }}" placeholder="e.g. Starbucks, Alfamart, Gojek" class="md-input">
                        @error('payee') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="md-input">
                        @error('description') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="md-label">Date</label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" class="md-input" required>
                        @error('transaction_date') <p class="md-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('transactions.index') }}" class="md-btn md-btn-outlined">Cancel</a>
                        <button type="submit" class="md-btn md-btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateCategories() {
            const type = document.getElementById('type').value;
            const catSelect = document.querySelector('select[name="category_id"]');
            catSelect.value = '';
            document.querySelectorAll('[data-type]').forEach(opt => {
                opt.style.display = opt.dataset.type === type ? '' : 'none';
            });
        }
        updateCategories();
    </script>
</x-app-layout>
