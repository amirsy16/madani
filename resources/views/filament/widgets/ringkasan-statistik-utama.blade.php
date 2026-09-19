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
                            {{-- Tombol Selengkapnya untuk Infaq Terikat: buka modal breakdown --}}
                            @if(str_contains($statLabel, 'INFAQ TERIKAT'))
                                <button
                                    wire:click="openInfaqTerikatModal"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors shadow-sm"
                                >
                                    <span>Lihat Selengkapnya</span>
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                            @endif

                            {{-- Tombol Selengkapnya untuk Dana Zakat: buka modal breakdown --}}
                            @if($statLabel === 'DANA ZAKAT')
                                <button
                                    wire:click="openZakatModal"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors shadow-sm"
                                >
                                    <span>Lihat Selengkapnya</span>
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                            @endif

                            {{-- Tombol Selengkapnya untuk Donasi Barang: buka modal breakdown --}}
                            @if(str_contains($statLabel, 'DONASI BARANG'))
                                <button
                                    wire:click="openBarangModal"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-colors shadow-sm"
                                >
                                    <span>Lihat Selengkapnya</span>
                                    <x-heroicon-o-eye class="w-3 h-3" />
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
        </div>

        {{-- Stats count info --}}
        @if($this->stats && count($this->stats) > 0)
            <div class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ count($this->stats) }} statistik untuk periode {{ strtolower($this->timePeriodLabel) }}
            </div>
        @endif

        {{-- Modal Breakdown "Lihat Selengkapnya" --}}
        @if($showBreakdownModal)
            @php
                $breakdownConfig = match ($showBreakdownModal) {
                    'infaq_terikat' => [
                        'title' => 'Detail Infaq Terikat per Kategori',
                        'items' => $this->infaqTerikatDetail,
                        'itemLabelKey' => 'kategori',
                        'icon' => 'heroicon-o-chart-pie',
                        'unitLabel' => 'kategori',
                        'iconClass' => 'text-sky-600 dark:text-sky-400',
                        'barClass' => 'bg-sky-500',
                        'btnClass' => 'bg-sky-600 hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600',
                    ],
                    'zakat' => [
                        'title' => 'Detail Dana Zakat per Jenis',
                        'items' => $this->zakatDetail,
                        'itemLabelKey' => 'jenis',
                        'icon' => 'heroicon-o-arrow-trending-up',
                        'unitLabel' => 'jenis',
                        'iconClass' => 'text-emerald-600 dark:text-emerald-400',
                        'barClass' => 'bg-emerald-500',
                        'btnClass' => 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600',
                    ],
                    'barang' => [
                        'title' => 'Detail Donasi Barang per Jenis',
                        'items' => $this->barangDetail,
                        'itemLabelKey' => 'jenis',
                        'icon' => 'heroicon-o-archive-box',
                        'unitLabel' => 'jenis',
                        'iconClass' => 'text-amber-600 dark:text-amber-400',
                        'barClass' => 'bg-amber-500',
                        'btnClass' => 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600',
                    ],
                    default => null,
                };
                $breakdownItems = $breakdownConfig['items'] ?? [];
                $breakdownTotalSum = array_sum(array_column($breakdownItems, 'total'));
                $breakdownTotalTx = array_sum(array_column($breakdownItems, 'jumlah_transaksi'));
            @endphp

            @if($breakdownConfig)
                <div
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="breakdown-modal-title"
                    role="dialog"
                    aria-modal="true"
                    wire:key="breakdown-modal-{{ $showBreakdownModal }}"
                    x-data
                    @keydown.escape.window="$wire.closeBreakdownModal()"
                >
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        {{-- Background overlay --}}
                        <div
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                            aria-hidden="true"
                            wire:click="closeBreakdownModal"
                        ></div>

                        <div class="relative w-full max-w-5xl bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all">
                            {{-- Header --}}
                            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                            <x-filament::icon
                                                :icon="$breakdownConfig['icon']"
                                                class="w-5 h-5 {{ $breakdownConfig['iconClass'] }}"
                                            />
                                            {{ $breakdownConfig['title'] }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Periode: {{ $this->timePeriodLabel }} • {{ count($breakdownItems) }} {{ $breakdownConfig['unitLabel'] }} • {{ $breakdownTotalTx }} transaksi
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="closeBreakdownModal"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                    >
                                        <x-heroicon-o-x-mark class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div
                                class="px-6 py-4 max-h-[70vh] overflow-y-auto"
                                wire:loading.class="opacity-50"
                                wire:target="openInfaqTerikatModal, openZakatModal, openBarangModal"
                            >
                                @if(count($breakdownItems) > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($breakdownItems as $item)
                                            @php
                                                $itemLabel = $item[$breakdownConfig['itemLabelKey']];
                                                $itemPct = $breakdownTotalSum > 0 ? ($item['total'] / $breakdownTotalSum) * 100 : 0;
                                            @endphp
                                            <div
                                                class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                                                wire:key="breakdown-item-{{ $showBreakdownModal }}-{{ $itemLabel }}"
                                            >
                                                <div class="flex items-start justify-between gap-2 mb-1">
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $itemLabel }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ number_format($itemPct, 1) }}%</span>
                                                </div>
                                                <div class="text-lg font-bold text-gray-900 dark:text-gray-100 tabular-nums">
                                                    Rp {{ number_format($item['total'], 0, ',', '.') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $item['jumlah_transaksi'] }} transaksi
                                                </div>

                                                {{-- Progress bar proporsi --}}
                                                <div class="mt-2 h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                    <div
                                                        class="h-full {{ $breakdownConfig['barClass'] }} rounded-full"
                                                        style="width: {{ min(100, $itemPct) }}%"
                                                    ></div>
                                                </div>

                                                <button
                                                    wire:click="{{ match ($showBreakdownModal) {
                                                        'infaq_terikat' => 'showInfaqTerikatDonasi',
                                                        'zakat' => 'showZakatDonasi',
                                                        'barang' => 'showBarangDonasiByJenis',
                                                    } }}({{ \Illuminate\Support\Js::from($itemLabel) }})"
                                                    type="button"
                                                    class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white {{ $breakdownConfig['btnClass'] }} rounded-md transition-colors"
                                                >
                                                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5" />
                                                    Lihat Detail Donasi
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-6 text-center">
                                        <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                                        <h4 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Data</h4>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                                            Belum ada data untuk periode ini.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer --}}
                            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 border-t border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between flex-wrap gap-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        Total {{ $breakdownTotalTx }} transaksi dari {{ count($breakdownItems) }} {{ $breakdownConfig['unitLabel'] }}
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <span class="text-gray-900 dark:text-gray-100 font-bold tabular-nums">
                                            Total: Rp {{ number_format($breakdownTotalSum, 0, ',', '.') }}
                                        </span>
                                        <button
                                            type="button"
                                            wire:click="closeBreakdownModal"
                                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- Modal Detail Donasi menggunakan Livewire --}}
        @if($showDetailDonasiModal)
            <div
                class="fixed inset-0 z-[60] overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
                wire:key="detail-modal-{{ $detailDonasiType }}-{{ $selectedKategori ?? $selectedJenisZakat }}"
                x-data
                @keydown.escape.window="$wire.closeDetailDonasiModal()"
            >
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    {{-- Background overlay --}}
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        aria-hidden="true"
                        wire:click="closeDetailDonasiModal"
                    ></div>

                    <div class="relative w-full max-w-7xl bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all">
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
                                        Total {{ number_format($this->detailDonasiData['total'], 0, ',', '.') }} transaksi • Periode: {{ $this->timePeriodLabel }}
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
