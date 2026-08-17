<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="md-page-title">
                {{ __('Dashboard') }}
            </h2>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month }}"
                    class="md-input w-auto text-sm"
                    onchange="this.form.submit()">
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Today's Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-[var(--md-status-positive-container)] text-[var(--md-status-positive)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <p class="text-xs font-medium text-[var(--md-sys-color-on-surface-variant)]">Today's Income</p>
                    </div>
                    <p class="text-2xl font-bold text-[var(--md-status-positive)]">Rp {{ number_format($todayIncome, 0, ',', '.') }}</p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">{{ now()->format('l, d F Y') }}</p>
                </div>
                <div class="md-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-[var(--md-status-negative-container)] text-[var(--md-status-negative)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </span>
                        <p class="text-xs font-medium text-[var(--md-sys-color-on-surface-variant)]">Today's Spending</p>
                    </div>
                    <p class="text-2xl font-bold text-[var(--md-status-negative)]">Rp {{ number_format($todayExpenses, 0, ',', '.') }}</p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">{{ now()->format('l, d F Y') }}</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="md-card p-6">
                    <p class="text-sm font-medium text-[var(--md-sys-color-on-surface-variant)]">Total Income</p>
                    <p class="text-2xl font-bold text-[var(--md-status-positive)]">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">{{ $month }}</p>
                </div>
                <div class="md-card p-6">
                    <p class="text-sm font-medium text-[var(--md-sys-color-on-surface-variant)]">Total Expenses</p>
                    <p class="text-2xl font-bold text-[var(--md-status-negative)]">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">{{ $month }}</p>
                </div>
                <div class="md-card p-6">
                    <p class="text-sm font-medium text-[var(--md-sys-color-on-surface-variant)]">Balance</p>
                    <p class="text-2xl font-bold text-[var(--md-sys-color-primary)]">Rp {{ number_format($monthlyIncome - $monthlyExpenses, 0, ',', '.') }}</p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">Income - Expenses</p>
                </div>
                <div class="md-card p-6">
                    <p class="text-sm font-medium text-[var(--md-sys-color-on-surface-variant)]">Active Debt</p>
                    <p class="text-2xl font-bold text-[var(--md-sys-color-tertiary)]">Rp {{ number_format($totalDebt, 0, ',', '.') }}</p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">Total remaining</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Monthly Chart (Last 6 Months) -->
                <div class="md-card p-6">
                    <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)] mb-4">Monthly Overview</h3>
                    <div class="space-y-3">
                        @foreach($monthlyData as $data)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-[var(--md-sys-color-on-surface-variant)]">{{ $data['label'] }}</span>
                                    <span class="text-[var(--md-sys-color-on-surface)] font-medium">Rp {{ number_format($data['income'] - $data['expenses'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex gap-1 h-6">
                                    <div class="bg-[var(--md-status-positive)] rounded-l-full" style="width: {{ $data['income'] > 0 ? min(50, ($data['income'] / (max($data['income'], $data['expenses']) ?: 1)) * 50) : 0 }}%"></div>
                                    <div class="bg-[var(--md-status-negative)] rounded-r-full" style="width: {{ $data['expenses'] > 0 ? min(50, ($data['expenses'] / (max($data['income'], $data['expenses']) ?: 1)) * 50) : 0 }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-[var(--md-sys-color-on-surface-variant)] mt-0.5">
                                    <span>Income: Rp {{ number_format($data['income'], 0, ',', '.') }}</span>
                                    <span>Expenses: Rp {{ number_format($data['expenses'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Expenses by Category (Pie Chart) -->
                <div class="md-card p-6">
                    <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)] mb-4">Expenses by Category</h3>
                    @if(count($categoryChart['data']) > 0)
                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <div class="relative w-56 h-56 shrink-0">
                                <canvas id="expensesPieChart"></canvas>
                            </div>
                            <div class="flex-1 w-full space-y-2">
                                @foreach($categoryChart['labels'] as $i => $label)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-2 text-[var(--md-sys-color-on-surface-variant)]">
                                            <span class="inline-block w-3 h-3 rounded-full shrink-0" style="background-color: {{ $categoryChart['colors'][$i] }}"></span>
                                            {{ $label }}
                                        </span>
                                        <span class="font-medium text-[var(--md-sys-color-on-surface)]">Rp {{ number_format($categoryChart['data'][$i], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-[var(--md-sys-color-on-surface-variant)] text-sm">No expenses this month.</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Budget Progress -->
                <div class="md-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Budget Progress</h3>
                        <a href="{{ route('budgets.index') }}" class="md-link">View All</a>
                    </div>
                    @if($budgets->count() > 0)
                        <div class="space-y-4">
                            @foreach($budgets as $budget)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-[var(--md-sys-color-on-surface-variant)]">{{ $budget->category->name ?? 'N/A' }}</span>
                                        <span class="text-[var(--md-sys-color-on-surface)] font-medium">
                                            Rp {{ number_format($budget->spent, 0, ',', '.') }} / Rp {{ number_format($budget->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="md-progress-track h-2.5">
                                        <div class="md-progress-fill {{ $budget->percentage > 100 ? 'md-progress-fill-negative' : ($budget->percentage > 75 ? 'md-progress-fill-warn' : 'md-progress-fill-positive') }}"
                                            style="width: {{ min(100, $budget->percentage) }}%"></div>
                                    </div>
                                    <span class="text-xs {{ $budget->percentage > 100 ? 'text-[var(--md-status-negative)]' : 'text-[var(--md-sys-color-on-surface-variant)]' }}">
                                        {{ $budget->percentage }}% used
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[var(--md-sys-color-on-surface-variant)] text-sm">No budgets set for this month.</p>
                    @endif
                </div>

                <!-- Investment Summary -->
                <div class="md-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Investment Summary</h3>
                        <a href="{{ route('investments.index') }}" class="md-link">View All</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="md-surface-variant rounded-xl p-3">
                            <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Total Invested</p>
                            <p class="text-lg font-bold text-[var(--md-sys-color-on-surface)]">Rp {{ number_format($totalInvested, 0, ',', '.') }}</p>
                        </div>
                        <div class="md-surface-variant rounded-xl p-3">
                            <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Current Value</p>
                            <p class="text-lg font-bold {{ $totalCurrentValue >= $totalInvested ? 'text-[var(--md-status-positive)]' : 'text-[var(--md-status-negative)]' }}">
                                Rp {{ number_format($totalCurrentValue, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @if($totalInvested > 0)
                        @php $change = (($totalCurrentValue - $totalInvested) / $totalInvested) * 100; @endphp
                        <p class="text-sm {{ $change >= 0 ? 'text-[var(--md-status-positive)]' : 'text-[var(--md-status-negative)]' }}">
                            {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}% overall return
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Savings Summary -->
                <div class="md-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Savings</h3>
                        <a href="{{ route('savings.index') }}" class="md-link">View All</a>
                    </div>
                    @if($totalSavingsPrincipal > 0)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="md-surface-variant rounded-xl p-3">
                                <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Total Deposited</p>
                                <p class="text-lg font-bold text-[var(--md-sys-color-on-surface)]">Rp {{ number_format($totalSavingsPrincipal, 0, ',', '.') }}</p>
                            </div>
                            <div class="md-surface-variant rounded-xl p-3">
                                <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Current Value</p>
                                <p class="text-lg font-bold text-[var(--md-status-positive)]">Rp {{ number_format($totalSavingsCurrent, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @if($totalSavingsInterest != 0)
                            <p class="text-sm {{ $totalSavingsInterest >= 0 ? 'text-[var(--md-status-positive)]' : 'text-[var(--md-status-negative)]' }}">
                                {{ $totalSavingsInterest >= 0 ? '+' : '' }}Rp {{ number_format($totalSavingsInterest, 0, ',', '.') }} interest earned
                            </p>
                        @endif
                    @else
                        <p class="text-[var(--md-sys-color-on-surface-variant)] text-sm">No savings yet. <a href="{{ route('investments.create') }}?type=savings" class="md-link">Start saving</a></p>
                    @endif
                </div>

                <!-- Term Deposit Summary -->
                <div class="md-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Time Deposits</h3>
                        <a href="{{ route('investments.create') }}?type=term_deposit" class="md-link">+ Add Deposit</a>
                    </div>
                    @if($totalTermPrincipal > 0)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="md-surface-variant rounded-xl p-3">
                                <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Total Deposited</p>
                                <p class="text-lg font-bold text-[var(--md-sys-color-on-surface)]">Rp {{ number_format($totalTermPrincipal, 0, ',', '.') }}</p>
                            </div>
                            <div class="md-surface-variant rounded-xl p-3">
                                <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Maturity Value</p>
                                <p class="text-lg font-bold text-[var(--md-status-warn)]">Rp {{ number_format($totalTermCurrent, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @php $termReturn = $totalTermCurrent - $totalTermPrincipal; @endphp
                        @if($termReturn != 0)
                            <p class="text-sm text-[var(--md-status-warn)]">+Rp {{ number_format($termReturn, 0, ',', '.') }} interest at maturity</p>
                        @endif
                    @else
                        <p class="text-[var(--md-sys-color-on-surface-variant)] text-sm">No time deposits yet. <a href="{{ route('investments.create') }}?type=term_deposit" class="md-link">Create one</a></p>
                    @endif
                </div>
                <!-- Recent Transactions -->
                <div class="md-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="md-link">View All</a>
                    </div>
                    @if($recentTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTransactions as $transaction)
                                <div class="flex justify-between items-center pb-2 border-b border-[var(--md-sys-color-outline-variant)]">
                                    <div>
                                        <p class="text-sm font-medium text-[var(--md-sys-color-on-surface)]">{{ $transaction->description ?: ($transaction->category->name ?? 'N/A') }}</p>
                                        <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">{{ $transaction->transaction_date->format('d M Y') }} • {{ $transaction->category->name ?? 'N/A' }}</p>
                                    </div>
                                    <span class="text-sm font-semibold {{ $transaction->type == 'income' ? 'text-[var(--md-status-positive)]' : 'text-[var(--md-status-negative)]' }}">
                                        {{ $transaction->type == 'income' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[var(--md-sys-color-on-surface-variant)] text-sm">No transactions yet.</p>
                    @endif
                </div>

                <!-- Active Financial Targets -->
                <div class="md-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Active Targets</h3>
                        <a href="{{ route('financial-targets.index') }}" class="md-link">View All</a>
                    </div>
                    @if($targets->count() > 0)
                        <div class="space-y-4">
                            @foreach($targets as $target)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-[var(--md-sys-color-on-surface-variant)]">{{ $target->name }}</span>
                                        <span class="text-[var(--md-sys-color-on-surface)] font-medium">{{ $target->progress_percentage }}%</span>
                                    </div>
                                    <div class="md-progress-track h-2.5">
                                        <div class="md-progress-fill" style="width: {{ $target->progress_percentage }}%"></div>
                                    </div>
                                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">
                                        Rp {{ number_format($target->current_amount, 0, ',', '.') }} / Rp {{ number_format($target->target_amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[var(--md-sys-color-on-surface-variant)] text-sm">No active targets. <a href="{{ route('financial-targets.create') }}" class="md-link">Create one</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(count($categoryChart['data']) > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('expensesPieChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($categoryChart['labels']),
                        datasets: [{
                            data: @json($categoryChart['data']),
                            backgroundColor: @json($categoryChart['colors']),
                            borderWidth: 2,
                            borderColor: '#F7F2FA',
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '62%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                        return ' ' + context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
