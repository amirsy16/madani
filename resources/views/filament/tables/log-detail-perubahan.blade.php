@php
    // Palet warna statis — jangan interpolasi string ke class Tailwind.
    [$labelAksi, $aksiBg, $aksiTeks] = match ($event) {
        'created' => ['Menambahkan', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400', 'text-emerald-600 dark:text-emerald-400'],
        'updated' => ['Memperbarui', 'bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400', 'text-amber-600 dark:text-amber-400'],
        'deleted' => ['Menghapus', 'bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400', 'text-rose-600 dark:text-rose-400'],
        'restored' => ['Memulihkan', 'bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400', 'text-sky-600 dark:text-sky-400'],
        default => [ucfirst($event), 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400', 'text-gray-600 dark:text-gray-400'],
    };
@endphp
<div>
    {{-- Header ringkas: data + modul + badge aksi --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-800 px-4 py-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $judul }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Modul {{ $modul }}</p>
        </div>
        <span class="flex-shrink-0 rounded-full {{ $aksiBg }} px-3 py-1 text-xs font-semibold {{ $aksiTeks }}">{{ $labelAksi }}</span>
    </div>

    {{-- Daftar kolom: label kiri, nilai kanan --}}
    <div class="mt-5 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
        @forelse ($rincian as $baris)
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 {{ $loop->first ? '' : 'border-t border-gray-200 dark:border-gray-800' }}">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $baris['kolom'] }}</p>
                @if ($baris['tambah'])
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $baris['baru'] }}</p>
                @else
                    <p class="text-sm">
                        <span class="text-gray-400 dark:text-gray-500 line-through">{{ $baris['lama'] }}</span>
                        <span class="mx-2 text-gray-400 dark:text-gray-500">→</span>
                        <span class="font-semibold {{ $aksiTeks }}">{{ $baris['baru'] }}</span>
                    </p>
                @endif
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">Tidak ada rincian penting yang tercatat.</p>
        @endforelse
    </div>
</div>
