<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\JenisDonasi;
use App\Models\MetodePembayaran;
use App\Models\Fundraiser;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use App\Models\BidangProgram;
use App\Models\Asnaf;
use App\Models\User;
use Carbon\Carbon;

class DonasiApril2025Seeder extends Seeder
{
    // Konstanta konfigurasi
    private $STRICT_DUPLICATION_CHECK = false; // Set false untuk memastikan semua donasi diimpor
    private $ALLOW_DUPLICATION = true; // Set true untuk mengizinkan duplikasi pada kasus tertentu
    private $FORCE_IMPORT_COMMON_AMOUNTS = true; // Set true untuk memaksa import nominal umum (round numbers)
    
    // Nominal umum yang sering berulang tapi valid
    private $COMMON_DONATION_AMOUNTS = [
        50000, 100000, 200000, 500000, 1000000, 2000000, 5000000
    ];
    
    /**
     * PENTING: Format Nama Donatur
     * 
     * Sistem ini menerapkan format nama donatur yang konsisten:
     * 1. Semua prefiks/sebutan (Bapak, Ibu, Pak, Bu, dll) DIHAPUS dari nama yang disimpan di database
     * 2. Website akan menampilkan prefiks yang sesuai secara otomatis berdasarkan gender yang tercatat
     * 3. Pola deteksi gender masih menggunakan prefiks dan pola nama, tetapi prefiks tidak disimpan
     * 4. Format ini memastikan konsistensi data dan tampilan yang lebih baik di website
     */

    private $importStats = [
        'total_processed' => 0,
        'donations_imported' => 0,
        'penyaluran_imported' => 0,
        'donors_created' => 0,
        'donors_updated' => 0,
        'errors' => 0,
        'warnings' => 0,
        'duplicates_allowed' => 0,
        'breakdown' => [
            'zakat' => 0,
            'infaq_terikat' => 0,
            'infaq_tidak_terikat' => 0,
            'dskl' => 0,
            'penyaluran' => 0
        ],
        'donor_types' => [
            'male' => 0,
            'female' => 0,
            'organization' => 0,
            'anonymous' => 0,
            'unknown' => 0
        ],
        'by_fundraiser' => [],
        'by_payment_method' => [],
        'total_amount' => 0,
        'total_penyaluran' => 0,
        'net_amount' => 0
    ];

    /**
     * Run the database seeds - OPTIMAL COMPLETE IMPORT APRIL 2025
     */
    public function run(): void
    {
        $this->command->info('🚀 OPTIMAL Import - Donasi April 2025');
        $this->command->info('📋 Features: Smart donor matching, Complete edge cases, Real-time stats');
        
        DB::beginTransaction();
        
        try {
            // Initialize all required master data
            $this->initializeMasterData();
            
            // Read and process CSV
            $csvPath = base_path('donasi_april - INPUT DATA (1).csv');
            if (!file_exists($csvPath)) {
                $this->command->warn("CSV file not found at standard path, trying alternative location...");
                // Try alternative locations
                $alternativePaths = [
                    base_path('donasi_april - INPUT DATA.csv'),
                    storage_path('app/donasi_april - INPUT DATA (1).csv'),
                    public_path('donasi_april - INPUT DATA (1).csv'),
                    base_path('database/seeders/data/donasi_april - INPUT DATA (1).csv')
                ];
                
                foreach ($alternativePaths as $path) {
                    if (file_exists($path)) {
                        $csvPath = $path;
                        $this->command->info("Found CSV at alternative location: {$path}");
                        break;
                    }
                }
                
                if (!file_exists($csvPath)) {
                    throw new \Exception("CSV file not found: {$csvPath}");
                }
            }
            
            $csvData = $this->readAndParseCSV($csvPath);
            
            // Validate that data is for April 2025
            $this->validateDataMonth($csvData);
            
            $this->processAllRows($csvData);
            
            DB::commit();
            
            $this->displayFinalSummary();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("💥 IMPORT FAILED: " . $e->getMessage());
            Log::emergency('Donasi April Import Failed', [
                'error' => $e->getMessage(),
                'stats' => $this->importStats
            ]);
            throw $e;
        }
    }

    /**
     * Initialize all required master data
     */
    private function initializeMasterData(): void
    {
        $this->command->info('📊 Initializing master data...');
        
        // Jenis Donasi - using existing ones only
        $this->command->info('📊 Using existing jenis donasi from database...');
        
        // Payment methods - use existing ones only
        $this->command->info('📱 Using existing payment methods from database...');
        
        // Fundraisers - April 2025 specific fundraisers
        $aprilFundraisers = [
            'ADI', 'HENDRA', 'SRI', 'KANTOR', 'MASYHUDA', 'JOKO', 'EKA', 'TANTI', 
            'MERI', 'ZULI', 'FUJI', 'MIRA', 'TIARA', 'DADANG', 'YUNITA', 'MIRDA'
        ];
        
        foreach ($aprilFundraisers as $fr) {
            Fundraiser::firstOrCreate(['nama_fundraiser' => $fr], [
                'aktif' => true
            ]);
        }
        
        // Penyaluran Master Data
        try {
            // Ensure "Penyaluran Langsung" exists in SumberDanaPenyaluran
            SumberDanaPenyaluran::firstOrCreate([
                'nama_sumber_dana' => 'Penyaluran Langsung'
            ], [
                'deskripsi' => 'Dana yang disalurkan langsung tanpa masuk ke kas',
                'aktif' => true
            ]);
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Penyaluran master data setup warning: " . $e->getMessage());
        }
        
        $this->command->info('✅ Master data initialized');
    }

    /**
     * Read and parse CSV with improved handling
     */
    private function readAndParseCSV(string $filePath): array
    {
        $this->command->info('📄 Reading CSV file...');
        
        $data = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open CSV file: {$filePath}");
        }
        
        $headers = null;
        $lineNumber = 0;
        
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            
            // Find header row - look for NO KWITANSI
            if ($headers === null && isset($row[0]) && 
                (strpos($row[0], 'NO KWITANSI') !== false || strpos($row[0], 'KWITANSI') !== false)) {
                $headers = array_map('trim', $row);
                continue;
            }
            
            // Skip until headers found
            if ($headers === null) continue;
            
            // Create associative array
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = isset($row[$index]) ? trim($row[$index]) : '';
            }
            
            $data[] = $rowData;
        }
        
        fclose($handle);
        
        $this->command->info("📊 CSV parsed: " . count($data) . " rows");
        return $data;
    }

    /**
     * Process all CSV rows with April validation
     */
    private function processAllRows(array $csvData): void
    {
        $this->command->info('🔄 Processing all rows...');
        
        // Set to track non-April dates
        $nonAprilDates = [];
        
        foreach ($csvData as $index => $row) {
            $this->importStats['total_processed']++;
            
            try {
                // Skip empty/invalid rows
                if ($this->shouldSkipRow($row)) {
                    continue;
                }
                
                // Check if date is from April 2025
                $dateString = $row['TANGGAL'] ?? '';
                if (!empty($dateString)) {
                    $date = $this->parseDate($dateString);
                    if ($date->format('Y-m') != '2025-04') {
                        // Track non-April dates for reporting
                        $monthYear = $date->format('Y-m');
                        if (!isset($nonAprilDates[$monthYear])) {
                            $nonAprilDates[$monthYear] = 0;
                        }
                        $nonAprilDates[$monthYear]++;
                        
                        // Continue processing anyway - we'll report later
                    }
                }
                
                // Determine row type and process accordingly
                if ($this->isPenyaluranRow($row)) {
                    $this->processPenyaluranRow($row);
                } elseif ($this->isDonationRow($row)) {
                    $this->processDonationRow($row);
                }
                
            } catch (\Exception $e) {
                $this->importStats['errors']++;
                $this->command->error("Error processing row " . ($index + 1) . ": " . $e->getMessage());
                Log::error('Row processing error', [
                    'row_index' => $index + 1,
                    'error' => $e->getMessage(),
                    'row_data' => $row
                ]);
            }
        }
        
        // Report on non-April dates if any were found
        if (!empty($nonAprilDates)) {
            $this->command->warn("⚠️ Found data from months other than April 2025:");
            foreach ($nonAprilDates as $monthYear => $count) {
                $this->command->warn("  - {$monthYear}: {$count} entries");
            }
        }
    }

    /**
     * Check if row should be skipped
     */
    private function shouldSkipRow(array $row): bool
    {
        // Empty rows
        if (empty(array_filter($row))) return true;
        
        // Get possible name fields
        $nama = $row['NAMA DONATUR'] ?? '';
        
        // Summary/header rows
        if (empty($nama) || 
            str_contains($nama, 'TOTAL DONASI') ||
            str_contains($nama, 'LAZ INSAN MADANI') ||
            str_contains($nama, 'APRIL 2025') ||
            preg_match('/^dona,*$/', $nama)) {
            return true;
        }
        
        // Get possible amount fields
        $jumlah = $row[' JUMLAH'] ?? $row['JUMLAH'] ?? '';
        
        // Only skip if both nama and jumlah are empty
        if (empty($nama) && empty($jumlah)) {
            return true;
        }
        
        return false;
    }

    /**
     * Is this a donation row?
     */
    private function isDonationRow(array $row): bool
    {
        $nama = $row['NAMA DONATUR'] ?? '';
        $jumlah = $row[' JUMLAH'] ?? $row['JUMLAH'] ?? '';
        
        // Skip if it's a penyaluran/refund entry (case insensitive)
        if (stripos($nama, 'penyaluran') !== false || stripos($nama, 'refund') !== false) {
            return false;
        }
        
        // Accept donations with or without kwitansi, as long as there's nama and jumlah
        return !empty($nama) && !empty($jumlah);
    }

    /**
     * Is this a penyaluran row?
     */
    private function isPenyaluranRow(array $row): bool
    {
        $nama = $row['NAMA DONATUR'] ?? '';
        $penyaluranAmount = $row[' PENYALURAN'] ?? $row['PENYALURAN'] ?? '';
        
        // Check if it has penyaluran amount (not empty, not dash)
        $hasPenyaluranAmount = !empty($penyaluranAmount) && 
                              $penyaluranAmount !== '-' && 
                              $penyaluranAmount !== '0';
        
        // Check if name indicates penyaluran/refund (case insensitive)
        $isPenyaluranName = stripos($nama, 'penyaluran') !== false ||
                           stripos($nama, 'refund') !== false;
        
        // Also check VIA column for "Penyaluran Langsung"
        $via = $row['VIA'] ?? '';
        $isPenyaluranVia = stripos($via, 'penyaluran langsung') !== false;
        
        $isPenyaluran = $hasPenyaluranAmount || $isPenyaluranName || $isPenyaluranVia;
        
        // Debug output for penyaluran detection
        if ($isPenyaluran) {
            $this->command->info("🔍 Penyaluran detected: {$nama} - Via: {$via} - Amount: {$penyaluranAmount}");
        }
        
        return $isPenyaluran;
    }

    /**
     * Process donation row with complete accuracy
     */
    private function processDonationRow(array $row): void
    {
        // Parse basic data
        $nomorKwitansi = $row['NO KWITANSI'] ?? '';
        $tanggal = $this->parseDate($row['TANGGAL'] ?? '');
        
        // Use flexible amount column names
        $jumlahColumn = $row[' JUMLAH'] ?? $row['JUMLAH'] ?? '';
        $jumlah = $this->parseAmount($jumlahColumn);
        
        // Skip if amount is 0 or invalid
        if ($jumlah <= 0) {
            $this->command->warn("⚠️ Skipping row with invalid amount: {$jumlahColumn}");
            return;
        }
        
        // Parse donor name first so we can use it for duplicate detection
        $donorName = $row['NAMA DONATUR'] ?? '';
        
        // Handle missing receipt number - generate one
        if (empty($nomorKwitansi)) {
            $dateStr = date('Ymd-His');
            $nomorKwitansi = 'APR-' . $dateStr . '-' . rand(1000, 9999);
            $this->command->info("Generated kwitansi: {$nomorKwitansi}");
        }
        
        // If not empty, add APR- prefix if it doesn't already have a prefix
        if (!empty($nomorKwitansi) && !preg_match('/^[A-Za-z]{2,4}-/', $nomorKwitansi)) {
            $nomorKwitansi = 'APR-' . $nomorKwitansi;
        }
        
        // Advanced anti-duplication system for recurring kwitansi numbers
        $originalKwitansi = $nomorKwitansi;
        $counter = 0;
        
        // Keep checking until we have a truly unique transaction number
        // This implements a reliable incrementing suffix strategy
        while (Donasi::where('nomor_transaksi_unik', $nomorKwitansi)->exists()) {
            $counter++;
            $nomorKwitansi = $originalKwitansi . '-' . $counter;
            if ($counter === 1) {
                $this->command->info("🔄 Adjusting kwitansi to avoid collision: {$nomorKwitansi}");
            }
            
            // Safety exit condition if we somehow can't find a unique ID after many tries
            if ($counter > 50) {
                // Last resort: use timestamp + random hash
                $dateStr = date('Ymd-His');
                $nomorKwitansi = 'APR-' . $dateStr . '-' . substr(md5(uniqid() . microtime(true)), 0, 8);
                $this->command->warn("⚠️ Using emergency unique ID generation: {$nomorKwitansi}");
                break;
            }
        }
        
        // Check for real duplicate with improved logic
        $isAprilPrefixed = strpos($nomorKwitansi, 'APR-') === 0;
        $isDuplicateCheck = false;
        $isCommonAmount = in_array((int)$jumlah, $this->COMMON_DONATION_AMOUNTS);
        
        // Skip duplikat check jika:
        // 1. ALLOW_DUPLICATION = true
        // 2. Kwitansi memiliki prefix APR-
        // 3. Nominal masuk kategori common amount dan FORCE_IMPORT_COMMON_AMOUNTS = true
        $shouldSkipDuplicationCheck = 
            $this->ALLOW_DUPLICATION || 
            $isAprilPrefixed || 
            ($isCommonAmount && $this->FORCE_IMPORT_COMMON_AMOUNTS);
            
        if (!$shouldSkipDuplicationCheck && !$this->STRICT_DUPLICATION_CHECK) {
            $possibleDuplicate = Donasi::whereMonth('tanggal_donasi', $tanggal->month)
                ->whereYear('tanggal_donasi', $tanggal->year)
                ->where('jumlah', $jumlah)
                ->where(function($query) use ($donorName) {
                    if ($this->isHambaAllah($donorName)) {
                        $query->where('atas_nama_hamba_allah', true);
                    } else {
                        $query->whereHas('donatur', function($q) use ($donorName) {
                            $q->where('nama', 'like', '%' . $donorName . '%');
                        });
                    }
                })
                ->first();
                
            if ($possibleDuplicate && $possibleDuplicate->tanggal_donasi) {
                // Only treat as duplicate if STRICT criteria is met
                $sameDayAndNumber = 
                    $possibleDuplicate->tanggal_donasi instanceof \Carbon\Carbon && 
                    $possibleDuplicate->tanggal_donasi->format('Y-m-d') === $tanggal->format('Y-m-d') &&
                    $possibleDuplicate->nomor_transaksi_unik === $nomorKwitansi;
                    
                if ($sameDayAndNumber) {
                    $this->command->warn("⚠️ True duplicate found: {$donorName} - {$jumlah} - {$tanggal->format('Y-m-d')} - {$nomorKwitansi}");
                    $this->importStats['warnings']++;
                    $isDuplicateCheck = true;
                } else {
                    // Kemungkinan donasi berbeda di hari yang sama - import saja
                    $this->command->info("🔄 Similar donation on different date - importing as unique: {$donorName} - {$jumlah}");
                    $this->importStats['duplicates_allowed']++;
                }
            }
        }
        
        // Skip jika benar-benar duplikat dan kita tidak memaksa import semua data
        if ($isDuplicateCheck && !$this->ALLOW_DUPLICATION) {
            return;
        }
        
        // Map donation type to existing ones in database
        $donationType = $row['JENIS DONASI'] ?? '';
        $jenisDonasi = $this->getJenisDonasi($donationType);
        if (!$jenisDonasi) {
            $this->command->warn("⚠️ Unknown donation type: '{$donationType}'");
            $this->importStats['errors']++;
            return;
        }
        
        // Map payment method to existing ones in database
        $paymentMethod = $row['VIA'] ?? '';
        $metodePembayaran = $this->getMetodePembayaran($paymentMethod);
        if (!$metodePembayaran) {
            $this->command->warn("⚠️ Unknown payment method: '{$paymentMethod}'");
            $this->importStats['errors']++;
            return;
        }
        
        // Handle donor
        $donatur = $this->getOrCreateDonatur($row);
        
        // Handle fundraiser
        $fundraiser = null;
        $fundraiserName = $row['FR'] ?? '';
        if (!empty($fundraiserName) && $fundraiserName !== '-') {
            $fundraiser = $this->getFundraiser($fundraiserName);
            $this->updateFundraiserStats($fundraiserName);
        }
        
        // Special cases
        $isBarang = strpos(strtolower($donationType), 'barang') !== false || 
                   strpos(strtolower($donationType), 'logistik') !== false;
        $donorName = $row['NAMA DONATUR'] ?? '';
        $isHambaAllah = $this->isHambaAllah($donorName);
        
        $keterangan = $row['KETERANGAN'] ?? '';
        $catatan = $row['CATATAN'] ?? '';
        
        // Create donation record
        try {
            $donasi = Donasi::create([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenisDonasi->id,
                'metode_pembayaran_id' => $metodePembayaran->id,
                'fundraiser_id' => $fundraiser ? $fundraiser->id : null,
                'jumlah' => $isBarang ? 0 : $jumlah,
                'perkiraan_nilai_barang' => $isBarang ? $jumlah : 0,
                'deskripsi_barang' => $isBarang ? $this->getBarangDescription($row) : null,
                'keterangan_infak_khusus' => $keterangan ?: null,
                'catatan_donatur' => $catatan ?: null,
                'tanggal_donasi' => $tanggal,
                'nomor_transaksi_unik' => $nomorKwitansi,
                'atas_nama_hamba_allah' => $isHambaAllah,
                'status_konfirmasi' => 'verified',
                'dikonfirmasi_pada' => now(),
                'dicatat_oleh_user_id' => 1,
                'dikofirmasi_oleh_user_id' => 1,
            ]);
            
            // Update statistics
            $this->updateDonationStats($jenisDonasi, $metodePembayaran, $jumlah);
            
            $this->importStats['donations_imported']++;
            
        } catch (\Exception $e) {
            $this->importStats['errors']++;
            $this->command->error("💥 Failed to create donation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process penyaluran row
     */
    private function processPenyaluranRow(array $row): void
    {
        try {
            $this->command->info("🎯 Processing penyaluran row...");
            
            $nama = $row['NAMA DONATUR'] ?? '';
            $jumlahDonasi = $this->parseAmount($row[' JUMLAH'] ?? $row['JUMLAH'] ?? '');
            $jumlahPenyaluran = $this->parseAmount($row[' PENYALURAN'] ?? $row['PENYALURAN'] ?? '');
            $tanggal = $this->parseDate($row['TANGGAL'] ?? '');
            
            // Use penyaluran amount if available, otherwise use donation amount
            $amount = $jumlahPenyaluran > 0 ? $jumlahPenyaluran : $jumlahDonasi;
            
            if ($amount <= 0) {
                $this->command->warn("⚠️ Skipping penyaluran with invalid amount");
                return;
            }
            
            // Get "Penyaluran Langsung" as sumber dana
            $sumberDana = SumberDanaPenyaluran::where('nama_sumber_dana', 'Penyaluran Langsung')->first();
            if (!$sumberDana) {
                throw new \Exception("Sumber Dana 'Penyaluran Langsung' not found");
            }
            
            // Create penyaluran record
            $programPenyaluran = ProgramPenyaluran::create([
                'nama_program' => "Penyaluran Langsung - {$nama}",
                'keterangan' => "Penyaluran langsung untuk {$nama}",
                'jumlah_penerima_manfaat' => 1,
                'jumlah_dana' => $amount,
                'tanggal_penyaluran' => $tanggal,
                'sumber_dana_penyaluran_id' => $sumberDana->id,
                'dicatat_oleh_id' => 1,
            ]);
            
            $this->importStats['penyaluran_imported']++;
            $this->importStats['total_penyaluran'] += $amount;
            $this->importStats['breakdown']['penyaluran']++;
            
            $this->command->info("✅ Penyaluran created: {$nama} - Rp " . number_format($amount));
            
        } catch (\Exception $e) {
            $this->importStats['errors']++;
            $this->command->error("💥 Failed to create penyaluran: " . $e->getMessage());
        }
    }

    // Include all helper methods from Maret seeder
    private function getOrCreateDonatur(array $row): Donatur
    {
        $namaDonatur = trim($row['NAMA DONATUR'] ?? '');
        $nomorHp = $this->cleanPhoneNumber($row['NO HP'] ?? '');
        $alamat = trim($row['ALAMAT'] ?? '');
        
        // Debug: Menampilkan contoh perubahan nama jika memiliki prefiks
        if (preg_match('/^(bapak|ibu|pak|bu|dr|prof|h\.|hj\.)\s/i', $namaDonatur)) {
            $beforeCleaning = $namaDonatur;
            $afterCleaning = $this->cleanDonorName($namaDonatur);
            if ($beforeCleaning !== $afterCleaning) {
                $this->command->info("🧹 Cleaned donor name: '{$beforeCleaning}' -> '{$afterCleaning}'");
            }
        }
        
        $donorInfo = $this->analyzeDonorName($namaDonatur);
        
        // Pastikan gender valid untuk enum database (male, female, organization)
        if (!in_array($donorInfo['gender'], ['male', 'female', 'organization'])) {
            $donorInfo['gender'] = 'male'; // Default fallback
        }
        
        // Track donor types for statistics
        if ($donorInfo['is_anonymous']) {
            $this->importStats['donor_types']['anonymous']++;
        } elseif ($donorInfo['gender'] === 'organization') {
            $this->importStats['donor_types']['organization']++;
        } elseif ($donorInfo['gender'] === 'male') {
            $this->importStats['donor_types']['male']++;
        } elseif ($donorInfo['gender'] === 'female') {
            $this->importStats['donor_types']['female']++;
        }
        
        // Try to find existing donor
        $existingDonatur = null;
        
        if ($nomorHp) {
            $existingDonatur = Donatur::where('nomor_hp', $nomorHp)->first();
        }
        
        if (!$existingDonatur && !$donorInfo['is_anonymous']) {
            $existingDonatur = Donatur::where('nama', $donorInfo['clean_name'])->first();
        }
        
        if ($existingDonatur) {
            $this->importStats['donors_updated']++;
            
            if (!$existingDonatur->nomor_hp && $nomorHp) {
                $existingDonatur->update(['nomor_hp' => $nomorHp]);
            }
            
            return $existingDonatur;
        }
        
        // Create new donor
        $newDonatur = Donatur::create([
            'nama' => $donorInfo['clean_name'],
            'gender' => $donorInfo['gender'],
            'nomor_hp' => $nomorHp,
            'alamat_detail' => $alamat,
            'alamat_lengkap' => $alamat,
        ]);
        
        $this->importStats['donors_created']++;
        return $newDonatur;
    }

    private function analyzeDonorName(string $nama): array
    {
        // Basic initialization
        $originalName = $nama;
        $cleanName = trim($nama);
        $gender = 'male'; // Default to male (sistem tidak menerima 'unknown')
        $isAnonymous = false;
        $donorType = 'individual'; // Default type: individual or organization
        
        // Skip processing for empty names
        if (empty($cleanName) || $cleanName === '-') {
            $isAnonymous = true;
            $cleanName = 'Donatur Anonim';
            $gender = 'male'; // Harus menggunakan 'male' karena 'unknown' tidak diizinkan dalam enum
            
            return [
                'clean_name' => $cleanName,
                'gender' => $gender,
                'is_anonymous' => $isAnonymous,
                'donor_type' => $donorType
            ];
        }
        
        // Simpan nama asli untuk pendeteksian gender dan tipe
        $originalNameForDetection = $cleanName;
        
        // 1. DETECT ORGANIZATIONS - Check this first to prevent false detections
        // Organization patterns and keywords with word boundaries
        $organizationPatterns = [
            // Common business entities
            '/\b(pt|cv|ud|tb|fa|nv|koperasi|puskop)\b\.?\s/i',
            '/\b(persero|tbk|firma|perseroda)\b/i',
            
            // Institutions
            '/\b(yayasan|lembaga|rumah sakit|rs|rsud|rsup|rsu|rsia|klinik|group|griya|balai)\b/i',
            '/\b(sekolah|sd|smp|sma|smk|stm|akademi|institut|universitas|univ|fakultas|politeknik)\b/i',
            '/\b(masjid|mushola|musholla|langgar|ponpes|pondok|pesantren|panti|tpa|tpq)\b/i',
            '/\b(majelis|majlis|taklim)\b/i',
            
            // Government/formal organizations
            '/\b(dinas|komite|organisasi|dewan|badan|balai|kantor|pusat|cabang|tim)\b/i',
            
            // Commercial establishments
            '/\b(toko|warung|kios|kedai|depot|resto|restoran|rumah makan|hotel|salon|bengkel|apotik|apotek)\b/i',
            '/\b(bank|bmt|koperasi|leasing|asuransi|agency|agen)\b/i',
            
            // Educational terminology
            '/\b(siswa|alumni|santri|kelas|jurusan|angkatan)\b/i',
            
            // Groups and collections
            '/\b(keluarga besar|komunitas|paguyuban|ikatan|himpunan|asosiasi|forum|relawan)\b/i',
            '/\b(karyawan|pegawai|team|staff|staf|jemaah|pengajian|majelis|panitia)\b/i'
        ];
        
        foreach ($organizationPatterns as $pattern) {
            if (preg_match($pattern, $cleanName)) {
                $donorType = 'organization';
                $gender = 'organization';
                break;
            }
        }
        
        // Check specific organization keywords (exact matches or standalone words)
        $organizationKeywords = [
            'Komunitas', 'Wong Solo', 'Sambal Lalap', 'Temphoyak', 'BSI', 'BRI', 'BCA', 'BMT',
            'KSPPS', 'LAZISMU', 'NU', 'Muhammadiyah', 'Aisyiyah', 'Pemkot', 'Pemkab', 'Pemrov',
            'RSUD', 'RSUP'
        ];
        
        if ($donorType !== 'organization') {
            foreach ($organizationKeywords as $keyword) {
                // Only match if it's a standalone word, not part of another word
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $cleanName)) {
                    $donorType = 'organization';
                    $gender = 'organization';
                    break;
                }
            }
        }
        
        // 2. DETECT ANONYMOUS DONORS - After organization check
        if ($donorType !== 'organization') {
            $anonymousPatterns = [
                'hamba allah', 'tanpa nama', 'anonim', 'anonymous', 'tidak dikenal',
                '\bnn\b', '\bn\.n\b', '\bn\/a\b', 'no name', 'tidak disebutkan',
                'seseorang', 'unknown'
            ];
            
            foreach ($anonymousPatterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $cleanName)) {
                    $isAnonymous = true;
                    $cleanName = 'Donatur Anonim';
                    $gender = 'male'; // Menggunakan 'male' sebagai default untuk anonymous donor
                    break;
                }
            }
            
            // Return early if anonymous
            if ($isAnonymous) {
                // Pastikan gender adalah salah satu dari enum yang valid ('male', 'female', 'organization')
                $gender = 'male'; // Fallback ke male untuk anonymous donors karena unknown tidak diterima
                
                return [
                    'clean_name' => $cleanName,
                    'gender' => $gender,
                    'is_anonymous' => $isAnonymous,
                    'donor_type' => $donorType
                ];
            }
        }
        
        // 3. ANALYZE INDIVIDUAL GENDER AND TITLES
        // Only process if not already identified as organization
        if ($donorType === 'individual') {
            // Common titles with gender associations
            $titlePatterns = [
                // Female specific
                'ibu\s' => 'female',
                'bu\s' => 'female',
                'sdr\.i' => 'female',
                'sdri\s' => 'female',
                'sdri\.' => 'female',
                'nyonya' => 'female',
                'ny\s' => 'female',
                'ny\.' => 'female',
                'nona\s' => 'female',
                'mbak\s' => 'female',
                'umi\s' => 'female',
                'hj\.' => 'female',
                'hjh\.' => 'female',
                'hajjah' => 'female',
                
                // Male specific
                'bapak\s' => 'male',
                'pak\s' => 'male',
                'sdr\.' => 'male',
                'saudara\s' => 'male',
                'tuan\s' => 'male',
                'mas\s' => 'male',
                'abi\s' => 'male',
                'h\.' => 'male',
                'haji\s' => 'male',
                'kyai\s' => 'male',
                'ustadz\s' => 'male',
                'ust\.' => 'male',
                'ajengan\s' => 'male',
                'gus\s' => 'male',
                
                // Professional titles - male default but needs further checking
                'prof\.' => 'male',
                'prof\s' => 'male',
                'dr\.' => 'male',
                'dr\s' => 'male',
                'drg\.' => 'male',
                'drg\s' => 'male',
                'ir\.' => 'male',
                'drs\.' => 'male',
                
                // Female professional titles
                'dra\.' => 'female',
                
                // Religious titles
                'kh\.' => 'male',
                'kiai\s' => 'male',
                
                // Military/police 
                'jenderal\s' => 'male',
                'letnan\s' => 'male',
                'kapten\s' => 'male',
                'sersan\s' => 'male',
                'kolonel\s' => 'male',
                'brigadir\s' => 'male',
                'ipda\s' => 'male',
                'iptu\s' => 'male',
                'aiptu\s' => 'male',
                'bripka\s' => 'male',
                
                // Neutral atau ambiguous (gunakan male sebagai default untuk menghindari error)
                'alm\.' => 'male',
                'almh\.' => 'female',
                'ananda\s' => 'male',
                'adik\s' => 'male',
                'kakak\s' => 'male',
                'sahabat\s' => 'male',
                'donatur\s' => 'male'
            ];
            
            // Check for title pattern at the start of the name
            // Gunakan nama asli untuk deteksi prefiks dan gender
            // tapi JANGAN simpan prefiks dalam hasil akhir
            $titleFound = false;
            foreach ($titlePatterns as $title => $genderValue) {
                if (preg_match('/^' . $title . '/i', $originalNameForDetection)) {
                    $gender = $genderValue;
                    $titleFound = true;
                    break;
                }
            }
            
            // Special cases for female names
            if (!$titleFound) {
                // Common Indonesian female name prefixes
                if (preg_match('/^siti\s/i', $originalNameForDetection)) {
                    $gender = 'female';
                    // Keep "Siti" as part of the name
                }
                
                // Check if it's likely a female doctor (when Dr. appears before female name)
                if (preg_match('/^dr\.\s+\w+\s+(kartika|sari|wulan|ningsih|wati|ani|ina|dewi|ratna)/i', $originalNameForDetection)) {
                    $gender = 'female';
                }
                
                // Check for obvious female names regardless of title
                if (preg_match('/\b(kartika|dewi|ratna|sinta|endang|wulan|indah|retno)\b/i', $originalNameForDetection)) {
                    $gender = 'female';
                }
            }
            
            // Gender detection for names without titles
            if ($gender === 'unknown') {
                // Common female name suffixes in Indonesian
                $femaleNameEndings = [
                    'wati$', 'ningsih$', 'yanti$', 'astuti$',
                    'atin$', 'atun$', 'atik$', 'iyah$', 'iah$', 'ani$',
                    'eni$', 'ina$', 'ita$', 'ida$', 'lia$', 'nia$',
                    'sari$', 'sih$', 'rina$', 'tari$', 'asih$', 'desi$',
                    'wulan', 'indah'
                ];
                
                foreach ($femaleNameEndings as $ending) {
                    if (preg_match('/' . $ending . '/i', $originalNameForDetection)) {
                        $gender = 'female';
                        break;
                    }
                }
                
                // If still unknown after suffix check, default to male
                if ($gender === 'unknown' || !in_array($gender, ['male', 'female', 'organization'])) {
                    $gender = 'male'; // Default fallback untuk memastikan nilai gender valid
                }
            }
        }
        
        // 4. FORMAT NAME PROPERLY for clean output
        
        // Bersihkan nama dari semua prefiks menggunakan fungsi cleanDonorName
        $cleanName = $this->cleanDonorName($cleanName);
        
        // Only for individuals - preserve original capitalization for organizations
        if ($donorType === 'individual') {
            // Simple title case fallback instead of using formatNameProper
            $cleanName = ucwords(strtolower($cleanName));
        }
        
        // Final validation - memastikan gender selalu valid sesuai dengan enum yang didukung
        if (!in_array($gender, ['male', 'female', 'organization'])) {
            $gender = 'male'; // Default fallback jika gender tidak valid
        }
        
        return [
            'clean_name' => $cleanName,
            'gender' => $gender,
            'is_anonymous' => $isAnonymous,
            'donor_type' => $donorType
        ];
    }

    /**
     * Check if donation is atas nama hamba allah
     */
    private function isHambaAllah(string $nama): bool
    {
        return str_contains(strtolower($nama), 'hamba allah') || 
               stripos($nama, 'anonim') !== false || 
               $nama === 'NN' || 
               $nama === 'N.N' || 
               $nama === '-';
    }

    /**
     * Get barang description
     */
    private function getBarangDescription(array $row): string
    {
        $keterangan = $row['KETERANGAN'] ?? '';
        $catatan = $row['CATATAN'] ?? '';
        
        if (str_contains($keterangan, 'SEMBAKO')) {
            return $catatan ?: 'Sembako';
        } elseif (str_contains($keterangan, 'NASI') || str_contains($keterangan, 'MAKANAN')) {
            return $catatan ?: 'Makanan/Nasi';
        } elseif (str_contains($keterangan, 'KUE')) {
            return $catatan ?: 'Kue';
        }
        
        return $catatan ?: 'Barang donasi';
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(string $dateString): Carbon
    {
        $dateString = trim($dateString);
        
        try {
            // Try parsing directly first
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            // Fallback to specific format if direct parsing fails
            try {
                return Carbon::createFromFormat('d/m/Y', $dateString);
            } catch (\Exception $e2) {
                // Final fallback to current date
                $this->command->warn("⚠️ Could not parse date '{$dateString}', using current date");
                return Carbon::now();
            }
        }
    }

    /**
     * Parse amount from currency string
     */
    private function parseAmount(string $amountString): float
    {
        if (empty($amountString) || $amountString === '-') {
            return 0;
        }
        
        // Simpan nilai asli untuk debugging dan kasus khusus
        $original = $amountString;
        
        // Handle scientific notation
        if (strpos($amountString, 'E') !== false || strpos($amountString, 'e') !== false) {
            return (float) $amountString;
        }
        
        // Remove Rp, spaces, but keep digits, dots and commas for decimal handling
        $cleaned = str_replace(['Rp', 'Rp.', 'RP', 'rp', ' '], ['', '', '', '', ''], trim($amountString));
        
        // Format Indonesia sering menggunakan titik sebagai pemisah ribuan dan koma untuk desimal
        // 1. Format dengan desimal koma: 1.000.000,50 -> 1000000.50
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $cleaned)) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        }
        // 2. Format dengan desimal titik: 1,000,000.50 -> 1000000.50
        elseif (preg_match('/^\d{1,3}(,\d{3})+(\.?\d+)?$/', $cleaned)) {
            $cleaned = str_replace(',', '', $cleaned);
        }
        // 3. Format tanpa desimal: 1000000
        elseif (preg_match('/^\d+$/', $cleaned)) {
            // No change needed
        }
        // 4. Format dengan koma sebagai desimal: 1000,50 -> 1000.50
        elseif (preg_match('/^\d+,\d+$/', $cleaned)) {
            $cleaned = str_replace(',', '.', $cleaned);
        }
        // 5. Fallback untuk menghapus semua karakter non-numerik kecuali titik desimal
        else {
            // Hapus semua koma dulu
            $cleaned = str_replace(',', '', $cleaned);
            // Kemudian hapus semua karakter non-numerik kecuali titik
            $cleaned = preg_replace('/[^0-9.]/', '', $cleaned);
        }
        
        if (empty($cleaned) || !is_numeric($cleaned)) {
            $this->command->warn("⚠️ Format jumlah tidak valid: '{$original}' -> '{$cleaned}', mengembalikan 0");
            return 0;
        }
        
        $result = (float) $cleaned;
        
        // Safety check - jika hasil parsing sangat berbeda dari ekspektasi
        if ($result < 1000 && preg_match('/1\s*jt|1\s*juta|1\s*million/i', $original)) {
            $result = 1000000;
            $this->command->info("🔄 Format khusus terdeteksi, mengoreksi: '{$original}' -> {$result}");
        }
        
        return $result;
    }

    /**
     * Get or create jenis donasi
     */
    private function getJenisDonasi(string $jenisString): ?JenisDonasi
    {
        // Exact match first
        $jenis = JenisDonasi::where('nama', $jenisString)->first();
        if ($jenis) {
            return $jenis;
        }
        
        // Mapping for variations - April specific
        $jenisMap = [
            'Infaq Umum' => 'Infaq Tidak Terikat',
            'Zakat Maal' => 'Zakat',
            'Zakat Profesi' => 'Zakat',
            'DSKL (Dana Sosial Keagamaan Lainnya)' => 'DSKL'
        ];
        
        $mappedName = $jenisMap[$jenisString] ?? null;
        if ($mappedName) {
            return JenisDonasi::where('nama', $mappedName)->first();
        }
        
        // Fallback - try partial matching
        if (stripos($jenisString, 'zakat') !== false) {
            return JenisDonasi::where('nama', 'like', '%Zakat%')->first();
        }
        
        if (stripos($jenisString, 'infaq') !== false) {
            if (stripos($jenisString, 'terikat') !== false) {
                return JenisDonasi::where('nama', 'Infaq Terikat')->first();
            } else {
                return JenisDonasi::where('nama', 'Infaq Tidak Terikat')->first();
            }
        }
        
        if (stripos($jenisString, 'dskl') !== false) {
            return JenisDonasi::where('nama', 'DSKL')->first();
        }
        
        // Final fallback to Infaq Tidak Terikat
        return JenisDonasi::where('nama', 'Infaq Tidak Terikat')->first() ?? JenisDonasi::first();
    }

    /**
     * Get or create metode pembayaran
     */
    private function getMetodePembayaran(string $viaString): ?MetodePembayaran
    {
        // Direct match with existing payment methods
        $payment = MetodePembayaran::where('nama', $viaString)->first();
        if ($payment) {
            return $payment;
        }
        
        // Handle specific mappings for April data
        $paymentMap = [
            'TF BSI 1809' => 'TF BSI 1809', // Create if doesn't exist
            'TF BSI 1972' => 'TF BSI 1972', // Create if doesn't exist
            'Barang' => 'Donasi Logistik/Barang',
            'Penyaluran Langsung' => 'Penyaluran'
        ];
        
        if (isset($paymentMap[$viaString])) {
            $mappedName = $paymentMap[$viaString];
            $method = MetodePembayaran::where('nama', $mappedName)->first();
            
            // If mapped method doesn't exist, create it (for new bank transfer methods)
            if (!$method && in_array($viaString, ['TF BSI 1809', 'TF BSI 1972'])) {
                $method = MetodePembayaran::create([
                    'nama' => $viaString,
                    'aktif' => true
                ]);
                $this->command->info("✅ Created new payment method: {$viaString}");
            }
            
            return $method;
        }
        
        // Try partial matching for bank transfers
        if (stripos($viaString, 'TF') !== false || stripos($viaString, 'Transfer') !== false) {
            // For TF methods, create new specific method
            return MetodePembayaran::firstOrCreate([
                'nama' => $viaString
            ], [
                'aktif' => true
            ]);
        }
        
        if (stripos($viaString, 'barang') !== false || stripos($viaString, 'logistik') !== false) {
            return MetodePembayaran::where('nama', 'Donasi Logistik/Barang')->first();
        }
        
        // Final fallback to Tunai
        return MetodePembayaran::where('nama', 'Tunai')->first() ?? MetodePembayaran::first();
    }

    /**
     * Get fundraiser
     */
    private function getFundraiser(string $frName): ?Fundraiser
    {
        return Fundraiser::where('nama_fundraiser', trim($frName))->first();
    }

    /**
     * Clean phone number
     */
    private function cleanPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^\d]/', '', $phone);
        
        if (empty($phone)) {
            return '';
        }
        
        // Normalize to format starting with 8 (without country code)
        if (str_starts_with($phone, '62')) {
            return substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            return substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            return $phone;
        }
        
        return $phone;
    }

    /**
     * Update donation statistics
     */
    private function updateDonationStats(JenisDonasi $jenis, MetodePembayaran $metode, float $jumlah): void
    {
        $this->importStats['total_amount'] += $jumlah;
        
        // By donation type
        if (str_contains($jenis->nama, 'Zakat')) {
            $this->importStats['breakdown']['zakat']++;
        } elseif (str_contains($jenis->nama, 'Infaq Terikat')) {
            $this->importStats['breakdown']['infaq_terikat']++;
        } elseif (str_contains($jenis->nama, 'Infaq Tidak Terikat')) {
            $this->importStats['breakdown']['infaq_tidak_terikat']++;
        } elseif (str_contains($jenis->nama, 'DSKL')) {
            $this->importStats['breakdown']['dskl']++;
        }
        
        // By payment method
        if (!isset($this->importStats['by_payment_method'][$metode->nama])) {
            $this->importStats['by_payment_method'][$metode->nama] = 0;
        }
        $this->importStats['by_payment_method'][$metode->nama]++;
    }

    /**
     * Update fundraiser statistics
     */
    private function updateFundraiserStats(string $frName): void
    {
        if (!isset($this->importStats['by_fundraiser'][$frName])) {
            $this->importStats['by_fundraiser'][$frName] = 0;
        }
        $this->importStats['by_fundraiser'][$frName]++;
    }

    /**
     * Display comprehensive final summary
     */
    private function displayFinalSummary(): void
    {
        $this->command->info('');
        $this->command->info('🎉 ===== IMPORT APRIL 2025 COMPLETED SUCCESSFULLY ===== 🎉');
        $this->command->info('');
        
        // Calculate net amount (donations - penyaluran)
        $this->importStats['net_amount'] = $this->importStats['total_amount'] - $this->importStats['total_penyaluran'];
        
        // Overall stats
        $this->command->info("📊 OVERALL STATISTICS:");
        $this->command->info("   ├─ Total rows processed: " . number_format($this->importStats['total_processed']));
        $this->command->info("   ├─ Donations imported: " . number_format($this->importStats['donations_imported']));
        $this->command->info("   ├─ Penyaluran imported: " . number_format($this->importStats['penyaluran_imported']));
        $this->command->info("   ├─ New donors created: " . number_format($this->importStats['donors_created']));
        $this->command->info("   ├─ Existing donors updated: " . number_format($this->importStats['donors_updated']));
        $this->command->info("   ├─ Total donations: Rp " . number_format($this->importStats['total_amount']));
        $this->command->info("   ├─ Total penyaluran: Rp " . number_format($this->importStats['total_penyaluran']));
        $this->command->info("   ├─ Net amount: Rp " . number_format($this->importStats['net_amount']));
        $this->command->info("   ├─ Expected total donasi: Rp 75,571,279");
        $this->command->info("   ├─ Expected penyaluran: Rp 17,629,000");
        $this->command->info("   ├─ Expected net: Rp 57,942,279");
        
        // Accuracy check
        $expectedTotal = 75571279; // Total donasi target
        $expectedPenyaluran = 17629000; // Total penyaluran target
        $expectedNet = 57942279; // Net target
        
        // Toleransi akurasi - menggunakan 5% toleransi untuk akurasi hampir cocok
        $toleranceAmount = $expectedTotal * 0.01; // 1% toleransi
        $difDonasi = abs($this->importStats['total_amount'] - $expectedTotal);
        $accuracyPercentDonasi = 100 - ($difDonasi / $expectedTotal * 100);
        
        // Cek akurasi donasi dengan toleransi
        if ($difDonasi < 1000) {
            $this->command->info("   ├─ Donation accuracy: ✅ PERFECT MATCH! (100%)");
        } elseif ($difDonasi <= $toleranceAmount) { 
            $this->command->info("   ├─ Donation accuracy: ✅ CLOSE MATCH! (" . number_format($accuracyPercentDonasi, 2) . "%) - Difference: Rp " . 
                number_format($difDonasi));
        } else {
            $this->command->warn("   ├─ Donation accuracy: ⚠️ Difference: Rp " . 
                number_format($difDonasi) . " (" . number_format($accuracyPercentDonasi, 2) . "%)");
        }
        
        // Cek akurasi penyaluran
        $difPenyaluran = abs($this->importStats['total_penyaluran'] - $expectedPenyaluran);
        $accuracyPercentPenyaluran = 100;
        if ($expectedPenyaluran > 0) {
            $accuracyPercentPenyaluran = 100 - ($difPenyaluran / $expectedPenyaluran * 100);
        }
        
        if ($difPenyaluran < 1000) {
            $this->command->info("   ├─ Penyaluran accuracy: ✅ PERFECT MATCH! (100%)");
        } else {
            $this->command->warn("   ├─ Penyaluran accuracy: ⚠️ Difference: Rp " . 
                number_format($difPenyaluran) . " (" . number_format($accuracyPercentPenyaluran, 2) . "%)");
        }
        
        $this->command->info("   ├─ Errors: " . $this->importStats['errors']);
        $this->command->info("   ├─ Warnings: " . $this->importStats['warnings']);
        if (isset($this->importStats['duplicates_allowed']) && $this->importStats['duplicates_allowed'] > 0) {
            $this->command->info("   ├─ Duplicates allowed (potential): " . $this->importStats['duplicates_allowed']);
        }
        
        // Donor types breakdown
        $this->command->info('');
        $this->command->info("👤 DONOR TYPES BREAKDOWN:");
        foreach ($this->importStats['donor_types'] as $type => $count) {
            if ($count > 0) {
                $this->command->info("   ├─ " . ucfirst($type) . ": {$count}");
            }
        }
        
        // Breakdown by donation type
        $this->command->info('');
        $this->command->info("💰 BREAKDOWN BY DONATION TYPE:");
        foreach ($this->importStats['breakdown'] as $type => $count) {
            if ($count > 0) {
                $this->command->info("   ├─ " . ucfirst(str_replace('_', ' ', $type)) . ": {$count}");
            }
        }
        
        // Top fundraisers
        if (!empty($this->importStats['by_fundraiser'])) {
            $this->command->info('');
            $this->command->info("🎯 TOP FUNDRAISERS:");
            arsort($this->importStats['by_fundraiser']);
            foreach (array_slice($this->importStats['by_fundraiser'], 0, 10, true) as $fr => $count) {
                $this->command->info("   ├─ {$fr}: {$count} donations");
            }
        }
        
        // Payment methods
        if (!empty($this->importStats['by_payment_method'])) {
            $this->command->info('');
            $this->command->info("💳 PAYMENT METHODS:");
            arsort($this->importStats['by_payment_method']);
            foreach ($this->importStats['by_payment_method'] as $method => $count) {
                $this->command->info("   ├─ {$method}: {$count} transactions");
            }
        }
        
        $this->command->info('');
        $this->command->info('✅ All April 2025 data has been imported successfully!');
        $this->command->info('📋 Ready for verification in the admin panel.');
        $this->command->info('');
    }
    
    /**
     * Validate that data in CSV is for April 2025
     */
    private function validateDataMonth(array $csvData): void
    {
        $this->command->info('🔄 Validating data month...');
        
        $monthCounts = [];
        $totalRows = 0;
        $aprilRows = 0;
        
        foreach ($csvData as $row) {
            if ($this->shouldSkipRow($row)) continue;
            
            $dateString = $row['TANGGAL'] ?? '';
            if (empty($dateString)) continue;
            
            try {
                $date = $this->parseDate($dateString);
                $month = $date->format('m');
                $year = $date->format('Y');
                
                // Count occurrences of each month
                $monthKey = $year . '-' . $month;
                if (!isset($monthCounts[$monthKey])) {
                    $monthCounts[$monthKey] = 0;
                }
                $monthCounts[$monthKey]++;
                $totalRows++;
                
                // Count April 2025 rows specifically
                if ($month == '04' && $year == '2025') {
                    $aprilRows++;
                }
                
            } catch (\Exception $e) {
                // Skip unparseable dates
                continue;
            }
        }
        
        // Display month distribution
        $this->command->info('📊 Data month distribution:');
        ksort($monthCounts);
        foreach ($monthCounts as $month => $count) {
            $percentage = $totalRows > 0 ? round(($count / $totalRows) * 100, 1) : 0;
            $this->command->info("  - {$month}: {$count} rows ({$percentage}%)");
        }
        
        // Warning if April 2025 data is less than 80% of total
        $aprilPercentage = $totalRows > 0 ? round(($aprilRows / $totalRows) * 100, 1) : 0;
        if ($aprilPercentage < 80) {
            $this->command->warn("⚠️ Warning: Only {$aprilPercentage}% of data is from April 2025!");
            $this->command->warn("⚠️ This seeder is designed for April 2025 data.");
            
            // Ask for confirmation to continue
            if (!$this->command->confirm('Continue with data import anyway?', true)) {
                throw new \Exception("Import canceled due to mismatched data period");
            }
        } else {
            $this->command->info("✅ Data validation passed: {$aprilPercentage}% of data is from April 2025");
        }
    }
    
    /**
     * Membersihkan nama donatur dari prefiks yang tidak perlu
     * seperti Bapak, Ibu, Pak, dll.
     */
    /**
     * Membersihkan nama donatur dari prefiks yang tidak perlu
     * seperti Bapak, Ibu, Pak, dll.
     * 
     * @param string $name Nama yang akan dibersihkan
     * @return string Nama yang sudah dibersihkan
     */
    private function cleanDonorName(string $name): string
    {
        if (empty($name) || trim($name) === '-') {
            return 'Donatur Anonim';
        }
        
        // Prefiks yang umum digunakan dengan lebih lengkap
        $prefixes = [
            // Umum
            'bapak\s', 'ibu\s', 'pak\s', 'bu\s', 
            'sdr\.?\s?', 'sdri\.?\s?', 'saudara\s', 'saudari\s',
            'tuan\s', 'nyonya\s', 'nona\s', 
            'tn\.?\s?', 'ny\.?\s?', 'nn\.?\s?',
            
            // Gelar akademis/profesional
            'prof\.?\s?', 'dr\.?\s?', 'ir\.?\s?', 
            'drs\.?\s?', 'drg\.?\s?', 'dra\.?\s?',
            
            // Gelar agama
            'haji\s', 'h\.?\s?', 'hj\.?\s?', 'hjh\.?\s?', 'hajjah\s',
            'kyai\s', 'kh\.?\s?', 'ustadz\s', 'ust\.?\s?',
            
            // Sebutan akrab
            'mas\s', 'mbak\s', 'kak\s', 'adik\s', 'kakak\s',
            'om\s', 'tante\s', 'budhe\s', 'pakdhe\s',
            'umi\s', 'abi\s', 'ayah\s', 'bunda\s', 'papa\s', 'mama\s',
            
            // Status
            'alm\.?\s?', 'almh\.?\s?', 'ananda\s', 'sahabat\s',
            
            // Militer/polisi
            'jenderal\s', 'letnan\s', 'kapten\s', 'sersan\s', 'kolonel\s',
            'brigadir\s', 'ipda\s', 'iptu\s', 'aiptu\s', 'bripka\s',
            
            // Lainnya
            'donatur\s', 'gus\s'
        ];
        
        // Bersihkan prefiks satu per satu
        $cleanedName = $name;
        foreach ($prefixes as $prefix) {
            $cleanedName = preg_replace('/^' . $prefix . '/i', '', $cleanedName);
        }
        
        // Jika nama sudah dibersihkan, periksa apakah masih ada prefiks yang tertinggal
        // Ulangi sampai tidak ada perubahan lagi (maksimal 3x untuk menghindari loop infinity)
        $iteration = 0;
        $previousName = '';
        while ($previousName !== $cleanedName && $iteration < 3) {
            $previousName = $cleanedName;
            foreach ($prefixes as $prefix) {
                $cleanedName = preg_replace('/^' . $prefix . '/i', '', $cleanedName);
            }
            $iteration++;
        }
        
        return trim($cleanedName);
    }
}
