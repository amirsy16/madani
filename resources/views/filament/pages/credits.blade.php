<x-filament-panels::page>
    <style>
        .credit-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .dark .credit-card {
            background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
        .credit-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        .lead-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }
        .avatar-ring {
            box-shadow: 0 0 0 4px rgba(255,255,255,0.9), 0 4px 15px rgba(0,0,0,0.2);
        }
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }
        .tech-badge {
            transition: all 0.2s ease;
        }
        .tech-badge:hover {
            transform: translateY(-2px);
        }
        
        /* Fix logo visibility in dark mode */
        .logo-dark-fix {
            transition: all 0.3s ease;
        }
        .dark .logo-dark-fix {
            background: white;
            padding: 0.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
        }
    </style>

    <div class="max-w-4xl mx-auto py-10">
        {{-- Logo Section --}}
        <div class="flex items-center justify-center gap-8 mb-10">
            <div class="flex flex-col items-center">
                <img src="{{ asset('images/LOGOIM.png') }}" alt="Logo Insan Madani" class="h-16 w-auto object-contain">
            </div>
            <div class="flex flex-col items-center">
                <img src="{{ asset('images/UNJA.png') }}" alt="Logo Universitas Jambi" class="h-16 w-auto object-contain">
            </div>
            <div class="flex flex-col items-center logo-dark-fix">
                <img src="{{ asset('images/LOGOSI.png') }}" alt="Logo Sistem Informasi UNJA" class="h-16 w-auto object-contain">
            </div>
        </div>

        {{-- Header Section --}}
        <div class="text-center mb-12" x-data="{ showChangelog: false }">
            <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                Sistem Informasi Manajemen Donasi
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">
                LAZ Insan Madani
            </p>
            <div class="flex items-center justify-center gap-2 mt-2">
                <span class="text-slate-400 dark:text-slate-500 text-sm font-medium">SIMADI v2.0</span>
                <button
                    @click="showChangelog = true"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition-all hover:scale-105"
                    style="background-color: #dbeafe; color: #1e40af;"
                    title="Lihat perubahan v2.0"
                >
                    <x-heroicon-s-clipboard-document-list class="w-3 h-3" />
                    Apa yang baru?
                </button>
            </div>
            <div class="section-divider w-32 mx-auto mt-6"></div>

            {{-- Changelog Modal --}}
            <div
                x-show="showChangelog"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background: rgba(0,0,0,0.5);"
                @click.self="showChangelog = false"
            >
                <div
                    x-show="showChangelog"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[80vh] overflow-y-auto text-left"
                >
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <x-heroicon-s-rocket-launch class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Changelog Simadi</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Riwayat perubahan versi</p>
                            </div>
                        </div>
                        <button @click="showChangelog = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- v2.0 --}}
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold" style="background-color: #800020; color: #ffffff;">v2.0</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">14 September 2026</span>
                            <span class="ml-auto px-2 py-0.5 rounded-full text-xs font-semibold" style="background-color: #d1fae5; color: #065f46;">Terbaru</span>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center gap-1.5 mb-2">
                                <x-heroicon-s-shield-check class="w-4 h-4" style="color: #16a34a;" />
                                <span class="text-xs font-bold uppercase tracking-wide" style="color: #16a34a;">Keamanan</span>
                            </div>
                            <ul class="space-y-2 pl-2">
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#16a34a;"></span>
                                    <span><strong>Sistem dibersihkan dari jejak backdoor/webshell</strong> dan seluruh lubang aksesnya ditutup.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#16a34a;"></span>
                                    <span><strong>Invoice &amp; bukti transfer kini privat</strong> — unduh invoice wajib login; dokumen tidak lagi bisa diakses publik.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#16a34a;"></span>
                                    <span><strong>Peran pengguna diperketat</strong> — akun tanpa peran tidak bisa masuk panel, laporan keuangan dilindungi izin.</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center gap-1.5 mb-2">
                                <x-heroicon-s-arrow-path class="w-4 h-4" style="color: #d97706;" />
                                <span class="text-xs font-bold uppercase tracking-wide" style="color: #d97706;">Diperbarui</span>
                            </div>
                            <ul class="space-y-2 pl-2">
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Angka laporan lebih dapat dipercaya</strong> — Laporan Perubahan Dana kini selalu balance dan saldo minus tidak disembunyikan.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Jauh lebih cepat</strong> — statistik dashboard di-cache dan index database dibetulkan; halaman tidak lagi berat.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Filter periode &amp; data alamat</strong> — filter Bulan/3/6 Bulan kini berfungsi, kota donatur tersimpan benar.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Tampilan dirapikan</strong> — login baru, kartu statistik seragam, tombol debug dihapus, Laporan Perubahan Dana disederhanakan.</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center gap-1.5 mb-2">
                                <x-heroicon-s-minus-circle class="w-4 h-4" style="color: #dc2626;" />
                                <span class="text-xs font-bold uppercase tracking-wide" style="color: #dc2626;">Dihapus</span>
                            </div>
                            <ul class="space-y-2 pl-2">
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#dc2626;"></span>
                                    <span><strong>Kirim Invoice via WhatsApp</strong> — fitur tidak pernah berfungsi sejak awal; PDF invoice tetap tersedia via tombol Download PDF.</span>
                                </li>
                            </ul>
                        </div>

                        <div class="section-divider mb-5"></div>

                        {{-- v1.2 --}}
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold" style="background-color: #dbeafe; color: #1e40af;">v1.2</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">24 Februari 2026</span>
                        </div>

                        {{-- Ditambah --}}
                        <div class="mb-4">
                            <div class="flex items-center gap-1.5 mb-2">
                                <x-heroicon-s-plus-circle class="w-4 h-4" style="color: #16a34a;" />
                                <span class="text-xs font-bold uppercase tracking-wide" style="color: #16a34a;">Ditambahkan</span>
                            </div>
                            <ul class="space-y-2 pl-2">
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#16a34a;"></span>
                                    <span><strong>Fitur Aduan &amp; Saran</strong> — pengguna kini bisa menyampaikan keluhan atau masukan terkait website langsung dari dalam sistem. Laporan akan otomatis dikirim ke email administrator.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#16a34a;"></span>
                                    <span><strong>Riwayat perubahan versi</strong> — informasi mengenai apa saja yang berubah di setiap versi kini bisa dilihat langsung dari halaman Tim Proyek.</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Diubah --}}
                        <div class="mb-4">
                            <div class="flex items-center gap-1.5 mb-2">
                                <x-heroicon-s-arrow-path class="w-4 h-4" style="color: #d97706;" />
                                <span class="text-xs font-bold uppercase tracking-wide" style="color: #d97706;">Diperbarui</span>
                            </div>
                            <ul class="space-y-2 pl-2">
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Pengaturan hak akses lebih sederhana</strong> — tampilan pengaturan peran pengguna dipangkas agar lebih ringkas dan mudah dipahami.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Perbaikan menu tidak sesuai peran</strong> — menu yang tampil di sidebar kini sudah sesuai dengan hak akses yang diberikan ke setiap pengguna.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#d97706;"></span>
                                    <span><strong>Tampilan form pengaturan peran</strong> — beberapa kolom yang tidak perlu diisi manual kini disembunyikan agar lebih rapi dan tidak membingungkan.</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Dihapus --}}
                        <div class="mb-6">
                            <div class="flex items-center gap-1.5 mb-2">
                                <x-heroicon-s-minus-circle class="w-4 h-4" style="color: #dc2626;" />
                                <span class="text-xs font-bold uppercase tracking-wide" style="color: #dc2626;">Dihapus</span>
                            </div>
                            <ul class="space-y-2 pl-2">
                                <li class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#dc2626;"></span>
                                    <span><strong>Menu Invoice Donasi</strong> — menu ini dihapus sementara karena menyebabkan gangguan saat dibuka.</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Divider --}}
                        <div class="section-divider mb-5"></div>

                        {{-- v1.0 --}}
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">v1.0</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">16 Oktober 2025</span>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 pl-2">Rilis perdana — manajemen donasi, donatur, fundraiser, laporan keuangan, dan sistem peran berbasis permission.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Project Lead & Advisor Section - Side by Side --}}
        <div class="grid gap-6 md:grid-cols-2" style="margin-top: 3rem; margin-bottom: 3rem;">
            {{-- Project Lead --}}
            <div class="credit-card rounded-2xl transition-all duration-300 border border-slate-200 dark:border-slate-700" style="padding: 2.5rem;">
                <div class="flex items-center gap-2 mb-8">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                        <x-heroicon-s-star class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 tracking-wide uppercase">Pemimpin Proyek</span>
                </div>
                
                <div class="flex flex-col items-center gap-6 text-center">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
                        <x-heroicon-s-user class="w-10 h-10 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Amir Syofian</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-1">Ketua Tim Pengembang</p>
                        <p class="text-slate-400 dark:text-slate-500 text-sm mb-4">Mahasiswa Universitas Jambi</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color: #dbeafe; color: #1e40af;">
                                <x-heroicon-s-command-line class="w-3.5 h-3.5" />
                                Pengembang Utama
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color: #d1fae5; color: #065f46;">
                                <x-heroicon-s-wrench-screwdriver class="w-3.5 h-3.5" />
                                Pemelihara Sistem
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advisor --}}
            <div class="credit-card rounded-2xl transition-all duration-300 border border-slate-200 dark:border-slate-700" style="padding: 2.5rem;">
                <div class="flex items-center gap-2 mb-8">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <x-heroicon-s-user-group class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 tracking-wide uppercase">Koordinator Lembaga</span>
                </div>
                
                <div class="flex flex-col items-center gap-6 text-center">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                        <x-heroicon-s-user class="w-10 h-10 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Joko Nurhadi</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Bendahara LAZ Insan Madani</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color: #fef3c7; color: #92400e;">
                                <x-heroicon-s-chat-bubble-left-right class="w-3.5 h-3.5" />
                                Narasumber
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color: #ffedd5; color: #c2410c;">
                                <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5" />
                                Koordinator
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Team Members Section --}}
        <div style="margin-top: 3rem; margin-bottom: 3rem;">
            <div class="flex items-center gap-2 mb-8">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <x-heroicon-s-user-group class="w-4 h-4 text-white" />
                </div>
                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 tracking-wide uppercase">Tim Kontributor</span>
            </div>
            
            <div class="grid gap-6 md:grid-cols-3">
                {{-- Team Member 1 --}}
                <div class="credit-card rounded-2xl p-6 text-center transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);">
                        <x-heroicon-s-user class="w-8 h-8 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100">Khaira Alya Fazira</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 mb-3">Mahasiswa Universitas Jambi</p>
                    <div class="flex flex-wrap justify-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #fce7f3; color: #9d174d;">
                            Analis Bisnis
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #d1fae5; color: #065f46;">
                            Kontributor
                        </span>
                    </div>
                </div>

                {{-- Team Member 2 --}}
                <div class="credit-card rounded-2xl p-6 text-center transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);">
                        <x-heroicon-s-user class="w-8 h-8 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100">M. Akbar Yoga Prasetya</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 mb-3">Mahasiswa Universitas Jambi</p>
                    <div class="flex flex-wrap justify-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #d1fae5; color: #065f46;">
                            Kontributor
                        </span>
                    </div>
                </div>

                {{-- Team Member 3 --}}
                <div class="credit-card rounded-2xl p-6 text-center transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
                        <x-heroicon-s-user class="w-8 h-8 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100">M. Nofriza</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 mb-3">Mahasiswa Universitas Jambi</p>
                    <div class="flex flex-wrap justify-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #d1fae5; color: #065f46;">
                            Kontributor
                        </span>
                    </div>
                </div>
                
                {{-- Team Member 4 --}}
                <div class="credit-card rounded-2xl p-6 text-center transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                        <x-heroicon-s-user class="w-8 h-8 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100">Suwaibah</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 mb-3">Mahasiswa Universitas Jambi</p>
                    <div class="flex flex-wrap justify-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #d1fae5; color: #065f46;">
                            Kontributor
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tech Stack Section --}}
        <div class="credit-card rounded-2xl border border-slate-200 dark:border-slate-700" style="margin-top: 3rem; padding: 3rem;">
            <div class="flex items-center gap-2 mb-10">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #475569 0%, #334155 100%);">
                    <x-heroicon-s-cpu-chip class="w-4 h-4 text-white" />
                </div>
                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 tracking-wide uppercase">Teknologi</span>
            </div>
            
            <div class="flex flex-wrap gap-4 justify-center" style="margin: 0 2rem;">
                <span class="tech-badge inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012a.27.27 0 01-.064-.026L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034h.001L5.044.05a.375.375 0 01.378 0L9.936 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.02.024.033.011.018.018.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.011.026-.023.041-.032h.001l4.513-2.598a.375.375 0 01.38 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033a.3.3 0 01.024.06c.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.514 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003-.002-.002c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.04-.01-.012-.021-.023-.028-.037h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087l-1.58-.907v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/></svg>
                    Laravel
                </span>
                <span class="tech-badge inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L1.5 6v12L12 24l10.5-6V6L12 0zm0 2.25l8.25 4.5v9l-8.25 4.5-8.25-4.5v-9L12 2.25z"/></svg>
                    Filament
                </span>
                <span class="tech-badge inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background-color: #fdf4ff; color: #c026d3; border: 1px solid #f5d0fe;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 3a7 7 0 100 14 7 7 0 000-14z"/></svg>
                    Livewire
                </span>
                <span class="tech-badge inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background-color: #ecfeff; color: #0891b2; border: 1px solid #a5f3fc;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/></svg>
                    Tailwind
                </span>
                <span class="tech-badge inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C7.58 3 4 4.79 4 7s3.58 4 8 4 8-1.79 8-4-3.58-4-8-4zM4 9v3c0 2.21 3.58 4 8 4s8-1.79 8-4V9c0 2.21-3.58 4-8 4s-8-1.79-8-4zm0 5v3c0 2.21 3.58 4 8 4s8-1.79 8-4v-3c0 2.21-3.58 4-8 4s-8-1.79-8-4z"/></svg>
                    MySQL
                </span>
                <span class="tech-badge inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background-color: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M0 12.5v6.792l6.75 3.896V16.49L0 12.5zm6.75 3.896l6.75 3.896v-6.698l-6.75-3.896v6.698zm6.75 3.896l6.75-3.896v-6.698l-6.75 3.896v6.698zM24 12.5l-6.75 3.99v6.698L24 19.292V12.5zM6.75.812L0 4.708v6.698l6.75-3.896V.812zm6.75 3.896L6.75.812v6.698l6.75 3.896V4.708zm0 0l6.75-3.896v6.698l-6.75 3.896V4.708zM24 4.708l-6.75-3.896v6.698L24 11.406V4.708z"/></svg>
                    Alpine.js
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center pt-4">
            <div class="inline-flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <x-heroicon-s-heart class="w-4 h-4 text-rose-500" />
                <span class="text-sm">Dibuat dengan dedikasi untuk LAZ Insan Madani</span>
            </div>
            <p class="text-slate-400 dark:text-slate-500 text-xs mt-2">
                © {{ date('Y') }}
            </p>
        </div>
    </div>
</x-filament-panels::page>
