<div class="space-y-4">
    @php
        $detail = $this->detailDonasiData;
        $summary = $detail['summary'];
        $offset = ($detail['page'] - 1) * \App\Filament\Widgets\RingkasanStatistikUtama::DETAIL_PER_PAGE;
    @endphp

{{-- Menggunakan Filament Table Style - Compact Version --}}
<div class="fi-ta-ctn divide-y divide-gray-200 dark:divide-white/10 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    {{-- Ringkasan Statistik - Compact (level-SQL, mencakup seluruh data bukan hanya halaman ini) --}}
    <div class="p-4">
        <div class="grid grid-cols-3 gap-4">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Total Transaksi</span>
                <span class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ number_format($summary['transaksi'], 0, ',', '.') }}
                </span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Total Donatur Unik</span>
                <span class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ number_format($summary['donatur'], 0, ',', '.') }}
                </span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Total Nominal</span>
                <span class="text-2xl font-semibold tracking-tight text-success-600 dark:text-success-400">
                    Rp {{ number_format($summary['nominal'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Filament Table --}}
    <div class="fi-ta-content relative divide-y divide-gray-200 dark:divide-white/10 overflow-x-auto">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                No
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Tanggal
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                @if($detailDonasiType === 'barang_by_jenis')
                                    Nomor Transaksi
                                @else
                                    Kode
                                @endif
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Donatur
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Jenis Donasi
                            </span>
                        </span>
                    </th>
                    @if($detailDonasiType === 'barang_by_jenis')
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Deskripsi Barang
                            </span>
                        </span>
                    </th>
                    @endif
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-end">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Nominal
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-center">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Metode
                            </span>
                        </span>
                    </th>
                    @if($detailDonasiType === 'barang_by_jenis')
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Fundraiser
                            </span>
                        </span>
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @forelse($detail['rows'] as $index => $donasi)
                    <tr wire:key="detail-donasi-{{ $donasi['id'] ?? ($offset + $index) }}" class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                {{ $offset + $index + 1 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                {{ \Carbon\Carbon::parse($donasi['tanggal'])->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30" style="--c-50:var(--gray-50);--c-400:var(--gray-400);--c-600:var(--gray-600);">
                                                <span class="font-mono text-xs">
                                                    @if($detailDonasiType === 'barang_by_jenis')
                                                        {{ $donasi['nomor_transaksi'] }}
                                                    @else
                                                        {{ $donasi['kode'] }}
                                                    @endif
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max flex-col gap-y-1">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-medium text-xs leading-5 text-gray-950 dark:text-white">
                                                {{ $donasi['donatur_nama'] }}
                                            </div>
                                            @if($donasi['donatur_hp'])
                                                <div class="fi-ta-text-item-description text-xs leading-4 text-gray-500 dark:text-gray-400">
                                                    {{ $donasi['donatur_hp'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                {{ $donasi['jenis_donasi'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @if($detailDonasiType === 'barang_by_jenis')
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white" title="{{ $donasi['deskripsi_barang'] }}">
                                                {{ Str::limit($donasi['deskripsi_barang'], 25) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endif
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex justify-end">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-semibold text-xs leading-5 fi-color-custom text-custom-600 dark:text-custom-400" style="--c-400:var(--success-400);--c-600:var(--success-600);">
                                                Rp {{ number_format($detailDonasiType === 'barang_by_jenis' ? $donasi['nilai_barang'] : $donasi['nominal'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex justify-center">
                                        <div class="flex max-w-max">
                                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight fi-color-primary bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                                {{ $donasi['metode_pembayaran'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @if($detailDonasiType === 'barang_by_jenis')
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                {{ $donasi['fundraiser'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $detailDonasiType === 'barang_by_jenis' ? '9' : '7' }}" class="fi-ta-empty-state px-6 py-12">
                            <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                                <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                                    <svg class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                    Tidak ada data donasi
                                </h4>
                                <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada transaksi donasi untuk kategori ini.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($detail['total'] > 0)
                <tfoot class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <td colspan="{{ $detailDonasiType === 'barang_by_jenis' ? '7' : '5' }}" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                <div class="flex justify-end">
                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-semibold text-xs leading-5 text-gray-950 dark:text-white">
                                        Total Keseluruhan:
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                <div class="flex justify-end">
                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-bold text-sm leading-5 fi-color-custom text-custom-600 dark:text-custom-400" style="--c-400:var(--success-400);--c-600:var(--success-600);">
                                        Rp {{ number_format($summary['nominal'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        @if($detailDonasiType === 'barang_by_jenis')
                        <td></td>
                        @endif
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Pagination --}}
    @if($detail['lastPage'] > 0 && $detail['total'] > 0)
        <div
            class="flex items-center justify-between gap-3 flex-wrap px-4 py-3 border-t border-gray-200 dark:border-white/10"
            wire:loading.class="opacity-50"
            wire:target="goToDetailPage"
        >
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Menampilkan {{ number_format($offset + 1, 0, ',', '.') }}–{{ number_format(min($offset + \App\Filament\Widgets\RingkasanStatistikUtama::DETAIL_PER_PAGE, $detail['total']), 0, ',', '.') }}
                dari {{ number_format($detail['total'], 0, ',', '.') }} transaksi
            </span>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="goToDetailPage({{ $detail['page'] - 1 }})"
                    {{ $detail['page'] <= 1 ? 'disabled' : '' }}
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                    Sebelumnya
                </button>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                    Halaman {{ $detail['page'] }} / {{ $detail['lastPage'] }}
                </span>
                <button
                    type="button"
                    wire:click="goToDetailPage({{ $detail['page'] + 1 }})"
                    {{ $detail['page'] >= $detail['lastPage'] ? 'disabled' : '' }}
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Berikutnya
                    <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    @endif
</div>
