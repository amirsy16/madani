<x-filament-panels::page.simple>

    {{-- Outer card bawaan (fi-simple-main) dimatikan — kartu login yang tampil --}}
    <style>
        .fi-simple-main:has(.simadi-login-card) {
            background: transparent;
            padding: 0;
            box-shadow: none;
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
        }
        /* Latar penuh: maroon gelap + glow merah kiri-bawah + tekstur titik */
        body:has(.simadi-login-card),
        .fi-simple-layout:has(.simadi-login-card) {
            background-color: #3d0a15;
            background-image:
                radial-gradient(56rem 36rem at 6% 96%, rgba(154, 15, 45, 0.60), transparent 62%),
                radial-gradient(rgba(255,255,255,0.10) 1px, transparent 1px);
            background-size: 100% 100%, 22px 22px;
            background-repeat: no-repeat, repeat;
        }
        .fi-simple-header:has(+ .simadi-login-card) img { filter: drop-shadow(0 1px 3px rgba(0,0,0,0.4)); }
    </style>

    <div class="simadi-login-card w-full">

        <div class="grid items-stretch gap-10 md:grid-cols-[1.05fr_1fr] md:gap-12">

            {{-- Panel brand — transparan, menyatu dengan latar halaman --}}
            <div class="relative hidden flex-col justify-between p-8 md:flex lg:p-10">

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
                        ['icon' => 'heroicon-o-check-badge', 'text' => 'Pencatatan donasi dan penyaluran dalam satu sistem'],
                        ['icon' => 'heroicon-o-chart-bar', 'text' => 'Laporan dana tersedia kapan saja'],
                        ['icon' => 'heroicon-o-shield-check', 'text' => 'Data donatur tersimpan dengan aman'],
                    ] as $item)
                        <li class="flex items-center gap-3 text-sm text-white/80">
                            <x-dynamic-component :component="$item['icon']" class="h-5 w-5 shrink-0 text-amber-300/90" />
                            <span>{{ $item['text'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="relative mt-10 text-[11px] leading-relaxed text-white/45">
                    &copy; {{ date('Y') }} LAZ Insan Madani
                </p>
            </div>

            {{-- Panel form — kartu putih --}}
            <div class="rounded-2xl bg-white p-8 shadow-2xl shadow-black/30 ring-1 ring-black/5 sm:p-10 dark:bg-gray-900 dark:ring-white/10">
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
