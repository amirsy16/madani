<x-filament-panels::page>
    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-4 md:p-6 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 rounded-lg p-2 bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                <x-heroicon-o-calendar-days class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Filter Periode Laporan</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Pilih rentang tanggal untuk melihat laporan perubahan dana</p>
            </div>
        </div>

        <form wire:submit.prevent="generateReport">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                    <input type="date" wire:model="startDate" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                    <input type="date" wire:model="endDate" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <x-filament::button type="submit" icon="heroicon-o-chart-bar">
                    Generate Laporan Perubahan Dana
                </x-filament::button>
            </div>
        </form>
    </div>

    <!-- Main Content -->
    <div class="space-y-6">
        @php
            // Filter out summary data from main report loop
            $filteredReportData = collect($reportData)->filter(function($data, $key) {
                return $key !== 'summary' && is_array($data) && isset($data['title']);
            });

            // Use totals from summary data (already excludes "Penyaluran Langsung")
            $totalPenerimaan = $summaryData['total_penerimaan'] ?? 0;
            $totalPenyaluran = $summaryData['total_penyaluran'] ?? 0;
            $totalSaldoAkhir = $summaryData['total_saldo_akhir'] ?? 0;

            // Note: These totals are calculated from DanaService and already exclude "Penyaluran Langsung"
            // This ensures consistency with RingkasanStatistikUtama widget

            $penyaluranPercentage = $totalPenerimaan > 0 ? ($totalPenyaluran / $totalPenerimaan) * 100 : 0;
        @endphp

        @if($filteredReportData->count() > 0)

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-metric-card
                label="Total Penerimaan"
                value="Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}"
                color="success"
                icon="heroicon-o-banknotes"
            />

            <x-metric-card
                label="Total Penyaluran"
                value="Rp {{ number_format($totalPenyaluran, 0, ',', '.') }}"
                description="{{ number_format($penyaluranPercentage, 1) }}% dari penerimaan"
                color="info"
                icon="heroicon-o-arrow-up-on-square"
            />

            @php
                $saldoSehat = $totalSaldoAkhir >= 0;
            @endphp
            <x-metric-card
                label="Sisa Saldo"
                value="Rp {{ number_format(abs($totalSaldoAkhir), 0, ',', '.') }}"
                color="{{ $saldoSehat ? 'success' : 'danger' }}"
                icon="{{ $saldoSehat ? 'heroicon-o-wallet' : 'heroicon-o-exclamation-triangle' }}"
            >
                <x-status-badge :status="$saldoSehat ? 'Surplus' : 'Defisit'" :color="$saldoSehat ? 'success' : 'danger'" />
            </x-metric-card>
        </div>

        <!-- Detailed Reports by Fund Type -->
        @foreach ($filteredReportData as $fundKey => $data)
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden" wire:key="fund-{{ $fundKey }}">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 rounded-lg p-2 bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                            <x-heroicon-o-wallet class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $data['title'] }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Periode: {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Akhir</p>
                        <p class="text-lg font-bold tabular-nums {{ ($data['saldo_akhir'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($data['saldo_akhir'] ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 md:p-6">
                    {{-- Special handling for "Penyaluran Langsung" --}}
                    @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                        <!-- Penyaluran Langsung Special Section -->
                        <div class="bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-900/50 rounded-lg p-4 mb-6">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex-shrink-0 rounded-lg p-2 bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400">
                                    <x-heroicon-o-information-circle class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sky-800 dark:text-sky-200 text-sm">Informasi Penyaluran Langsung</h4>
                                    <p class="text-sm text-sky-600 dark:text-sky-400">{{ $data['keterangan'] ?? 'Donasi langsung disalurkan oleh donatur' }}</p>
                                </div>
                            </div>

                            @if(isset($data['total_donasi_terdokumentasi']) && $data['total_donasi_terdokumentasi'] > 0)
                                <div class="bg-white dark:bg-gray-900 rounded-lg p-3 border border-sky-200 dark:border-sky-900/50">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Total Donasi Terdokumentasi:</span>
                                        <span class="font-bold tabular-nums text-sky-600 dark:text-sky-400">
                                            Rp {{ number_format($data['total_donasi_terdokumentasi'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                {{-- Conditional display based on fund type --}}
                                @if(!isset($data['is_penyaluran_langsung']) || !$data['is_penyaluran_langsung'])
                                    <!-- Saldo Awal (hanya untuk dana yang dikelola) -->
                                    <tr class="border-b border-gray-200 dark:border-gray-800">
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                            Saldo Awal
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold tabular-nums text-sky-600 dark:text-sky-400">
                                            Rp {{ number_format($data['saldo_awal'] ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif

                                <!-- Penerimaan Dana -->
                                <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                    <td class="px-4 py-3 font-bold text-emerald-800 dark:text-emerald-200">
                                        <span class="inline-flex items-center gap-2">
                                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                            @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                                                Dokumentasi Donasi
                                            @else
                                                1. Penerimaan Dana
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>

                                <!-- Rincian Penerimaan -->
                                @if(isset($data['rincian_penerimaan']) && count($data['rincian_penerimaan']) > 0)
                                    @foreach($data['rincian_penerimaan'] as $jenis => $jumlah)
                                        <tr wire:key="penerimaan-{{ $fundKey }}-{{ $loop->index }}">
                                            <td class="px-4 py-2 pl-8 text-sm text-gray-700 dark:text-gray-300">
                                                <span class="inline-flex items-center">
                                                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span>
                                                    {{ $jenis }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- Subtotal Penerimaan -->
                                    <tr class="border-t-2 border-emerald-200 dark:border-emerald-900">
                                        <td class="px-4 py-3 pl-8 font-semibold text-emerald-800 dark:text-emerald-200">
                                            @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                                                Total Donasi Terdokumentasi
                                            @else
                                                Total Penerimaan
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-base tabular-nums text-emerald-600 dark:text-emerald-400">
                                            @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                                                Rp {{ number_format($data['total_donasi_terdokumentasi'] ?? 0, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format($data['penerimaan'], 0, ',', '.') }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Bagian Amil (jika ada) - TIDAK untuk Penyaluran Langsung -->
                                    @if(!isset($data['is_penyaluran_langsung']) || !$data['is_penyaluran_langsung'])
                                        @if(isset($data['bagian_amil']) && $data['bagian_amil'] > 0 && strtolower($data['title']) !== 'hak amil')
                                            <tr wire:key="bagian-amil-{{ $fundKey }}">
                                                <td class="px-4 py-2 pl-8 text-sm text-amber-700 dark:text-amber-300">
                                                    <span class="inline-flex items-center">
                                                        <span class="w-2 h-2 bg-amber-500 rounded-full mr-2"></span>
                                                        Dikurangi: Bagian Amil ({{ number_format($data['persentase_hak_amil'] ?? 12, 1) }}%)
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-right tabular-nums font-semibold text-amber-600 dark:text-amber-400">
                                                    (Rp {{ number_format($data['bagian_amil'], 0, ',', '.') }})
                                                </td>
                                            </tr>
                                            <tr class="border-t border-gray-200 dark:border-gray-800" wire:key="penerimaan-bersih-{{ $fundKey }}">
                                                <td class="px-4 py-3 pl-8 font-semibold text-gray-800 dark:text-gray-200">
                                                    Penerimaan Bersih
                                                </td>
                                                <td class="px-4 py-3 text-right font-bold tabular-nums text-sky-600 dark:text-sky-400">
                                                    Rp {{ number_format(($data['penerimaan'] ?? 0) - ($data['bagian_amil'] ?? 0), 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                @else
                                    <tr wire:key="penerimaan-kosong-{{ $fundKey }}">
                                        <td class="px-4 py-3 pl-8 text-sm text-gray-500 dark:text-gray-400 italic">
                                            Tidak ada penerimaan dalam periode ini
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($data['penerimaan'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif

                                <!-- Penyaluran Dana -->
                                <tr class="bg-rose-50 dark:bg-rose-900/20">
                                    <td class="px-4 py-3 font-bold text-rose-800 dark:text-rose-200">
                                        <span class="inline-flex items-center gap-2">
                                            <x-heroicon-o-arrow-up-on-square class="w-4 h-4" />
                                            2. Penyaluran Dana
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>

                                <!-- Penyaluran berdasarkan Asnaf -->
                                @if(isset($data['rincian_penyaluran_asnaf']) && count($data['rincian_penyaluran_asnaf']) > 0)
                                    <tr class="bg-sky-50 dark:bg-sky-900/20" wire:key="asnaf-header-{{ $fundKey }}">
                                        <td class="px-4 py-3 pl-8 font-semibold text-sky-800 dark:text-sky-200 text-sm">
                                            2.1 Penyaluran berdasarkan Asnaf
                                        </td>
                                        <td class="px-4 py-3"></td>
                                    </tr>
                                    @foreach($data['rincian_penyaluran_asnaf'] as $asnaf => $jumlah)
                                        <tr wire:key="asnaf-{{ $fundKey }}-{{ $loop->index }}">
                                            <td class="px-4 py-2 pl-12 text-sm text-gray-700 dark:text-gray-300">
                                                <span class="inline-flex items-center">
                                                    <span class="w-2 h-2 bg-rose-500 rounded-full mr-2"></span>
                                                    {{ $asnaf }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right tabular-nums font-semibold text-rose-600 dark:text-rose-400">
                                                Rp {{ number_format($jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                <!-- Penyaluran berdasarkan Bidang Program -->
                                @if(isset($data['rincian_penyaluran_bidang']) && count($data['rincian_penyaluran_bidang']) > 0)
                                    <tr class="bg-sky-50 dark:bg-sky-900/20" wire:key="bidang-header-{{ $fundKey }}">
                                        <td class="px-4 py-3 pl-8 font-semibold text-sky-800 dark:text-sky-200 text-sm">
                                            2.2 Penyaluran berdasarkan Bidang Program
                                        </td>
                                        <td class="px-4 py-3"></td>
                                    </tr>
                                    @foreach($data['rincian_penyaluran_bidang'] as $bidang => $jumlah)
                                        <tr wire:key="bidang-{{ $fundKey }}-{{ $loop->index }}">
                                            <td class="px-4 py-2 pl-12 text-sm text-gray-700 dark:text-gray-300">
                                                <span class="inline-flex items-center">
                                                    <span class="w-2 h-2 bg-rose-500 rounded-full mr-2"></span>
                                                    {{ $bidang }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right tabular-nums font-semibold text-rose-600 dark:text-rose-400">
                                                Rp {{ number_format($jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                <!-- Total Penyaluran -->
                                <tr class="border-t-2 border-rose-200 dark:border-rose-900" wire:key="total-penyaluran-{{ $fundKey }}">
                                    <td class="px-4 py-3 pl-8 font-semibold text-rose-800 dark:text-rose-200">
                                        Total Penyaluran
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-base tabular-nums text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($data['penyaluran'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- Saldo Akhir -->
                                <tr class="border-t-4 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50" wire:key="saldo-akhir-{{ $fundKey }}">
                                    <td class="px-4 py-4 font-bold text-base text-gray-900 dark:text-white">
                                        @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                                            Status Penyaluran
                                        @else
                                            Saldo Akhir
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                                                <x-status-badge status="Direct Distribution" color="info" />
                                            @else
                                                <span class="text-lg font-bold tabular-nums {{ ($data['saldo_akhir'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                    Rp {{ number_format(abs($data['saldo_akhir'] ?? 0), 0, ',', '.') }}
                                                </span>
                                                <x-status-badge :status="($data['saldo_akhir'] ?? 0) >= 0 ? 'Surplus' : 'Defisit'" :color="($data['saldo_akhir'] ?? 0) >= 0 ? 'success' : 'danger'" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Fund Health Indicator -->
                    @if(isset($data['is_penyaluran_langsung']) && $data['is_penyaluran_langsung'])
                        <!-- Special indicator for Penyaluran Langsung -->
                        <div class="mt-6 p-4 rounded-lg bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-900/50">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 rounded-lg p-2 bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400">
                                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sky-800 dark:text-sky-200 text-sm">
                                            Status Penyaluran Langsung
                                        </h4>
                                        <p class="text-sm text-sky-600 dark:text-sky-400">
                                            Dana tidak masuk ke kas organisasi - disalurkan langsung oleh donatur
                                        </p>
                                    </div>
                                </div>
                                <div class="sm:text-right">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Mode Distribusi</p>
                                    <p class="text-base font-bold text-sky-600 dark:text-sky-400">Direct Transfer</p>
                                    <div class="mt-1">
                                        <x-status-badge status="No impact on cash flow" color="success" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Regular fund health indicator -->
                        @php
                            $utilizationRate = ($data['penerimaan'] ?? 0) > 0 ? (($data['penyaluran'] ?? 0) / ($data['penerimaan'] ?? 0)) * 100 : 0;
                            $danaSehat = ($data['saldo_akhir'] ?? 0) >= 0;
                        @endphp
                        <div class="mt-6 p-4 rounded-lg border {{ $danaSehat ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 dark:border-rose-900/50' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 rounded-lg p-2 {{ $danaSehat ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400' }}">
                                        @if($danaSehat)
                                            <x-heroicon-o-check-circle class="w-5 h-5" />
                                        @else
                                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm {{ $danaSehat ? 'text-emerald-800 dark:text-emerald-200' : 'text-rose-800 dark:text-rose-200' }}">
                                            Status Kesehatan Dana
                                        </h4>
                                        <p class="text-sm {{ $danaSehat ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $danaSehat ? 'Dana dalam kondisi sehat dengan saldo positif' : 'Perlu perhatian: Saldo defisit' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="sm:text-right">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Tingkat Penyaluran</p>
                                    <p class="text-base font-bold tabular-nums {{ $utilizationRate >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($utilizationRate >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                        {{ number_format($utilizationRate, 1) }}%
                                    </p>
                                    <div class="mt-1 h-2 w-28 ml-auto bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $utilizationRate >= 80 ? 'bg-emerald-500' : ($utilizationRate >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                             style="width: {{ min($utilizationRate, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Grand Summary Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 rounded-lg p-2 bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-400">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ringkasan Keseluruhan</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total seluruh sumber dana periode ini</p>
                    </div>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status Keseluruhan</p>
                    <x-status-badge :status="$totalSaldoAkhir >= 0 ? 'Sehat' : 'Perlu Perhatian'" :color="$totalSaldoAkhir >= 0 ? 'success' : 'warning'" />
                </div>
            </div>

            <div class="p-4 md:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-metric-card
                        label="Total Penerimaan"
                        value="Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}"
                        color="success"
                        icon="heroicon-o-banknotes"
                    />

                    <x-metric-card
                        label="Total Penyaluran"
                        value="Rp {{ number_format($totalPenyaluran, 0, ',', '.') }}"
                        description="{{ number_format($penyaluranPercentage, 1) }}% dari penerimaan"
                        color="info"
                        icon="heroicon-o-arrow-up-on-square"
                    />

                    <x-metric-card
                        label="Sisa Saldo"
                        value="Rp {{ number_format(abs($totalSaldoAkhir), 0, ',', '.') }}"
                        color="{{ $totalSaldoAkhir >= 0 ? 'success' : 'danger' }}"
                        icon="{{ $totalSaldoAkhir >= 0 ? 'heroicon-o-wallet' : 'heroicon-o-exclamation-triangle' }}"
                    >
                        <x-status-badge :status="$totalSaldoAkhir >= 0 ? 'Surplus' : 'Defisit'" :color="$totalSaldoAkhir >= 0 ? 'success' : 'danger'" />
                    </x-metric-card>
                </div>

                <!-- Performance Metrics -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <h4 class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Efisiensi Penyaluran</h4>
                        <p class="mt-1 text-xl font-bold tabular-nums {{ $penyaluranPercentage >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($penyaluranPercentage >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                            {{ number_format($penyaluranPercentage, 1) }}%
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <h4 class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah Sumber Dana</h4>
                        <p class="mt-1 text-xl font-bold tabular-nums text-sky-600 dark:text-sky-400">
                            {{ $filteredReportData->count() }}
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <h4 class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Rata-rata Saldo</h4>
                        <p class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white">
                            Rp {{ number_format($filteredReportData->count() > 0 ? $totalSaldoAkhir / $filteredReportData->count() : 0, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <h4 class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode Laporan</h4>
                        <p class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white">
                            {{ Carbon\Carbon::parse($startDate)->diffInDays(Carbon\Carbon::parse($endDate)) + 1 }} Hari
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-6 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <x-heroicon-o-chart-bar class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum Ada Data</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto text-sm">
                Silakan pilih periode tanggal dan klik "Generate Laporan Perubahan Dana" untuk melihat data laporan.
            </p>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 rounded-lg text-sm">
                <x-heroicon-o-information-circle class="w-4 h-4" />
                Gunakan filter di atas untuk memulai
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
