<x-filament-panels::page.simple>

    <div class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-900/5 dark:border-gray-700 dark:bg-gray-900">

        <div class="grid md:grid-cols-[1.05fr_1fr]">

            {{-- Panel brand --}}
            <div class="relative hidden flex-col justify-between overflow-hidden bg-[#3d0a15] p-8 md:flex">
                {{-- tekstur titik halus, sangat rendah, murni dekoratif --}}
                <div
                    class="absolute inset-0 opacity-[0.14]"
                    style="background-image: radial-gradient(rgba(255,255,255,0.55) 1px, transparent 1px); background-size: 22px 22px;"
                ></div>
                <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-[#800020]/70 blur-2xl"></div>

                <div class="relative">
                    <div class="inline-flex items-center rounded-lg bg-white/95 px-3.5 py-2.5 shadow-sm">
                        <img src="{{ asset('images/LOGOIM.png') }}" alt="LAZ Insan Madani" class="h-9 w-auto">
                    </div>

                    <h2 class="mt-8 text-3xl font-bold tracking-tight text-white">
                        SIMADI
                        <span class="ml-1 align-middle text-[11px] font-semibold uppercase tracking-widest text-amber-300/90">v2.0</span>
                    </h2>
                    <p class="mt-2 max-w-xs text-sm leading-relaxed text-white/70">
                        Sistem Informasi Pengelolaan Dana Zakat, Infaq &amp; Sedekah — LAZ Insan Madani.
                    </p>
                </div>

                <ul class="relative mt-10 space-y-3.5">
                    @foreach ([
                        ['icon' => 'heroicon-o-check-badge', 'text' => 'Verifikasi donasi & penyaluran teraudit'],
                        ['icon' => 'heroicon-o-chart-bar', 'text' => 'Laporan dana real-time dengan detail per kategori'],
                        ['icon' => 'heroicon-o-shield-check', 'text' => 'Data dokumen sensitif tersimpan privat'],
                    ] as $item)
                        <li class="flex items-center gap-3 text-sm text-white/80">
                            <x-dynamic-component :component="$item['icon']" class="h-5 w-5 shrink-0 text-amber-300/90" />
                            <span>{{ $item['text'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="relative mt-10 text-[11px] leading-relaxed text-white/45">
                    &copy; {{ date('Y') }} LAZ Insan Madani &middot; Sistem internal — akses terbatas
                    untuk pengelola yang berwenang.
                </p>
            </div>

            {{-- Panel form --}}
            <div class="p-8 sm:p-10">
                <div class="mb-8 flex items-center justify-between md:hidden">
                    <img src="{{ asset('images/LOGOIM.png') }}" alt="LAZ Insan Madani" class="h-8 w-auto">
                </div>

                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Masuk ke akun Anda
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gunakan akun pengelola yang telah diberikan administrator.
                </p>

                <x-filament-panels::form
                    id="form"
                    wire:submit="authenticate"
                    class="mt-6"
                >
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>

                <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 text-xs dark:border-gray-800">
                    <a
                        href="/admin/password-reset"
                        class="font-medium text-primary-700 hover:underline dark:text-primary-400"
                    >
                        Lupa kata sandi?
                    </a>
                    <span class="text-gray-400 dark:text-gray-500">SIMADI v2.0 &middot; LAZ Insan Madani</span>
                </div>
            </div>

        </div>
    </div>

</x-filament-panels::page.simple>
