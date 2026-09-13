<x-filament-panels::page>

    {{-- Penjelasan Menu --}}
    <div class="rounded-xl border border-primary-200 bg-primary-50 p-5 dark:border-primary-800 dark:bg-primary-950/30">
        <div class="flex gap-4">
            <div class="mt-0.5 flex-shrink-0 text-primary-600 dark:text-primary-400">
                <x-heroicon-o-information-circle class="h-6 w-6" />
            </div>
            <div class="space-y-1">
                <p class="text-sm font-semibold text-primary-800 dark:text-primary-200">Tentang Menu Ini</p>
                <p class="text-sm text-primary-700 dark:text-primary-300">
                    Menu <strong>Aduan &amp; Saran</strong> disediakan sebagai saluran komunikasi resmi antara seluruh
                    pihak internal &mdash; pengelola zakat, fundraiser, dan staf &mdash; dengan tim pengembang website.
                </p>
                <p class="text-sm text-primary-700 dark:text-primary-300 mt-1">
                    Gunakan menu ini untuk:
                </p>
                <ul class="mt-1 list-inside list-disc space-y-0.5 text-sm text-primary-700 dark:text-primary-300">
                    <li><strong>Aduan</strong> — melaporkan masalah, bug, atau gangguan pada sistem website.</li>
                    <li><strong>Saran</strong> — menyampaikan ide atau usulan pengembangan fitur website.</li>
                    <li><strong>Pertanyaan</strong> — menanyakan hal yang belum dipahami terkait penggunaan sistem.</li>
                </ul>
                <p class="mt-2 text-xs text-primary-600 dark:text-primary-400">
                    Laporan akan langsung diterima oleh administrator melalui email dan akan ditindaklanjuti sesegera mungkin.
                </p>
            </div>
        </div>
    </div>

    {{-- Web3Forms Handler via Alpine.js (submit dari browser, bypass SSL server) --}}
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
        {{-- Form --}}
        <form wire:submit="submit" class="mt-2">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    type="submit"
                    icon="heroicon-o-paper-airplane"
                    size="lg"
                    x-bind:disabled="sending"
                >
                    <span x-show="!sending">Kirim Laporan</span>
                    <span x-show="sending" x-cloak>Mengirim...</span>
                </x-filament::button>
            </div>
        </form>
    </div>

    <x-filament-actions::modals />

</x-filament-panels::page>
