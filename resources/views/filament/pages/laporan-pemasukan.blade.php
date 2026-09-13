<x-filament-panels::page>
    {{-- Section untuk Filter --}}
    <div class="mb-6">
        {{-- Header Filter dengan Tombol Reset --}}
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter untuk Tabel Detail
            </h2>
            @if($this->hasActiveFilters())
                <button 
                    wire:click="resetAllFilters" 
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-danger-600 hover:bg-danger-700 rounded-lg shadow-sm transition-colors duration-150"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Reset Semua Filter
                </button>
            @endif
        </div>
        
        {{-- Alert Penjelasan Sistem --}}
        <div class="mb-3 p-3 bg-info-50 dark:bg-info-900/20 border-l-4 border-info-500 rounded-r-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-info-600 dark:text-info-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-info-800 dark:text-info-200">ℹ️ Informasi Penting</p>
                    <p class="text-xs text-info-700 dark:text-info-300 mt-1">
                        <strong>Ringkasan</strong> menampilkan SEMUA data terverifikasi (periode terpilih).<br>
                        <strong>Filter di bawah</strong> HANYA mempengaruhi <strong>Tabel Detail Transaksi</strong>.
                    </p>
                </div>
            </div>
        </div>
        
        <form wire:submit.prevent="submitFilters" class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
            {{ $this->form }}
        </form>
    </div>

    {{-- Section untuk Ringkasan Total & Komparasi --}}
    <div class="mb-6 space-y-4">
        {{-- Header Ringkasan --}}
        <div class="p-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-xl shadow border border-primary-200 dark:border-primary-700">
            <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Ringkasan Pemasukan (Semua Data Terverifikasi)
                <span class="ml-2 px-2 py-1 text-xs font-medium bg-success-100 text-success-800 dark:bg-success-800/50 dark:text-success-200 rounded-full">
                    Global
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">📅 Periode:</span>
                    <span class="font-semibold text-gray-900 dark:text-white ml-2">
                        {{ $this->startDate ? \Carbon\Carbon::parse($this->startDate)->translatedFormat('d M Y') : 'N/A' }} - 
                        {{ $this->endDate ? \Carbon\Carbon::parse($this->endDate)->translatedFormat('d M Y') : 'N/A' }}
                    </span>
                </div>
                @if($this->previousPeriodLabel)
                <div>
                    <span class="text-gray-600 dark:text-gray-400">🔄 Perbandingan:</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300 ml-2">{{ $this->previousPeriodLabel }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Metrik Utama --}}
        <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
            @php
            $metrics = [
                [
                    'title' => 'Total Pemasukan (Uang)',
                    'currentValue' => $this->totalPemasukan,
                    'previousValue' => $this->totalPemasukanPrev,
                    'change' => $this->pemasukanChange,
                    'formatter' => 'number_format_rp',
                    'icon' => '💵'
                ],
                [
                    'title' => 'Total Nilai Barang',
                    'currentValue' => $this->totalNilaiBarang,
                    'previousValue' => $this->totalNilaiBarangPrev,
                    'change' => $this->nilaiBarangChange,
                    'formatter' => 'number_format_rp',
                    'icon' => '📦'
                ],
                [
                    'title' => 'Grand Total Pemasukan',
                    'currentValue' => $this->grandTotalPemasukan,
                    'previousValue' => $this->grandTotalPemasukanPrev,
                    'change' => $this->grandTotalChange,
                    'formatter' => 'number_format_rp',
                    'icon' => '💰'
                ],
                [
                    'title' => 'Total Transaksi',
                    'currentValue' => $this->totalTransaksi,
                    'previousValue' => $this->totalTransaksiPrev,
                    'change' => $this->transaksiChange,
                    'formatter' => 'number_format',
                    'icon' => '📊'
                ],
            ];
            @endphp

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($metrics as $metric)
                    @php
                        $change = $metric['change'];
                        $currentValue = $metric['currentValue'];
                        $previousValue = $metric['previousValue'];
                        $formatter = $metric['formatter'];

                        $icon = null;
                        if (!is_null($change)) {
                            $icon = $change > 0 ? 'heroicon-s-arrow-trending-up' : ($change < 0 ? 'heroicon-s-arrow-trending-down' : 'heroicon-s-minus');
                        }
                        $colorClass = 'text-gray-600 dark:text-gray-400';
                        if (!is_null($change)) {
                            $colorClass = $change > 0 ? 'text-green-600 dark:text-green-400' : ($change < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400');
                        }
                        
                        $bgColorClass = 'bg-gray-50 dark:bg-gray-700/50';
                         if (!is_null($change)) {
                            $bgColorClass = $change > 0 ? 'bg-green-50 dark:bg-green-800/50' : ($change < 0 ? 'bg-red-50 dark:bg-red-800/50' : 'bg-gray-50 dark:bg-gray-700/50');
                        }

                        if ($formatter === 'number_format_rp') {
                            $displayValue = 'Rp ' . number_format($currentValue, 0, ',', '.');
                            $displayPreviousValue = 'Rp ' . number_format($previousValue, 0, ',', '.');
                        } elseif ($formatter === 'number_format') {
                            $displayValue = number_format($currentValue, 0, ',', '.');
                            $displayPreviousValue = number_format($previousValue, 0, ',', '.');
                        } else {
                            $displayValue = $currentValue; 
                            $displayPreviousValue = $previousValue;
                        }
                    @endphp
                    <div @class(['overflow-hidden rounded-lg px-4 py-5 shadow sm:p-6', $bgColorClass])>
                        <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <span>{{ $metric['icon'] }}</span>
                            {{ $metric['title'] }}
                        </dt>
                        <dd class="mt-1 flex items-baseline justify-between">
                            <span class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                {{ $displayValue }}
                            </span>
                            @if (!is_null($icon))
                                <span @class(['ml-2 flex items-baseline text-sm font-semibold', $colorClass])>
                                    <x-filament::icon :icon="$icon" class="h-5 w-5 self-center"/>
                                    {{ number_format(abs($change ?? 0), 1) }}%
                                    <span class="sr-only"> {{ $change > 0 ? 'meningkat' : ($change < 0 ? 'menurun' : '') }} </span>
                                </span>
                            @endif
                        </dd>
                        @if (!is_null($change))
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Sebelumnya: {{ $displayPreviousValue }}
                        </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Informasi Detail Breakdown --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Breakdown Donatur & Status --}}
            <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Informasi Donatur & Transaksi
                </h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">👥 Total Donatur Unik</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($this->totalDonatur ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">✅ Terverifikasi</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ number_format($this->transaksiVerified ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">⏳ Pending</span>
                        <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format($this->transaksiPending ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">❌ Ditolak</span>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ number_format($this->transaksiRejected ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Breakdown Per Kategori Donasi --}}
            <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Breakdown Per Kategori
                </h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">🕌 Zakat</span>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">Rp {{ number_format($this->totalZakat ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">💚 Infaq</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($this->totalInfaq ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">💝 Sedekah</span>
                        <span class="text-sm font-bold text-purple-600 dark:text-purple-400">Rp {{ number_format($this->totalSedekah ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">🏢 CSR</span>
                        <span class="text-sm font-bold text-orange-600 dark:text-orange-400">Rp {{ number_format($this->totalCSR ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Section untuk Tabel Data --}}
    <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-white">Detail Transaksi Donasi</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold text-primary-600 dark:text-primary-400">{{ number_format($this->totalTransaksi, 0, ',', '.') }}</span> transaksi ditampilkan
            </div>
        </div>
        {{ $this->table }}
    </div>

    {{-- Section untuk Ringkasan per Jenis Donasi (jika aktif) --}}
    @if($this->groupByJenisDonasi && !empty($this->summaryByJenisDonasi))
        <div class="mt-6 p-4 bg-white rounded-xl shadow dark:bg-gray-800">
            <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-white mb-4">Ringkasan per Jenis Donasi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-750">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jenis Donasi</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Uang (Rp)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Nilai Barang (Rp)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grand Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->summaryByJenisDonasi as $summary)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $summary['jenis_donasi_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right">{{ number_format($summary['total_jumlah'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right">{{ number_format($summary['total_nilai_barang'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold text-right">{{ number_format($summary['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-750">
                        <tr>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Keseluruhan</td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700 dark:text-white">
                                Rp {{ number_format(array_sum(array_column($this->summaryByJenisDonasi, 'total_jumlah')), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700 dark:text-white">
                                Rp {{ number_format(array_sum(array_column($this->summaryByJenisDonasi, 'total_nilai_barang')), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700 dark:text-white">
                                Rp {{ number_format(array_sum(array_column($this->summaryByJenisDonasi, 'total')), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

@push('scripts')
<script>
    // No scripts needed for chart as it was removed.
</script>
@endpush

</x-filament-panels::page>