<button {{ $attributes->merge(['type' => 'submit', 'class' => 'md-btn md-btn-error']) }}>
    {{ $slot }}
</button>
