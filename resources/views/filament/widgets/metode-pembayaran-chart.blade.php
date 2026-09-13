<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header --}}
        <div class="space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        Statistik Metode Pembayaran
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Periode: <span class="font-medium text-primary-600 dark:text-primary-400">{{ $this->getPeriodeLabel() }}</span>
                    </p>
                </div>
                
                <x-filament::button 
                    wire:click="$set('showInfoModal', true)"
                    icon="heroicon-o-information-circle"
                    size="sm"
                >
                    Informasi
                </x-filament::button>
            </div>
            
            {{-- Filter Section --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Periode
                        </label>
                        <select 
                            wire:model.live="filter" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-primary-500 focus:border-primary-500"
                        >
                            @foreach($this->getFilters() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if($showCustomDateRange)
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Tanggal Mulai
                            </label>
                            <input 
                                type="date" 
                                wire:model.blur="customStartDate"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-primary-500 focus:border-primary-500"
                            />
                        </div>
                        
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Tanggal Akhir
                            </label>
                            <input 
                                type="date" 
                                wire:model.blur="customEndDate"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-primary-500 focus:border-primary-500"
                            />
                        </div>
                    @endif
                </div>
                
                @if($showCustomDateRange && (!$customStartDate || !$customEndDate))
                    <div class="mt-3 flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 p-2 rounded">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                        <span>Silakan pilih tanggal mulai dan tanggal akhir</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $totalKeseluruhan = $this->getTotalKeseluruhan();
            $totalMetode = count($metodePembayaranStats);
            $totalTransaksi = array_sum(array_column($metodePembayaranStats, 'jumlah_transaksi'));
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Total Donasi --}}
            <x-metric-card
                label="Total Donasi"
                value="Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}"
                description="Donasi terverifikasi"
                color="primary"
                icon="heroicon-o-banknotes"
            />

            {{-- Total Transaksi --}}
            <x-metric-card
                label="Total Transaksi"
                value="{{ number_format($totalTransaksi, 0, ',', '.') }}"
                description="Transaksi pada periode terpilih"
                color="success"
                icon="heroicon-o-document-text"
            />

            {{-- Metode Aktif --}}
            <x-metric-card
                label="Metode Aktif"
                value="{{ $totalMetode }}"
                description="Metode pembayaran tersedia"
                color="info"
                icon="heroicon-o-credit-card"
            />
        </div>

        {{-- Tabel Ringkasan Metode Pembayaran --}}
        @if(count($metodePembayaranStats) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Table Header --}}
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <x-heroicon-o-table-cells class="w-4 h-4" />
                        Rekapitulasi Per Metode Pembayaran
                    </h3>
                </div>
                
                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Metode</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Transaksi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="min-width: 200px;">Kontribusi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Top Donatur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($metodePembayaranStats as $index => $metode)
                                @php
                                    $persentase = $this->getPersentase($metode['total_nominal']);
                                    $rankColors = [
                                        0 => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400',
                                        1 => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        2 => 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-400',
                                    ];
                                    $barColor = match(true) {
                                        $persentase >= 25 => 'bg-emerald-500',
                                        $persentase >= 15 => 'bg-blue-500',
                                        $persentase >= 10 => 'bg-violet-500',
                                        $persentase >= 5 => 'bg-amber-500',
                                        default => 'bg-gray-400',
                                    };
                                @endphp
                                <tr
                                    wire:click="selectMetode('{{ $metode['nama'] }}')"
                                    wire:key="metode-{{ $metode['nama'] }}"
                                    class="cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/50"
                                >
                                    {{-- Rank --}}
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $rankColors[$index] ?? 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    
                                    {{-- Metode Name --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-primary-50 dark:bg-primary-900/30 rounded-lg p-2">
                                                <x-heroicon-o-credit-card class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                            </div>
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $metode['nama'] }}</span>
                                        </div>
                                    </td>
                                    
                                    {{-- Total Nominal --}}
                                    <td class="px-4 py-4 text-right">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $metode['formatted_total'] }}</span>
                                    </td>
                                    
                                    {{-- Jumlah Transaksi --}}
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                            <x-heroicon-s-document-text class="w-3 h-3" />
                                            {{ number_format($metode['jumlah_transaksi']) }}
                                        </span>
                                    </td>
                                    
                                    {{-- Progress Bar --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div 
                                                    class="h-full {{ $barColor }} rounded-full transition-all duration-500"
                                                    style="width: {{ $persentase }}%"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 w-14 text-right">{{ number_format($persentase, 1) }}%</span>
                                        </div>
                                    </td>
                                    
                                    {{-- Top Donatur --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-s-trophy class="w-4 h-4 text-amber-500 flex-shrink-0" />
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $metode['top_donatur']['nama'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $metode['top_donatur']['formatted_total'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak ada data</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada donasi pada periode yang dipilih</p>
            </div>
        @endif

        {{-- Modal Detail Transaksi --}}
        @if($selectedMetode)
            @php
                $detailData = $this->getDetailDonasi($selectedMetode);
                $selectedStat = collect($metodePembayaranStats)->firstWhere('nama', $selectedMetode);
            @endphp
            
            <div 
                x-data="{ show: true }"
                x-show="show"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div class="fixed inset-0 bg-black/50 pointer-events-none" x-transition.opacity></div>
                
                <div class="flex min-h-screen items-center justify-center p-4 pointer-events-none">
                    <div 
                        class="relative w-full max-w-3xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl pointer-events-auto"
                        x-transition
                        @click.stop
                    >
                        {{-- Header --}}
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Detail Transaksi</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedMetode }}</p>
                            </div>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-4 max-h-[65vh] overflow-y-auto">
                            {{-- Stats --}}
                            @if($selectedStat)
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Total Nominal</p>
                                        <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $selectedStat['formatted_total'] }}</p>
                                    </div>
                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 border border-emerald-200 dark:border-emerald-800">
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-1">Jumlah Transaksi</p>
                                        <p class="text-lg font-bold text-emerald-900 dark:text-emerald-100">{{ number_format($selectedStat['jumlah_transaksi']) }}</p>
                                    </div>
                                    <div class="bg-violet-50 dark:bg-violet-900/20 rounded-lg p-3 border border-violet-200 dark:border-violet-800">
                                        <p class="text-xs text-violet-600 dark:text-violet-400 mb-1">Top Donatur</p>
                                        <p class="text-sm font-bold text-violet-900 dark:text-violet-100 truncate">{{ $selectedStat['top_donatur']['nama'] }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Table --}}
                            @if(count($detailData) > 0)
                                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-900">
                                            <tr>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-12">No</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Donatur</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($detailData as $index => $item)
                                                <tr wire:key="detail-{{ $index }}-{{ $item['donatur'] }}">
                                                    <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item['donatur'] }}</td>
                                                    <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">{{ $item['tanggal'] }}</td>
                                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item['nominal'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">Menampilkan 20 transaksi terbaru</p>
                            @else
                                <div class="text-center py-8">
                                    <x-heroicon-o-inbox class="mx-auto h-12 w-12 text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada transaksi ditemukan</p>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end">
                            <x-filament::button wire:click="closeModal" color="gray">
                                Tutup
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Modal Informasi --}}
        @if($showInfoModal)
            <div 
                x-data="{ show: true }"
                x-show="show"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div class="fixed inset-0 bg-black/50 pointer-events-none" x-transition.opacity></div>
                
                <div class="flex min-h-screen items-center justify-center p-4 pointer-events-none">
                    <div 
                        class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl pointer-events-auto"
                        x-transition
                        @click.stop
                    >
                        {{-- Header --}}
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-primary-600 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 rounded-lg p-2">
                                    <x-heroicon-o-information-circle class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-white">Informasi Metode Pembayaran</h2>
                                    <p class="text-sm text-primary-100">Panduan dan daftar metode</p>
                                </div>
                            </div>
                            <button @click="$wire.set('showInfoModal', false)" class="text-white/80 hover:text-white">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-5 max-h-[60vh] overflow-y-auto space-y-5">
                            {{-- Metode List --}}
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <x-heroicon-o-credit-card class="w-4 h-4 text-primary-600" />
                                    Metode Tersedia
                                </h3>
                                
                                @if(count($metodePembayaranStats) > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($metodePembayaranStats as $metode)
                                            <span wire:key="info-metode-{{ $metode['nama'] }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800 text-sm">
                                                <x-heroicon-s-check-circle class="w-4 h-4 text-primary-600" />
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $metode['nama'] }}</span>
                                                <span class="text-xs text-gray-500 bg-white dark:bg-gray-900 px-1.5 py-0.5 rounded">{{ number_format($metode['jumlah_transaksi']) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada metode pembayaran aktif</p>
                                @endif
                            </div>

                            {{-- Info Cards --}}
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <x-heroicon-o-chart-bar class="w-4 h-4 text-emerald-600" />
                                    Informasi Statistik
                                </h3>
                                
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Periode Data</p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Data berdasarkan periode: <strong>{{ $this->getPeriodeLabel() }}</strong></p>
                                </div>
                                
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 border border-emerald-200 dark:border-emerald-800">
                                    <p class="text-sm font-medium text-emerald-900 dark:text-emerald-100">Status Donasi</p>
                                    <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1">Hanya menampilkan donasi yang sudah <strong>terverifikasi</strong></p>
                                </div>
                            </div>

                            {{-- Manage Link --}}
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-orange-600" />
                                    Kelola Data
                                </h3>
                                
                                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">Kelola metode pembayaran yang tersedia.</p>
                                    <a
                                        href="{{ route('filament.admin.resources.metode-pembayarans.index') }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
                                    >
                                        <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                                        Kelola Metode
                                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <x-filament::button @click="$wire.set('showInfoModal', false)" color="gray">
                                Tutup
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
