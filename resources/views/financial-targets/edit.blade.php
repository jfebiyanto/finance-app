<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Financial Target') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="POST" action="{{ route('financial-targets.update', $financialTarget) }}">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Target Name</label>
                        <input type="text" name="name" value="{{ old('name', $financialTarget->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach(['savings','debt_payment','investment','other'] as $t)
                                <option value="{{ $t }}" {{ $financialTarget->type == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Target Amount (Rp)</label>
                            <input type="number" step="0.01" min="0" name="target_amount" value="{{ old('target_amount', $financialTarget->target_amount) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Amount (Rp)</label>
                            <input type="number" step="0.01" min="0" name="current_amount" value="{{ old('current_amount', $financialTarget->current_amount) }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:ring-indigo-500" readonly>
                            <p class="text-xs text-gray-400 mt-1">Auto-calculated from linked investments. Set to 0 if no investments are linked.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Target Date</label>
                            <input type="date" name="target_date" value="{{ old('target_date', $financialTarget->target_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ $financialTarget->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="achieved" {{ $financialTarget->status == 'achieved' ? 'selected' : '' }}>Achieved</option>
                                <option value="cancelled" {{ $financialTarget->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $financialTarget->notes) }}</textarea>
                    </div>

                    {{-- Linked Investments Summary --}}
                    @if($financialTarget->investments->count() > 0)
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Linked Investments</h4>
                            <div class="space-y-2">
                                @foreach($financialTarget->investments as $inv)
                                    <div class="flex justify-between items-center text-sm">
                                        <div>
                                            <span class="text-gray-700">{{ $inv->name }}</span>
                                            @if($inv->status === 'sold')
                                                <span class="text-gray-400 text-xs ml-1">(sold)</span>
                                            @endif
                                        </div>
                                        <span class="{{ $inv->status === 'active' ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">
                                            Rp {{ number_format($inv->current_value, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between items-center text-sm font-semibold text-gray-800 mt-3 pt-3 border-t border-gray-200">
                                <span>Total from Investments</span>
                                <span class="text-indigo-600">
                                    Rp {{ number_format($financialTarget->investments->where('status', 'active')->sum('current_value'), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Linked Savings Summary --}}
                    @if($financialTarget->savings->count() > 0)
                        <div class="mb-4 p-4 bg-emerald-50 rounded-lg">
                            <h4 class="text-sm font-semibold text-emerald-800 mb-3">Linked Savings</h4>
                            <div class="space-y-2">
                                @foreach($financialTarget->savings as $saving)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-700">{{ $saving->name }}</span>
                                        <span class="text-emerald-600 font-medium">Rp {{ number_format($saving->current_value, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between items-center text-sm font-semibold text-gray-800 mt-3 pt-3 border-t border-emerald-200">
                                <span>Total from Savings</span>
                                <span class="text-emerald-600">Rp {{ number_format($financialTarget->savings->sum('current_value'), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('financial-targets.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
