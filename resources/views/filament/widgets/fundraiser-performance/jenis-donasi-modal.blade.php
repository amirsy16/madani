@php
    use App\Models\Donasi;
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    // Query untuk mendapatkan breakdown per jenis donasi
    $query = Donasi::query()
        ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
        ->where('donasis.fundraiser_id', $fundraiserId)
        ->where('donasis.status_konfirmasi', 'verified')
        ->select([
            'jenis_donasis.id',
            'jenis_donasis.nama as jenis_nama',
            DB::raw('COUNT(donasis.id) as jumlah_transaksi'),
            DB::raw('SUM(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as total_nominal'),
            DB::raw('AVG(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as rata_rata_nominal'),
        ]);

    // Terapkan filter periode jika ada
    $now = Carbon::now();
    switch ($timePeriod) {
        case 'current_month':
            $query->whereMonth('donasis.tanggal_donasi', $now->month)
                  ->whereYear('donasis.tanggal_donasi', $now->year);
            break;
        case 'last_month':
            $lastMonth = $now->copy()->subMonth();
            $query->whereMonth('donasis.tanggal_donasi', $lastMonth->month)
                  ->whereYear('donasis.tanggal_donasi', $lastMonth->year);
            break;
        case 'current_year':
            $query->whereYear('donasis.tanggal_donasi', $now->year);
            break;
        case 'last_3_months':
            $query->where('donasis.tanggal_donasi', '>=', $now->copy()->subMonths(3)->startOfDay());
            break;
        case 'last_6_months':
            $query->where('donasis.tanggal_donasi', '>=', $now->copy()->subMonths(6)->startOfDay());
            break;
    }

    $jenisDonasiBreakdown = $query->groupBy('jenis_donasis.id', 'jenis_donasis.nama')
        ->orderByDesc('total_nominal')
        ->get();
    

    // Definisikan warna untuk setiap card
    $colors = [
        'bg-blue-500',
        'bg-green-500', 
        'bg-yellow-500',
        'bg-red-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-teal-500',
        'bg-orange-500',
        'bg-cyan-500',
    ];

    $icons = [
        '💰', '🎁', '📦', '🏥', '📚', '🍽️', '🏠', '💡', '🌟', '❤️'
    ];
@endphp

<div class="p-4">
    @if($jenisDonasiBreakdown->isEmpty())
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📊</div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Data</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada donasi untuk fundraiser ini pada periode yang dipilih.
            </p>
        </div>
    @else
        <!-- Summary Card -->
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg text-white">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-sm opacity-90">Total Jenis Donasi</div>
                    <div class="text-2xl font-bold">{{ $jenisDonasiBreakdown->count() }}</div>
                </div>
                <div>
                    <div class="text-sm opacity-90">Total Transaksi</div>
                    <div class="text-2xl font-bold">{{ number_format($jenisDonasiBreakdown->sum('jumlah_transaksi')) }}</div>
                </div>
                <div>
                    <div class="text-sm opacity-90">Total Dana</div>
                    <div class="text-2xl font-bold">Rp {{ number_format($totalDana, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Breakdown Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($jenisDonasiBreakdown as $index => $jenis)
                @php
                    $persentase = $totalDana > 0 ? ($jenis->total_nominal / $totalDana) * 100 : 0;
                    $colorClass = $colors[$index % count($colors)];
                    $iconEmoji = $icons[$index % count($icons)];
                        // ...existing code...
                @endphp
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
                    <!-- Header dengan warna -->
                    <div class="{{ $colorClass }} p-4" style="min-height: 80px;">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <span class="text-3xl flex-shrink-0">{{ $iconEmoji }}</span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-lg leading-tight text-gray-900 dark:text-white mb-1" style="word-break: break-word;">
                                        {{ $jenis->jenis_nama ?? 'NAMA TIDAK ADA' }}
                                    </h4>
                                    <p class="text-xs text-gray-800 dark:text-gray-200 opacity-80">
                                        {{ number_format($jenis->jumlah_transaksi) }} transaksi
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-3">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($persentase, 1) }}%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Body dengan detail -->
                    <div class="p-4 space-y-3">
                        <!-- Total Nominal -->
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-sm text-gray-600 dark:text-gray-400">💵 Total Nominal</span>
                            <span class="font-bold text-lg text-gray-900 dark:text-white">
                                Rp {{ number_format($jenis->total_nominal, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Jumlah Transaksi -->
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-sm text-gray-600 dark:text-gray-400">🔢 Jumlah Transaksi</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ number_format($jenis->jumlah_transaksi) }} kali
                            </span>
                        </div>

                        <!-- Rata-rata Donasi -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">📊 Rata-rata Donasi</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($jenis->rata_rata_nominal, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="pt-3">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="{{ $colorClass }} h-2 rounded-full transition-all duration-500" 
                                     style="width: {{ min($persentase, 100) }}%">
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
                                {{ number_format($persentase, 2) }}% dari total dana
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Footer Summary -->
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                <p class="mb-2">
                    <strong>Total {{ $jenisDonasiBreakdown->count() }} jenis donasi</strong> dengan 
                    <strong>{{ number_format($jenisDonasiBreakdown->sum('jumlah_transaksi')) }} transaksi</strong>
                </p>
                <p class="text-xs">
                    Periode: 
                    @switch($timePeriod)
                        @case('current_month')
                            Bulan Ini
                            @break
                        @case('last_month')
                            Bulan Lalu
                            @break
                        @case('current_year')
                            Tahun Ini
                            @break
                        @case('last_3_months')
                            3 Bulan Terakhir
                            @break
                        @case('last_6_months')
                            6 Bulan Terakhir
                            @break
                        @default
                            Semua Waktu
                    @endswitch
                </p>
            </div>
        </div>
    @endif
</div>
