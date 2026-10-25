@php
    // Palet warna statis — jangan interpolasi string ke class Tailwind.
    $event = (string) (isset($getState) ? $getState() : null);
    [$label, $ikon, $ikonBg, $teks] = match ($event) {
        'created' => ['Menambahkan', 'heroicon-o-plus-circle', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400', 'text-emerald-600 dark:text-emerald-400'],
        'updated' => ['Memperbarui', 'heroicon-o-pencil-square', 'bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400', 'text-amber-600 dark:text-amber-400'],
        'deleted' => ['Menghapus', 'heroicon-o-trash', 'bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400', 'text-rose-600 dark:text-rose-400'],
        'restored' => ['Memulihkan', 'heroicon-o-arrow-path', 'bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400', 'text-sky-600 dark:text-sky-400'],
        default => [ucfirst($event), 'heroicon-o-bolt', 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400', 'text-gray-600 dark:text-gray-400'],
    };
@endphp
<div class="flex items-center gap-2">
    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full {{ $ikonBg }}">
        <x-dynamic-component :component="$ikon" class="h-4 w-4" />
    </div>
    <span class="text-sm font-medium {{ $teks }}">{{ $label }}</span>
</div>
