@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2 rounded-full text-start text-base font-medium text-[var(--md-sys-color-on-secondary-container)] bg-[var(--md-sys-color-secondary-container)] transition duration-150 ease-in-out'
            : 'block w-full px-4 py-2 rounded-full text-start text-base font-medium text-[var(--md-sys-color-on-surface-variant)] hover:bg-[var(--md-sys-color-surface-variant)] hover:text-[var(--md-sys-color-on-surface)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
