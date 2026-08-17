@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'md-alert md-alert-info']) }}>
        {{ $status }}
    </div>
@endif
