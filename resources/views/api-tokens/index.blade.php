<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="md-page-title">{{ __('API Tokens') }}</h2>
            <a href="{{ route('profile.edit') }}" class="md-link">← Back to Profile</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('apiToken'))
                <div class="md-alert md-alert-warn">
                    <div>
                        <p class="font-semibold mb-1">Copy your new API token now — it won't be shown again.</p>
                        <code class="block break-all bg-white border border-amber-200 rounded p-3 text-sm">{{ session('apiToken') }}</code>
                    </div>
                </div>
            @endif

            @if(session('status') === 'token-revoked')
                <div class="md-alert md-alert-success">Token revoked.</div>
            @endif

            <!-- Create token -->
            <div class="md-card p-6 sm:p-8">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-[var(--md-sys-color-on-surface)]">Create API Token</h3>
                    <p class="mt-1 text-sm text-[var(--md-sys-color-on-surface-variant)]">
                        Use this token to authenticate requests to <code class="text-[var(--md-sys-color-on-surface)]">/api/*</code> with
                        <code class="text-[var(--md-sys-color-on-surface)]">Authorization: Bearer &lt;token&gt;</code>.
                    </p>
                    <form method="POST" action="{{ route('api-tokens.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="md-label">Token name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="e.g. phone-app, cron-script" class="md-input" required>
                            @error('name') <p class="md-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-4">
                            <button type="submit" class="md-btn md-btn-primary">Create Token</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing tokens -->
            <div class="md-card p-6 sm:p-8">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-[var(--md-sys-color-on-surface)]">Your Tokens</h3>
                    @if($tokens->count() > 0)
                        <ul class="mt-4 divide-y divide-[var(--md-sys-color-outline-variant)]">
                            @foreach($tokens as $token)
                                <li class="py-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-[var(--md-sys-color-on-surface)]">{{ $token->name }}</p>
                                        <p class="text-xs text-[var(--md-sys-color-on-surface-variant)]">Last used: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'never' }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('api-tokens.destroy', $token) }}" onsubmit="return confirm('Revoke this token?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="md-link-danger">Revoke</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-[var(--md-sys-color-on-surface-variant)]">No tokens yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
