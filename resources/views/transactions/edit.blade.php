<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Transaction') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" id="edit-type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required onchange="updateEditCategories()">
                            <option value="expense" {{ $transaction->type == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="income" {{ $transaction->type == 'income' ? 'selected' : '' }}>Income</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                        <label class="block text-sm font-medium text-gray-700">Amount (Rp)</label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $transaction->amount) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Payee <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="payee" value="{{ old('payee', $transaction->payee) }}" placeholder="e.g. Starbucks, Alfamart, Gojek"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('payee') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <input type="text" name="description" value="{{ old('description', $transaction->description) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
