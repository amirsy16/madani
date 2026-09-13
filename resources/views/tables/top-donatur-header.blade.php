<div class="mb-4">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        {{ $heading }}
    </h2>
    @if($description)
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            {{ $description }}
        </p>
    @endif
</div>
