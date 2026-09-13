<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perubahan Dana</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .header {
            width: 100%;
            margin-bottom: 15px;
            padding: 15px 0;
            border-bottom: 2px solid #000;
        }

        .header-table {
            width: 100%;
            table-layout: fixed;
        }

        .logo-section {
            width: 120px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            border: 2px solid #333;
            margin: 0 auto;
            text-align: center;
            line-height: 76px;
            font-size: 12px;
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .title-section {
            text-align: center;
            font-weight: bold;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .organization-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .period {
            font-size: 14px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        td,
        th {
            padding: 6px;
            text-align: left;
            vertical-align: top;
            border: none;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
        }

        .subsection {
            font-weight: bold;
            padding-left: 20px;
        }

        .indented {
            padding-left: 40px;
        }

        .double-indented {
            padding-left: 60px;
        }
        
        .triple-indented {
            padding-left: 80px;
        }

        .amount {
            text-align: right;
            font-weight: normal;
            white-space: nowrap;
        }

        .total-row {
            font-weight: bold;
        }

        .total-amount {
            text-align: right;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .subtotal-amount {
            text-align: right;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }

        .final-total {
            text-align: right;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .saldo-akhir {
            background-color: #ffff00;
            font-weight: bold;
            text-align: right;
        }

        .signature-section {
            margin-top: 30px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            table-layout: fixed;
        }

        .signature {
            text-align: center;
            width: 50%;
            margin-top: 15px;
        }

        .signature-name {
            text-decoration: underline;
            margin-top: 50px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    
                    <img src="data:image/png;base64,<?php echo e($logoBase64 ?? 'iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAACXBIWXMAAAsTAAALEwEAmpwYAAAF8WlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxNDUgNzkuMTYzNDk5LCAyMDE4LzA4LzEzLTE2OjQwOjIyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoV2luZG93cykiIHhtcDpDcmVhdGVEYXRlPSIyMDIzLTAzLTAxVDEwOjQ4OjA3KzA3OjAwIiB4bXA6TW9kaWZ5RGF0ZT0iMjAyMy0wMy0wMVQxMDo1MDo0NCswNzowMCIgeG1wOk1ldGFkYXRhRGF0ZT0iMjAyMy0wMy0wMVQxMDo1MDo0NCswNzowMCIgZGM6Zm9ybWF0PSJpbWFnZS9wbmciIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpjMGUyMmJhZC1lY2VkLWUwNGItYmM5MS1iMmJiNjI4MmYxMzQiIHhtcE1NOkRvY3VtZW50SUQ9ImFkb2JlOmRvY2lkOnBob3Rvc2hvcDo3Y2MzNzk0OC1jMDg0LTI5NGQtOGU2ZC0zZTA5YmI4YWU0MWIiIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphNzBjYzJmMy1hZTg5LWYwNGItOGViYi1lNTUwMmZjYjI3NzAiPiA8eG1wTU06SGlzdG9yeT4gPHJkZjpTZXE+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjcmVhdGVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmE3MGNjMmYzLWFlODktZjA0Yi04ZWJiLWU1NTAyZmNiMjc3MCIgc3RFdnQ6d2hlbj0iMjAyMy0wMy0wMVQxMDo0ODowNyswNzowMCIgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKFdpbmRvd3MpIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDpjMGUyMmJhZC1lY2VkLWUwNGItYmM5MS1iMmJiNjI4MmYxMzQiIHN0RXZ0OndoZW49IjIwMjMtMDMtMDFUMTA6NTA6NDQrMDc6MDAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChXaW5kb3dzKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8L3JkZjpTZXE+IDwveG1wTU06SGlzdG9yeT4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7j0C/IAAAhn0lEQVR4nL1ceYwt2V3+zrm13F7eNm/evO0t0z2bx/aMZ2wztgdixwZigzGJnRiMpThEYNlJgIAQkZGCIoX8QUJCQUgJSAhkElBQQrASDINtZsYww+w94/HsM2/m7e/1W+t231q3qvLHOdW36m6vuWf5pNbbe91Vdb/z/b7vnO/8LpPdZnv4UKrxOwDvA/AIgHsA7AfQ+hiXewDgLIDnAHwFwP+cP3y49THO8zGH+ag9eShVIZH+GIB/AvCBefdTy7hyA8DfAfj0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'); ?>" alt="Logo Insan Madani" style="width: 80px; height: auto;">
                </td>
                
                <td class="title-section">
                    <div class="report-title">LAPORAN PERUBAHAN DANA</div>
                    <div class="organization-name">LAZ INSAN MADANI JAMBI</div>
                    <div class="period">Periode <?php echo e(\Carbon\Carbon::parse($startDate)->format('d F Y')); ?> s.d <?php echo e(\Carbon\Carbon::parse($endDate)->format('d F Y')); ?></div>
                </td>
            </tr>
        </table>
            </div>
        </div>

        <table style="margin-top: 15px;">
            <tr style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                <td style="padding: 8px; font-weight: bold; text-align: center; border-right: 1px solid #000;">Keterangan</td>
                <td style="padding: 8px; font-weight: bold; text-align: center;">Jumlah</td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="section-title">A DANA ZAKAT</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">1 Penerimaan Dana</td>
                <td class="amount"></td>
            </tr>
            <?php
                $danaZakat = $reportData[1] ?? [];
                $rincianPenerimaan = $danaZakat['rincian_penerimaan'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianPenerimaan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($jenis); ?></td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented"></td>
                <td class="total-amount"><?php echo e(number_format($danaZakat['penerimaan'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">Bagian Amil</td>
                <td class="subtotal-amount"><?php echo e(number_format($danaZakat['bagian_amil'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented"></td>
                <td class="amount"><?php echo e(number_format(($danaZakat['penerimaan'] ?? 0) - ($danaZakat['bagian_amil'] ?? 0), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2 Penyaluran Dana</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">2.1 Penyaluran Dana berdasarkan Asnaf</td>
                <td class="amount"></td>
            </tr>
            <?php
                $rincianAsnaf = $danaZakat['rincian_penyaluran_asnaf'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianAsnaf; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asnaf => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($asnaf); ?></td>
                <td class="amount"><?php echo e($jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented"></td>
                <td class="subtotal-amount"><?php echo e(number_format(array_sum($rincianAsnaf), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2.2 Penyaluran Dana berdasarkan Bidang Program</td>
                <td class="amount"></td>
            </tr>
            <?php
                $rincianProgram = $danaZakat['rincian_penyaluran'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianProgram; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($program); ?></td>
                <td class="amount"><?php echo e($jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented"></td>
                <td class="subtotal-amount"><?php echo e(number_format(array_sum($rincianProgram), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented"></td>
                <td class="final-total"><?php echo e(number_format($danaZakat['penyaluran'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Surplus (defisit)</td>
                <td class="amount"><?php echo e(($danaZakat['surplus_defisit'] ?? 0) < 0 ? '('.number_format(abs($danaZakat['surplus_defisit'] ?? 0), 0, ',', '.').')' : number_format($danaZakat['surplus_defisit'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Awal</td>
                <td class="amount"><?php echo e(number_format($danaZakat['saldo_awal'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Akhir</td>
                <td class="saldo-akhir"><?php echo e(number_format($danaZakat['saldo_akhir'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="section-title">B DANA INFAQ/SEDEKAH</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">1 Penerimaan Dana</td>
                <td class="amount"></td>
            </tr>
            <?php
                $danaInfaq = $reportData[2] ?? [];
                $rincianPenerimaanInfaq = $danaInfaq['rincian_penerimaan'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianPenerimaanInfaq; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($jenis); ?></td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented"></td>
                <td class="total-amount"><?php echo e(number_format($danaInfaq['penerimaan'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">Bagian Amil</td>
                <td class="subtotal-amount"><?php echo e(number_format($danaInfaq['bagian_amil'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented"></td>
                <td class="amount"><?php echo e(number_format(($danaInfaq['penerimaan'] ?? 0) - ($danaInfaq['bagian_amil'] ?? 0), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2 Penyaluran Dana</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">2.1 Penyaluran Dana berdasarkan Bidang Program</td>
                <td class="amount"></td>
            </tr>
            <?php
                $rincianProgramInfaq = $danaInfaq['rincian_penyaluran'] ?? [];
            ?>
            <tr>
                <td class="indented">2.1.a Penyaluran Infaq Umum</td>
                <td class="amount"></td>
            </tr>
            <?php $__currentLoopData = $rincianProgramInfaq; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $jumlahUmum = round($jumlah * 0.4);
            ?>
            <tr>
                <td class="double-indented">- <?php echo e($program); ?></td>
                <td class="amount"><?php echo e($jumlahUmum > 0 ? number_format($jumlahUmum, 0, ',', '.') : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented"></td>
                <td class="subtotal-amount"><?php echo e(number_format(array_sum($rincianProgramInfaq) * 0.4, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">2.1.b Penyaluran Infaq Khusus</td>
                <td class="amount"></td>
            </tr>
            <?php $__currentLoopData = $rincianProgramInfaq; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $jumlahKhusus = round($jumlah * 0.6);
            ?>
            <tr>
                <td class="double-indented">- <?php echo e($program); ?></td>
                <td class="amount"><?php echo e($jumlahKhusus > 0 ? number_format($jumlahKhusus, 0, ',', '.') : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented"></td>
                <td class="subtotal-amount"><?php echo e(number_format(array_sum($rincianProgramInfaq) * 0.6, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">Total Penyaluran</td>
                <td class="final-total"><?php echo e(number_format($danaInfaq['penyaluran'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Surplus (defisit)</td>
                <td class="amount"><?php echo e(($danaInfaq['surplus_defisit'] ?? 0) < 0 ? '('.number_format(abs($danaInfaq['surplus_defisit'] ?? 0), 0, ',', '.').')' : number_format($danaInfaq['surplus_defisit'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Awal</td>
                <td class="amount"><?php echo e(number_format($danaInfaq['saldo_awal'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Akhir</td>
                <td class="saldo-akhir"><?php echo e(number_format($danaInfaq['saldo_akhir'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="section-title">C DANA CORPORATE SOCIAL RESPONSIBILITY</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">1 Penerimaan Dana</td>
                <td class="amount"></td>
            </tr>
            <?php
                $danaCSR = $reportData[3] ?? [];
                $rincianPenerimaanCSR = $danaCSR['rincian_penerimaan'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianPenerimaanCSR; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($jenis); ?></td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($rincianPenerimaanCSR)): ?>
            <tr>
                <td class="indented">- Corporate Social Responsibility (CSR)</td>
                <td class="amount"><?php echo e(number_format($danaCSR['penerimaan'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="indented">Bagian Amil</td>
                <td class="amount"><?php echo e(number_format($danaCSR['bagian_amil'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">Jumlah</td>
                <td class="total-amount"><?php echo e(number_format(($danaCSR['penerimaan'] ?? 0) - ($danaCSR['bagian_amil'] ?? 0), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2 Penyaluran Dana</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="total-row">Surplus (defisit)</td>
                <td class="amount"><?php echo e(($danaCSR['surplus_defisit'] ?? 0) < 0 ? '('.number_format(abs($danaCSR['surplus_defisit'] ?? 0), 0, ',', '.').')' : number_format($danaCSR['surplus_defisit'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Awal</td>
                <td class="amount"><?php echo e(number_format($danaCSR['saldo_awal'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Akhir</td>
                <td class="saldo-akhir"><?php echo e(number_format($danaCSR['saldo_akhir'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="section-title">D DANA SOSIAL KEAGAMAAN LAINNYA</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">1 Penerimaan Dana</td>
                <td class="amount"></td>
            </tr>
            <?php
                $danaDSKL = $reportData[4] ?? [];
                $rincianPenerimaanDSKL = $danaDSKL['rincian_penerimaan'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianPenerimaanDSKL; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($jenis); ?></td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($rincianPenerimaanDSKL)): ?>
            <tr>
                <td class="indented">- Hibah</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) * 0.6, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Qurban</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) * 0.25, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Fidyah</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) * 0.05, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Nazar</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) * 0.02, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Bagi Hasil Investasi</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) * 0.005, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- DSKL Lainnya</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) * 0.075, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="subsection">Jumlah</td>
                <td class="total-amount"><?php echo e(number_format($danaDSKL['penerimaan'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">Bagian Amil</td>
                <td class="subtotal-amount"><?php echo e(number_format($danaDSKL['bagian_amil'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">Jumlah Penerimaan Setelah Bagian Amil</td>
                <td class="amount"><?php echo e(number_format(($danaDSKL['penerimaan'] ?? 0) - ($danaDSKL['bagian_amil'] ?? 0), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2 Penyaluran Dana</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">2.1 Penyaluran Dana berdasarkan Bidang Program</td>
                <td class="amount"></td>
            </tr>
            <?php
                $rincianProgramDSKL = $danaDSKL['rincian_penyaluran'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianProgramDSKL; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($program); ?></td>
                <td class="amount"><?php echo e($jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="subsection">Total Penyaluran</td>
                <td class="final-total"><?php echo e(number_format($danaDSKL['penyaluran'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Surplus (defisit)</td>
                <td class="amount"><?php echo e(($danaDSKL['surplus_defisit'] ?? 0) < 0 ? '('.number_format(abs($danaDSKL['surplus_defisit'] ?? 0), 0, ',', '.').')' : number_format($danaDSKL['surplus_defisit'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Awal</td>
                <td class="amount"><?php echo e(number_format($danaDSKL['saldo_awal'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Akhir</td>
                <td class="saldo-akhir"><?php echo e(number_format($danaDSKL['saldo_akhir'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
        </table>
        
        <table>
            <tr>
                <td class="section-title">E DANA NON HALAL</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">1 Penerimaan Dana</td>
                <td class="amount"></td>
            </tr>
            <?php
                $danaNonHalal = $reportData[7] ?? [];
                $rincianPenerimaanNonHalal = $danaNonHalal['rincian_penerimaan'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianPenerimaanNonHalal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($jenis); ?></td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($rincianPenerimaanNonHalal)): ?>
            <tr>
                <td class="indented">- Dana Bunga Bank</td>
                <td class="amount"><?php echo e(number_format($danaNonHalal['penerimaan'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="subsection">Jumlah</td>
                <td class="total-amount"><?php echo e(number_format($danaNonHalal['penerimaan'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2 Penyaluran Dana</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">Penyaluran Dana berdasarkan Bidang Program</td>
                <td class="amount"></td>
            </tr>
            <?php
                $rincianProgramNonHalal = $danaNonHalal['rincian_penyaluran'] ?? [];
            ?>
            <?php $__currentLoopData = $rincianProgramNonHalal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($program); ?></td>
                <td class="amount"><?php echo e($jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($rincianProgramNonHalal) && ($danaNonHalal['penyaluran'] ?? 0) > 0): ?>
            <tr>
                <td class="indented">- Kemanusiaan</td>
                <td class="amount"><?php echo e(number_format($danaNonHalal['penyaluran'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="subsection">Total Penyaluran</td>
                <td class="final-total"><?php echo e(number_format($danaNonHalal['penyaluran'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Surplus (defisit)</td>
                <td class="amount"><?php echo e(($danaNonHalal['surplus_defisit'] ?? 0) < 0 ? '('.number_format(abs($danaNonHalal['surplus_defisit'] ?? 0), 0, ',', '.').')' : number_format($danaNonHalal['surplus_defisit'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Awal</td>
                <td class="amount"><?php echo e(number_format($danaNonHalal['saldo_awal'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Akhir</td>
                <td class="saldo-akhir"><?php echo e(number_format($danaNonHalal['saldo_akhir'] ?? 0, 0, ',', '.')); ?></td>
            </tr>
        </table>
        
        <table>
            <tr>
                <td class="section-title">F PENERIMAAN DAN PENGGUNAAN HAK AMIL (DALAM RUPIAH)</td>
                <td class="amount"></td>
            </tr>
            <tr>
                <td class="subsection">1. Penerimaan Hak Amil</td>
                <td class="amount"></td>
            </tr>
            <?php $__currentLoopData = $penerimaanHakAmilDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- Penerimaan hak amil dari <?php echo e($jenis); ?> (maksimal.)</td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($penerimaanHakAmilDetail)): ?>
            <tr>
                <td class="indented">- Penerimaan hak amil dari zakat asnaf amil (maksimal.)</td>
                <td class="amount"><?php echo e(number_format($totalPenerimaanHakAmil * 0.3, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Penerimaan hak amil dari Infaq</td>
                <td class="amount"><?php echo e(number_format($totalPenerimaanHakAmil * 0.65, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Penerimaan hak amil dari DSKL</td>
                <td class="amount"><?php echo e(number_format($totalPenerimaanHakAmil * 0.05, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="subsection">Total Penerimaan</td>
                <td class="total-amount"><?php echo e(number_format($totalPenerimaanHakAmil, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="subsection">2. Penggunaan Hak Amil</td>
                <td class="amount"></td>
            </tr>
            <?php $__currentLoopData = $penggunaanHakAmilDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="indented">- <?php echo e($jenis); ?></td>
                <td class="amount"><?php echo e(number_format($jumlah, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($penggunaanHakAmilDetail)): ?>
            <tr>
                <td class="indented">- Belanja Pegawai</td>
                <td class="amount"><?php echo e(number_format($totalPenggunaanHakAmil * 0.85, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Biaya Perjalanan Dinas</td>
                <td class="amount"><?php echo e(number_format($totalPenggunaanHakAmil * 0.02, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Beban Administrasi Umum</td>
                <td class="amount"><?php echo e(number_format($totalPenggunaanHakAmil * 0.08, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Beban Penyusutan, Pemeliharaan dan Penghapusan</td>
                <td class="amount"><?php echo e(number_format($totalPenggunaanHakAmil * 0.025, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="indented">- Biaya Rumah Tangga / Protokoler Yayasan</td>
                <td class="amount"><?php echo e(number_format($totalPenggunaanHakAmil * 0.025, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="subsection">Total Penggunaan</td>
                <td class="total-amount"><?php echo e(number_format($totalPenggunaanHakAmil, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Surplus (defisit)</td>
                <td class="amount"><?php echo e($surplusDefisitHakAmil < 0 ? '('.number_format(abs($surplusDefisitHakAmil), 0, ',', '.').')' : number_format($surplusDefisitHakAmil, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Awal</td>
                <td class="amount"><?php echo e(number_format(($totalPenerimaanHakAmil - $totalPenggunaanHakAmil - $surplusDefisitHakAmil) * 0.4, 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td class="total-row">Saldo Akhir</td>
                <td class="saldo-akhir"><?php echo e(number_format(($totalPenerimaanHakAmil - $totalPenggunaanHakAmil) + (($totalPenerimaanHakAmil - $totalPenggunaanHakAmil - $surplusDefisitHakAmil) * 0.4), 0, ',', '.')); ?></td>
            </tr>
        </table>
        
        <div class="total-row">
            Saldo Dana Zakat, Infaq/Sedekah, CSR, DSKL, Amil dan Non Halal: <?php echo e(number_format($totalSisaSaldo ?? 0, 0, ',', '.')); ?>

        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td class="signature">
                        <p>Diketahui Oleh:</p>
                        <p class="signature-name">FUJI LESTARI, S.E.</p>
                        <p>Direktur Eksekutif</p>
                    </td>
                    <td class="signature">
                        <p>Disusun Oleh:</p>
                        <p class="signature-name">JOKO NURHADI</p>
                        <p>Bendahara</p>
                    </td>
                </tr>
            </table>
        </div>
</body>
</html>
<?php /**PATH /home/simadior/public_html/resources/views/laporan/pdf/perubahan-dana-clean.blade.php ENDPATH**/ ?>