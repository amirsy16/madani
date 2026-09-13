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
use Illuminate\Console\Command;

class DonasiJanuariSampaiMei2025Seeder extends Seeder
{
    private $importStats = [
        'total_processed' => 0,
        'donations_imported' => 0,
        'penyaluran_imported' => 0,
        'donors_created' => 0,
        'donors_updated' => 0,
        'errors' => 0,
        'warnings' => 0,
        'breakdown' => [
            'zakat' => 0,
            'infaq_terikat' => 0,
            'infaq_tidak_terikat' => 0,
            'dskl' => 0,
            'barang' => 0,
            'penyaluran' => 0
        ],
        'donor_types' => [
            'male' => 0,
            'female' => 0,
            'organization' => 0,
            'anonymous' => 0,
            'unknown' => 0
        ],
        'monthly_stats' => [
            'januari' => [
                'total' => 0,
                'success' => 0,
                'errors' => 0
            ],
            'februari' => [
                'total' => 0,
                'success' => 0,
                'errors' => 0
            ],
            'maret' => [
                'total' => 0,
                'success' => 0,
                'errors' => 0
            ],
            'april' => [
                'total' => 0,
                'success' => 0,
                'errors' => 0
            ],
            'mei' => [
                'total' => 0,
                'success' => 0,
                'errors' => 0
            ]
        ],
        'by_fundraiser' => [],
        'by_payment_method' => [],
        'total_amount' => 0,
        'total_penyaluran' => 0,
        'net_amount' => 0
    ];

    /**
     * Run the database seeds - IMPORTS ALL DONATIONS JANUARI-MEI 2025
     */
    public function run(): void
    {
        $this->command->info('🚀 BATCH IMPORT - Donasi Januari sampai Mei 2025');
        $this->command->info('📋 Features: Perfect database mapping, Smart donor matching, Anti-duplikasi kwitansi, Statistik lengkap');
        
        DB::beginTransaction();
        
        try {
            // Initialize all master data
            $this->initializeMasterData();
            
            // Process each month in order
            $months = [
                'januari' => 'data_januari - INPUT DATA.csv',
                'februari' => 'Donasi_Februari - INPUT DATA (1).csv', 
                'maret' => 'donasi_maret - INPUT DATA.csv',
                'april' => 'donasi_april - INPUT DATA.csv',
                'mei' => 'donasi_mei - INPUT DATA.csv'
            ];
            
            foreach ($months as $month => $csvFile) {
                $this->command->info('');
                $this->command->info('🗓️ Processing ' . ucfirst($month) . ' 2025...');
                
                // Try to find the CSV file
                $csvPath = $this->findCSVFile($csvFile);
                
                // Process the month if CSV found
                if ($csvPath) {
                    $this->command->info("📄 Found CSV at: {$csvPath}");
                    $csvData = $this->readAndParseCSV($csvPath);
                    $this->processMonth($month, $csvData);
                } else {
                    $this->command->error("❌ CSV file not found for {$month}: {$csvFile}");
                    $this->importStats['monthly_stats'][$month]['errors'] = 1;
                }
            }
            
            DB::commit();
            
            $this->displayFinalSummary();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("💥 IMPORT FAILED: " . $e->getMessage());
            Log::emergency('Donasi Januari-Mei Import Failed', [
                'error' => $e->getMessage(),
                'stats' => $this->importStats
            ]);
            throw $e;
        }
    }

    /**
     * Find CSV file in various possible locations
     */
    private function findCSVFile($filename): ?string
    {
        $possiblePaths = [
            base_path($filename),
            storage_path('app/' . $filename),
            public_path($filename),
            base_path('database/seeders/data/' . $filename),
            base_path('../' . $filename),
            base_path('../../' . $filename)
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }

    /**
     * Initialize all required master data
     */
    private function initializeMasterData(): void
    {
        $this->command->info('📊 Initializing master data...');
        
        // Jenis Donasi - using existing ones, create missing
        $this->command->info('📊 Checking jenis donasi...');
        
        // Create missing "Penerimaan Zakat Maal" if not exists
        JenisDonasi::firstOrCreate([
            'nama' => 'Penerimaan Zakat Maal'
        ], [
            'deskripsi' => 'Penerimaan Zakat Maal',
            'aktif' => true
        ]);
        
        $this->command->info('📱 Using existing payment methods from database...');
        
        // Fundraisers - ensure all fundraisers exist
        $allFundraisers = [
            'ADI', 'HENDRA', 'MASYHUDA', 'KANTOR', 'SRI', 'TANTI', 'JOKO', 'EKA', 
            'ZULI', 'MIRA', 'DADANG', 'FUJI', 'MERI', 'TIARA', 'YUNITA', 'MIRDA',
            'ANNISA', 'TASLIMAH H', 'DADAN', 'SYAHRUL'
        ];
        
        foreach ($allFundraisers as $fr) {
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
            
            // Create default BidangProgram if not exists
            $bidangPendidikan = BidangProgram::firstOrCreate([
                'nama' => 'Pendidikan'
            ], [
                'deskripsi' => 'Program bidang pendidikan',
                'aktif' => true
            ]);
            
            $bidangKesehatan = BidangProgram::firstOrCreate([
                'nama' => 'Kesehatan'
            ], [
                'deskripsi' => 'Program bidang kesehatan',
                'aktif' => true
            ]);
            
            $bidangEkonomi = BidangProgram::firstOrCreate([
                'nama' => 'Ekonomi'
            ], [
                'deskripsi' => 'Program bidang ekonomi',
                'aktif' => true
            ]);
            
            $bidangKemanusiaan = BidangProgram::firstOrCreate([
                'nama' => 'Kemanusiaan'
            ], [
                'deskripsi' => 'Program bidang kemanusiaan',
                'aktif' => true
            ]);
            
            $bidangDakwah = BidangProgram::firstOrCreate([
                'nama' => 'Dakwah'
            ], [
                'deskripsi' => 'Program bidang dakwah',
                'aktif' => true
            ]);
            
        } catch (\Exception $e) {
            $this->command->error("Gagal membuat master data penyaluran: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process one month of data
     */
    private function processMonth(string $month, array $csvData): void
    {
        $startTime = microtime(true);
        $monthPrefix = strtoupper(substr($month, 0, 3)); // JAN, FEB, MAR, APR, MEI
        $totalRows = count($csvData);
        $this->importStats['monthly_stats'][$month]['total'] = $totalRows;
        
        $this->command->info("🔄 Processing {$totalRows} rows for {$month}...");
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($csvData as $index => $row) {
            try {
                // Apply month-specific prefix to avoid receipt number duplicates
                $kwitansi = isset($row['no_kwitansi']) ? $row['no_kwitansi'] : null;
                if ($kwitansi) {
                    // If kwitansi doesn't already have month prefix, add it
                    if (strpos($kwitansi, $monthPrefix.'-') !== 0) {
                        $row['no_kwitansi'] = $monthPrefix . '-' . $kwitansi;
                    }
                }
                
                // Process donation
                $this->processDonationRow($row, $month);
                $successCount++;
                
                // Progress update for every 50 rows
                if (($index + 1) % 50 === 0 || $index === $totalRows - 1) {
                    $progress = round(($index + 1) / $totalRows * 100);
                    $this->command->info("{$month}: Processed " . ($index + 1) . " of {$totalRows} rows ({$progress}%)");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->importStats['errors']++;
                $this->command->error("Row " . ($index + 1) . " Error: " . $e->getMessage());
                Log::error("Import {$month} Row " . ($index + 1) . " Error", [
                    'error' => $e->getMessage(),
                    'data' => $row ?? 'No data'
                ]);
            }
        }
        
        $this->importStats['monthly_stats'][$month]['success'] = $successCount;
        $this->importStats['monthly_stats'][$month]['errors'] = $errorCount;
        
        $duration = round(microtime(true) - $startTime, 2);
        $this->command->info("✅ {$month} import completed in {$duration} seconds. Success: {$successCount}, Errors: {$errorCount}");
    }
    
    /**
     * Process a single donation row
     */
    private function processDonationRow(array $row, string $month): void
    {
        $this->importStats['total_processed']++;
        
        // Debug row data for troubleshooting
        // $this->command->info("Processing row: " . json_encode($row));
        
        // Get donation amount - check multiple possible columns
        $amount = 0;
        if (!empty($row['jumlah'])) {
            $amount = $this->normalizeAmount($row['jumlah']);
        } elseif (!empty($row[' jumlah'])) { // Note the space before "jumlah" which appears in some files
            $amount = $this->normalizeAmount($row[' jumlah']);
        } elseif (!empty($row['_jumlah'])) {
            $amount = $this->normalizeAmount($row['_jumlah']);
        }
        
        // Skip rows with zero amount unless it's a non-cash donation
        if ($amount <= 0 && !$this->isBarangDonation($row)) {
            $this->command->warn("⚠️ Skipping row with zero amount and not barang donation");
            return;
        }
        
        // Handle empty or null kwitansi
        $monthPrefix = strtoupper(substr($month, 0, 3));
        if (empty($row['no_kwitansi'])) {
            // Generate unique kwitansi based on month, donor and amount
            $donorName = $row['nama_donatur'] ?? 'unknown';
            $dateStr = date('Ymd');
            $uniqueHash = substr(md5($donorName . $amount . microtime()), 0, 6);
            $row['no_kwitansi'] = $monthPrefix . '-' . $dateStr . '-' . $uniqueHash;
            $this->command->info("📝 Generated kwitansi: {$row['no_kwitansi']}");
        } else {
            // Ensure kwitansi has month prefix
            if (strpos($row['no_kwitansi'], $monthPrefix . '-') !== 0) {
                $row['no_kwitansi'] = $monthPrefix . '-' . $row['no_kwitansi'];
            }
        }
        
        // Check for duplicates and handle
        $nomor = $row['no_kwitansi'];
        $existingDonation = Donasi::where('nomor_transaksi_unik', $nomor)->first();
        
        if ($existingDonation) {
            // Generate a new unique receipt number with suffix
            $originalKwitansi = $nomor;
            $counter = 1;
            
            do {
                $newKwitansi = $originalKwitansi . '-' . $counter;
                $exists = Donasi::where('nomor_transaksi_unik', $newKwitansi)->exists();
                if (!$exists) {
                    $row['no_kwitansi'] = $newKwitansi;
                    $this->importStats['warnings']++;
                    $this->command->warn("🔄 Duplicate kwitansi found: {$originalKwitansi}, using {$newKwitansi} instead");
                    break;
                }
                $counter++;
            } while ($counter < 100);  // Safety limit
            
            if ($counter >= 100) {
                // Emergency unique number if all attempts fail
                $row['no_kwitansi'] = $originalKwitansi . '-' . substr(uniqid(), -8);
                $this->command->warn("⚠️ Using emergency unique kwitansi: {$row['no_kwitansi']}");
            }
        }
        
        // Process donor information
        $donorData = $this->processDonorInfo($row);
        
        // Determine if this is penyaluran (outflow) or donation (inflow)
        $isPenyaluran = $this->isPenyaluran($row);
        
        // Parse donation date
        $monthMap = [
            'januari' => 1, 
            'februari' => 2, 
            'maret' => 3, 
            'april' => 4, 
            'mei' => 5
        ];
        
        // Set default date if not provided or can't be parsed
        $donationDate = null;
        $monthMap = [
            'januari' => 1, 
            'februari' => 2, 
            'maret' => 3, 
            'april' => 4, 
            'mei' => 5
        ];
        
        if (!empty($row['tanggal'])) {
            try {
                $donationDate = $this->parseDate($row['tanggal']);
            } catch (\Exception $e) {
                $this->command->warn("⚠️ Could not parse date: {$row['tanggal']}, using default");
                $donationDate = null;
            }
        }
        
        // Use default date if still null
        if (!$donationDate) {
            $monthNumber = $monthMap[$month];
            $donationDate = Carbon::createFromDate(2025, $monthNumber, 15);
            $this->command->info("📅 Using default date: {$donationDate->format('Y-m-d')}");
        }
        
        // Get donation type based on the available fields
        $jenisDonasi = $this->getJenisDonasi($row);
        
        // Get payment method - either from 'via' or 'metode_pembayaran' column
        $metodePembayaran = $this->getMetodePembayaran($row);
        
        // Get fundraiser - check multiple possible column names
        $fundraiser = $this->getFundraiser($row);

        // Create record based on type (donation/penyaluran)
        if ($isPenyaluran) {
            $this->createPenyaluran($row, $donorData, $donationDate, $amount);
            $this->command->info("💰 Penyaluran created: {$row['no_kwitansi']} - Rp " . number_format($amount));
        } else {
            $this->createDonation($row, $donorData, $donationDate, $jenisDonasi, $metodePembayaran, $fundraiser, $amount);
            $this->command->info("💎 Donation created: {$row['no_kwitansi']} - Rp " . number_format($amount));
        }
    }
    
    /**
     * Check if this is a non-cash/barang donation
     */
    private function isBarangDonation(array $row): bool
    {
        $jenisLower = strtolower($row['jenis_donasi'] ?? '');
        $keteranganLower = strtolower($row['keterangan'] ?? '');
        $catatanLower = strtolower($row['catatan'] ?? '');
        
        // Check various fields for barang/logistik indicators
        return strpos($jenisLower, 'barang') !== false ||
               strpos($jenisLower, 'logistik') !== false ||
               strpos($keteranganLower, 'barang') !== false ||
               strpos($keteranganLower, 'sembako') !== false ||
               strpos($keteranganLower, 'pakaian') !== false ||
               strpos($catatanLower, 'barang') !== false;
    }
    
    /**
     * Process donor information, create or update donor in DB
     */
    private function processDonorInfo(array $row): array
    {
        $donorName = $row['nama_donatur'] ?? ($row['nama'] ?? null);
        $donorAnalysis = $this->analyzeDonorName($donorName);
        $donorType = $donorAnalysis['donor_type'];
        $cleanName = $donorAnalysis['clean_name'];
        $gender = $donorAnalysis['gender'];
        
        // Update stats
        $this->importStats['donor_types'][$donorType]++;
        
        // Try to find existing donor with same name or phone
        $donorQuery = Donatur::query();
        
        if (!empty($cleanName)) {
            $donorQuery->where('nama', 'like', '%' . $cleanName . '%');
        }
        
        if (!empty($row['no_hp']) && strlen($row['no_hp']) >= 10) {
            $donorQuery->orWhere('no_hp', $row['no_hp']);
        }
        
        $existingDonor = $donorQuery->first();
        
        if ($existingDonor) {
            // Update existing donor info
            $existingDonor->update([
                'nama' => $cleanName ?: $existingDonor->nama,
                'no_hp' => $row['no_hp'] ?: $existingDonor->no_hp,
                'alamat' => $row['alamat'] ?: $existingDonor->alamat,
                'email' => $row['email'] ?: $existingDonor->email,
                'tipe_donatur' => $donorType === 'organization' ? 'organisasi' : ($donorType === 'anonymous' ? 'anonim' : 'individu'),
                'gender' => $gender ?: $existingDonor->gender
            ]);
            
            $this->importStats['donors_updated']++;
            $donaturId = $existingDonor->id;
        } else {
            // Create new donor
            $newDonor = Donatur::create([
                'nama' => $cleanName ?: ($donorName ?: 'Hamba Allah'),
                'no_hp' => $row['no_hp'] ?? null,
                'alamat' => $row['alamat'] ?? null,
                'email' => $row['email'] ?? null,
                'tipe_donatur' => $donorType === 'organization' ? 'organisasi' : ($donorType === 'anonymous' ? 'anonim' : 'individu'),
                'gender' => $gender,
                'aktif' => true,
            ]);
            
            $this->importStats['donors_created']++;
            $donaturId = $newDonor->id;
        }
        
        return [
            'id' => $donaturId,
            'donor_type' => $donorType,
            'clean_name' => $cleanName
        ];
    }
    
    /**
     * Create normal donation record
     */
    private function createDonation(array $row, array $donorData, $date, $jenisDonasi, $metodePembayaran, $fundraiser, $amount): void
    {
        $infaqType = null;
        
        if (isset($row['jenis_donasi'])) {
            $jenisText = strtolower($row['jenis_donasi']);
            
            if (strpos($jenisText, 'zakat') !== false) {
                $this->importStats['breakdown']['zakat']++;
                $infaqType = null;
            } elseif (strpos($jenisText, 'infaq terikat') !== false) {
                $this->importStats['breakdown']['infaq_terikat']++;
                $infaqType = 'terikat';
            } elseif (strpos($jenisText, 'infaq tidak terikat') !== false || strpos($jenisText, 'infaq umum') !== false) {
                $this->importStats['breakdown']['infaq_tidak_terikat']++;
                $infaqType = 'tidak_terikat';
            } elseif (strpos($jenisText, 'dana sosial') !== false || strpos($jenisText, 'dskl') !== false) {
                $this->importStats['breakdown']['dskl']++;
                $infaqType = 'dskl';
            } elseif (strpos($jenisText, 'barang') !== false) {
                $this->importStats['breakdown']['barang']++;
                $infaqType = 'barang';
            } else {
                // Default to infaq tidak terikat
                $this->importStats['breakdown']['infaq_tidak_terikat']++;
                $infaqType = 'tidak_terikat';
            }
        } else {
            // Default when jenis_donasi is not available
            $this->importStats['breakdown']['infaq_tidak_terikat']++;
            $infaqType = 'tidak_terikat';
        }
        
        $admin = User::first(); // Get first admin user for created_by
        
        $donasi = Donasi::create([
            'donatur_id' => $donorData['id'],
            'jenis_donasi_id' => $jenisDonasi->id,
            'metode_pembayaran_id' => $metodePembayaran->id,
            'fundraiser_id' => $fundraiser->id,
            'no_kwitansi' => $row['no_kwitansi'],
            'jumlah' => $amount,
            'keterangan' => $row['keterangan'] ?? '',
            'bukti_pembayaran' => null,
            'status' => 'diterima', // All imported donations are considered received
            'tanggal' => $date,
            'tipe_infaq' => $infaqType,
            'created_by' => $admin ? $admin->id : null
        ]);
        
        // Update stats
        $this->importStats['donations_imported']++;
        $this->importStats['total_amount'] += $amount;
        
        // By fundraiser stats
        $fundraiserName = $fundraiser->nama_fundraiser;
        if (!isset($this->importStats['by_fundraiser'][$fundraiserName])) {
            $this->importStats['by_fundraiser'][$fundraiserName] = 0;
        }
        $this->importStats['by_fundraiser'][$fundraiserName] += $amount;
        
        // By payment method stats
        $metodeName = $metodePembayaran->nama_metode;
        if (!isset($this->importStats['by_payment_method'][$metodeName])) {
            $this->importStats['by_payment_method'][$metodeName] = 0;
        }
        $this->importStats['by_payment_method'][$metodeName] += $amount;
    }
    
    /**
     * Create penyaluran record
     */
    private function createPenyaluran(array $row, array $donorData, $date, $amount): void
    {
        $admin = User::first(); // Get first admin user
        $sumberDana = SumberDanaPenyaluran::where('nama_sumber_dana', 'Penyaluran Langsung')->first();
        
        if (!$sumberDana) {
            throw new \Exception("Sumber Dana 'Penyaluran Langsung' not found");
        }
        
        // Create program penyaluran if needed (using bidang_program id 1 as default)
        $bidangProgram = BidangProgram::first();
        
        $program = ProgramPenyaluran::create([
            'nama_program' => $row['keterangan'] ?? 'Penyaluran ' . $date->format('d-m-Y'),
            'deskripsi' => $row['keterangan'] ?? 'Penyaluran langsung',
            'tanggal_mulai' => $date,
            'tanggal_selesai' => $date,
            'bidang_program_id' => $bidangProgram ? $bidangProgram->id : 1,
            'anggaran' => $amount,
            'status' => 'selesai',
            'created_by' => $admin ? $admin->id : null
        ]);
        
        // Create penyaluran record
        $donasi = Donasi::create([
            'donatur_id' => $donorData['id'], // Penerima dana
            'jenis_donasi_id' => 2, // Penyaluran
            'metode_pembayaran_id' => 1, // Cash/default
            'program_penyaluran_id' => $program->id,
            'sumber_dana_id' => $sumberDana->id,
            'no_kwitansi' => $row['no_kwitansi'],
            'jumlah' => $amount * -1, // Negative for penyaluran
            'keterangan' => $row['keterangan'] ?? 'Penyaluran langsung',
            'bukti_pembayaran' => null,
            'status' => 'diterima', // All imported transactions are considered final
            'tanggal' => $date,
            'tipe_penyaluran' => 'langsung',
            'created_by' => $admin ? $admin->id : null
        ]);
        
        // Update statistics
        $this->importStats['penyaluran_imported']++;
        $this->importStats['total_penyaluran'] += $amount;
        $this->importStats['breakdown']['penyaluran']++;
        $this->importStats['net_amount'] = $this->importStats['total_amount'] - $this->importStats['total_penyaluran'];
    }
    
    /**
     * Check if a row is a penyaluran (expense) instead of a donation (income)
     */
    private function isPenyaluran(array $row): bool
    {
        // Check specific penyaluran column first
        if (!empty($row['penyaluran']) && $row['penyaluran'] !== '-' && $row['penyaluran'] !== '0') {
            return true;
        }
        
        // Check jenis_donasi field
        if (!empty($row['jenis_donasi'])) {
            $jenisDonasi = strtolower($row['jenis_donasi']);
            if (strpos($jenisDonasi, 'penyaluran') !== false || 
                strpos($jenisDonasi, 'pengeluaran') !== false) {
                return true;
            }
        }
        
        // Check donor name for penyaluran indicators
        if (!empty($row['nama_donatur'])) {
            $namaDonatur = strtolower($row['nama_donatur']);
            if (strpos($namaDonatur, 'penyaluran') !== false || 
                strpos($namaDonatur, 'refund') !== false ||
                strpos($namaDonatur, 'biaya') !== false ||
                strpos($namaDonatur, 'operasional') !== false) {
                return true;
            }
        }
        
        // Check keterangan for penyaluran indicators
        if (!empty($row['keterangan'])) {
            $keterangan = strtolower($row['keterangan']);
            if (strpos($keterangan, 'penyaluran') !== false || 
                strpos($keterangan, 'refund') !== false ||
                strpos($keterangan, 'pengeluaran') !== false) {
                return true;
            }
        }
        
        // Check via/payment method for "Penyaluran Langsung"
        if (!empty($row['metode_pembayaran'])) {
            $via = strtolower($row['metode_pembayaran']);
            if (strpos($via, 'penyaluran langsung') !== false) {
                return true;
            }
        }
        
        // Double check via field if different from metode_pembayaran
        if (!empty($row['via']) && $row['via'] !== $row['metode_pembayaran']) {
            $via = strtolower($row['via']);
            if (strpos($via, 'penyaluran langsung') !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get or create JenisDonasi record
     */
    private function getJenisDonasi(array $row): JenisDonasi
    {
        $jenisDonasiName = $row['jenis_donasi'] ?? 'Penerimaan Zakat Maal';
        
        return JenisDonasi::firstOrCreate(
            ['nama' => $jenisDonasiName],
            [
                'deskripsi' => $jenisDonasiName,
                'aktif' => true
            ]
        );
    }
    
    /**
     * Get or create MetodePembayaran record
     */
    private function getMetodePembayaran(array $row): MetodePembayaran
    {
        $metodeName = $row['metode_pembayaran'] ?? 'Cash';
        
        return MetodePembayaran::firstOrCreate(
            ['nama_metode' => $metodeName],
            [
                'deskripsi' => $metodeName,
                'aktif' => true
            ]
        );
    }
    
    /**
     * Get or create Fundraiser record
     */
    private function getFundraiser(array $row): Fundraiser
    {
        $fundraiserName = $row['fundraiser'] ?? ($row['amil'] ?? 'KANTOR');
        $fundraiserName = strtoupper($fundraiserName);
        
        return Fundraiser::firstOrCreate(
            ['nama_fundraiser' => $fundraiserName],
            ['aktif' => true]
        );
    }
    
    /**
     * Analyze donor name to detect type, clean format, and extract gender
     */
    private function analyzeDonorName(?string $name): array
    {
        if (empty($name)) {
            return [
                'donor_type' => 'anonymous',
                'clean_name' => 'Hamba Allah',
                'gender' => null
            ];
        }
        
        // Clean up the name
        $originalName = $name;
        $name = trim($name);
        $nameLower = strtolower($name);
        
        // Detect anonymous donors
        $anonymousPatterns = [
            'hamba allah', 'hamba alloh', 'nn', 'anonymous', 'anonim', 'tanpa nama',
            'no name', 'samaran', 'tidak ingin disebutkan', 'n/a', '-', 'h a', 'kosong',
            'hmbaAlloh', 'hambaalloh', 'tidak ingin di sebut', 'rahasia'
        ];
        
        foreach ($anonymousPatterns as $pattern) {
            if (strpos($nameLower, $pattern) !== false || $nameLower === $pattern) {
                return [
                    'donor_type' => 'anonymous',
                    'clean_name' => 'Hamba Allah',
                    'gender' => null
                ];
            }
        }
        
        // Detect organizations
        $organizationPatterns = [
            'pt', 'cv', 'ud', 'ud.', 'fa', 'fa.', 'pabrik', 'toko', 'tb', 'tb.',
            'yayasan', 'perusahaan', 'firma', 'kantor', 'koperasi', 'kpri', 'ksu',
            'organisasi', 'masjid', 'mushola', 'pondok', 'pesantren', 'lembaga', 
            'sekolah', 'sd', 'sd.', 'sma', 'sma.', 'smp', 'smp.', 'smk', 'smk.', 'tk', 'tk.',
            'paud', 'paud.', 'ma', 'ma.', 'mi', 'mi.', 'univ', 'universitas', 'kampus',
            'pt.', 'cv.', 'perum', 'perumahan', 'dinas', 'instansi', 'cafe', 'hotel',
            'restoran', 'rumah makan', 'warung', 'puskesmas', 'klinik', 'rs', 'rs.',
            'rumah sakit', 'bank', 'bmt', 'kbih', 'ypai', 'corp', 'company', 'co', 'ltd',
            'restaurant', 'asrama', 'gerai', 'outlet', 'bengkel', 'studio', 'sanggar', 'tm',
            'bimbel', 'group', 'grup', 'association', 'asosiasi', 'foundation', 'komunitas'
        ];
        
        // Check if name starts with organization pattern
        $words = explode(' ', $nameLower);
        $firstWord = $words[0] ?? '';
        $secondWord = $words[1] ?? '';
        
        if (in_array($firstWord, $organizationPatterns) || 
            (strlen($firstWord) <= 3 && in_array($firstWord . '.', $organizationPatterns))) {
            return [
                'donor_type' => 'organization',
                'clean_name' => $this->formatNameProper($name),
                'gender' => null
            ];
        }
        
        // Additional check for "PT Something" pattern
        if (($firstWord === 'pt' || $firstWord === 'pt.') && !empty($secondWord)) {
            return [
                'donor_type' => 'organization',
                'clean_name' => $this->formatNameProper($name),
                'gender' => null
            ];
        }
        
        // Check for organization patterns anywhere in the name
        foreach ($organizationPatterns as $pattern) {
            if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/', $nameLower)) {
                return [
                    'donor_type' => 'organization',
                    'clean_name' => $this->formatNameProper($name),
                    'gender' => null
                ];
            }
        }
        
        // Detect gender for individuals
        $gender = null;
        $malePatterns = [
            'bapak', 'pak ', 'bp ', 'bp. ', 'akh ', 'akhi', 'sdr ', 'saudara ', 'bro ', 'brother',
            'tuan', 'mas ', 'kang ', 'bang ', 'om ', 'uncle', 'kakek', 'eyang kakung', 'opa', 'ayah',
            'babe', 'papah', 'papa', 'paman', 'abah', 'abi', 'aba', 'aa '
        ];
        
        $femalePatterns = [
            'ibu', 'bu ', 'ib ', 'ib. ', 'ny ', 'nyonya', 'nn ', 'nona', 'sdri ', 'saudari ',
            'sis ', 'sister', 'nyai', 'hj ', 'hj. ', 'hajjah', 'mbak ', 'teh ', 'umi', 'umi ', 'mba ',
            'mami', 'mama', 'bunda', 'tante', 'bibi', 'nenek', 'eyang putri', 'oma', 'aa '
        ];
        
        foreach ($malePatterns as $pattern) {
            if (stripos($nameLower . ' ', $pattern) === 0 || // starts with pattern
                stripos(' ' . $nameLower, ' ' . $pattern) !== false) { // contains pattern with word boundary
                $gender = 'male';
                break;
            }
        }
        
        if (!$gender) {
            foreach ($femalePatterns as $pattern) {
                if (stripos($nameLower . ' ', $pattern) === 0 || // starts with pattern
                    stripos(' ' . $nameLower, ' ' . $pattern) !== false) { // contains pattern with word boundary
                    $gender = 'female';
                    break;
                }
            }
        }
        
        // Additional common names recognition
        $commonMaleNames = [
            'muhammad', 'ahmad', 'abdullah', 'abdul', 'rahman', 'rahim', 'karim', 'aziz',
            'ali', 'hasan', 'husein', 'ibrahim', 'imran', 'ismael', 'ismail', 'mahmud',
            'mohamed', 'mohammed', 'bilal', 'yusuf', 'zakariya', 'hamza', 'mustapha',
            'saleh', 'suharto', 'sukarno', 'budi', 'bambang', 'agus', 'gunawan',
            'amir', 'hadi', 'imam', 'joko', 'dodi', 'dedy', 'ferry', 'hendri', 'hendry',
            'kevin', 'ryan', 'michael', 'david', 'john', 'james', 'william', 'richard',
            'thomas', 'daniel', 'matthew', 'anthony', 'mark', 'slamet', 'udin'
        ];
        
        $commonFemaleNames = [
            'fatima', 'aisha', 'aminah', 'asma', 'hafsa', 'hajar', 'halima',
            'khadija', 'maryam', 'rabia', 'safiya', 'samira', 'zaynab', 'zulaikha',
            'ayu', 'dewi', 'dina', 'endang', 'fitri', 'heni', 'indah', 'linda',
            'maria', 'maya', 'nina', 'putri', 'rina', 'sari', 'siti', 'susan',
            'tuti', 'wati', 'yanti', 'yulia', 'mary', 'patricia', 'jennifer',
            'elizabeth', 'linda', 'barbara', 'susan', 'jessica', 'sarah', 'karen',
            'nancy', 'betty', 'dorothy', 'helen', 'sandra', 'donna', 'carol',
            'ruth', 'anna', 'dini', 'lastri', 'yuni'
        ];
        
        // If no gender detected yet, check against common names
        if (!$gender) {
            $nameWords = explode(' ', $nameLower);
            $firstName = trim($nameWords[0]);
            
            if (in_array($firstName, $commonMaleNames)) {
                $gender = 'male';
            } else if (in_array($firstName, $commonFemaleNames)) {
                $gender = 'female';
            }
        }
        
        // Clean the name for final output
        $cleanName = $this->formatNameProper($name);
        
        return [
            'donor_type' => $gender ? 'individual' : 'unknown',
            'clean_name' => $cleanName,
            'gender' => $gender
        ];
    }
    
    /**
     * Format a name properly with correct capitalization
     */
    private function formatNameProper(string $name): string
    {
        // Don't process empty names
        if (empty($name)) {
            return 'Hamba Allah';
        }

        // Handle special case when the name is just "H A"
        if (strtolower($name) === 'h a') {
            return 'Hamba Allah';
        }

        // Common name prefixes that should stay lowercase
        $prefixes = ['bin', 'binti', 'pt', 'cv', 'ud', 'tb', 'fa', 'hj', 'h', 'drg', 'dr', 'ir', 'prof'];
        
        // Words that should stay in all caps
        $allCapsWords = ['sd', 'smp', 'sma', 'smk', 'paud', 'tk', 'mi', 'ma', 'pt', 'cv', 'ud', 'tb', 'fa', 'rs'];
        
        // First capitalize each word
        $name = ucwords(strtolower($name));
        
        // Handle prefixes - they should be lowercase except at the start of the name
        $words = explode(' ', $name);
        for ($i = 1; $i < count($words); $i++) {
            $lowerWord = strtolower($words[$i]);
            if (in_array($lowerWord, $prefixes)) {
                $words[$i] = $lowerWord;
            }
        }
        
        // Handle all caps words
        for ($i = 0; $i < count($words); $i++) {
            if (in_array(strtolower($words[$i]), $allCapsWords)) {
                $words[$i] = strtoupper($words[$i]);
            }
        }
        
        $processedName = implode(' ', $words);
        
        // Remove multiple spaces, leading/trailing spaces
        $processedName = trim(preg_replace('/\s+/', ' ', $processedName));
        
        return $processedName;
    }
    
    /**
     * Normalize the amount string to an integer
     */
    private function normalizeAmount($amountStr): int
    {
        if (is_numeric($amountStr)) {
            return (int)$amountStr;
        }
        
        // Remove non-numeric characters
        $amount = preg_replace('/[^0-9]/', '', $amountStr);
        
        return empty($amount) ? 0 : (int)$amount;
    }
    
    /**
     * Parse a date from various formats
     */
    private function parseDate($dateStr)
    {
        if (empty($dateStr)) {
            return Carbon::now();
        }
        
        if ($dateStr instanceof Carbon) {
            return $dateStr;
        }
        
        // Try different date formats
        try {
            // Try standard formats
            return Carbon::parse($dateStr);
        } catch (\Exception $e) {
            try {
                // Try dd/mm/yyyy format
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                    return Carbon::createFromDate($matches[3], $matches[2], $matches[1]);
                }
                
                // Try dd-mm-yyyy format
                if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateStr, $matches)) {
                    return Carbon::createFromDate($matches[3], $matches[2], $matches[1]);
                }
                
                // Try Excel date format (serial number)
                if (is_numeric($dateStr)) {
                    // Excel dates are days since 1900-01-00, but PHP dates are days since 1899-12-31
                    $excelDate = (int)$dateStr;
                    $unixTimestamp = ($excelDate - 25569) * 86400;
                    return Carbon::createFromTimestamp($unixTimestamp);
                }
                
                throw new \Exception("Could not parse date: $dateStr");
            } catch (\Exception $e2) {
                throw new \Exception("Could not parse date: $dateStr");
            }
        }
    }

    /**
     * Read and parse CSV file
     */
    private function readAndParseCSV(string $csvPath): array
    {
        $this->command->info("📂 Reading CSV file: {$csvPath}");
        
        $csvData = [];
        $handle = fopen($csvPath, 'r');
        
        if (!$handle) {
            throw new \Exception("Could not open CSV file: {$csvPath}");
        }
        
        // Skip header rows until we find the real header row (with "NO KWITANSI")
        $headers = null;
        $lineNumber = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Find header row - look for "NO KWITANSI"
            $foundHeader = false;
            foreach ($row as $cell) {
                if (preg_match('/(NO\s*KWITANSI|NOMOR\s*KWITANSI)/i', $cell)) {
                    $headers = $row;
                    $foundHeader = true;
                    $this->command->info("📋 Found header row at line {$lineNumber}");
                    break;
                }
            }
            
            if ($foundHeader) {
                break;
            }
            
            // Skip non-header rows
            if ($lineNumber > 10) {
                $this->command->warn("⚠️ Could not find header row in first 10 lines, using first non-empty row as header");
                rewind($handle);
                $headers = fgetcsv($handle);
                break;
            }
        }
        
        if (!$headers) {
            throw new \Exception("Could not find header row in CSV");
        }
        
        // Standardize header names
        $headerMap = [];
        foreach ($headers as $index => $header) {
            if (empty($header)) continue;
            
            $headerClean = trim(strtolower($header));
            
            // Map common variations to standard field names
            switch (true) {
                // Donation fields
                case preg_match('/(no\.?\s*kwitansi|nomor\s*kwitansi|kwitansi|no\s*kwitansi|nomor\s*bukti)/i', $headerClean):
                    $headerMap[$index] = 'no_kwitansi';
                    break;
                
                case preg_match('/(tgl|tanggal)/', $headerClean):
                    $headerMap[$index] = 'tanggal';
                    break;
                
                case preg_match('/(jenis|kategori)\s*(donasi|donatur)/i', $headerClean):
                    $headerMap[$index] = 'jenis_donasi';
                    break;
                
                case preg_match('/(jumlah|nominal|total)/i', $headerClean):
                    $headerMap[$index] = 'jumlah';
                    break;
                
                case preg_match('/^keterangan$/i', $headerClean):
                    $headerMap[$index] = 'keterangan';
                    break;
                
                case preg_match('/^catatan$/i', $headerClean):
                    $headerMap[$index] = 'catatan';
                    break;
                
                case preg_match('/(via|metode|cara|system|channel)\s*(bayar|pembayaran)?/i', $headerClean):
                    $headerMap[$index] = 'metode_pembayaran';
                    break;
                
                case preg_match('/(amil|^fr$|fr\s|petugas|fundraiser)/i', $headerClean):
                    $headerMap[$index] = 'fundraiser';
                    break;
                
                // Donor fields
                case preg_match('/(nama\s*(donatur)?|donatur)/i', $headerClean):
                    $headerMap[$index] = 'nama_donatur';
                    break;
                
                case preg_match('/(no\.?\s*(hp|telepon|telp)|telepon|telp|hp|kontak|contact)/i', $headerClean):
                    $headerMap[$index] = 'no_hp';
                    break;
                
                case preg_match('/(alamat|address)/i', $headerClean):
                    $headerMap[$index] = 'alamat';
                    break;
                
                case preg_match('/(email|e-mail)/i', $headerClean):
                    $headerMap[$index] = 'email';
                    break;
                
                case preg_match('/(penyaluran|pengeluaran)/i', $headerClean):
                    $headerMap[$index] = 'penyaluran';
                    break;
                
                // If no mapping is found, use the original header name
                default:
                    $headerMap[$index] = str_replace(' ', '_', $headerClean);
                    break;
            }
        }
        
        $this->command->info("🗂️ Mapped headers: " . implode(", ", array_values($headerMap)));
        
        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            // Skip header, summary and empty rows
            if (empty(array_filter($row)) || 
                (isset($row[0]) && preg_match('/(TOTAL|JUMLAH|INPUTAN|LAZ)/i', $row[0]))) {
                continue;
            }
            
            $rowData = [];
            foreach ($row as $index => $value) {
                if (isset($headerMap[$index])) {
                    $rowData[$headerMap[$index]] = $value;
                }
            }
            
            // Only add rows that have at least donor name or kwitansi
            if (!empty($rowData['nama_donatur']) || !empty($rowData['no_kwitansi'])) {
                $csvData[] = $rowData;
            }
        }
        
        fclose($handle);
        $this->command->info("📊 Found " . count($csvData) . " valid data rows");
        
        return $csvData;
    }
    
    /**
     * Display final summary of the import process
     */
    private function displayFinalSummary(): void
    {
        $this->command->info('');
        $this->command->info('===== 🎉 IMPORT COMPLETED =====');
        $this->command->info('');
        
        // Month by Month Summary
        $this->command->info('📅 MONTHLY BREAKDOWN:');
        foreach ($this->importStats['monthly_stats'] as $month => $stats) {
            $successRate = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0;
            $this->command->info("- " . ucfirst($month) . ": {$stats['success']}/{$stats['total']} rows ({$successRate}% success rate)");
        }
        
        $this->command->info('');
        $this->command->info('📊 OVERALL STATISTICS:');
        $this->command->info("Total rows processed: " . $this->importStats['total_processed']);
        $this->command->info("Donations imported: " . $this->importStats['donations_imported']);
        $this->command->info("Penyaluran imported: " . $this->importStats['penyaluran_imported']);
        $this->command->info("New donors created: " . $this->importStats['donors_created']);
        $this->command->info("Existing donors updated: " . $this->importStats['donors_updated']);
        $this->command->info("Warnings: " . $this->importStats['warnings']);
        $this->command->info("Errors: " . $this->importStats['errors']);
        
        $this->command->info('');
        $this->command->info('💰 DONATION BREAKDOWN:');
        $this->command->info("Zakat: " . $this->importStats['breakdown']['zakat'] . " donations");
        $this->command->info("Infaq Terikat: " . $this->importStats['breakdown']['infaq_terikat'] . " donations");
        $this->command->info("Infaq Tidak Terikat: " . $this->importStats['breakdown']['infaq_tidak_terikat'] . " donations");
        $this->command->info("DSKL: " . $this->importStats['breakdown']['dskl'] . " donations");
        $this->command->info("Barang: " . $this->importStats['breakdown']['barang'] . " donations");
        $this->command->info("Penyaluran: " . $this->importStats['breakdown']['penyaluran'] . " transactions");
        
        $this->command->info('');
        $this->command->info('👤 DONOR TYPES:');
        $this->command->info("Male: " . $this->importStats['donor_types']['male'] . " donors");
        $this->command->info("Female: " . $this->importStats['donor_types']['female'] . " donors");
        $this->command->info("Organization: " . $this->importStats['donor_types']['organization'] . " donors");
        $this->command->info("Anonymous: " . $this->importStats['donor_types']['anonymous'] . " donors");
        $this->command->info("Unknown: " . $this->importStats['donor_types']['unknown'] . " donors");
        
        $this->command->info('');
        $this->command->info('💵 FINANCIAL SUMMARY:');
        $totalAmount = number_format($this->importStats['total_amount'], 0, ',', '.');
        $totalPenyaluran = number_format($this->importStats['total_penyaluran'], 0, ',', '.');
        $netAmount = number_format($this->importStats['total_amount'] - $this->importStats['total_penyaluran'], 0, ',', '.');
        $this->command->info("Total donations: Rp {$totalAmount}");
        $this->command->info("Total penyaluran: Rp {$totalPenyaluran}");
        $this->command->info("Net amount: Rp {$netAmount}");
        
        $this->command->info('');
        $this->command->info('✅ Import process completed successfully!');
    }
}
