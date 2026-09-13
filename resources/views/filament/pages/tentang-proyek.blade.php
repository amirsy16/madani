<x-filament-panels::page>

    {{-- Hero --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="relative bg-[#3d0a15] px-6 py-8 sm:px-10 sm:py-10">
            <div class="absolute inset-0 opacity-[0.12]"
                 style="background-image: radial-gradient(rgba(255,255,255,0.55) 1px, transparent 1px); background-size: 22px 22px;"></div>
            <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-[#800020]/70 blur-2xl"></div>

            <div class="relative flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/40 bg-amber-300/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-widest text-amber-300">
                        Rilis Besar
                    </div>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-white">
                        SIMADI v2.0
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-white/70">
                        Pembenahan menyeluruh atas sistem pengelolaan dana ZIS: keamanan, ketepatan angka
                        laporan, kecepatan tampilan, dan antarmuka yang baru. Dilakukan berdasarkan audit
                        independen atas seluruh kode sistem.
                    </p>
                </div>

                <div class="flex items-center gap-3 rounded-xl bg-white/95 px-4 py-3 shadow-sm">
                    <img src="{{ asset('images/LOGOIM.png') }}" alt="LAZ Insan Madani" class="h-10 w-auto">
                </div>
            </div>

            {{-- Angka ringkas --}}
            <div class="relative mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([
                    ['n' => '20+', 'l' => 'Artefak berbahaya dibersihkan'],
                    ['n' => '30+', 'l' => 'Bug & celah ditutup'],
                    ['n' => '10×', 'l' => 'Dashboard lebih ringan (cache)'],
                    ['n' => '15', 'l' => 'Tes otomatis menjaga regresi'],
                ] as $s)
                    <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                        <div class="text-2xl font-bold text-amber-300">{{ $s['n'] }}</div>
                        <div class="mt-0.5 text-[11px] leading-snug text-white/60">{{ $s['l'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Apa yang baru --}}
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Apa yang baru</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Ringkasan perubahan besar pada rilis ini, dikelompokkan per bidang.
        </p>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">

            @foreach ([
                [
                    'icon' => 'heroicon-o-shield-check',
                    'color' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'title' => 'Keamanan',
                    'items' => [
                        'Jejak backdoor/webshell dibersihkan total dan lubang aksesnya ditutup',
                        'File & folder sensitif kini tak bisa diakses publik lewat server',
                        'Invoice PDF & bukti transfer pindah ke penyimpanan privat',
                        'Unduh invoice wajib login + izin; URL WhatsApp memakai tanda tangan berbatas waktu',
                        'Kata sandi akun tanpa peran tidak bisa masuk panel lagi',
                    ],
                ],
                [
                    'icon' => 'heroicon-o-calculator',
                    'color' => 'text-primary-700 bg-primary-50 dark:bg-primary-900/30 dark:text-primary-400',
                    'title' => 'Ketepatan Angka',
                    'items' => [
                        'Laporan Perubahan Dana kini selalu balance: penerimaan − bagian amil − penyaluran = surplus',
                        'Saldo minus (dana terpakai lebih) tidak lagi disembunyikan',
                        'Index database dibuat sungguhan — laporan berbulan-bulan kini terindex',
                        'Kota/kabupaten donatur kini tersimpan benar',
                        'Pencatat & verifikator setiap donasi tercatat kembali',
                    ],
                ],
                [
                    'icon' => 'heroicon-o-bolt',
                    'color' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-400',
                    'title' => 'Kecepatan',
                    'items' => [
                        'Statistik dashboard di-cache 10 menit — tidak menghitung ulang tiap klik',
                        'Query berulang di tabel & laporan dipangkas puluhan kali per halaman',
                        'Chart tren donasi jadi satu query ringan',
                    ],
                ],
                [
                    'icon' => 'heroicon-o-wrench-screwdriver',
                    'color' => 'text-sky-600 bg-sky-50 dark:bg-sky-900/30 dark:text-sky-400',
                    'title' => 'Perbaikan Fitur',
                    'items' => [
                        'Filter periode Bulan Lalu / 3 Bulan / 6 Bulan kini benar-benar memfilter',
                        'Fitur kirim invoice WhatsApp dihentikan (tidak pernah berfungsi) — PDF tetap tersedia',
                        'Kode donatur anti-bentrok saat input bersamaan',
                        'Penyaluran dana dicek ulang saldo-nya di server sebelum disimpan',
                    ],
                ],
            ] as $block)
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $block['color'] }}">
                            <x-dynamic-component :component="$block['icon']" class="h-5 w-5" />
                        </span>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $block['title'] }}</h3>
                    </div>
                    <ul class="mt-3.5 space-y-2.5">
                        @foreach ($block['items'] as $item)
                            <li class="flex gap-2.5 text-[13px] leading-snug text-gray-600 dark:text-gray-300">
                                <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

        </div>
    </div>

    {{-- Antarmuka + Peta berikutnya --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <x-heroicon-o-paint-brush class="h-5 w-5" />
                </span>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Antarmuka v2</h3>
            </div>
            <ul class="mt-3.5 space-y-2.5">
                @foreach ([
                    'Halaman login, statistik, dan laporan dirancang ulang — lebih ringkas dan searah',
                    'Kartu statistik seragam: satu pola, satu palet status, satu format rupiah',
                    'Tombol debug dan elemen eksperimen dibersihkan dari dashboard',
                    'Tampilan teruji di layar kecil: tabel punya scroll horizontal sendiri',
                ] as $item)
                    <li class="flex gap-2.5 text-[13px] leading-snug text-gray-600 dark:text-gray-300">
                        <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/60 p-5 dark:border-gray-700 dark:bg-gray-900/60">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <x-heroicon-o-map class="h-5 w-5" />
                </span>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rencana berikutnya</h3>
            </div>
            <ul class="mt-3.5 space-y-2.5">
                @foreach ([
                    'Semua kartu statistik dapat diklik untuk melihat detail transaksinya',
                    'Grafik bawaan panel (tanpa skrip eksternal) agar lebih cepat & konsisten',
                    'Backup terjadwal & pemantauan kesalahan sistem',
                ] as $item)
                    <li class="flex gap-2.5 text-[13px] leading-snug text-gray-600 dark:text-gray-300">
                        <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 text-[11px] text-gray-400 dark:text-gray-500">
                Ada temuan atau saran? Gunakan menu <strong>Aduan &amp; Saran</strong> — laporan Anda sampai
                langsung ke tim pengembang.
            </p>
        </div>

    </div>

</x-filament-panels::page>
