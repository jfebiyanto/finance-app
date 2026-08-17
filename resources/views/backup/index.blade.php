<x-app-layout>
    <x-slot name="header">
        <h2 class="md-page-title">{{ __('Database Backup') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="md-alert md-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="md-alert md-alert-error">{{ session('error') }}</div>
            @endif

            <!-- Info banner -->
            <div class="md-card p-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-[var(--md-sys-color-on-surface)]">Database: <span class="font-semibold">{{ $database }}</span></p>
                    <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-1">{{ $tableCount }} tables • Structure + data • .sql format</p>
                </div>
                <span class="md-chip md-chip-primary">MySQL</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Download -->
                <div class="md-card p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-[var(--md-sys-color-primary-container)] text-[var(--md-sys-color-on-primary-container)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                        </span>
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Download Backup</h3>
                    </div>
                    <p class="text-sm text-[var(--md-sys-color-on-surface-variant)] mb-5">
                        Generate a complete <strong>.sql</strong> dump of the database (structure and data) and download it to your device.
                    </p>
                    <form method="POST" action="{{ route('backup.download') }}">
                        @csrf
                        <button type="submit" class="md-btn md-btn-primary w-full">
                            Download .sql file
                        </button>
                    </form>
                </div>

                <!-- Email -->
                <div class="md-card p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-[var(--md-sys-color-secondary-container)] text-[var(--md-sys-color-on-secondary-container)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)]">Email Backup</h3>
                    </div>
                    <p class="text-sm text-[var(--md-sys-color-on-surface-variant)] mb-5">
                        Send the <strong>.sql</strong> backup file to an email address of your choice.
                    </p>
                    <form method="POST" action="{{ route('backup.email') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="email" class="md-label">Send to email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="recipient@example.com" class="md-input" required>
                            @error('email') <p class="md-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="md-btn md-btn-tonal w-full">
                            Email backup
                        </button>
                    </form>
                </div>
            </div>

            <!-- Restore instructions -->
            <div class="md-card p-6 mt-6">
                <h3 class="text-lg font-semibold text-[var(--md-sys-color-on-surface)] mb-3">How to restore on another environment</h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-[var(--md-sys-color-on-surface-variant)]">
                    <li>Copy the downloaded <strong>.sql</strong> file to the target server.</li>
                    <li>Import it with your database tool (phpMyAdmin &gt; Import), or from the command line:</li>
                </ol>
                <pre class="mt-3 p-3 rounded-lg bg-[var(--md-sys-color-surface-variant)] text-xs overflow-x-auto text-[var(--md-sys-color-on-surface)]"><code>mysql -u username -p database_name &lt; backup.sql</code></pre>
                <p class="text-xs text-[var(--md-sys-color-on-surface-variant)] mt-3">The dump recreates every table and re-inserts all data, and handles foreign keys automatically.</p>
            </div>
        </div>
    </div>
</x-app-layout>
