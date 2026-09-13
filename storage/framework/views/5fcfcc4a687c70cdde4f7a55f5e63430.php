<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-6 text-white">
        <h2 class="text-2xl font-bold mb-2">Dashboard Analisis Donatur</h2>
        <p class="text-blue-100">Insight mendalam untuk strategi fundraising yang lebih efektif</p>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 rounded-xl p-5 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm font-semibold text-blue-700 dark:text-blue-300">Total Donatur Aktif</div>
                <svg class="w-8 h-8 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-blue-900 dark:text-blue-100 mb-1">
                <?php echo e(number_format($totalDonatur)); ?>

            </div>
            <div class="text-xs text-blue-600 dark:text-blue-400">
                📊 Top <?php echo e($topCount); ?> ditampilkan di tabel
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/20 rounded-xl p-5 border border-green-200 dark:border-green-700">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm font-semibold text-green-700 dark:text-green-300">Total Kontribusi</div>
                <svg class="w-8 h-8 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-green-900 dark:text-green-100 mb-1">
                Rp <?php echo e(number_format($totalKontribusi / 1000000, 1)); ?>M
            </div>
            <div class="text-xs text-green-600 dark:text-green-400">
                💰 Total: Rp <?php echo e(number_format($totalKontribusi, 0, ',', '.')); ?>

            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/20 rounded-xl p-5 border border-purple-200 dark:border-purple-700">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm font-semibold text-purple-700 dark:text-purple-300">Rata-rata Kontribusi</div>
                <svg class="w-8 h-8 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-purple-900 dark:text-purple-100 mb-1">
                Rp <?php echo e(number_format($avgKontribusi / 1000, 0)); ?>K
            </div>
            <div class="text-xs text-purple-600 dark:text-purple-400">
                📈 Per donatur aktif
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/20 rounded-xl p-5 border border-orange-200 dark:border-orange-700">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm font-semibold text-orange-700 dark:text-orange-300">Median Kontribusi</div>
                <svg class="w-8 h-8 text-orange-500 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-orange-900 dark:text-orange-100 mb-1">
                Rp <?php echo e(number_format($medianKontribusi / 1000, 0)); ?>K
            </div>
            <div class="text-xs text-orange-600 dark:text-orange-400">
                📊 Nilai tengah distribusi
            </div>
        </div>
    </div>

    <!-- Segmentasi Donatur Berdasarkan Kontribusi -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">💎 Segmentasi Donatur Berdasarkan Total Kontribusi</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Klasifikasi donatur berdasarkan nilai kontribusi keseluruhan</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Platinum -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/10 rounded-xl p-4 border-2 border-yellow-300 dark:border-yellow-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-yellow-700 dark:text-yellow-400">👑 PLATINUM</span>
                    <span class="text-2xl">💎</span>
                </div>
                <div class="text-3xl font-bold text-yellow-900 dark:text-yellow-100 mb-2">
                    <?php echo e(number_format($segmentasi['platinum'])); ?>

                </div>
                <div class="text-xs text-yellow-700 dark:text-yellow-400 font-medium mb-2">
                    ≥ Rp 50.000.000
                </div>
                <div class="w-full bg-yellow-200 dark:bg-yellow-900/50 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($segmentasi['platinum'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-yellow-600 dark:text-yellow-500 mt-1 text-center">
                    <?php echo e($totalDonatur > 0 ? number_format($segmentasi['platinum'] / $totalDonatur * 100, 1) : 0); ?>% dari total
                </div>
            </div>

            <!-- Gold -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/10 rounded-xl p-4 border-2 border-green-300 dark:border-green-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-green-700 dark:text-green-400">🥇 GOLD</span>
                    <span class="text-2xl">🌟</span>
                </div>
                <div class="text-3xl font-bold text-green-900 dark:text-green-100 mb-2">
                    <?php echo e(number_format($segmentasi['gold'])); ?>

                </div>
                <div class="text-xs text-green-700 dark:text-green-400 font-medium mb-2">
                    Rp 10JT - 50JT
                </div>
                <div class="w-full bg-green-200 dark:bg-green-900/50 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($segmentasi['gold'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-green-600 dark:text-green-500 mt-1 text-center">
                    <?php echo e($totalDonatur > 0 ? number_format($segmentasi['gold'] / $totalDonatur * 100, 1) : 0); ?>% dari total
                </div>
            </div>

            <!-- Silver -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/10 rounded-xl p-4 border-2 border-blue-300 dark:border-blue-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-blue-700 dark:text-blue-400">🥈 SILVER</span>
                    <span class="text-2xl">⭐</span>
                </div>
                <div class="text-3xl font-bold text-blue-900 dark:text-blue-100 mb-2">
                    <?php echo e(number_format($segmentasi['silver'])); ?>

                </div>
                <div class="text-xs text-blue-700 dark:text-blue-400 font-medium mb-2">
                    Rp 5JT - 10JT
                </div>
                <div class="w-full bg-blue-200 dark:bg-blue-900/50 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($segmentasi['silver'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-blue-600 dark:text-blue-500 mt-1 text-center">
                    <?php echo e($totalDonatur > 0 ? number_format($segmentasi['silver'] / $totalDonatur * 100, 1) : 0); ?>% dari total
                </div>
            </div>

            <!-- Bronze -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/10 rounded-xl p-4 border-2 border-orange-300 dark:border-orange-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-orange-700 dark:text-orange-400">🥉 BRONZE</span>
                    <span class="text-2xl">✨</span>
                </div>
                <div class="text-3xl font-bold text-orange-900 dark:text-orange-100 mb-2">
                    <?php echo e(number_format($segmentasi['bronze'])); ?>

                </div>
                <div class="text-xs text-orange-700 dark:text-orange-400 font-medium mb-2">
                    Rp 1JT - 5JT
                </div>
                <div class="w-full bg-orange-200 dark:bg-orange-900/50 rounded-full h-2">
                    <div class="bg-orange-500 h-2 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($segmentasi['bronze'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-orange-600 dark:text-orange-500 mt-1 text-center">
                    <?php echo e($totalDonatur > 0 ? number_format($segmentasi['bronze'] / $totalDonatur * 100, 1) : 0); ?>% dari total
                </div>
            </div>

            <!-- Regular -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/10 rounded-xl p-4 border-2 border-gray-300 dark:border-gray-700 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-400">⭐ REGULAR</span>
                    <span class="text-2xl">💙</span>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    <?php echo e(number_format($segmentasi['regular'])); ?>

                </div>
                <div class="text-xs text-gray-700 dark:text-gray-400 font-medium mb-2">
                    < Rp 1.000.000
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-900/50 rounded-full h-2">
                    <div class="bg-gray-500 h-2 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($segmentasi['regular'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-500 mt-1 text-center">
                    <?php echo e($totalDonatur > 0 ? number_format($segmentasi['regular'] / $totalDonatur * 100, 1) : 0); ?>% dari total
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis Frekuensi Donasi -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">🔄 Analisis Frekuensi Donasi</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Klasifikasi donatur berdasarkan keaktifan dan loyalitas</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Sangat Loyal -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/10 rounded-xl p-5 border-2 border-purple-300 dark:border-purple-700">
                <div class="text-center mb-3">
                    <div class="text-4xl mb-2">🏆</div>
                    <div class="text-sm font-bold text-purple-700 dark:text-purple-400">SANGAT LOYAL</div>
                </div>
                <div class="text-3xl font-bold text-purple-900 dark:text-purple-100 text-center mb-2">
                    <?php echo e(number_format($frekuensi['sangat_loyal'])); ?>

                </div>
                <div class="text-xs text-purple-700 dark:text-purple-400 text-center font-medium mb-3">
                    ≥ 20 kali donasi
                </div>
                <div class="w-full bg-purple-200 dark:bg-purple-900/50 rounded-full h-3">
                    <div class="bg-purple-600 h-3 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($frekuensi['sangat_loyal'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-purple-600 dark:text-purple-500 mt-2 text-center font-semibold">
                    <?php echo e($totalDonatur > 0 ? number_format($frekuensi['sangat_loyal'] / $totalDonatur * 100, 1) : 0); ?>%
                </div>
            </div>

            <!-- Loyal -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/10 rounded-xl p-5 border-2 border-indigo-300 dark:border-indigo-700">
                <div class="text-center mb-3">
                    <div class="text-4xl mb-2">🎯</div>
                    <div class="text-sm font-bold text-indigo-700 dark:text-indigo-400">LOYAL</div>
                </div>
                <div class="text-3xl font-bold text-indigo-900 dark:text-indigo-100 text-center mb-2">
                    <?php echo e(number_format($frekuensi['loyal'])); ?>

                </div>
                <div class="text-xs text-indigo-700 dark:text-indigo-400 text-center font-medium mb-3">
                    10-19 kali donasi
                </div>
                <div class="w-full bg-indigo-200 dark:bg-indigo-900/50 rounded-full h-3">
                    <div class="bg-indigo-600 h-3 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($frekuensi['loyal'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-indigo-600 dark:text-indigo-500 mt-2 text-center font-semibold">
                    <?php echo e($totalDonatur > 0 ? number_format($frekuensi['loyal'] / $totalDonatur * 100, 1) : 0); ?>%
                </div>
            </div>

            <!-- Aktif -->
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/10 rounded-xl p-5 border-2 border-teal-300 dark:border-teal-700">
                <div class="text-center mb-3">
                    <div class="text-4xl mb-2">✅</div>
                    <div class="text-sm font-bold text-teal-700 dark:text-teal-400">AKTIF</div>
                </div>
                <div class="text-3xl font-bold text-teal-900 dark:text-teal-100 text-center mb-2">
                    <?php echo e(number_format($frekuensi['aktif'])); ?>

                </div>
                <div class="text-xs text-teal-700 dark:text-teal-400 text-center font-medium mb-3">
                    5-9 kali donasi
                </div>
                <div class="w-full bg-teal-200 dark:bg-teal-900/50 rounded-full h-3">
                    <div class="bg-teal-600 h-3 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($frekuensi['aktif'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-teal-600 dark:text-teal-500 mt-2 text-center font-semibold">
                    <?php echo e($totalDonatur > 0 ? number_format($frekuensi['aktif'] / $totalDonatur * 100, 1) : 0); ?>%
                </div>
            </div>

            <!-- Kasual -->
            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/10 rounded-xl p-5 border-2 border-cyan-300 dark:border-cyan-700">
                <div class="text-center mb-3">
                    <div class="text-4xl mb-2">🌱</div>
                    <div class="text-sm font-bold text-cyan-700 dark:text-cyan-400">KASUAL</div>
                </div>
                <div class="text-3xl font-bold text-cyan-900 dark:text-cyan-100 text-center mb-2">
                    <?php echo e(number_format($frekuensi['kasual'])); ?>

                </div>
                <div class="text-xs text-cyan-700 dark:text-cyan-400 text-center font-medium mb-3">
                    < 5 kali donasi
                </div>
                <div class="w-full bg-cyan-200 dark:bg-cyan-900/50 rounded-full h-3">
                    <div class="bg-cyan-600 h-3 rounded-full" style="width: <?php echo e($totalDonatur > 0 ? ($frekuensi['kasual'] / $totalDonatur * 100) : 0); ?>%"></div>
                </div>
                <div class="text-xs text-cyan-600 dark:text-cyan-500 mt-2 text-center font-semibold">
                    <?php echo e($totalDonatur > 0 ? number_format($frekuensi['kasual'] / $totalDonatur * 100, 1) : 0); ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis Pertumbuhan & Retensi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/10 rounded-xl p-6 border border-emerald-200 dark:border-emerald-700">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400">👥 Donatur Baru</div>
                <div class="text-3xl">🎉</div>
            </div>
            <div class="text-4xl font-bold text-emerald-900 dark:text-emerald-100 mb-2">
                <?php echo e(number_format($donaturBaru)); ?>

            </div>
            <div class="text-sm text-emerald-700 dark:text-emerald-400 mb-3">
                Donatur dalam 3 bulan terakhir
            </div>
            <div class="flex items-center text-xs text-emerald-600 dark:text-emerald-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                </svg>
                Growth Rate Positif
            </div>
        </div>

        <div class="bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/20 dark:to-rose-800/10 rounded-xl p-6 border border-rose-200 dark:border-rose-700">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-bold text-rose-700 dark:text-rose-400">⚠️ Tidak Aktif</div>
                <div class="text-3xl">😴</div>
            </div>
            <div class="text-4xl font-bold text-rose-900 dark:text-rose-100 mb-2">
                <?php echo e(number_format($donaturTidakAktif)); ?>

            </div>
            <div class="text-sm text-rose-700 dark:text-rose-400 mb-3">
                Tidak donasi > 6 bulan
            </div>
            <div class="flex items-center text-xs text-rose-600 dark:text-rose-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Perlu Program Reaktivasi
            </div>
        </div>

        <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/10 rounded-xl p-6 border border-amber-200 dark:border-amber-700">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-bold text-amber-700 dark:text-amber-400">📊 Pareto 80/20</div>
                <div class="text-3xl">💪</div>
            </div>
            <div class="text-4xl font-bold text-amber-900 dark:text-amber-100 mb-2">
                <?php echo e(number_format($persenTop10, 1)); ?>%
            </div>
            <div class="text-sm text-amber-700 dark:text-amber-400 mb-3">
                Kontribusi dari Top 10 donatur
            </div>
            <div class="w-full bg-amber-200 dark:bg-amber-900/50 rounded-full h-3">
                <div class="bg-amber-600 h-3 rounded-full transition-all" style="width: <?php echo e($persenTop10); ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Strategic Insights & Recommendations -->
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-700">
        <div class="flex items-center mb-5">
            <div class="text-3xl mr-3">💡</div>
            <div>
                <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-100">Strategic Insights & Action Plan</h3>
                <p class="text-sm text-indigo-700 dark:text-indigo-400">Rekomendasi strategis berdasarkan analisis data donatur</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- High Priority Actions -->
            <div class="space-y-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-red-500">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-red-700 dark:text-red-400 mb-1">🚨 PRIORITAS TINGGI</div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Program Reaktivasi Donatur:</span> 
                                Ada <span class="font-bold text-red-600"><?php echo e(number_format($donaturTidakAktif)); ?> donatur</span> yang tidak aktif lebih dari 6 bulan. 
                                Implementasikan kampanye re-engagement dengan personalisasi pesan dan special offer.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-yellow-500">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-yellow-700 dark:text-yellow-400 mb-1">⭐ VIP RETENTION</div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Program Eksklusif untuk Platinum & Gold:</span> 
                                <span class="font-bold text-yellow-600"><?php echo e(number_format($segmentasi['platinum'] + $segmentasi['gold'])); ?> donatur premium</span> 
                                berkontribusi signifikan. Berikan appreciation program, event khusus, dan laporan impact berkala.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-blue-700 dark:text-blue-400 mb-1">📈 UPGRADE STRATEGY</div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Naikkan Tier Donatur Silver & Bronze:</span> 
                                Target <span class="font-bold text-blue-600"><?php echo e(number_format($segmentasi['silver'] + $segmentasi['bronze'])); ?> donatur</span> 
                                untuk upgrade dengan milestone challenges, matched giving, atau recurring donation incentives.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Opportunities -->
            <div class="space-y-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-green-500">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-green-700 dark:text-green-400 mb-1">🎉 WELCOME NEW DONORS</div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Onboarding Donatur Baru:</span> 
                                <span class="font-bold text-green-600"><?php echo e(number_format($donaturBaru)); ?> donatur baru</span> dalam 3 bulan terakhir. 
                                Implementasikan welcome series, edukasi program, dan first-time donor appreciation untuk meningkatkan retention.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-purple-700 dark:text-purple-400 mb-1">🔄 FREQUENCY BOOST</div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Tingkatkan Frekuensi Donatur Kasual:</span> 
                                <span class="font-bold text-purple-600"><?php echo e(number_format($frekuensi['kasual'])); ?> donatur kasual</span> 
                                (<5 donasi) punya potensi besar. Promosikan recurring donation program dan reminder berdasarkan preferensi donasi sebelumnya.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-orange-500">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-orange-700 dark:text-orange-400 mb-1">🏆 LOYALTY REWARDS</div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Apresiasi Donatur Sangat Loyal:</span> 
                                <span class="font-bold text-orange-600"><?php echo e(number_format($frekuensi['sangat_loyal'])); ?> donatur sangat loyal</span> 
                                (≥20 donasi) adalah brand ambassadors terbaik. Berikan special recognition, certificate of appreciation, dan involve dalam decision making.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data-Driven Action Items -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center mb-5">
            <div class="text-3xl mr-3">🎯</div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Actionable Next Steps</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Quick wins yang bisa diimplementasikan segera</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/10 rounded-lg p-4">
                <div class="text-2xl mb-2">📧</div>
                <div class="text-sm font-bold text-blue-900 dark:text-blue-100 mb-2">Segmented Email Campaign</div>
                <div class="text-xs text-blue-700 dark:text-blue-400">
                    Kirim email personalized berdasarkan tier donatur dengan konten dan CTA yang relevan
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/10 rounded-lg p-4">
                <div class="text-2xl mb-2">📞</div>
                <div class="text-sm font-bold text-green-900 dark:text-green-100 mb-2">Personal Touch Program</div>
                <div class="text-xs text-green-700 dark:text-green-400">
                    Hubungi langsung top 20 donatur untuk thank you call dan mendengar feedback mereka
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/10 rounded-lg p-4">
                <div class="text-2xl mb-2">📊</div>
                <div class="text-sm font-bold text-purple-900 dark:text-purple-100 mb-2">Impact Report Dashboard</div>
                <div class="text-xs text-purple-700 dark:text-purple-400">
                    Buat dashboard khusus untuk donatur premium showing real-time impact dari kontribusi mereka
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/10 rounded-lg p-4">
                <div class="text-2xl mb-2">🎁</div>
                <div class="text-sm font-bold text-orange-900 dark:text-orange-100 mb-2">Referral Program</div>
                <div class="text-xs text-orange-700 dark:text-orange-400">
                    Leverage loyal donors untuk ajak teman dengan referral incentives yang menarik
                </div>
            </div>
        </div>
    </div>

    <!-- Tips Penggunaan Dashboard -->
    <div class="bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-xl p-6 border border-cyan-200 dark:border-cyan-700">
        <div class="flex items-center mb-4">
            <div class="text-3xl mr-3">💡</div>
            <div>
                <h3 class="text-lg font-bold text-cyan-900 dark:text-cyan-100">Tips Maksimalkan Dashboard Ini</h3>
                <p class="text-sm text-cyan-700 dark:text-cyan-400">Panduan penggunaan untuk analisis yang lebih efektif</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="flex items-start space-x-3">
                <div class="text-lg">🔍</div>
                <div class="text-sm text-cyan-800 dark:text-cyan-300">
                    <span class="font-semibold">Filter Periode:</span> Gunakan filter waktu di tabel utama untuk melihat performa donatur di periode spesifik
                </div>
            </div>
            
            <div class="flex items-start space-x-3">
                <div class="text-lg">📊</div>
                <div class="text-sm text-cyan-800 dark:text-cyan-300">
                    <span class="font-semibold">Segmentasi:</span> Filter berdasarkan kategori untuk targeting campaign yang lebih presisi
                </div>
            </div>
            
            <div class="flex items-start space-x-3">
                <div class="text-lg">📈</div>
                <div class="text-sm text-cyan-800 dark:text-cyan-300">
                    <span class="font-semibold">Export Data:</span> Gunakan bulk export untuk analisis lebih dalam di Excel atau import ke CRM
                </div>
            </div>
            
            <div class="flex items-start space-x-3">
                <div class="text-lg">💬</div>
                <div class="text-sm text-cyan-800 dark:text-cyan-300">
                    <span class="font-semibold">WhatsApp Direct:</span> Klik tombol WA untuk komunikasi langsung dengan donatur prioritas
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/simadior/public_html/resources/views/filament/widgets/top-donatur/analytics-modal.blade.php ENDPATH**/ ?>