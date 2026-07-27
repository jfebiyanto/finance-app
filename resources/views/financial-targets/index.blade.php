<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Financial Targets') }}</h2>
            <a href="{{ route('financial-targets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Add Target
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($targets as $target)
                    <div class="bg-white rounded-xl shadow-sm p-6 {{ $target->status == 'achieved' ? 'border-2 border-green-400' : ($target->status == 'cancelled' ? 'opacity-60' : '') }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $target->name }}</h3>
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @if($target->type == 'savings') bg-blue-100 text-blue-800
                                    @elseif($target->type == 'debt_payment') bg-purple-100 text-purple-800
                                    @elseif($target->type == 'investment') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $target->type)) }}
                                </span>
                                <span class="ml-2 px-2 py-0.5 text-xs rounded-full
                                    @if($target->status == 'active') bg-yellow-100 text-yellow-800
                                    @elseif($target->status == 'achieved') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($target->status) }}
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('financial-targets.edit', $target) }}" class="text-xs text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="POST" action="{{ route('financial-targets.destroy', $target) }}" class="inline" onsubmit="return confirm('Delete this target?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500">Progress</span>
                                <span class="font-medium">{{ $target->progress_percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $target->progress_percentage >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                                    style="width: {{ min(100, $target->progress_percentage) }}%"></div>
                            </div>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Rp {{ number_format($target->current_amount, 0, ',', '.') }}</span>
                            <span class="text-gray-600">Target: Rp {{ number_format($target->target_amount, 0, ',', '.') }}</span>
                        </div>

                        @if($target->target_date)
                            <p class="text-xs text-gray-400 mt-2">Target date: {{ $target->target_date->format('d M Y') }}</p>
                        @endif
                        @if($target->notes)
                            <p class="text-xs text-gray-500 mt-1">{{ $target->notes }}</p>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
                        No financial targets yet. <a href="{{ route('financial-targets.create') }}" class="text-indigo-600 hover:text-indigo-800">Create your first target</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
