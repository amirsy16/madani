@php
    $nilai = isset($getState) ? $getState() : null;
    /** @var array{nama: string, email: string} $nilai */
    $pengguna = is_array($nilai) ? $nilai : ['nama' => 'Sistem', 'email' => 'console / sistem'];
@endphp
<div class="flex items-center gap-2.5">
    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
        <span class="text-xs font-bold">{{ strtoupper(mb_substr($pengguna['nama'], 0, 1)) }}</span>
    </div>
    <div class="min-w-0">
        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pengguna['nama'] }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pengguna['email'] }}</p>
    </div>
</div>
