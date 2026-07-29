<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month }}"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    onchange="this.form.submit()">
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Today's Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Today's Income</p>
                    </div>
                    <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($todayIncome, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ now()->format('l, d F Y') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-rose-500">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Today's Spending</p>
                    </div>
                    <p class="text-2xl font-bold text-rose-600">Rp {{ number_format($todayExpenses, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ now()->format('l, d F Y') }}</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500">Total Income</p>
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $month }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                    <p class="text-2xl font-bold text-red-600">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $month }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-sm font-medium text-gray-500">Balance</p>
                    <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($monthlyIncome - $monthlyExpenses, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Income - Expenses</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                    <p class="text-sm font-medium text-gray-500">Active Debt</p>
                    <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($totalDebt, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total remaining</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Monthly Chart (Last 6 Months) -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Overview</h3>
                    <div class="space-y-3">
                        @foreach($monthlyData as $data)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">{{ $data['label'] }}</span>
                                    <span class="text-gray-800 font-medium">Rp {{ number_format($data['income'] - $data['expenses'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex gap-1 h-6">
                                    <div class="bg-green-400 rounded-l" style="width: {{ $data['income'] > 0 ? min(50, ($data['income'] / (max($data['income'], $data['expenses']) ?: 1)) * 50) : 0 }}%"></div>
                                    <div class="bg-red-400 rounded-r" style="width: {{ $data['expenses'] > 0 ? min(50, ($data['expenses'] / (max($data['income'], $data['expenses']) ?: 1)) * 50) : 0 }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-400 mt-0.5">
                                    <span>Income: Rp {{ number_format($data['income'], 0, ',', '.') }}</span>
                                    <span>Expenses: Rp {{ number_format($data['expenses'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Expenses by Category -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Expenses by Category</h3>
                    @if($expensesByCategory->count() > 0)
                        <div class="space-y-3">
                            @php $totalExpensesCat = $expensesByCategory->sum('total'); @endphp
                            @foreach($expensesByCategory as $item)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $item->category->name ?? 'Uncategorized' }}</span>
                                        <span class="text-gray-800 font-medium">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $totalExpensesCat > 0 ? ($item->total / $totalExpensesCat) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No expenses this month.</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Budget Progress -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Budget Progress</h3>
                        <a href="{{ route('budgets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    @if($budgets->count() > 0)
                        <div class="space-y-4">
                            @foreach($budgets as $budget)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $budget->category->name ?? 'N/A' }}</span>
                                        <span class="text-gray-800 font-medium">
                                            Rp {{ number_format($budget->spent, 0, ',', '.') }} / Rp {{ number_format($budget->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full {{ $budget->percentage > 100 ? 'bg-red-500' : ($budget->percentage > 75 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                            style="width: {{ min(100, $budget->percentage) }}%"></div>
                                    </div>
                                    <span class="text-xs {{ $budget->percentage > 100 ? 'text-red-500' : 'text-gray-400' }}">
                                        {{ $budget->percentage }}% used
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No budgets set for this month.</p>
                    @endif
                </div>

                <!-- Investment Summary -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Investment Summary</h3>
                        <a href="{{ route('investments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Total Invested</p>
                            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($totalInvested, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Current Value</p>
                            <p class="text-lg font-bold {{ $totalCurrentValue >= $totalInvested ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($totalCurrentValue, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @if($totalInvested > 0)
                        @php $change = (($totalCurrentValue - $totalInvested) / $totalInvested) * 100; @endphp
                        <p class="text-sm {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}% overall return
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Savings Summary -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Savings</h3>
                        <a href="{{ route('savings.index') }}" class="text-sm text-emerald-600 hover:text-emerald-800">View All</a>
                    </div>
                    @if($totalSavingsPrincipal > 0)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Total Deposited</p>
                                <p class="text-lg font-bold text-gray-800">Rp {{ number_format($totalSavingsPrincipal, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Current Value</p>
                                <p class="text-lg font-bold text-emerald-600">Rp {{ number_format($totalSavingsCurrent, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @if($totalSavingsInterest != 0)
                            <p class="text-sm {{ $totalSavingsInterest >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $totalSavingsInterest >= 0 ? '+' : '' }}Rp {{ number_format($totalSavingsInterest, 0, ',', '.') }} interest earned
                            </p>
                        @endif
                    @else
                        <p class="text-gray-500 text-sm">No savings yet. <a href="{{ route('investments.create') }}?type=savings" class="text-emerald-600 hover:text-emerald-800">Start saving</a></p>
                    @endif
                </div>

                <!-- Term Deposit Summary -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Time Deposits</h3>
                        <a href="{{ route('investments.create') }}?type=term_deposit" class="text-sm text-amber-600 hover:text-amber-800">+ Add Deposit</a>
                    </div>
                    @if($totalTermPrincipal > 0)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Total Deposited</p>
                                <p class="text-lg font-bold text-gray-800">Rp {{ number_format($totalTermPrincipal, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Maturity Value</p>
                                <p class="text-lg font-bold text-amber-600">Rp {{ number_format($totalTermCurrent, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @php $termReturn = $totalTermCurrent - $totalTermPrincipal; @endphp
                        @if($termReturn != 0)
                            <p class="text-sm text-amber-600">+Rp {{ number_format($termReturn, 0, ',', '.') }} interest at maturity</p>
                        @endif
                    @else
                        <p class="text-gray-500 text-sm">No time deposits yet. <a href="{{ route('investments.create') }}?type=term_deposit" class="text-amber-600 hover:text-amber-800">Create one</a></p>
                    @endif
                </div>
                <!-- Recent Transactions -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    @if($recentTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTransactions as $transaction)
                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $transaction->description ?: ($transaction->category->name ?? 'N/A') }}</p>
                                        <p class="text-xs text-gray-500">{{ $transaction->transaction_date->format('d M Y') }} • {{ $transaction->category->name ?? 'N/A' }}</p>
                                    </div>
                                    <span class="text-sm font-semibold {{ $transaction->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type == 'income' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No transactions yet.</p>
                    @endif
                </div>

                <!-- Active Financial Targets -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Active Targets</h3>
                        <a href="{{ route('financial-targets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    @if($targets->count() > 0)
                        <div class="space-y-4">
                            @foreach($targets as $target)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $target->name }}</span>
                                        <span class="text-gray-800 font-medium">{{ $target->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ $target->progress_percentage }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Rp {{ number_format($target->current_amount, 0, ',', '.') }} / Rp {{ number_format($target->target_amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No active targets. <a href="{{ route('financial-targets.create') }}" class="text-indigo-600 hover:text-indigo-800">Create one</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
