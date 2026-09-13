<!DOCTYPE html>
<html>
<head>
    <title>Invoice Donasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .invoice-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e(config('app.organization_name', 'Laz Insan Madani Jambi')); ?></h1>
        <p>Invoice Donasi</p>
    </div>
    
    <div class="content">
        <p>Assalamu'alaikum <?php echo e($donatur->nama); ?>,</p>
        
        <p>Jazakallahu khairan atas donasi yang telah Anda berikan. Berikut adalah invoice donasi Anda:</p>
        
        <div class="invoice-details">
            <h3>Detail Donasi</h3>
            <table>
                <tr>
                    <td><strong>Invoice:</strong></td>
                    <td><?php echo e($invoice_number); ?></td>
                </tr>
                <tr>
                    <td><strong>Nama Donatur:</strong></td>
                    <td><?php echo e($donatur->nama); ?></td>
                </tr>
                <tr>
                    <td><strong>Jumlah Donasi:</strong></td>
                    <td>Rp <?php echo e(number_format($donasi->jumlah, 0, ',', '.')); ?></td>
                </tr>
                <tr>
                    <td><strong>Jenis Donasi:</strong></td>
                    <td><?php echo e($donasi->jenisDonasi?->nama ?? 'Donasi Umum'); ?></td>
                </tr>
                <tr>
                    <td><strong>Tanggal:</strong></td>
                    <td><?php echo e($donasi->tanggal_donasi->format('d/m/Y')); ?></td>
                </tr>
            </table>
        </div>
        
        <p>Invoice PDF terlampir dalam email ini.</p>
        
        <p>Barakallahu fiikum atas kepercayaan Anda kepada kami.</p>
        
        <p>Wassalamu'alaikum warahmatullahi wabarakatuh</p>
    </div>
    
    <div class="footer">
        <p><?php echo e(config('app.organization_name', 'Laz Insan Madani Jambi')); ?></p>
        <p><?php echo e(config('app.organization_address', 'Jambi, Indonesia')); ?></p>
        <p><?php echo e(config('app.organization_phone', '0741-xxx-xxx')); ?> | <?php echo e(config('app.organization_email', 'info@example.com')); ?></p>
    </div>
</body>
</html>
<?php /**PATH /home/simadior/public_html/resources/views/emails/invoice.blade.php ENDPATH**/ ?>