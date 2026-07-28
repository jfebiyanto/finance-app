<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Investments') }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('investments.index', $showHistory ? [] : ['history' => 1]) }}"
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md {{ $showHistory ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $showHistory ? '● Active' : '○ Show History' }}
                </a>
                <a href="{{ route('investments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    + Add Investment
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500">Total Invested</p>
                    <p class="text-xl font-bold text-blue-600">Rp {{ number_format($totalInvested, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Current Value</p>
                    <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalCurrentValue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $totalProfitLoss >= 0 ? 'border-green-500' : 'border-red-500' }}">
                    <p class="text-sm text-gray-500">Profit/Loss</p>
                    <p class="text-xl font-bold {{ $totalProfitLoss >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $totalProfitLoss >= 0 ? '+' : '-' }}Rp {{ number_format(abs($totalProfitLoss), 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($investments->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Shares</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Invested</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Value</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">P/L</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Return</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($investments as $investment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-800">{{ $investment->name }}</span>
                                            @if($investment->status === 'sold')
                                                <span class="px-1.5 py-0.5 text-xs rounded bg-gray-200 text-gray-500">Sold</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($investment->status === 'sold')
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">{{ ucfirst(str_replace('_', ' ', $investment->type)) }}</span>
                                        @elseif($investment->isTermDeposit())
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Term Deposit</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ ucfirst($investment->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-800">
                                        @if($investment->shares)
                                            {{ number_format($investment->shares, 4, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">
                                        @if($investment->avg_cost)
                                            Rp {{ number_format($investment->avg_cost, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-800">Rp {{ number_format($investment->amount_invested, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-800">
                                        <!-- Inline edit for current_value -->
                                        <form method="POST" action="{{ route('investments.updateValue', $investment) }}" class="inline-flex items-center gap-1">
                                            @csrf @method('PATCH')
                                            <input type="number" step="0.01" min="0" name="current_value" value="{{ $investment->current_value }}"
                                                class="w-28 text-right text-sm border-gray-200 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
                                                onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold {{ $investment->profit_loss >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $investment->profit_loss >= 0 ? '+' : '-' }}Rp {{ number_format(abs($investment->profit_loss), 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right {{ $investment->return_percentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $investment->return_percentage >= 0 ? '+' : '' }}{{ $investment->return_percentage }}%
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                        @if($investment->status === 'active')
                                            <button onclick="document.getElementById('top-up-{{ $investment->id }}').showModal()" class="text-green-600 hover:text-green-900 mr-2 font-medium">Top Up</button>
                                            <button onclick="document.getElementById('mark-sold-{{ $investment->id }}').showModal()" class="text-amber-600 hover:text-amber-900 mr-2">Sell</button>
                                        @endif
                                        <a href="{{ route('investments.edit', $investment) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                        <form method="POST" action="{{ route('investments.destroy', $investment) }}" class="inline" onsubmit="return confirm('Delete this investment?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>

                                        {{-- Target badge --}}
                                        @if($investment->target)
                                            <div class="text-xs text-gray-500 mt-1">Target: <a href="{{ route('financial-targets.edit', $investment->target) }}" class="text-indigo-500 hover:text-indigo-700">{{ $investment->target->name }}</a></div>
                                        @endif

                                        {{-- Top Up Dialog --}}
                                        <dialog id="top-up-{{ $investment->id }}" class="rounded-xl shadow-xl p-6 w-96">
                                            <div class="flex justify-between items-center mb-4">
                                                <h3 class="text-lg font-semibold text-gray-800">Top Up "{{ $investment->name }}"</h3>
                                                <button type="button" onclick="this.closest('dialog').close()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                                            </div>
                                            <form method="POST" action="{{ route('investments.topUp', $investment) }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700">Additional Amount (Rp)</label>
                                                    <input type="number" step="0.01" min="0.01" name="additional_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700">Additional Shares (optional)</label>
                                                    <input type="number" step="0.0001" min="0" name="additional_shares" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 50">
                                                    @if($investment->shares > 0)
                                                        <p class="text-xs text-gray-400 mt-1">Current shares: {{ number_format($investment->shares, 4, ',', '.') }} @ Avg: Rp {{ number_format($investment->avg_cost, 0, ',', '.') }}</p>
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700">New Current Value (optional)</label>
                                                    <input type="number" step="0.01" min="0" name="new_current_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Leave empty to auto-calculate">
                                                </div>
                                                <div class="flex justify-end gap-3">
                                                    <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</button>
                                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Confirm Top Up</button>
                                                </div>
                                            </form>
                                        </dialog>

                                        {{-- Mark as Sold Dialog --}}
                                        <dialog id="mark-sold-{{ $investment->id }}" class="rounded-xl shadow-xl p-6 w-96">
                                            <div class="flex justify-between items-center mb-4">
                                                <h3 class="text-lg font-semibold text-gray-800">Sell "{{ $investment->name }}"</h3>
                                                <button type="button" onclick="this.closest('dialog').close()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                                            </div>
                                            <form method="POST" action="{{ route('investments.markSold', $investment) }}">
                                                @csrf
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700">Final Sell Value (Rp)</label>
                                                    <input type="number" step="0.01" min="0" name="current_value" value="{{ $investment->current_value }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700">Sell Date</label>
                                                    <input type="date" name="sold_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                </div>
                                                <div class="flex justify-end gap-3">
                                                    <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</button>
                                                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">Confirm Sell</button>
                                                </div>
                                            </form>
                                        </dialog>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-6 text-center text-gray-500">No investments yet. <a href="{{ route('investments.create') }}" class="text-indigo-600 hover:text-indigo-800">Add an investment</a></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
