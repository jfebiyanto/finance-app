<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Budgets') }}</h2>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('budgets.copyNextMonth') }}" class="inline">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200"
                        onclick="return confirm('Copy all budgets from {{ $month }} to next month?')">
                        Copy to Next Month
                    </button>
                </form>
                <a href="{{ route('budgets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    + Set Budget
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
            @endif

            <!-- Month Filter -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form method="GET" action="{{ route('budgets.index') }}" class="flex items-center gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month</label>
                        <input type="month" name="month" value="{{ $month }}"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500">Total Budget</p>
                    <p class="text-xl font-bold text-blue-600">Rp {{ number_format($totalBudget, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $totalSpent <= $totalBudget ? 'border-green-500' : 'border-red-500' }}">
                    <p class="text-sm text-gray-500">Total Spent</p>
                    <p class="text-xl font-bold {{ $totalSpent <= $totalBudget ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($totalSpent, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Budget vs Actual Report -->
            @if($reportBudgets->count() > 0)
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Budget vs Actual</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Budget</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actual</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Variance</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($reportBudgets as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $item->category->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-800">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right {{ $item->actual > $item->amount ? 'text-red-600 font-semibold' : 'text-gray-800' }}">
                                        Rp {{ number_format($item->actual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold {{ $item->variance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $item->variance >= 0 ? '+' : '' }}Rp {{ number_format($item->variance, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->onTrack)
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">On Track</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Over Budget</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <td class="px-4 py-3 text-sm font-bold text-gray-800">Total</td>
                                <td class="px-4 py-3 text-sm font-bold text-right text-gray-800">Rp {{ number_format($totalReportBudget, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-right {{ $totalReportActual > $totalReportBudget ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format($totalReportActual, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-right {{ ($totalReportBudget - $totalReportActual) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ ($totalReportBudget - $totalReportActual) >= 0 ? '+' : '' }}Rp {{ number_format($totalReportBudget - $totalReportActual, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            <!-- Budget List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($budgets->count() > 0)
                    <div class="p-6 space-y-6">
                        @foreach($budgets as $budget)
                            <div class="border-b border-gray-100 pb-4 last:border-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ $budget->category->name ?? 'N/A' }}</h4>
                                        <p class="text-sm text-gray-500">Rp {{ number_format($budget->spent, 0, ',', '.') }} of Rp {{ number_format($budget->amount, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold {{ $budget->percentage > 100 ? 'text-red-600' : ($budget->percentage > 75 ? 'text-yellow-600' : 'text-green-600') }}">
                                            {{ $budget->percentage }}%
                                        </span>
                                        <div class="flex gap-2 mt-1">
                                            <a href="{{ route('budgets.edit', $budget) }}" class="text-xs text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form method="POST" action="{{ route('budgets.destroy', $budget) }}" class="inline" onsubmit="return confirm('Delete this budget?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="h-3 rounded-full {{ $budget->percentage > 100 ? 'bg-red-500' : ($budget->percentage > 75 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                        style="width: {{ min(100, $budget->percentage) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">
                                    @if($budget->remaining > 0)
                                        Rp {{ number_format($budget->remaining, 0, ',', '.') }} remaining
                                    @else
                                        Budget exceeded by Rp {{ number_format(abs($budget->spent - $budget->amount), 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        No budgets set for this month. <a href="{{ route('budgets.create') }}" class="text-indigo-600 hover:text-indigo-800">Set a budget</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
