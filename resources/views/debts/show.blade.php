<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $debt->name }}</h2>
            <a href="{{ route('debts.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Back to Debts</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <!-- Debt Details -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Principal Amount</p>
                        <p class="text-lg font-bold text-gray-800">Rp {{ number_format($debt->principal_amount ?? $debt->total_amount, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Interest ({{ $debt->interest_rate ?? 0 }}%)</p>
                        <p class="text-lg font-bold text-orange-500">
                            Rp {{ number_format((float)($debt->principal_amount ?? $debt->total_amount) * (float)($debt->interest_rate ?? 0) / 100, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total After Interest</p>
                        <p class="text-lg font-bold text-gray-800">Rp {{ number_format($debt->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <hr class="my-3">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Remaining</p>
                        <p class="text-lg font-bold text-red-600">Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $debt->status == 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($debt->status) }}
                        </span>
                    </div>
                    @if($debt->payment_term)
                    <div>
                        <p class="text-sm text-gray-500">Payment Term</p>
                        <p class="text-lg font-bold text-gray-800">{{ ucfirst($debt->payment_term) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Per Term</p>
                        <p class="text-lg font-bold text-indigo-600">Rp {{ number_format($debt->term_amount, 0, ',', '.') }}</p>
                    </div>
                    @endif
                </div>
                @if($debt->due_date)
                    <p class="mt-2 text-sm text-gray-500">Due: {{ $debt->due_date->format('d M Y') }}</p>
                @endif
                @if($debt->notes)
                    <p class="mt-2 text-sm text-gray-600">{{ $debt->notes }}</p>
                @endif
            </div>

            <!-- Payment Schedule -->
            @if(count($schedule) > 0)
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Schedule</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($schedule as $term)
                            <tr class="{{ $term['paid'] ? 'bg-green-50' : '' }}">
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $term['term'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($term['due_date'])->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-right text-gray-800">Rp {{ number_format($term['amount'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($term['paid'])
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-gray-800">
                                Rp {{ number_format($debt->term_amount * $debt->term_count, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif

            <!-- Add Payment -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Record Payment</h3>
                <form method="POST" action="{{ route('debts.payments.store', $debt) }}" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (Rp)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <input type="text" name="notes" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Add Payment</button>
                </form>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment History</h3>
                @if($payments->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-green-600 text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $payment->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500 text-sm">No payments recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
