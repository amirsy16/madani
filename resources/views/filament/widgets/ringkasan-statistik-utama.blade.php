<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with filter buttons and toggle --}}
        <x-slot name="heading">
            <div class="w-full">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            Ringkasan Statistik Utama
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            @if($this->currentPeriod === 'custom' && $this->startDate && $this->endDate)
                                Periode: {{ \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($this->endDate)->format('d/m/Y') }}
                            @else
                                Periode: {{ $this->timePeriodLabel }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Filter Buttons --}}
                        <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                            @foreach($this->timePeriodOptions as $value => $label)
                                <button
                                    wire:click="setTimePeriod('{{ $value }}')"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors
                                           {{ $this->currentPeriod === $value
                                              ? 'bg-primary-600 text-white shadow-sm'
                                              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Date Range Picker --}}
                @if($showDatePicker)
                    <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal:</label>
                                <input
                                    type="date"
                                    wire:model="startDate"
                                    class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal:</label>
                                <input
                                    type="date"
                                    wire:model="endDate"
                                    class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="applyDateFilter"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md shadow-sm transition-colors"
                                >
                                    <x-heroicon-o-check class="w-4 h-4" />
                                    Terapkan Filter
                                </button>

                                <button
                                    wire:click="resetDateFilter"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm transition-colors"
                                >
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot>

        {{-- Stats Grid with loading state --}}
        <div class="relative">
            {{-- Loading overlay --}}
            <div wire:loading.flex wire:target="setTimePeriod" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 items-center justify-center rounded-lg z-10">
                <div class="flex items-center space-x-2 text-primary-600">
                    <x-filament::loading-indicator class="w-5 h-5" />
                    <span class="text-sm font-medium">Memuat data...</span>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" wire:loading.class="opacity-50" wire:target="setTimePeriod">
                @php
                    // Peta ikon emoji (dikirim dari widget PHP) ke Heroicon setara.
                    $statIconMap = [
                        '💰' => 'heroicon-o-banknotes',
                        '🕌' => 'heroicon-o-building-library',
                        '💚' => 'heroicon-o-heart',
                        '🎯' => 'heroicon-o-viewfinder-circle',
                        '🌟' => 'heroicon-o-star',
                        '💝' => 'heroicon-o-gift',
                        '📦' => 'heroicon-o-archive-box',
                        '🏢' => 'heroicon-o-building-office-2',
                        '🏛️' => 'heroicon-o-building-office',
                        '🏛' => 'heroicon-o-building-office',
                        '👥' => 'heroicon-o-users',
                        '💼' => 'heroicon-o-briefcase',
                        '🔄' => 'heroicon-o-arrow-path',
                        '🏦' => 'heroicon-o-wallet',
                        '📊' => 'heroicon-o-chart-bar',
                        '🎁' => 'heroicon-o-gift-top',
                    ];
                @endphp
                @forelse($this->stats ?? [] as $index => $stat)
                    @php
                        $statIcon = $statIconMap[$stat['icon'] ?? ''] ?? null;
                        $statLabel = $stat['label'] ?? 'Unknown';
                    @endphp
                    <div wire:key="stat-{{ $statLabel }}">
                        <x-metric-card
                            :label="$statLabel"
                            :value="$stat['value'] ?? 'Rp 0'"
                            :description="$stat['description'] ?? null"
                            :color="$stat['color'] ?? 'gray'"
                            :icon="$statIcon"
                        >
                            {{-- Tombol Selengkapnya untuk Infaq Terikat --}}
                            @if(str_contains($statLabel, 'INFAQ TERIKAT'))
                                <button
                                    wire:click="toggleInfaqTerikatDetail"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors shadow-sm"
                                >
                                    <span>{{ $showInfaqTerikatDetail ? 'Sembunyikan Detail' : 'Lihat Selengkapnya' }}</span>
                                    <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform {{ $showInfaqTerikatDetail ? 'rotate-180' : '' }}" />
                                </button>
                            @endif

                            {{-- Tombol Selengkapnya untuk Dana Zakat --}}
                            @if($statLabel === 'DANA ZAKAT')
                                <button
                                    wire:click="toggleZakatDetail"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors shadow-sm"
                                >
                                    <span>{{ $showZakatDetail ? 'Sembunyikan Detail' : 'Lihat Selengkapnya' }}</span>
                                    <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform {{ $showZakatDetail ? 'rotate-180' : '' }}" />
                                </button>
                            @endif

                            {{-- Tombol Selengkapnya untuk Donasi Barang --}}
                            @if(str_contains($statLabel, 'DONASI BARANG'))
                                <button
                                    wire:click="toggleBarangDetail"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors shadow-sm"
                                >
                                    <span>{{ $showBarangDetail ? 'Sembunyikan Detail' : 'Lihat Selengkapnya' }}</span>
                                    <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform {{ $showBarangDetail ? 'rotate-180' : '' }}" />
                                </button>
                            @endif
                        </x-metric-card>
                    </div>
                @empty
                    {{-- Empty state --}}
                    <div class="col-span-full">
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-6 text-center">
                            <x-heroicon-o-exclamation-triangle class="w-8 h-8 mx-auto mb-2 text-amber-600 dark:text-amber-400" />
                            <h3 class="text-amber-800 dark:text-amber-200 font-semibold mb-1">Tidak Ada Data</h3>
                            <p class="text-amber-700 dark:text-amber-300 text-sm">
                                Belum ada data statistik yang tersedia untuk periode ini.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Detail Infaq Terikat per Kategori --}}
            @if($showInfaqTerikatDetail && count($this->infaqTerikatDetail ?? []) > 0)
                <div
                    class="mt-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-sky-200 dark:border-sky-900/50 p-4"
                    wire:loading.class="opacity-50"
                    wire:target="toggleInfaqTerikatDetail"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-chart-pie class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                            Detail Infaq Terikat per Kategori
                        </h3>
                        <x-status-badge :status="count($this->infaqTerikatDetail) . ' Kategori'" color="info" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->infaqTerikatDetail as $detail)
                            <x-metric-card
                                :label="$detail['kategori']"
                                value="Rp {{ number_format($detail['total'], 0, ',', '.') }}"
                                :description="$detail['jumlah_transaksi'] . ' transaksi'"
                                color="info"
                                icon="heroicon-o-viewfinder-circle"
                                class="h-full"
                            />
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                Total {{ array_sum(array_column($this->infaqTerikatDetail, 'jumlah_transaksi')) }} transaksi dari {{ count($this->infaqTerikatDetail) }} kategori
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-bold tabular-nums">
                                Total: Rp {{ number_format(array_sum(array_column($this->infaqTerikatDetail, 'total')), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Empty state untuk detail infaq terikat --}}
            @if($showInfaqTerikatDetail && count($this->infaqTerikatDetail ?? []) === 0)
                <div class="mt-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-800 p-6 text-center">
                    <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                    <h3 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Detail Kategori</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data infaq terikat dengan kategori untuk periode ini.
                    </p>
                </div>
            @endif

            {{-- Detail Zakat per Jenis --}}
            @if($showZakatDetail && count($this->zakatDetail ?? []) > 0)
                <div
                    class="mt-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-emerald-200 dark:border-emerald-900/50 p-4"
                    wire:loading.class="opacity-50"
                    wire:target="toggleZakatDetail"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            Detail Dana Zakat per Jenis
                        </h3>
                        <x-status-badge :status="count($this->zakatDetail) . ' Jenis'" color="success" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->zakatDetail as $detail)
                            <x-metric-card
                                :label="$detail['jenis']"
                                value="Rp {{ number_format($detail['total'], 0, ',', '.') }}"
                                :description="$detail['jumlah_transaksi'] . ' transaksi'"
                                color="success"
                                icon="heroicon-o-banknotes"
                                class="h-full"
                            >
                                <button
                                    wire:click="showZakatDonasi({{ \Illuminate\Support\Js::from($detail['jenis']) }})"
                                    type="button"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 rounded-md transition-colors"
                                >
                                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5" />
                                    Lihat Detail Donasi
                                </button>
                            </x-metric-card>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                Total {{ array_sum(array_column($this->zakatDetail, 'jumlah_transaksi')) }} transaksi dari {{ count($this->zakatDetail) }} jenis
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-bold tabular-nums">
                                Total: Rp {{ number_format(array_sum(array_column($this->zakatDetail, 'total')), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Empty state untuk detail zakat --}}
            @if($showZakatDetail && count($this->zakatDetail ?? []) === 0)
                <div class="mt-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-800 p-6 text-center">
                    <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                    <h3 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Detail Jenis</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data zakat dengan jenis untuk periode ini.
                    </p>
                </div>
            @endif

            {{-- Detail Donasi Barang per Jenis --}}
            @if($showBarangDetail && count($this->barangDetail ?? []) > 0)
                <div
                    class="mt-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-amber-200 dark:border-amber-900/50 p-4"
                    wire:loading.class="opacity-50"
                    wire:target="toggleBarangDetail"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-archive-box class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            Detail Donasi Barang per Jenis
                        </h3>
                        <x-status-badge :status="count($this->barangDetail) . ' Jenis'" color="warning" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->barangDetail as $detail)
                            <x-metric-card
                                :label="$detail['jenis']"
                                value="Rp {{ number_format($detail['total'], 0, ',', '.') }}"
                                :description="$detail['jumlah_transaksi'] . ' transaksi'"
                                color="warning"
                                icon="heroicon-o-archive-box"
                                class="h-full"
                            >
                                <button
                                    wire:click="showBarangDonasiByJenis({{ \Illuminate\Support\Js::from($detail['jenis']) }})"
                                    type="button"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 rounded-md transition-colors"
                                >
                                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5" />
                                    Lihat Detail Donasi
                                </button>
                            </x-metric-card>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                Total {{ array_sum(array_column($this->barangDetail, 'jumlah_transaksi')) }} transaksi dari {{ count($this->barangDetail) }} jenis
                            </span>
                            <span class="text-gray-900 dark:text-gray-100 font-bold tabular-nums">
                                Total: Rp {{ number_format(array_sum(array_column($this->barangDetail, 'total')), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Empty state untuk detail donasi barang --}}
            @if($showBarangDetail && count($this->barangDetail ?? []) === 0)
                <div class="mt-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-800 p-6 text-center">
                    <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                    <h3 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Detail Jenis</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data donasi barang dengan jenis untuk periode ini.
                    </p>
                </div>
            @endif
        </div>

        {{-- Stats count info --}}
        @if($this->stats && count($this->stats) > 0)
            <div class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ count($this->stats) }} statistik untuk periode {{ strtolower($this->timePeriodLabel) }}
            </div>
        @endif

        {{-- Modal Detail Donasi menggunakan Livewire --}}
        @if($showDetailDonasiModal)
            <div
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
                wire:key="detail-modal-{{ $detailDonasiType }}-{{ $selectedKategori ?? $selectedJenisZakat }}"
            >
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    {{-- Background overlay --}}
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        aria-hidden="true"
                        wire:click="closeDetailDonasiModal"
                    ></div>

                    {{-- Center modal --}}
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                        {{-- Header --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        @if($detailDonasiType === 'infaq')
                                            Detail Donasi Infaq Terikat - {{ $selectedKategori }}
                                        @elseif($detailDonasiType === 'zakat')
                                            Detail Donasi Zakat - {{ $selectedJenisZakat }}
                                        @elseif($detailDonasiType === 'barang_by_jenis')
                                            Detail Donasi Barang - {{ $selectedJenisBarang }}
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Total {{ count($this->detailDonasiData) }} transaksi • Periode: {{ $this->timePeriodLabel }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    wire:click="closeDetailDonasiModal"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                >
                                    <x-heroicon-o-x-mark class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                            @include('filament.widgets.ringkasan-statistik-utama.detail-donasi-modal')
                        </div>

                        {{-- Footer --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 flex justify-end border-t border-gray-200 dark:border-gray-800">
                            <button
                                type="button"
                                wire:click="closeDetailDonasiModal"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
