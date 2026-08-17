<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Debts') }}</h2>
            <a href="{{ route('debts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Add Debt
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <!-- Summary -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Total Active Debt</p>
                <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($totalActiveDebt, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($debts->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Principal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">After Interest</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Term</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($debts as $debt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('debts.show', $debt) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">{{ $debt->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-800">Rp {{ number_format($debt->principal_amount ?? $debt->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-800">Rp {{ number_format($debt->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold {{ $debt->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center text-gray-600">
                                        @if($debt->payment_term)
                                            {{ $debt->term_count }}x {{ ucfirst($debt->payment_term) }}<br>
                                            <span class="text-xs text-indigo-500">Rp {{ number_format($debt->term_amount, 0, ',', '.') }}/term</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $debt->due_date ? $debt->due_date->format('d M Y') : '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $debt->status == 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($debt->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <div class="inline-flex items-center gap-3">
                                            <a href="{{ route('debts.show', $debt) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="{{ route('debts.edit', $debt) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form method="POST" action="{{ route('debts.destroy', $debt) }}" class="inline-flex items-center" onsubmit="return confirm('Delete this debt?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-6 text-center text-gray-500">No debts recorded. <a href="{{ route('debts.create') }}" class="text-indigo-600 hover:text-indigo-800">Add a debt</a></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
