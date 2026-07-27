<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Savings') }}</h2>
            <a href="{{ route('savings.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                + Add Savings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-emerald-500">
                    <p class="text-sm text-gray-500">Total Deposited</p>
                    <p class="text-xl font-bold text-gray-800">Rp {{ number_format($totalPrincipal, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-emerald-500">
                    <p class="text-sm text-gray-500">Current Value</p>
                    <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalCurrent, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $totalInterest >= 0 ? 'border-emerald-500' : 'border-red-500' }}">
                    <p class="text-sm text-gray-500">Interest Earned</p>
                    <p class="text-xl font-bold {{ $totalInterest >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $totalInterest >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalInterest), 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($savings->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Currency</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deposited</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Interest Rate</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Value</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Deposit Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($savings as $saving)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $saving->name }}</td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        @if($saving->currency)
                                            <span class="font-medium text-gray-700">{{ $saving->currency }}</span>
                                            @if($saving->amount_invested_foreign)
                                                <br><span class="text-xs text-gray-400">{{ number_format($saving->amount_invested_foreign, 2) }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">IDR</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-800">Rp {{ number_format($saving->amount_invested, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $saving->interest_rate ? $saving->interest_rate.'%' : '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-emerald-600">Rp {{ number_format($saving->current_value, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $saving->purchase_date ? $saving->purchase_date->format('d M Y') : '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('savings.edit', $saving) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form method="POST" action="{{ route('savings.destroy', $saving) }}" class="inline" onsubmit="return confirm('Delete this saving?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-6 text-center text-gray-500">No savings yet. <a href="{{ route('savings.create') }}" class="text-emerald-600 hover:text-emerald-800">Start saving</a></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
