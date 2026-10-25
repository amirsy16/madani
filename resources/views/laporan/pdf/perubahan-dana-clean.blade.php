<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perubahan Dana — LAZ Insan Madani Jambi</title>
    <style>
        @page { margin: 15mm 14mm 16mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            color: #1f1f1f;
            margin: 0;
        }

        /* ---------- Kop dokumen ---------- */
        .doc-header { width: 100%; }
        .doc-header td { vertical-align: middle; }
        .logo-cell { width: 88px; text-align: left; }
        .logo-cell img { width: 72px; height: auto; }
        .org-name { font-size: 12pt; font-weight: bold; color: #800020; }
        .doc-title { font-size: 15pt; font-weight: bold; margin-top: 2px; }
        .doc-period { font-size: 9.5pt; color: #444444; margin-top: 3px; }
        .rule-thick { border-bottom: 2.5px solid #800020; margin-top: 10px; }
        .rule-thin { border-bottom: 0.5px solid #800020; margin-top: 1.5px; }

        /* ---------- Tabel per sumber dana ---------- */
        table.fund { width: 100%; border-collapse: collapse; margin-top: 30px; }
        table.fund th {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #800020;
            border-bottom: 1px solid #800020;
            padding: 4px 4px 5px 4px;
        }
        th.col-ket { text-align: left; }
        th.col-jml { text-align: right; }

        table.fund td { padding: 3.5px 4px; vertical-align: top; }
        td.amt { text-align: right; white-space: nowrap; }

        tr { page-break-inside: avoid; }

        tr.fund-title td {
            font-size: 11pt;
            font-weight: bold;
            color: #800020;
            border-left: 3px solid #800020;
            padding: 12px 0 5px 8px;
        }
        tr.group td { font-weight: bold; padding-top: 9px; }
        tr.subgroup td { font-weight: bold; padding-top: 7px; font-size: 9.5pt; }
        tr.item td { color: #333333; border-bottom: 0.5px solid #ececec; padding-left: 20px; }
        tr.item td.amt { color: #1f1f1f; }
        tr.empty td {
            font-style: italic;
            color: #8a8a8a;
            padding-left: 20px;
            font-size: 9.5pt;
        }
        tr.sum td { font-weight: bold; border-top: 1px solid #b7b7b7; padding-top: 5px; }
        tr.sum-strong td {
            font-weight: bold;
            border-top: 1px solid #800020;
            border-bottom: 1px solid #800020;
        }
        tr.result td { font-weight: bold; padding-top: 6px; }
        td.pos { color: #15803d; }
        td.neg { color: #b91c1c; }
        tr.saldo td {
            background-color: #f9eef1;
            font-weight: bold;
            border-bottom: 2px solid #800020;
            padding: 6px 4px 6px 8px;
        }
        tr.saldo td.amt { color: #800020; }

        /* ---------- Total gabungan ---------- */
        table.grand { width: 100%; border-collapse: collapse; margin-top: 18px; page-break-inside: avoid; }
        table.grand td {
            border-top: 2px solid #800020;
            border-bottom: 2px solid #800020;
            font-weight: bold;
            font-size: 10.5pt;
            padding: 8px 4px;
        }
        table.grand td.amt { color: #800020; white-space: nowrap; }

        /* ---------- Tanda tangan ---------- */
        .sig-date { text-align: right; margin-top: 26px; font-size: 10pt; }
        table.sig { width: 100%; margin-top: 4px; page-break-inside: avoid; }
        table.sig td { width: 50%; text-align: center; vertical-align: top; padding-top: 6px; }
        .sig-role { font-weight: bold; }
    </style>
</head>
<body>

@php
    // Locale Indonesia agar nama bulan pada kop & tanda tangan lokal (bukan "January" dst).
    \Carbon\Carbon::setLocale('id');

    // Format angka: 1234567 => "1.234.567"; negatif ditampilkan (1.234.567).
    $fmt = fn ($v) => number_format((float) ($v ?? 0), 0, ',', '.');
    $fmtNeg = function ($v) {
        $v = (float) ($v ?? 0);

        return $v < 0 ? '(' . number_format(abs($v), 0, ',', '.') . ')' : number_format($v, 0, ',', '.');
    };
    $signClass = fn ($v) => (float) ($v ?? 0) < 0 ? 'neg' : 'pos';
@endphp

{{-- ============================ KOP DOKUMEN ============================ --}}
<table class="doc-header">
    <tr>
        <td class="logo-cell">
            {{-- Logo base64 agar tidak bergantung path file saat rendering DomPDF --}}
            @if(!empty($logoBase64))
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo LAZ Insan Madani Jambi">
            @else
                <div style="width: 72px; height: 72px; border: 1px solid #800020; color: #800020; font-size: 7pt; text-align: center; line-height: 70px;">LOGO</div>
            @endif
        </td>
        <td style="text-align: center;">
            <div class="org-name">LAZ INSAN MADANI JAMBI</div>
            <div class="doc-title">LAPORAN PERUBAHAN DANA</div>
            <div class="doc-period">Periode {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</div>
        </td>
        <td style="width: 88px;"></td>
    </tr>
</table>
<div class="rule-thick"></div>
<div class="rule-thin"></div>

{{-- ============================ A. DANA ZAKAT ============================ --}}
@php
    $zakat = $reportData[1] ?? [];
    $rincianZakat = $zakat['rincian_penerimaan'] ?? [];
    $rincianAsnaf = $zakat['rincian_penyaluran_asnaf'] ?? [];
    $rincianProgramZakat = $zakat['rincian_penyaluran'] ?? [];
@endphp
<table class="fund">
    <thead>
        <tr>
            <th class="col-ket">Keterangan</th>
            <th class="col-jml">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fund-title"><td colspan="2">A. DANA ZAKAT</td></tr>
        <tr class="group"><td>1. Penerimaan Dana</td><td class="amt"></td></tr>
        @forelse($rincianZakat as $jenis => $jumlah)
            <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penerimaan</td><td class="amt">{{ $fmt($zakat['penerimaan'] ?? 0) }}</td></tr>
        <tr class="item"><td>Dikurangi: Bagian Amil</td><td class="amt">{{ $fmt($zakat['bagian_amil'] ?? 0) }}</td></tr>
        <tr class="sum"><td>Penerimaan Setelah Bagian Amil</td><td class="amt">{{ $fmt(($zakat['penerimaan'] ?? 0) - ($zakat['bagian_amil'] ?? 0)) }}</td></tr>

        <tr class="group"><td>2. Penyaluran Dana</td><td class="amt"></td></tr>
        <tr class="subgroup"><td>2.1 Penyaluran berdasarkan Asnaf</td><td class="amt"></td></tr>
        @forelse($rincianAsnaf as $asnaf => $jumlah)
            <tr class="item"><td>{{ $asnaf }}</td><td class="amt">{{ $jumlah > 0 ? $fmt($jumlah) : '-' }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penyaluran per Asnaf</td><td class="amt">{{ $fmt(array_sum($rincianAsnaf)) }}</td></tr>
        <tr class="subgroup"><td>2.2 Penyaluran berdasarkan Bidang Program</td><td class="amt"></td></tr>
        @forelse($rincianProgramZakat as $program => $jumlah)
            <tr class="item"><td>{{ $program }}</td><td class="amt">{{ $jumlah > 0 ? $fmt($jumlah) : '-' }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penyaluran per Bidang Program</td><td class="amt">{{ $fmt(array_sum($rincianProgramZakat)) }}</td></tr>
        <tr class="sum-strong"><td>Total Penyaluran Dana Zakat</td><td class="amt">{{ $fmt($zakat['penyaluran'] ?? 0) }}</td></tr>

        @php

            $surplusZakat = (float) ($zakat['surplus_defisit'] ?? 0);

        @endphp
        <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusZakat) }}">{{ $fmtNeg($surplusZakat) }}</td></tr>
        <tr class="result"><td>Saldo Awal</td><td class="amt">{{ $fmt($zakat['saldo_awal'] ?? 0) }}</td></tr>
        <tr class="saldo"><td>Saldo Akhir Dana Zakat</td><td class="amt">{{ $fmt($zakat['saldo_akhir'] ?? 0) }}</td></tr>
    </tbody>
</table>

{{-- ============================ B. DANA INFAQ/SEDEKAH ============================ --}}
@php
    $infaq = $reportData[2] ?? [];
    $rincianInfaq = $infaq['rincian_penerimaan'] ?? [];
    $rincianProgramInfaq = $infaq['rincian_penyaluran'] ?? [];
@endphp
<table class="fund">
    <thead>
        <tr>
            <th class="col-ket">Keterangan</th>
            <th class="col-jml">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fund-title"><td colspan="2">B. DANA INFAQ/SEDEKAH</td></tr>
        <tr class="group"><td>1. Penerimaan Dana</td><td class="amt"></td></tr>
        @forelse($rincianInfaq as $jenis => $jumlah)
            <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penerimaan</td><td class="amt">{{ $fmt($infaq['penerimaan'] ?? 0) }}</td></tr>
        <tr class="item"><td>Dikurangi: Bagian Amil</td><td class="amt">{{ $fmt($infaq['bagian_amil'] ?? 0) }}</td></tr>
        <tr class="sum"><td>Penerimaan Setelah Bagian Amil</td><td class="amt">{{ $fmt(($infaq['penerimaan'] ?? 0) - ($infaq['bagian_amil'] ?? 0)) }}</td></tr>

        <tr class="group"><td>2. Penyaluran Dana berdasarkan Bidang Program</td><td class="amt"></td></tr>
        @forelse($rincianProgramInfaq as $program => $jumlah)
            <tr class="item"><td>{{ $program }}</td><td class="amt">{{ $jumlah > 0 ? $fmt($jumlah) : '-' }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum-strong"><td>Total Penyaluran Dana Infaq/Sedekah</td><td class="amt">{{ $fmt($infaq['penyaluran'] ?? 0) }}</td></tr>

        @php

            $surplusInfaq = (float) ($infaq['surplus_defisit'] ?? 0);

        @endphp
        <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusInfaq) }}">{{ $fmtNeg($surplusInfaq) }}</td></tr>
        <tr class="result"><td>Saldo Awal</td><td class="amt">{{ $fmt($infaq['saldo_awal'] ?? 0) }}</td></tr>
        <tr class="saldo"><td>Saldo Akhir Dana Infaq/Sedekah</td><td class="amt">{{ $fmt($infaq['saldo_akhir'] ?? 0) }}</td></tr>
    </tbody>
</table>

{{-- ============================ C. DANA CSR ============================ --}}
@php
    $csr = $reportData[3] ?? [];
    $rincianCsr = $csr['rincian_penerimaan'] ?? [];
    $rincianProgramCsr = $csr['rincian_penyaluran'] ?? [];
@endphp
<table class="fund">
    <thead>
        <tr>
            <th class="col-ket">Keterangan</th>
            <th class="col-jml">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fund-title"><td colspan="2">C. DANA CORPORATE SOCIAL RESPONSIBILITY (CSR)</td></tr>
        <tr class="group"><td>1. Penerimaan Dana</td><td class="amt"></td></tr>
        @forelse($rincianCsr as $jenis => $jumlah)
            <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penerimaan</td><td class="amt">{{ $fmt($csr['penerimaan'] ?? 0) }}</td></tr>
        <tr class="item"><td>Dikurangi: Bagian Amil</td><td class="amt">{{ $fmt($csr['bagian_amil'] ?? 0) }}</td></tr>
        <tr class="sum"><td>Penerimaan Setelah Bagian Amil</td><td class="amt">{{ $fmt(($csr['penerimaan'] ?? 0) - ($csr['bagian_amil'] ?? 0)) }}</td></tr>

        <tr class="group"><td>2. Penyaluran Dana berdasarkan Bidang Program</td><td class="amt"></td></tr>
        @forelse($rincianProgramCsr as $program => $jumlah)
            <tr class="item"><td>{{ $program }}</td><td class="amt">{{ $jumlah > 0 ? $fmt($jumlah) : '-' }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum-strong"><td>Total Penyaluran Dana CSR</td><td class="amt">{{ $fmt($csr['penyaluran'] ?? 0) }}</td></tr>

        @php

            $surplusCsr = (float) ($csr['surplus_defisit'] ?? 0);

        @endphp
        <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusCsr) }}">{{ $fmtNeg($surplusCsr) }}</td></tr>
        <tr class="result"><td>Saldo Awal</td><td class="amt">{{ $fmt($csr['saldo_awal'] ?? 0) }}</td></tr>
        <tr class="saldo"><td>Saldo Akhir Dana CSR</td><td class="amt">{{ $fmt($csr['saldo_akhir'] ?? 0) }}</td></tr>
    </tbody>
</table>

{{-- ============================ D. DANA SOSIAL KEAGAMAAN LAINNYA ============================ --}}
@php
    $dskl = $reportData[4] ?? [];
    $rincianDskl = $dskl['rincian_penerimaan'] ?? [];
    $rincianProgramDskl = $dskl['rincian_penyaluran'] ?? [];
@endphp
<table class="fund">
    <thead>
        <tr>
            <th class="col-ket">Keterangan</th>
            <th class="col-jml">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fund-title"><td colspan="2">D. DANA SOSIAL KEAGAMAAN LAINNYA</td></tr>
        <tr class="group"><td>1. Penerimaan Dana</td><td class="amt"></td></tr>
        @forelse($rincianDskl as $jenis => $jumlah)
            <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penerimaan</td><td class="amt">{{ $fmt($dskl['penerimaan'] ?? 0) }}</td></tr>
        <tr class="item"><td>Dikurangi: Bagian Amil</td><td class="amt">{{ $fmt($dskl['bagian_amil'] ?? 0) }}</td></tr>
        <tr class="sum"><td>Penerimaan Setelah Bagian Amil</td><td class="amt">{{ $fmt(($dskl['penerimaan'] ?? 0) - ($dskl['bagian_amil'] ?? 0)) }}</td></tr>

        <tr class="group"><td>2. Penyaluran Dana berdasarkan Bidang Program</td><td class="amt"></td></tr>
        @forelse($rincianProgramDskl as $program => $jumlah)
            <tr class="item"><td>{{ $program }}</td><td class="amt">{{ $jumlah > 0 ? $fmt($jumlah) : '-' }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum-strong"><td>Total Penyaluran Dana Sosial Keagamaan Lainnya</td><td class="amt">{{ $fmt($dskl['penyaluran'] ?? 0) }}</td></tr>

        @php

            $surplusDskl = (float) ($dskl['surplus_defisit'] ?? 0);

        @endphp
        <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusDskl) }}">{{ $fmtNeg($surplusDskl) }}</td></tr>
        <tr class="result"><td>Saldo Awal</td><td class="amt">{{ $fmt($dskl['saldo_awal'] ?? 0) }}</td></tr>
        <tr class="saldo"><td>Saldo Akhir Dana Sosial Keagamaan Lainnya</td><td class="amt">{{ $fmt($dskl['saldo_akhir'] ?? 0) }}</td></tr>
    </tbody>
</table>

{{-- ============================ E. DANA NON HALAL ============================ --}}
@php
    $nonHalal = $reportData[7] ?? [];
    $rincianNonHalal = $nonHalal['rincian_penerimaan'] ?? [];
    $rincianProgramNonHalal = $nonHalal['rincian_penyaluran'] ?? [];
@endphp
<table class="fund">
    <thead>
        <tr>
            <th class="col-ket">Keterangan</th>
            <th class="col-jml">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fund-title"><td colspan="2">E. DANA NON HALAL</td></tr>
        <tr class="group"><td>1. Penerimaan Dana</td><td class="amt"></td></tr>
        @forelse($rincianNonHalal as $jenis => $jumlah)
            <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Jumlah Penerimaan</td><td class="amt">{{ $fmt($nonHalal['penerimaan'] ?? 0) }}</td></tr>
        <tr class="item"><td>Dikurangi: Bagian Amil</td><td class="amt">{{ $fmt($nonHalal['bagian_amil'] ?? 0) }}</td></tr>
        <tr class="sum"><td>Penerimaan Setelah Bagian Amil</td><td class="amt">{{ $fmt(($nonHalal['penerimaan'] ?? 0) - ($nonHalal['bagian_amil'] ?? 0)) }}</td></tr>

        <tr class="group"><td>2. Penyaluran Dana berdasarkan Bidang Program</td><td class="amt"></td></tr>
        @forelse($rincianProgramNonHalal as $program => $jumlah)
            <tr class="item"><td>{{ $program }}</td><td class="amt">{{ $jumlah > 0 ? $fmt($jumlah) : '-' }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum-strong"><td>Total Penyaluran Dana Non Halal</td><td class="amt">{{ $fmt($nonHalal['penyaluran'] ?? 0) }}</td></tr>

        @php

            $surplusNonHalal = (float) ($nonHalal['surplus_defisit'] ?? 0);

        @endphp
        <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusNonHalal) }}">{{ $fmtNeg($surplusNonHalal) }}</td></tr>
        <tr class="result"><td>Saldo Awal</td><td class="amt">{{ $fmt($nonHalal['saldo_awal'] ?? 0) }}</td></tr>
        <tr class="saldo"><td>Saldo Akhir Dana Non Halal</td><td class="amt">{{ $fmt($nonHalal['saldo_akhir'] ?? 0) }}</td></tr>
    </tbody>
</table>

{{-- ============================ F. HAK AMIL ============================ --}}
<table class="fund">
    <thead>
        <tr>
            <th class="col-ket">Keterangan</th>
            <th class="col-jml">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fund-title"><td colspan="2">F. PENERIMAAN DAN PENGGUNAAN HAK AMIL</td></tr>
        <tr class="group"><td>1. Penerimaan Hak Amil</td><td class="amt"></td></tr>
        @forelse($penerimaanHakAmilDetail ?? [] as $jenis => $jumlah)
            <tr class="item"><td>Penerimaan hak amil dari {{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Total Penerimaan Hak Amil</td><td class="amt">{{ $fmt($totalPenerimaanHakAmil ?? 0) }}</td></tr>

        <tr class="group"><td>2. Penggunaan Hak Amil</td><td class="amt"></td></tr>
        @forelse($penggunaanHakAmilDetail ?? [] as $jenis => $jumlah)
            <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
        @empty
            <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
        @endforelse
        <tr class="sum"><td>Total Penggunaan Hak Amil</td><td class="amt">{{ $fmt($totalPenggunaanHakAmil ?? 0) }}</td></tr>

        @php

            $surplusAmil = (float) ($surplusDefisitHakAmil ?? 0);

        @endphp
        <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusAmil) }}">{{ $fmtNeg($surplusAmil) }}</td></tr>
        <tr class="saldo"><td>Sisa Saldo Hak Amil</td><td class="amt">{{ $fmt(($totalPenerimaanHakAmil ?? 0) - ($totalPenggunaanHakAmil ?? 0)) }}</td></tr>
    </tbody>
</table>

{{-- ============================ G. DONASI LAINNYA (opsional) ============================ --}}
@php
    $donasiLainnya = $reportData['donasi_lainnya'] ?? [];
@endphp
@if(!empty($donasiLainnya))
    <table class="fund">
        <thead>
            <tr>
                <th class="col-ket">Keterangan</th>
                <th class="col-jml">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="fund-title"><td colspan="2">G. DONASI TANPA SUMBER DANA TERPISAH (DONASI LAINNYA)</td></tr>
            @forelse($donasiLainnya['rincian_penerimaan'] ?? [] as $jenis => $jumlah)
                <tr class="item"><td>{{ $jenis }}</td><td class="amt">{{ $fmt($jumlah) }}</td></tr>
            @empty
                <tr class="empty"><td colspan="2">Tidak ada rincian transaksi pada periode ini</td></tr>
            @endforelse
            <tr class="sum"><td>Jumlah Penerimaan</td><td class="amt">{{ $fmt($donasiLainnya['penerimaan'] ?? 0) }}</td></tr>
            @php
                $surplusLainnya = (float) ($donasiLainnya['surplus_defisit'] ?? 0);
            @endphp
            <tr class="result"><td>Surplus (Defisit)</td><td class="amt {{ $signClass($surplusLainnya) }}">{{ $fmtNeg($surplusLainnya) }}</td></tr>
            <tr class="saldo"><td>Saldo Akhir Donasi Lainnya</td><td class="amt">{{ $fmt($donasiLainnya['saldo_akhir'] ?? 0) }}</td></tr>
        </tbody>
    </table>
@endif

{{-- ============================ TOTAL GABUNGAN ============================ --}}
<table class="grand">
    <tr>
        <td>TOTAL SALDO AKHIR GABUNGAN — SELURUH SUMBER DANA</td>
        <td class="amt">{{ $fmt($totalSisaSaldo ?? 0) }}</td>
    </tr>
</table>

{{-- ============================ TANDA TANGAN ============================ --}}
<div class="sig-date">Jambi, {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</div>
<table class="sig">
    <tr>
        <td>
            Diketahui oleh,<br><br><br><br>
            ( ................................ )<br>
            <span class="sig-role">Direktur Eksekutif</span>
        </td>
        <td>
            Disusun oleh,<br><br><br><br>
            ( ................................ )<br>
            <span class="sig-role">Bendahara</span>
        </td>
    </tr>
</table>

</body>
</html>
