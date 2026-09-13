<?php
    // Data processing for invoice template
    $isZakat = false;
    $zakatTypes = ['zakat maal', 'zakat fitrah', 'zakat perusahaan'];
    
    $jenisToCheck = '';
    if (isset($jenis_zakat)) {
        $jenisToCheck = strtolower(trim($jenis_zakat));
    } elseif (isset($jenis_donasi)) {
        $jenisToCheck = strtolower(trim($jenis_donasi));
    } elseif (isset($donasi) && isset($donasi->jenisDonasi->nama)) {
        $jenisToCheck = strtolower(trim($donasi->jenisDonasi->nama));
    }
    
    if (!empty($jenisToCheck)) {
        foreach ($zakatTypes as $zakatType) {
            if (stripos($jenisToCheck, $zakatType) !== false) {
                $isZakat = true;
                break;
            }
        }
    }
    
    // Set variables
    $transactionType = $isZakat ? 'ZAKAT' : 'DONASI';
    $personLabel = $isZakat ? 'Muzakki' : 'Donatur';
    $invoiceNumber = $invoice_number ?? $nomor_transaksi ?? 'INV-' . date('YmdHis');
    
    // Extract data from objects with proper fallbacks
    if (isset($donasi)) {
        $nama = $donasi->donatur->nama ?? '';
        $email = $donasi->donatur->email ?? '';
        $telepon = $donasi->donatur->nomor_hp ?? '';
        $alamat = $donasi->donatur->alamat_lengkap ?? '';
        $jenisTransaksi = $donasi->jenisDonasi->nama ?? '';
        $tanggalTransaksi = $donasi->tanggal_donasi ?? '';
        $metodePembayaran = $donasi->metodePembayaran->nama ?? '';
        $keterangan = $donasi->keterangan ?? '';
        $jumlahNumeric = $donasi->jumlah ?? 0;
        $jumlahFormatted = 'Rp ' . number_format($jumlahNumeric, 0, ',', '.');
        $donaturId = $donasi->donatur->id ?? '';
    } else {
        $nama = $nama_donatur ?? 'N/A';
        $email = $email_donatur ?? '';
        $telepon = $telepon_donatur ?? '';
        $alamat = $alamat_donatur ?? '';
        $jenisTransaksi = $jenis_donasi ?? 'Donasi Umum';
        $tanggalTransaksi = $tanggal_donasi ?? date('Y-m-d');
        $metodePembayaran = $metode_pembayaran ?? 'Transfer Bank';
        $keterangan = $catatan ?? '';
        $jumlahFormatted = $jumlah_formatted ?? 'Rp 0';
        $jumlahNumeric = is_numeric($jumlah ?? 0) ? $jumlah : 0;
        $donaturId = $id_donatur ?? '';
    }
    
    // Date formatting
    $tanggalFormatted = date('d/m/Y', strtotime($tanggalTransaksi));
    
    // Simple terbilang function
    function terbilang($number) {
        $x = abs($number);
        $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        
        if ($x < 12) return " " . $angka[$x];
        if ($x < 20) return terbilang($x - 10) . " belas";
        if ($x < 100) return terbilang($x/10) . " puluh" . terbilang($x % 10);
        if ($x < 200) return " seratus" . terbilang($x - 100);
        if ($x < 1000) return terbilang($x/100) . " ratus" . terbilang($x % 100);
        if ($x < 2000) return " seribu" . terbilang($x - 1000);
        if ($x < 1000000) return terbilang($x/1000) . " ribu" . terbilang($x % 1000);
        if ($x < 1000000000) return terbilang($x/1000000) . " juta" . terbilang($x % 1000000);
        
        return "";
    }
    
    $jumlahTerbilang = $jumlahNumeric > 0 ? ucfirst(trim(terbilang($jumlahNumeric))) . ' rupiah' : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo e($transactionType); ?> - <?php echo e($invoiceNumber); ?></title>
    <style>
        @page {
            margin: 8mm;
            size: A5 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            line-height: 1.1;
            color: #333;
            background: #fff;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 100%;
            border: 2px solid #000;
            background: linear-gradient(135deg, #ffeeee 0%, #eeffee 100%);
            page-break-inside: avoid;
        }
        
        .header {
            background-color: #d63384;
            color: white;
            padding: 4px 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            flex: 2;
        }
        
        .header-right {
            flex: 1;
            text-align: right;
        }
        
        .invoice-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1px;
        }
        
        .invoice-subtitle {
            font-size: 8px;
            opacity: 0.9;
        }
        
        .badges {
            margin-top: 2px;
        }
        
        .badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 1px 4px;
            border-radius: 8px;
            font-size: 5px;
            margin: 0 1px;
            border: 1px solid #fff;
        }
        
        .org-info {
            font-size: 7px;
            line-height: 1.2;
        }
        
        .main-content {
            padding: 6px 8px;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            gap: 8px;
        }
        
        .left-section {
            flex: 1;
        }
        
        .right-section {
            flex: 1;
        }
        
        .bismillah {
            font-size: 8px;
            font-style: italic;
            margin-bottom: 3px;
            color: #666;
            text-align: center;
        }
        
        .invoice-info {
            font-size: 6px;
            margin-bottom: 4px;
            text-align: center;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 6px;
        }
        
        .field-group {
            margin-bottom: 3px;
        }
        
        .field-label {
            font-size: 6px;
            color: #666;
            margin-bottom: 0px;
        }
        
        .field-value {
            border-bottom: 1px solid #333;
            min-height: 10px;
            padding: 1px 2px;
            font-size: 7px;
            font-weight: 500;
        }
        
        .amount-section {
            text-align: center;
            margin: 4px 0;
            padding: 4px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        
        .amount-label {
            font-size: 6px;
            color: #666;
            margin-bottom: 1px;
        }
        
        .amount-value {
            font-size: 12px;
            font-weight: bold;
            color: #d63384;
            margin-bottom: 2px;
        }
        
        .amount-words {
            font-size: 6px;
            font-style: italic;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 2px;
        }
        
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 6px;
        }
        
        .transaction-table th,
        .transaction-table td {
            border: 1px solid #333;
            padding: 2px;
            text-align: left;
        }
        
        .transaction-table th {
            background-color: #f1f3f4;
            font-weight: bold;
            font-size: 6px;
        }
        
        .method-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
            margin: 3px 0;
        }
        
        .method-badge {
            padding: 1px 4px;
            border-radius: 10px;
            font-size: 5px;
            border: 1px solid #ddd;
        }
        
        .method-badge.active {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .method-badge.inactive {
            background-color: #f8f9fa;
            color: #666;
        }
        
        .tax-section {
            background-color: #fff3cd;
            border: 1px solid #856404;
            padding: 4px;
            margin: 4px 0;
            font-size: 6px;
        }
        
        .tax-title {
            background-color: #856404;
            color: white;
            padding: 1px 4px;
            font-size: 6px;
            font-weight: bold;
            margin-bottom: 2px;
            text-align: center;
        }
        
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
        }
        
        .signature-box {
            width: 48%;
            text-align: center;
            border: 1px solid #ddd;
            padding: 3px;
            min-height: 25px;
        }
        
        .signature-label {
            font-size: 5px;
            margin-bottom: 2px;
        }
        
        .qr-section {
            text-align: center;
            margin: 4px 0;
        }
        
        .qr-placeholder {
            width: 40px;
            height: 40px;
            border: 1px solid #333;
            background: repeating-linear-gradient(45deg, #ccc, #ccc 2px, #fff 2px, #fff 4px);
            margin: 0 auto 2px;
        }
        
        .qr-text {
            font-size: 5px;
            text-align: center;
        }
        
        .footer-section {
            margin-top: 4px;
        }
        
        .footer-note {
            font-size: 5px;
            text-align: justify;
            margin: 2px 0;
            line-height: 1.2;
            color: #666;
        }
        
        .organization-info {
            text-align: center;
            font-size: 6px;
            margin: 2px 0;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 2px;
            border: 1px solid #ddd;
        }
        
        .contact-footer {
            background-color: #d63384;
            color: white;
            padding: 3px 6px;
            text-align: center;
            font-size: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .print-only {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="invoice-title">Invoice <?php echo e($transactionType); ?></div>
                <div class="invoice-subtitle"><?php echo e($invoiceNumber); ?></div>
                <div class="badges">
                    <span class="badge">TERAKREDITASI</span>
                    <span class="badge">WTP</span>
                </div>
            </div>
            <div class="header-right">
                <div class="org-info">
                    <div style="font-weight: bold;">♥ Insan Madani Jambi</div>
                    <div>LEMBAGA AMIL ZAKAT</div>
                    <div>No. SK 378/2018</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="left-section">
                <div class="bismillah">bismillaahirrahmaanirrahim</div>
                <div class="invoice-info">
                    <strong>LAZ Yayasan Insan Madani Jambi</strong><br>
                    <em>Invoice <?php echo e($transactionType); ?> - Terima kasih atas amanah Anda</em>
                </div>
                
                <div class="invoice-details">
                    <div>
                        <strong>Tanggal:</strong> <?php echo e($tanggalFormatted); ?><br>
                        <strong>Cabang:</strong> Jambi<br>
                        <strong>Mata Uang:</strong> IDR
                    </div>
                </div>
                
                <div class="field-group">
                    <div class="field-label">Nama <?php echo e($personLabel); ?></div>
                    <div class="field-value"><?php echo e($nama); ?></div>
                </div>
                
                <div class="field-group">
                    <div class="field-label">ID <?php echo e($personLabel); ?></div>
                    <div class="field-value"><?php echo e($donaturId); ?></div>
                </div>
                
                <div class="field-group">
                    <div class="field-label">Alamat</div>
                    <div class="field-value"><?php echo e($alamat); ?></div>
                </div>
                
                <div class="field-group">
                    <div class="field-label">No. Telepon</div>
                    <div class="field-value"><?php echo e($telepon); ?></div>
                </div>

                <!-- Payment Methods -->
                <div class="method-badges">
                    <div class="method-badge <?php echo e(strtolower($metodePembayaran) === 'tunai' || strtolower($metodePembayaran) === 'cash' ? 'active' : 'inactive'); ?>">
                        💰 Tunai
                    </div>
                    <div class="method-badge <?php echo e(stripos($metodePembayaran, 'transfer') !== false || stripos($metodePembayaran, 'bank') !== false ? 'active' : 'inactive'); ?>">
                        🏦 Transfer
                    </div>
                    <div class="method-badge <?php echo e(stripos($metodePembayaran, 'card') !== false || stripos($metodePembayaran, 'debit') !== false ? 'active' : 'inactive'); ?>">
                        💳 Kartu
                    </div>
                </div>
            </div>
            
            <div class="right-section">
                <!-- Transaction Details -->
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Jenis <?php echo e($transactionType); ?></th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo e($isZakat ? '✓' : '○'); ?> Zakat</td>
                            <td><?php echo e($isZakat ? $jenisTransaksi : ''); ?></td>
                            <td><?php echo e($isZakat ? 'Rp ' . number_format($jumlahNumeric, 0, ',', '.') : ''); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo e(!$isZakat ? '✓' : '○'); ?> Infaq/Sedekah</td>
                            <td><?php echo e(!$isZakat ? $jenisTransaksi : ''); ?></td>
                            <td><?php echo e(!$isZakat ? 'Rp ' . number_format($jumlahNumeric, 0, ',', '.') : ''); ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Amount Section -->
                <div class="amount-section">
                    <div class="amount-label">Total <?php echo e($transactionType); ?></div>
                    <div class="amount-value">Rp <?php echo e(number_format($jumlahNumeric, 0, ',', '.')); ?></div>
                    <div class="amount-words"><?php echo e($jumlahTerbilang); ?></div>
                </div>

                <!-- QR Code Section -->
                <div class="qr-section">
                    <div class="qr-placeholder"></div>
                    <div class="qr-text">Scan untuk donasi digital</div>
                </div>
            </div>
        </div>

        <!-- Tax Section -->
        <div class="tax-section">
            <div class="tax-title">Untuk Pengurangan Pajak Penghasilan (Income Tax Deduction)</div>
            <div>
                <strong>NPWP:</strong> <span style="border-bottom: 1px solid #333; display: inline-block; width: 120px; margin-left: 5px;"></span>
                <span style="margin-left: 10px; font-size: 5px;">
                    Lampiran SPT Tahunan sesuai KEP-163/PJ/2003
                </span>
            </div>
            <div class="signature-area">
                <div class="signature-box">
                    <div class="signature-label">Tanda Tangan Penyetor</div>
                    <div style="margin-top: 15px; border-bottom: 1px solid #333;"></div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Pengesahan Petugas</div>
                    <div style="color: #d63384; font-size: 10px; margin-top: 5px; transform: rotate(-15deg);">
                        ♥ Insan Madani
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="footer-note">
                Harta yang diberikan adalah amanah. LAZ Insan Madani hanya menerima donasi dari sumber halal 
                sesuai peraturan yang berlaku. Terima kasih atas kepercayaan Anda.
            </div>
            
            <div class="organization-info">
                LAZ INSAN MADANI JAMBI - LEMBAGA AMIL ZAKAT RESMI SKALA PROVINSI<br>
                SK KEMENTERIAN AGAMA RI NO. 378 TAHUN 2018
            </div>
            
            <div class="contact-footer">
                <div>Jl. Otto Iskandardinata No. 15, Sei Asam, Pasar Jambi ☎ 0811.743.1231</div>
                <div>📱 insanmadanijambi.org</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /home/simadior/public_html/resources/views/invoices/simple-invoice.blade.php ENDPATH**/ ?>