<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="md-page-title">{{ __('Import Transactions') }}</h2>
            <a href="{{ route('transactions.index') }}" class="md-link">← Back to Transactions</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('importSuccessCount'))
                <div class="md-alert md-alert-success">
                    {{ session('importSuccessCount') }} transaction(s) imported successfully.
                </div>
            @endif

            @if(session('importErrors') && count(session('importErrors')) > 0)
                <div class="md-alert md-alert-error">
                    <div>
                        <p class="font-semibold mb-2">The following rows could not be imported:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach(session('importErrors') as $error)
                                <li>Row {{ $error['row'] }}: {{ $error['error'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Download template -->
                <div class="md-card p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-[var(--md-sys-color-primary-container)] text-[var(--md-sys-color-on-primary-container)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                        </span>
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Download Template</h3>
                    </div>
                    <p class="text-sm text-[var(--md-sys-color-on-surface-variant)] mb-5">
                        Download the <strong>.xlsx</strong> template, fill in your transactions, then upload it.
                        It includes an example row and a list of your existing categories.
                    </p>
                    <a href="{{ route('transactions.template') }}" class="md-btn md-btn-tonal w-full">
                        Download template (.xlsx)
                    </a>
                </div>

                <!-- Upload -->
                <div class="md-card p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-[var(--md-sys-color-secondary-container)] text-[var(--md-sys-color-on-secondary-container)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </span>
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Upload File</h3>
                    </div>
                    <p class="text-sm text-[var(--md-sys-color-on-surface-variant)] mb-5">
                        Upload your filled template (.xlsx, .xls or .csv). Only <strong>Amount</strong> is required per row.
                    </p>
                    <form method="POST" action="{{ route('transactions.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="file" class="md-label">Excel / CSV file</label>
                            <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="md-input" required>
                            @error('file') <p class="md-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="md-btn md-btn-primary w-full">Import transactions</button>
                    </form>
                </div>
            </div>

            <!-- Column reference -->
            <div class="md-card p-6 mt-6">
                <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)] mb-4">Template columns</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[var(--md-sys-color-outline-variant)]">
                        <thead>
                            <tr>
                                <th class="md-th">Column</th>
                                <th class="md-th">Required</th>
                                <th class="md-th">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--md-sys-color-outline-variant)]">
                            <tr class="md-row-hover">
                                <td class="md-td font-medium">Amount</td>
                                <td class="md-td"><span class="md-chip md-chip-negative">Required</span></td>
                                <td class="md-td">Transaction amount (number ≥ 0)</td>
                            </tr>
                            <tr class="md-row-hover">
                                <td class="md-td font-medium">Type</td>
                                <td class="md-td"><span class="md-chip md-chip-neutral">Optional</span></td>
                                <td class="md-td"><code>expense</code> (default) or <code>income</code></td>
                            </tr>
                            <tr class="md-row-hover">
                                <td class="md-td font-medium">Date</td>
                                <td class="md-td"><span class="md-chip md-chip-neutral">Optional</span></td>
                                <td class="md-td">YYYY-MM-DD — defaults to today if blank</td>
                            </tr>
                            <tr class="md-row-hover">
                                <td class="md-td font-medium">Category</td>
                                <td class="md-td"><span class="md-chip md-chip-neutral">Optional</span></td>
                                <td class="md-td">Category name — reused if it exists, otherwise auto-created. Blank = Uncategorized</td>
                            </tr>
                            <tr class="md-row-hover">
                                <td class="md-td font-medium">Payee</td>
                                <td class="md-td"><span class="md-chip md-chip-neutral">Optional</span></td>
                                <td class="md-td">Merchant / store name</td>
                            </tr>
                            <tr class="md-row-hover">
                                <td class="md-td font-medium">Description</td>
                                <td class="md-td"><span class="md-chip md-chip-neutral">Optional</span></td>
                                <td class="md-td">Free-text note</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
