@props([
    'label' => null,
    'value' => null,
    'description' => null,
    'color' => 'gray',
    'icon' => null,
])

@php
    // Palet warna statis — jangan interpolasi string ke class Tailwind.
    $colorMap = [
        'success' => [
            'value' => 'text-emerald-600 dark:text-emerald-400',
            'icon' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400',
        ],
        'danger' => [
            'value' => 'text-rose-600 dark:text-rose-400',
            'icon' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400',
        ],
        'warning' => [
            'value' => 'text-amber-600 dark:text-amber-400',
            'icon' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
        ],
        'info' => [
            'value' => 'text-sky-600 dark:text-sky-400',
            'icon' => 'bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400',
        ],
        'primary' => [
            'value' => 'text-primary-700 dark:text-primary-400',
            'icon' => 'bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400',
        ],
        'gray' => [
            'value' => 'text-gray-900 dark:text-gray-100',
            'icon' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
        ],
    ];

    $palette = $colorMap[$color] ?? $colorMap['gray'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-4']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            @if($label !== null && $label !== '')
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ $label }}
                </p>
            @endif

            @if($value !== null && $value !== '')
                <p class="mt-1 text-2xl font-bold tabular-nums {{ $palette['value'] }}">
                    {{ $value }}
                </p>
            @endif

            @if($description !== null && $description !== '')
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if($icon !== null && $icon !== '')
            <div class="flex-shrink-0 rounded-lg p-2 {{ $palette['icon'] }}">
                <x-dynamic-component :component="$icon" class="w-5 h-5" />
            </div>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="mt-3">
            {{ $slot }}
        </div>
    @endif
</div>
