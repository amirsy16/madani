@php
    // ===== Data turunan untuk template invoice formal (gaya kuitansi LAZ) =====
    $lembaga = $lembaga ?? config('simadi.lembaga');

    $jenisNama = $donasi->jenisDonasi->nama ?? ($jenis_donasi ?? 'Donasi');
    $isBarang = (bool) ($donasi->jenisDonasi->apakah_barang ?? false);

    // Nilai efektif: barang dinilai dari perkiraan nilai, uang dari jumlah.
    $nilai = (float) ($isBarang
        ? ($donasi->perkiraan_nilai_barang ?? $donasi->jumlah ?? 0)
        : ($donasi->jumlah ?? $donasi->perkiraan_nilai_barang ?? 0));
    $nilaiTeks = 'Rp. ' . number_format($nilai, 0, ',', '.') . ',-';

    $deskripsiItem = $jenisNama;
    if ($isBarang && filled($donasi->deskripsi_barang)) {
        $deskripsiItem .= ' - ' . $donasi->deskripsi_barang;
    }

    $tanggalId = \Carbon\Carbon::parse($donasi->tanggal_donasi)->locale('id')->translatedFormat('d F Y');

    $namaDonatur = $donasi->donatur->nama ?? ($nama_donatur ?? 'Hamba Allah');
    $alamatDonatur = $donasi->donatur->alamat_lengkap ?? ($alamat_donatur ?? '');
    $tandaTangan = $donasi->dicatatOleh?->name ?? '.........................................';

    // Rekening tujuan: metode pembayaran donasi bila menyimpan nomor
    // rekening; selain itu rekening transfer aktif pertama.
    $m = $donasi->metodePembayaran;
    if ($m && filled($m->nomor_rekening)) {
        $bank = [
            'nama' => $m->bank_name ?: $m->nama,
            'rekening' => $m->nomor_rekening,
            'atas_nama' => $m->atas_nama_rekening ?: $lembaga['nama_tampil'],
        ];
    } else {
        $fallback = \App\Models\MetodePembayaran::where('tipe', 'transfer_bank')
            ->where('aktif', true)->orderBy('id')->first();
        $bank = $fallback ? [
            'nama' => $fallback->bank_name ?: $fallback->nama,
            'rekening' => $fallback->nomor_rekening,
            'atas_nama' => $fallback->atas_nama_rekening ?: $lembaga['nama_tampil'],
        ] : null;
    }
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice_number }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #1f1f1f;
            background: #ffffff;
            position: relative;
            padding: 14px 18px 40px 18px;
            line-height: 1.35;
        }

        .watermark {
            position: absolute;
            right: 10px;
            bottom: 18px;
            width: 190px;
        }

        .header { width: 100%; }
        .header td { vertical-align: middle; }
        .header td.brand { width: 31%; }
        .header td.brand-logo { width: 38%; text-align: center; }
        .header td.brand-right { width: 31%; text-align: right; }
        .brand-nama { font-size: 9.5px; font-weight: bold; color: #800020; letter-spacing: 0.4px; }
        .brand-sub { font-size: 6px; color: #444; margin-top: 2px; }
        .brand-legal { font-size: 5.5px; color: #666; margin-top: 1px; }
        .brand-logo img { height: 34px; }

        .judul { text-align: center; margin: 8px 0 10px 0; }
        .judul span {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 3px;
            border-bottom: 2.5px solid #1f1f1f;
            padding: 0 14px 2px 14px;
        }

        .meta { width: 100%; margin-top: 6px; }
        .meta td { vertical-align: top; }
        .meta td.kiri { width: 46%; }
        .meta td.kanan { width: 54%; padding-left: 8px; }
        .meta .label { font-weight: bold; text-decoration: underline; }
        .meta .program-nama { font-size: 9px; margin-top: 3px; }
        .meta-baris { margin-bottom: 2px; }
        .meta-baris .lbl { display: inline-block; width: 34px; font-weight: bold; }

        .blok { margin-top: 8px; }
        .blok .judul-blok { font-weight: bold; margin-bottom: 8px; }
        .blok .isi { line-height: 1.7; }

        table.rincian {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.rincian th, table.rincian td {
            border: 1px solid #1f1f1f;
            padding: 2.5px 5px;
        }
        table.rincian th { font-weight: bold; }
        table.rincian td.total { font-weight: bold; }

        .transfer { margin-top: 8px; }
        .transfer .pengantar { font-weight: bold; margin-bottom: 8px; }
        .transfer .baris { margin-bottom: 2px; }
        .transfer .baris .lbl { display: inline-block; width: 84px; font-weight: bold; }
        .transfer .baris .val { font-weight: bold; }

        .ttd { width: 100%; margin-top: 10px; }
        .ttd td { vertical-align: top; }
        .ttd td.kanan { width: 170px; text-align: center; font-weight: bold; }
        .ttd .ruang { height: 26px; }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 6px;
            text-align: center;
        }
        .footer .legal { font-size: 6px; font-weight: bold; color: #222; }
        .footer .alamat { font-size: 5.5px; color: #444; margin-top: 1px; }
        .footer .pita { height: 7px; background: #800020; margin-top: 4px; }
    </style>
</head>
<body>
    @if (! empty($watermark))
        <img class="watermark" src="{{ $watermark }}" alt="">
    @endif

    <table class="header">
        <tr>
            <td class="brand">
                <div class="brand-nama">{{ $lembaga['nama'] }}</div>
                <div class="brand-sub">{{ $lembaga['tagline'] }}</div>
                <div class="brand-legal">{{ $lembaga['legal'] }}</div>
            </td>
            <td class="brand-logo">
                @if (! empty($logo_base64))
                    <img src="{{ $logo_base64 }}" alt="Logo {{ $lembaga['nama_tampil'] }}" style="height: 64px;">
                @endif
            </td>
            <td class="brand-right">
                <div class="brand-nama">SIMADI</div>
                <div class="brand-sub">Sistem Informasi<br>Manajemen Donasi</div>
            </td>
        </tr>
    </table>

    <div class="judul"><span>INVOICE</span></div>

    <table class="meta">
        <tr>
            <td class="kiri">
                <div class="label">Program :</div>
                <div class="program-nama">{{ $jenisNama }}</div>
            </td>
            <td class="kanan">
                <div class="meta-baris"><span class="lbl">Tanggal</span> : {{ $tanggalId }}</div>
                <div class="meta-baris"><span class="lbl">No</span> : {{ $invoice_number }}</div>
                <div class="meta-baris"><span class="lbl">Telp</span> : {{ $lembaga['telp'] }}</div>
                <div class="meta-baris"><span class="lbl">Web</span> : {{ $lembaga['web'] }}</div>
            </td>
        </tr>
    </table>

    <div class="blok">
        <div class="judul-blok">Kepada :</div>
        <div class="isi">
            {{ $namaDonatur }}
            @if (filled($alamatDonatur))
                <br>{{ $alamatDonatur }}
            @endif
        </div>
    </div>

    <table class="rincian">
        <tr>
            <th style="width: 68%;">Deskripsi</th>
            <th style="width: 32%;">Jumlah</th>
        </tr>
        <tr>
            <td>{{ $deskripsiItem }}</td>
            <td>{{ $nilaiTeks }}</td>
        </tr>
        <tr>
            <td class="total">TOTAL</td>
            <td class="total">{{ $nilaiTeks }}</td>
        </tr>
    </table>

    @if ($bank)
        <div class="transfer">
            <div class="pengantar">Dana tersebut telah kami terima melalui:</div>
            <div class="baris"><span class="lbl">Nama Bank</span> : <span class="val">{{ $bank['nama'] }}</span></div>
            <div class="baris"><span class="lbl">No Rekening</span> : <span class="val">{{ $bank['rekening'] }}</span></div>
            <div class="baris"><span class="lbl">Atas Nama</span> : <span class="val">{{ $bank['atas_nama'] }}</span></div>
        </div>
    @endif

    <table class="ttd">
        <tr>
            <td></td>
            <td class="kanan">
                Hormat Kami
                <div class="ruang"></div>
                ( {{ $tandaTangan }} )
            </td>
        </tr>
    </table>

    <div class="footer">
        <div class="legal">{{ $lembaga['nama'] }} &mdash; {{ $lembaga['legal'] }}</div>
        <div class="alamat">{{ $lembaga['alamat'] }} &nbsp;&bull;&nbsp; Telp. {{ $lembaga['telp'] }} &nbsp;&bull;&nbsp; {{ $lembaga['web'] }}</div>
        <div class="pita"></div>
    </div>
</body>
</html>
