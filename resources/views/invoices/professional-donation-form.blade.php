<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Setoran Donasi</title>
    <style>
        @page {
            margin: 10mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 5px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: center;
        }
        
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        
        .subtitle {
            font-size: 11px;
            font-style: italic;
            color: #000;
            margin-bottom: 8px;
        }
        
        .bismillah {
            font-size: 10px;
            margin-bottom: 8px;
            font-style: italic;
        }
        
        .organization-info {
            font-size: 9px;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .certificates {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .certificate {
            display: table-cell;
            width: 45px;
            height: 45px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
            vertical-align: middle;
            font-size: 6px;
            font-weight: bold;
            line-height: 1.1;
            padding: 2px;
        }
        
        .form-number {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 50px;
            border: 1px solid #000;
            margin: 0 auto 5px auto;
            text-align: center;
            line-height: 48px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .main-section {
            border: 2px solid #000;
            padding: 5px;
            margin-bottom: 5px;
        }
        
        .date-section {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .date-left {
            display: table-cell;
            width: 33%;
            vertical-align: top;
        }
        
        .date-center {
            display: table-cell;
            width: 33%;
            vertical-align: top;
            padding-left: 10px;
        }
        
        .date-right {
            display: table-cell;
            width: 34%;
            vertical-align: top;
            padding-left: 10px;
        }
        
        .form-field {
            margin-bottom: 5px;
        }
        
        .field-row {
            display: table;
            width: 100%;
        }
        
        .field-label {
            display: table-cell;
            width: 30%;
            font-size: 8px;
            vertical-align: top;
            padding-right: 5px;
        }
        
        .field-value {
            display: table-cell;
            width: 70%;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            font-size: 9px;
            font-weight: bold;
            min-height: 12px;
        }
        
        .donor-section {
            margin: 8px 0;
        }
        
        .tax-section {
            border: 1px solid #000;
            padding: 5px;
            margin: 8px 0;
            font-size: 8px;
        }
        
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 5px;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            text-align: center;
            border: 1px solid #000;
            padding: 20px 5px 5px 5px;
            font-size: 7px;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            border: 1px solid #000;
            border-left: none;
            padding: 20px 5px 5px 5px;
            font-size: 7px;
        }
        
        .donation-type-section {
            border: 1px solid #000;
            margin: 8px 0;
            display: table;
            width: 100%;
        }
        
        .donation-header {
            background-color: #000;
            color: #fff;
            padding: 3px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
        }
        
        .donation-content {
            display: table;
            width: 100%;
            padding: 5px;
        }
        
        .donation-left {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            padding-right: 5px;
            font-size: 8px;
        }
        
        .donation-right {
            display: table-cell;
            width: 75%;
            vertical-align: top;
            border-left: 1px solid #000;
            padding-left: 5px;
            min-height: 40px;
            font-size: 8px;
        }
        
        .payment-section {
            border: 1px solid #000;
            margin: 8px 0;
        }
        
        .payment-header {
            background-color: #000;
            color: #fff;
            padding: 3px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
        }
        
        .payment-content {
            display: table;
            width: 100%;
            padding: 5px;
        }
        
        .payment-left {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            padding-right: 5px;
            font-size: 8px;
        }
        
        .payment-center {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 0 5px;
            font-size: 8px;
            text-align: center;
        }
        
        .payment-right {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            padding-left: 5px;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        .checkbox {
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 3px;
            text-align: center;
            line-height: 8px;
            font-size: 8px;
            vertical-align: middle;
        }
        
        .checkbox.checked {
            background-color: #000;
            color: #fff;
        }
        
        .total-section {
            border-top: 2px solid #000;
            padding-top: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 5px 0;
        }
        
        .words-section {
            margin: 8px 0;
        }
        
        .words-box {
            border: 1px solid #000;
            padding: 5px;
            min-height: 25px;
            font-size: 8px;
        }
        
        .qr-section {
            text-align: center;
            margin: 10px 0;
        }
        
        .qr-code {
            width: 60px;
            height: 60px;
            border: 1px solid #000;
            margin: 0 auto 5px auto;
            text-align: center;
            line-height: 58px;
            font-size: 6px;
        }
        
        .footer-note {
            font-size: 7px;
            margin: 8px 0;
            text-align: justify;
            line-height: 1.2;
        }
        
        .contact-info {
            font-size: 7px;
            text-align: center;
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .italic {
            font-style: italic;
        }
        
        .center {
            text-align: center;
        }
    </style>
</head>
<<body>
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <div class="header-left">
            <div class="title">Formulir Setoran Donasi</div>
            <div class="subtitle">Deposit Donations Form</div>
            
            <div class="bismillah">bismillaahirrahmaanirrahiim</div>
            
            <div class="organization-info">
                <span class="bold">kepada to LAZ Yayasan Insan Madani Jambi</span><br>
                mohon dicatat transaksi berikut <span class="italic">please record this transaction</span>
            </div>
        </div>
        
        <div class="header-right">
            <!-- Certificates Section -->
            <div class="certificates">
                <div class="certificate">PREDIKAT<br>TERBAIK<br>KUALITAS<br>SKALA<br>BAIK</div>
                <div class="certificate">OPINI<br>AUDIT<br>KEUANGAN<br>WTP</div>
            </div>
            
            <div class="form-number">No. {{ $invoice_number ?? '24' }}</div>
            
            <div class="logo-placeholder">
                Insan<br>Madani<br>Jambi
            </div>
            
            <div style="font-size: 8px; margin-top: 5px;">
                <span class="bold">LEMBAGA AMIL ZAKAT</span>
            </div>
        </div>
    </div>
    
    <!-- Main Form Section -->
    <div class="main-section">
        <!-- Date and Branch Section -->
        <div class="date-section">
            <div class="date-left">
                <div class="form-field">
                    <div class="field-row">
                        <div class="field-label">tanggal <span class="italic">date</span></div>
                        <div class="field-value">{{ $donasi->tanggal_donasi ? $donasi->tanggal_donasi->format('d/m/Y') : date('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
            <div class="date-center">
                <div class="form-field">
                    <div class="field-row">
                        <div class="field-label">cabang <span class="italic">branch</span></div>
                        <div class="field-value">Jambi</div>
                    </div>
                </div>
            </div>
            <div class="date-right">
                <div class="form-field">
                    <div class="field-row">
                        <div class="field-label">valute <span class="italic">currency</span></div>
                        <div class="field-value">IDR</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Donor Information Section -->
        <div class="donor-section">
            <div class="form-field">
                <div class="field-row">
                    <div class="field-label">nama<br><span class="italic">name</span></div>
                    <div class="field-value">{{ $donatur->nama ?? 'N/A' }}</div>
                </div>
            </div>
            
            <div class="form-field">
                <div class="field-row">
                    <div class="field-label">ID donatur<br><span class="italic">donor ID</span></div>
                    <div class="field-value">{{ $donatur->kode_donatur ?? 'N/A' }}</div>
                </div>
            </div>
            
            <div class="form-field">
                <div class="field-row">
                    <div class="field-label">alamat<br><span class="italic">address</span></div>
                    <div class="field-value">{{ $donatur->alamat_lengkap ?? 'N/A' }}</div>
                </div>
            </div>
            
            <div class="form-field">
                <div class="field-row">
                    <div class="field-label">nomor telepon<br><span class="italic">phone number</span></div>
                    <div class="field-value">{{ $donatur->nomor_hp ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Tax Section -->
        <div class="tax-section">
            <div class="bold">untuk pengurang pajak penghasilan</div>
            <div class="italic">for income tax deduction</div>
            
            <div class="form-field" style="margin-top: 5px;">
                <div class="field-row">
                    <div class="field-label">Nomor Pokok Wajib Pajak (NPWP)</div>
                    <div class="field-value">{{ $donatur->npwp ?? '' }}</div>
                </div>
            </div>
            
            <div style="font-size: 7px; margin: 5px 0;">
                Diisi sebagai lampiran SPT Tahunan Pajak Penghasilan untuk pengurang Penghasilan<br>
                Kena Pajak (PKP) sesuai keputusan Dirjen Pajak No. KEP-163/PJ/2003
            </div>
            
            <div class="signature-section">
                <div class="signature-left">
                    Tanda Tangan Penyetor<br>
                    <span class="italic">depositor signature</span>
                </div>
                <div class="signature-right">
                    Pengesahan Petugas Amil<br>
                    <span class="italic">amil officer authentication</span>
                </div>
            </div>
        </div>
        
        <!-- Donation Type Section -->
        <div class="donation-type-section">
            <div class="donation-header">
                jenis donasi&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;uraian&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nominal<br>
                <span class="italic">kind of donation&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;note&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;amount</span>
            </div>
            
            <div class="donation-content">
                <div class="donation-left">
                    @php
                        $jenis_donasi_lower = strtolower($jenis_donasi ?? '');
                        $isZakat = str_contains($jenis_donasi_lower, 'zakat');
                        $isInfak = str_contains($jenis_donasi_lower, 'infaq') || str_contains($jenis_donasi_lower, 'infak');
                        $isInfakDSKL = str_contains($jenis_donasi_lower, 'dskl');
                    @endphp
                    
                    <div style="margin-bottom: 8px;">
                        <span class="checkbox {{ $isZakat ? 'checked' : '' }}">{{ $isZakat ? '✓' : '' }}</span> zakat<br>
                        <span class="checkbox {{ $isZakat ? 'checked' : '' }}">{{ $isZakat ? '✓' : '' }}</span> <span class="italic">zakah</span>
                    </div>
                    
                    <div style="margin-bottom: 8px;">
                        <span class="checkbox {{ ($isInfak && !$isInfakDSKL) ? 'checked' : '' }}">{{ ($isInfak && !$isInfakDSKL) ? '✓' : '' }}</span> infak/ sedekah<br>
                        <span class="checkbox {{ ($isInfak && !$isInfakDSKL) ? 'checked' : '' }}">{{ ($isInfak && !$isInfakDSKL) ? '✓' : '' }}</span> <span class="italic">donation</span>
                    </div>
                    
                    <div>
                        <span class="checkbox {{ $isInfakDSKL ? 'checked' : '' }}">{{ $isInfakDSKL ? '✓' : '' }}</span> infak DSKL
                    </div>
                </div>
                <div class="donation-right">
                    {{ $keterangan ?? $donasi->keterangan_infak_khusus ?? $donasi->catatan_donatur ?? '' }}
                    <br><br>
                    <div class="bold center" style="font-size: 10px;">
                        Rp {{ number_format($donasi->jumlah, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Method Section -->
        <div class="payment-section">
            <div class="payment-header">
                berupa&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;penerbit/ nomor&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nominal<br>
                <span class="italic">consist of&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;issued by/ number&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;amount</span>
            </div>
            
            <div class="payment-content">
                <div class="payment-left">
                    @php
                        $metodePembayaran = strtolower($donasi->metodePembayaran->nama ?? '');
                        $isCash = str_contains($metodePembayaran, 'tunai') || str_contains($metodePembayaran, 'cash');
                        $isTransfer = str_contains($metodePembayaran, 'transfer') || str_contains($metodePembayaran, 'bank');
                        $isCekDebet = str_contains($metodePembayaran, 'cek') || str_contains($metodePembayaran, 'debet');
                        $isBarang = str_contains($metodePembayaran, 'barang') || str_contains($metodePembayaran, 'goods');
                    @endphp
                    
                    <div style="margin-bottom: 5px;">
                        <span class="checkbox {{ $isCash ? 'checked' : '' }}">{{ $isCash ? '✓' : '' }}</span> tunai<br>
                        <span class="checkbox {{ $isCash ? 'checked' : '' }}">{{ $isCash ? '✓' : '' }}</span> <span class="italic">cash</span>
                    </div>
                    
                    <div style="margin-bottom: 5px;">
                        <span class="checkbox {{ $isTransfer ? 'checked' : '' }}">{{ $isTransfer ? '✓' : '' }}</span> transfer<br>
                        <span class="checkbox {{ $isTransfer ? 'checked' : '' }}">{{ $isTransfer ? '✓' : '' }}</span> <span class="italic">transfer</span>
                    </div>
                    
                    <div style="margin-bottom: 5px;">
                        <span class="checkbox {{ $isCekDebet ? 'checked' : '' }}">{{ $isCekDebet ? '✓' : '' }}</span> cek/ debet<br>
                        <span class="checkbox {{ $isCekDebet ? 'checked' : '' }}">{{ $isCekDebet ? '✓' : '' }}</span> <span class="italic">cheque/ debit card</span>
                    </div>
                    
                    <div>
                        <span class="checkbox {{ $isBarang ? 'checked' : '' }}">{{ $isBarang ? '✓' : '' }}</span> barang/ jasa<br>
                        <span class="checkbox {{ $isBarang ? 'checked' : '' }}">{{ $isBarang ? '✓' : '' }}</span> <span class="italic">goods/ services</span>
                    </div>
                </div>
                <div class="payment-center">
                    {{ $donasi->metodePembayaran->nama ?? 'N/A' }}<br>
                    @if($donasi->metodePembayaran && $donasi->metodePembayaran->nomor_rekening)
                        {{ $donasi->metodePembayaran->nomor_rekening }}
                    @endif
                </div>
                <div class="payment-right">
                    Rp {{ number_format($donasi->jumlah, 0, ',', '.') }}
                </div>
            </div>
            
            <div class="total-section">
                <span class="bold">TOTAL</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp {{ number_format($donasi->jumlah, 0, ',', '.') }}
            </div>
        </div>
        
        <!-- Words Section -->
        <div class="words-section">
            <div class="bold">terbilang</div> <span class="italic">in words</span>
            <div class="words-box">
                @php
                    function terbilang($angka) {
                        $angka = abs($angka);
                        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
                        $temp = "";
                        
                        if ($angka < 12) {
                            $temp = " " . $huruf[$angka];
                        } else if ($angka < 20) {
                            $temp = terbilang($angka - 10) . " belas";
                        } else if ($angka < 100) {
                            $temp = terbilang($angka / 10) . " puluh" . terbilang($angka % 10);
                        } else if ($angka < 200) {
                            $temp = " seratus" . terbilang($angka - 100);
                        } else if ($angka < 1000) {
                            $temp = terbilang($angka / 100) . " ratus" . terbilang($angka % 100);
                        } else if ($angka < 2000) {
                            $temp = " seribu" . terbilang($angka - 1000);
                        } else if ($angka < 1000000) {
                            $temp = terbilang($angka / 1000) . " ribu" . terbilang($angka % 1000);
                        } else if ($angka < 1000000000) {
                            $temp = terbilang($angka / 1000000) . " juta" . terbilang($angka % 1000000);
                        } else if ($angka < 1000000000000) {
                            $temp = terbilang($angka / 1000000000) . " milyar" . terbilang(fmod($angka, 1000000000));
                        }
                        
                        return $temp;
                    }
                @endphp
                {{ ucwords(trim(terbilang($donasi->jumlah))) }} rupiah
            </div>
        </div>
    </div>
    
    <!-- QR Code Section -->
    <div class="qr-section">
        <div class="qr-code">
            QR CODE<br>
            PLACEHOLDER
        </div>
        <div style="font-size: 7px; margin-top: 3px;">
            kunjungi Rumah Quran digital<br>
            <span class="bold">rumahquran.insanmadanijambi.org</span>
        </div>
    </div>
    
    <!-- Footer Notes -->
    <div class="footer-note">
        <span class="bold">Wajib disimpan sebagai bukti sahih untuk masa uang kepada yang mengembalikan tidak merawat</span><br>
        <span class="italic">keep this donation receipt evidence as they do not conform with regulations and does not constitute money laundering</span><br><br>
        
        <span class="bold">LAZ INSAN MADANI JAMBI - LEMBAGA AMIL ZAKAT RESMI SKALA PROVINSI</span><br>
        <span class="italic">DISTRIBUSI BERKAS ISLAM KEMENTERIAN AGAMA REPUBLIK INDONESIA</span><br><br>
        
        semoga Allah membalikkan pahala atas apa yang telah Anda berikan, menjadikaannya suci<br>
        dan mensucikan, serta Allah memberikan keberkahan atas harta Anda yang tersisa<br>
        <span class="italic">(Doa untuk penyumbang Zakat)</span>
    </div>
    
    <!-- Contact Information -->
    <div class="contact-info">
        <span class="bold">Head Office :</span> Jl. Otto Iskandardinata No. 15 Kel. Sei Asam Kec. Pasar Jambi Kota Jambi &nbsp;&nbsp;&nbsp; 📞 0811.743.1231<br>
        📷 📺 📘 <span class="bold">insanmadanijambi.org</span>
    </div>
</div>
</body>
</html>
