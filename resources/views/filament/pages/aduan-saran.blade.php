<x-filament-panels::page>

    <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">

        {{-- Kolom informasi --}}
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400">
                        <x-heroicon-o-chat-bubble-oval-left-ellipsis class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Saluran langsung ke tim pengembang</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Website SIMADI &middot; LAZ Insan Madani</p>
                    </div>
                </div>

                <p class="mt-4 text-[13px] leading-relaxed text-gray-600 dark:text-gray-300">
                    Gunakan formulir di samping untuk menyampaikan laporan Anda. Setiap kiriman diterima
                    administrator melalui email dan ditindaklanjuti sesegera mungkin.
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Untuk apa menu ini
                </h3>
                <ul class="mt-3.5 space-y-3.5">
                    @foreach ([
                        ['icon' => 'heroicon-o-exclamation-triangle', 'title' => 'Aduan', 'desc' => 'Bug, gangguan, atau perilaku sistem yang tidak sesuai.'],
                        ['icon' => 'heroicon-o-light-bulb', 'title' => 'Saran', 'desc' => 'Ide fitur atau perbaikan pengalaman penggunaan.'],
                        ['icon' => 'heroicon-o-question-mark-circle', 'title' => 'Pertanyaan', 'desc' => 'Hal yang belum jelas tentang cara pakai sistem.'],
                    ] as $item)
                        <li class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <x-dynamic-component :component="$item['icon']" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-[13px] font-medium text-gray-900 dark:text-white">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-xs leading-snug text-gray-500 dark:text-gray-400">{{ $item['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Tips laporan cepat ditangani:</span>
                    tulis halaman/menu yang bermasalah, apa yang terjadi, dan apa yang seharusnya terjadi.
                    Untuk aduan, pilih prioritas sesuai dampaknya.
                </p>
            </div>
        </div>

        {{-- Kolom formulir --}}
        <div>
            <div
                x-data="{ sending: false }"
                x-on:send-to-web3forms.window="
                    sending = true;
                    fetch('https://api.web3forms.com/submit', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify($event.detail.payload)
                    })
                    .then(r => r.json())
                    .then(data => {
                        sending = false;
                        if (data.success) {
                            $wire.notifySuccess();
                        } else {
                            $wire.notifyError(data.message || 'Gagal mengirim ke Web3Forms.');
                        }
                    })
                    .catch(err => {
                        sending = false;
                        $wire.notifyError('Koneksi gagal: ' + err.message);
                    })
                "
            >
                <form wire:submit="submit" class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    {{ $this->form }}

                    <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">
                            Nama &amp; email diambil otomatis dari akun Anda.
                        </p>
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-paper-airplane"
                            x-bind:disabled="sending"
                        >
                            <span x-show="!sending">Kirim Laporan</span>
                            <span x-show="sending" x-cloak>Mengirim&hellip;</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <x-filament-actions::modals />

</x-filament-panels::page>
