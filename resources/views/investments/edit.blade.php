<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Investment') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="POST" action="{{ route('investments.update', $investment) }}">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Investment Name</label>
                        <input type="text" name="name" value="{{ old('name', $investment->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" id="investment-type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach(['stocks','crypto','gold','property','mutual_fund','term_deposit','other'] as $t)
                                <option value="{{ $t }}" {{ $investment->type == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Term Deposit Fields --}}
                    <div id="term-deposit-fields" class="mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200" style="display: none;">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-3">Term Deposit Details</h4>
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $investment->bank_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Bank Mandiri">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Term</label>
                                <select name="term_months" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Select --</option>
                                    <option value="1" {{ old('term_months', $investment->term_months) == 1 ? 'selected' : '' }}>1 Month</option>
                                    <option value="3" {{ old('term_months', $investment->term_months) == 3 ? 'selected' : '' }}>3 Months</option>
                                    <option value="6" {{ old('term_months', $investment->term_months) == 6 ? 'selected' : '' }}>6 Months</option>
                                    <option value="12" {{ old('term_months', $investment->term_months) == 12 ? 'selected' : '' }}>12 Months</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Interest Rate (% p.a.)</label>
                                <input type="number" step="0.01" min="0" max="100" name="interest_rate" value="{{ old('interest_rate', $investment->interest_rate) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 4.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Maturity Date</label>
                                <input type="date" name="maturity_date" value="{{ old('maturity_date', $investment->maturity_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="text-xs text-gray-400 mt-1">Auto-calculated from deposit date + term.</p>
                            </div>
                        </div>
                        @if($investment->isTermDeposit() && $investment->estimated_interest)
                            <p class="text-xs text-gray-600 mt-2">Estimated interest: <strong>Rp {{ number_format($investment->estimated_interest, 0, ',', '.') }}</strong> | Maturity value: <strong>Rp {{ number_format($investment->maturity_value, 0, ',', '.') }}</strong></p>
                        @endif
                    </div>

                    {{-- Shares & Avg Cost (hidden for term deposits) --}}
                    <div id="shares-fields" class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Shares & Avg Cost</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shares / Units</label>
                                <input type="number" step="0.0001" min="0" name="shares" value="{{ old('shares', $investment->shares) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Buying Price / Avg Cost (Rp)</label>
                                <input type="number" step="0.01" min="0" name="avg_cost" value="{{ old('avg_cost', $investment->avg_cost) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 580">
                            </div>
                        </div>
                    </div>


                    {{-- Interest Rate (shown for Savings & Term Deposit) --}}
                    <div id="interest-rate-field" class="mb-4" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700">Annual Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate" value="{{ old('interest_rate', $investment->interest_rate) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 4.5">
                        <p id="interest-rate-hint" class="text-xs text-gray-400 mt-1">Required for auto-calculating current value.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount Invested (Rp)</label>
                            <input type="number" step="0.01" min="0" name="amount_invested" value="{{ old('amount_invested', $investment->amount_invested) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Value (Rp)</label>
                            <input type="number" step="0.01" min="0" name="current_value" value="{{ old('current_value', $investment->current_value) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p id="current-value-hint" class="text-xs text-gray-400 mt-1">Leave empty to auto-calculate for Term Deposits.</p>                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Deposit / Purchase Date</label>
                        <input type="date" name="purchase_date" id="purchase-date" value="{{ old('purchase_date', $investment->purchase_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Link to Financial Target</label>
                        <select name="target_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- None --</option>
                            @foreach($targets as $target)
                                <option value="{{ $target->id }}" {{ old('target_id', $investment->target_id) == $target->id ? 'selected' : '' }}>{{ $target->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">The investment's current value will contribute to this target's progress.</p>
                    </div>

                    <script>
                        const typeSelect = document.getElementById('investment-type');
                        const termFields = document.getElementById('term-deposit-fields');
                        const sharesFields = document.getElementById('shares-fields');
                        const purchaseDateInput = document.getElementById('purchase-date');
                        const termMonthsSelect = document.querySelector('select[name="term_months"]');
                        const maturityDateInput = document.querySelector('input[name="maturity_date"]');

                        function toggleFields() {
                            const isTermDeposit = typeSelect.value === 'term_deposit';
                            termFields.style.display = isTermDeposit ? 'block' : 'none';
                            sharesFields.style.display = isTermDeposit ? 'none' : 'block';
                            document.getElementById('current-value-hint').textContent = isTermDeposit
                                ? 'Auto-calculated as principal + (principal × rate% × term/12).'
                                : 'Leave empty to auto-calculate for Term Deposits.';
                        }

                        function calcMaturity() {
                            if (purchaseDateInput.value && termMonthsSelect.value) {
                                const d = new Date(purchaseDateInput.value);
                                d.setMonth(d.getMonth() + parseInt(termMonthsSelect.value));
                                maturityDateInput.value = d.toISOString().split('T')[0];
                            }
                        }

                        typeSelect.addEventListener('change', toggleFields);
                        purchaseDateInput.addEventListener('change', calcMaturity);
                        termMonthsSelect.addEventListener('change', calcMaturity);
                        toggleFields();
                    </script>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $investment->notes) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-4">
                        <a href="{{ route('investments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
