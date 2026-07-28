<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Savings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="POST" action="{{ route('savings.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Savings Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Initial Deposit (Rp)</label>
                            <input type="number" step="0.01" min="0" name="amount_invested" id="amount_invested" value="{{ old('amount_invested') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                oninput="clearForeignCalc()">
                            <p class="text-xs text-gray-400 mt-1">Auto-calculated when foreign currency is filled below.</p>
                            @error('amount_invested') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Annual Interest Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="interest_rate" value="{{ old('interest_rate') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. 4.5">
                            @error('interest_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Foreign Currency Savings --}}
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-semibold text-blue-800 mb-3">Foreign Currency Savings (Optional)</h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency</label>
                                <select name="currency" id="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" onchange="autoCalcIdr()">
                                    <option value="">-- Select --</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="SGD" {{ old('currency') == 'SGD' ? 'selected' : '' }}>SGD</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="JPY" {{ old('currency') == 'JPY' ? 'selected' : '' }}>JPY</option>
                                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                                    <option value="MYR" {{ old('currency') == 'MYR' ? 'selected' : '' }}>MYR</option>
                                    <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD</option>
                                    <option value="CNY" {{ old('currency') == 'CNY' ? 'selected' : '' }}>CNY</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Exchange Rate (1 to IDR)</label>
                                <input type="number" step="0.01" min="0" name="exchange_rate" id="exchange_rate" value="{{ old('exchange_rate') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="e.g. 15000" oninput="autoCalcIdr()">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount in Foreign Currency</label>
                                <input type="number" step="0.01" min="0" name="amount_invested_foreign" id="amount_invested_foreign" value="{{ old('amount_invested_foreign') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="e.g. 1000" oninput="autoCalcIdr()">
                            </div>
                        </div>
                        <p class="text-xs text-blue-500 mt-2">Initial Deposit (IDR) will be auto-calculated as Foreign Amount × Exchange Rate.</p>
                    </div>

                    <script>
                    function autoCalcIdr() {
                        const rate = parseFloat(document.getElementById('exchange_rate').value);
                        const foreign = parseFloat(document.getElementById('amount_invested_foreign').value);
                        const currency = document.getElementById('currency').value;
                        if (currency && rate > 0 && foreign > 0) {
                            document.getElementById('amount_invested').value = (foreign * rate).toFixed(2);
                            document.getElementById('amount_invested').readOnly = true;
                        } else {
                            document.getElementById('amount_invested').readOnly = false;
                        }
                    }
                    function clearForeignCalc() {
                        // Allow manual entry if user edits IDR directly
                        document.getElementById('amount_invested').readOnly = false;
                    }
                    // Run on load for old() values
                    autoCalcIdr();
                    </script>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Deposit Date</label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('purchase_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Link to Financial Target (Optional)</label>
                        <select name="target_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- None --</option>
                            @foreach($targets as $target)
                                <option value="{{ $target->id }}" {{ old('target_id') == $target->id ? 'selected' : '' }}>{{ $target->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">The savings current value will contribute to this target's progress.</p>
                    </div>
                    <p class="text-xs text-gray-400 mb-4">Current value is auto-calculated as: Principal + (Principal × Rate% × Days/365 × 0.8).</p>
                    <div class="flex justify-end gap-4">
                        <a href="{{ route('savings.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
