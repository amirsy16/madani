<?php
    // Data processing for donation form invoice template
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
            margin: 5mm;
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
            line-height: 1.2;
            color: #333;
            background: #fff;
        }
        
        .invoice-container {
            width: 100%;
            border: 2px solid #d63384;
            background: #fff;
        }
        
        .header {
            background: linear-gradient(90deg, #d63384 0%, #d63384 60%, #28a745 60%, #28a745 100%);
            color: white;
            padding: 6px 8px;
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: middle;
            background: #28a745;
            padding: 6px;
            margin: -6px -8px -6px 0;
        }
        
        .invoice-title {
            font-size: 12px;
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
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 1px 4px;
            border-radius: 8px;
            font-size: 5px;
            margin: 0 1px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .org-info {
            font-size: 7px;
            line-height: 1.1;
        }
        
        .bismillah {
            text-align: center;
            font-style: italic;
            padding: 4px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-size: 7px;
            color: #666;
        }
        
        .main-content {
            display: table;
            width: 100%;
            background: #fff;
        }
        
        .left-section {
            display: table-cell;
            width: 50%;
            padding: 6px;
            vertical-align: top;
            border-right: 1px solid #dee2e6;
        }
        
        .right-section {
            display: table-cell;
            width: 50%;
            padding: 6px;
            vertical-align: top;
        }
        
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #d63384;
            margin-bottom: 6px;
            padding-bottom: 1px;
            border-bottom: 1px solid #d63384;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            font-size: 7px;
            color: #666;
            vertical-align: top;
            padding-right: 3px;
        }
        
        .info-value {
            display: table-cell;
            width: 65%;
            font-size: 7px;
            font-weight: 500;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 1px;
        }
        
        .amount-highlight {
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #d63384;
            border-radius: 6px;
            padding: 6px;
            margin: 6px 0;
        }
        
        .amount-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 2px;
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
            border-top: 1px solid #dee2e6;
            padding-top: 2px;
        }
        
        .donation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 7px;
        }
        
        .donation-table th,
        .donation-table td {
            border: 1px solid #dee2e6;
            padding: 4px;
            text-align: left;
        }
        
        .donation-table th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 7px;
            text-align: center;
        }
        
        .payment-methods {
            margin: 4px 0;
        }
        
        .method-item {
            display: inline-block;
            margin: 1px;
            padding: 2px 4px;
            border-radius: 8px;
            font-size: 6px;
            border: 1px solid #dee2e6;
        }
        
        .method-active {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .method-inactive {
            background: #f8f9fa;
            color: #666;
        }
        
        .tax-section {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 3px;
            padding: 6px;
            margin: 6px;
            font-size: 7px;
        }
        
        .tax-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 4px;
            text-align: center;
        }
        
        .npwp-row {
            margin: 4px 0;
        }
        
        .npwp-field {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 100px;
            height: 15px;
            margin-left: 6px;
        }
        
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 4px;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            padding-right: 4px;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            padding-left: 4px;
        }
        
        .signature-box {
            border: 1px solid #dee2e6;
            height: 30px;
            text-align: center;
            padding: 3px;
            background: #fff;
        }
        
        .signature-label {
            font-size: 6px;
            color: #666;
            margin-bottom: 1px;
        }
        
        .qr-code {
            width: 40px;
            height: 40px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            margin: 4px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5px;
            color: #666;
        }
        
        .footer {
            background: #d63384;
            color: white;
            padding: 4px 6px;
            text-align: center;
            font-size: 6px;
        }
        
        .footer-org {
            font-weight: bold;
            margin-bottom: 1px;
        }
        
        .footer-contact {
            display: table;
            width: 100%;
            margin-top: 2px;
        }
        
        .footer-left {
            display: table-cell;
            width: 70%;
            text-align: left;
        }
        
        .footer-right {
            display: table-cell;
            width: 30%;
            text-align: right;
        }
        
        .checkbox {
            width: 8px;
            height: 8px;
            border: 1px solid #333;
            display: inline-block;
            text-align: center;
            line-height: 6px;
            margin-right: 3px;
            font-size: 5px;
        }
        
        .checkbox-checked {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="invoice-title">INVOICE <?php echo e($transactionType); ?></div>
                <div class="invoice-subtitle"><?php echo e($invoiceNumber); ?></div>
                <div class="badges">
                    <span class="badge">TERAKREDITASI</span>
                    <span class="badge">WTP</span>
                </div>
            </div>
            <div class="header-right">
                <div class="org-info">
                    <div style="font-weight: bold;">♥ INSAN MADANI JAMBI</div>
                    <div>LEMBAGA AMIL ZAKAT</div>
                    <div>No. SK 378/2018</div>
                </div>
            </div>
        </div>

        <!-- Bismillah -->
        <div class="bismillah">
            بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ<br>
            <em>LAZ Yayasan Insan Madani Jambi - Terima kasih atas amanah Anda</em>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Section: Informasi Donatur -->
            <div class="left-section">
                <div class="section-title">INFORMASI <?php echo e($personLabel); ?></div>
                
                <div class="info-row">
                    <div class="info-label">Tanggal:</div>
                    <div class="info-value"><?php echo e($tanggalFormatted); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Nama <?php echo e($personLabel); ?>:</div>
                    <div class="info-value"><?php echo e($nama); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">ID <?php echo e($personLabel); ?>:</div>
                    <div class="info-value"><?php echo e($donaturId); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Alamat:</div>
                    <div class="info-value"><?php echo e($alamat); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">No. Telepon:</div>
                    <div class="info-value"><?php echo e($telepon); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Cabang:</div>
                    <div class="info-value">Jambi</div>
                </div>

                <!-- Payment Methods -->
                <div style="margin-top: 8px;">
                    <div style="font-size: 7px; font-weight: bold; margin-bottom: 3px;">Metode Pembayaran:</div>
                    <div class="payment-methods">
                        <?php
                            $metodeLower = strtolower($metodePembayaran);
                            $isTunai = in_array($metodeLower, ['tunai', 'cash', 'uang tunai']);
                            $isTransfer = stripos($metodePembayaran, 'transfer') !== false || 
                                         stripos($metodePembayaran, 'bank') !== false || 
                                         stripos($metodePembayaran, 'bri') !== false || 
                                         stripos($metodePembayaran, 'bca') !== false || 
                                         stripos($metodePembayaran, 'mandiri') !== false || 
                                         stripos($metodePembayaran, 'bni') !== false ||
                                         stripos($metodePembayaran, 'va') !== false ||
                                         stripos($metodePembayaran, 'virtual') !== false;
                            $isKartu = stripos($metodePembayaran, 'card') !== false || 
                                      stripos($metodePembayaran, 'debit') !== false || 
                                      stripos($metodePembayaran, 'kredit') !== false ||
                                      stripos($metodePembayaran, 'visa') !== false ||
                                      stripos($metodePembayaran, 'mastercard') !== false;
                            $isEwallet = stripos($metodePembayaran, 'ovo') !== false || 
                                        stripos($metodePembayaran, 'gopay') !== false || 
                                        stripos($metodePembayaran, 'dana') !== false ||
                                        stripos($metodePembayaran, 'shopeepay') !== false ||
                                        stripos($metodePembayaran, 'linkaja') !== false ||
                                        stripos($metodePembayaran, 'wallet') !== false;
                            $isQris = stripos($metodePembayaran, 'qris') !== false || 
                                     stripos($metodePembayaran, 'qr') !== false;
                        ?>
                        
                        <span class="method-item <?php echo e($isTunai ? 'method-active' : 'method-inactive'); ?>">
                            💰 Tunai
                        </span>
                        <span class="method-item <?php echo e($isTransfer ? 'method-active' : 'method-inactive'); ?>">
                            🏦 Transfer
                        </span>
                        <span class="method-item <?php echo e($isKartu ? 'method-active' : 'method-inactive'); ?>">
                            💳 Kartu
                        </span>
                        <span class="method-item <?php echo e($isEwallet ? 'method-active' : 'method-inactive'); ?>">
                            📱 E-Wallet
                        </span>
                        <span class="method-item <?php echo e($isQris ? 'method-active' : 'method-inactive'); ?>">
                            � QRIS
                        </span>
                    </div>
                    
                    <!-- Tampilkan metode pembayaran yang dipilih -->
                    <div style="margin-top: 3px; font-size: 6px; color: #d63384; font-weight: bold;">
                        Dipilih: <?php echo e($metodePembayaran); ?>

                    </div>
                </div>
            </div>
            
            <!-- Right Section: Detail Transaksi -->
            <div class="right-section">
                <div class="section-title">DETAIL <?php echo e($transactionType); ?></div>
                
                <!-- Transaction Table -->
                <table class="donation-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Jenis <?php echo e($transactionType); ?></th>
                            <th style="width: 60%;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="margin-bottom: 2px;">
                                    <span class="checkbox <?php echo e($isZakat ? 'checkbox-checked' : ''); ?>"><?php echo e($isZakat ? '✓' : ''); ?></span>
                                    Zakat
                                </div>
                                <div style="margin-bottom: 2px;">
                                    <span class="checkbox <?php echo e(!$isZakat ? 'checkbox-checked' : ''); ?>"><?php echo e(!$isZakat ? '✓' : ''); ?></span>
                                    Infaq/Sedekah/Donasi
                                </div>
                                
                                <!-- Detail Jenis Spesifik -->
                                <div style="margin-top: 4px; font-size: 6px; color: #666; border-top: 1px dotted #ccc; padding-top: 2px;">
                                    <strong>Kategori:</strong><br>
                                    <?php echo e($jenisTransaksi); ?>

                                </div>
                            </td>
                            <td>
                                <strong><?php echo e($jenisTransaksi); ?></strong><br>
                                <div style="margin-top: 2px; font-size: 6px; color: #666;">
                                    <?php echo e($keterangan); ?>

                                </div>
                                
                                <?php if($isZakat): ?>
                                    <div style="margin-top: 3px; padding: 2px; background: #e8f5e8; border-radius: 2px; font-size: 6px;">
                                        <strong>Zakat:</strong> Kewajiban agama
                                    </div>
                                <?php else: ?>
                                    <div style="margin-top: 3px; padding: 2px; background: #fff3cd; border-radius: 2px; font-size: 6px;">
                                        <strong>Donasi:</strong> Amal jariyah
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Amount Highlight -->
                <div class="amount-highlight">
                    <div class="amount-label">TOTAL <?php echo e($transactionType); ?></div>
                    <div class="amount-value">Rp <?php echo e(number_format($jumlahNumeric, 0, ',', '.')); ?></div>
                    <div class="amount-words"><?php echo e($jumlahTerbilang); ?></div>
                </div>

                <!-- QR Code -->
                <div class="qr-code">
                    <div>
                        QR CODE<br>
                        <small>Donasi Digital</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Section -->
        <div class="tax-section">
            <div class="tax-title">UNTUK PENGURANGAN PAJAK PENGHASILAN</div>
            <div class="npwp-row">
                <strong>NPWP:</strong>
                <span class="npwp-field"></span>
                <span style="margin-left: 6px; font-size: 6px; color: #666;">
                    Lampiran SPT Tahunan KEP-163/PJ/2003
                </span>
            </div>
            
            <div class="signature-section">
                <div class="signature-left">
                    <div class="signature-label">TTD Penyetor</div>
                    <div class="signature-box">
                        <div style="margin-top: 10px; font-size: 6px; color: #666;">
                            <?php echo e($nama); ?>

                        </div>
                    </div>
                </div>
                <div class="signature-right">
                    <div class="signature-label">Pengesahan</div>
                    <div class="signature-box">
                        <div style="color: #d63384; font-weight: bold; margin-top: 6px;">
                            ♥ INSAN MADANI
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-org">
                LAZ INSAN MADANI JAMBI - LEMBAGA AMIL ZAKAT RESMI SKALA PROVINSI<br>
                SK KEMENTERIAN AGAMA RI NO. 378 TAHUN 2018
            </div>
            <div class="footer-contact">
                <div class="footer-left">
                    Jl. Otto Iskandardinata No. 15, Sei Asam, Pasar Jambi ☎ 0811.743.1231
                </div>
                <div class="footer-right">
                    🌐 insanmadanijambi.org
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\laragon\www\madaniX\resources\views/invoices/donation-form-invoice.blade.php ENDPATH**/ ?>