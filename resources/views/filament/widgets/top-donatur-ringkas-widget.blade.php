<x-filament-widgets::widget>
    <x-metric-card
        label="Top 5 Donatur"
        :description="\Carbon\Carbon::now()->format('F Y')"
        color="primary"
        icon="heroicon-o-user-group"
    >
        @if ($topDonaturs && $topDonaturs->count() > 0)
            <div class="space-y-1">
                @foreach ($topDonaturs as $index => $donatur)
                    <div wire:key="top-donatur-{{ $donatur->id }}" class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                        {{-- Left side: rank and name --}}
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            {{-- Rank badge --}}
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' :
                                   ($index === 1 ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' :
                                   ($index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' :
                                   'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300')) }}">
                                {{ $index + 1 }}
                            </div>

                            {{-- Donatur name --}}
                            <a href="{{ $this->getDonaturUrl($donatur->id) }}"
                               class="text-sm font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 truncate transition-colors"
                               title="{{ $donatur->nama }}">
                                {{ $donatur->nama }}
                            </a>
                        </div>

                        {{-- Right side: donation amount --}}
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                                Rp {{ number_format($donatur->total_donasi, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $donatur->jumlah_transaksi }}x donasi
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-user-circle class="w-6 h-6 text-gray-400" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Belum ada data donatur
                </p>
            </div>
        @endif
    </x-metric-card>
</x-filament-widgets::widget>
