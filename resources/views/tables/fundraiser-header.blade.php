<div class="mb-4">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        {{ $heading }}
    </h2>
    @if($description)
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            {{ $description }}
        </p>
    @endif
</div>
