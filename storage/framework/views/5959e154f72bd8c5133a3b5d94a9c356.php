
<div class="p-6 bg-white rounded-xl shadow dark:bg-gray-800">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <tbody>
                <tr>
                    <th colspan="2" class="px-2 py-2 font-bold text-lg">HAK AMIL</th>
                </tr>
                <tr>
                    <td class="px-2 py-2 font-bold">1. Penerimaan Hak Amil</td>
                    <td class="px-2 py-2 text-right font-bold"><?php echo e(number_format($totalPenerimaanHakAmil ?? 0, 0, ',', '.')); ?></td>
                </tr>
                <?php if(!empty($penerimaanHakAmilDetail)): ?>
                    <?php $__currentLoopData = $penerimaanHakAmilDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-2 py-1 pl-8">- <?php echo e($jenis); ?></td>
                            <td class="px-2 py-1 text-right"><?php echo e(number_format($jumlah ?? 0, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
                <tr>
                    <td class="px-2 py-2 font-bold">2. Penggunaan Hak Amil</td>
                    <td class="px-2 py-2 text-right font-bold"><?php echo e(number_format($totalPenggunaanHakAmil ?? 0, 0, ',', '.')); ?></td>
                </tr>
                <?php if(!empty($penggunaanHakAmilDetail)): ?>
                    <?php $__currentLoopData = $penggunaanHakAmilDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-2 py-1 pl-8">- <?php echo e($jenis); ?></td>
                            <td class="px-2 py-1 text-right"><?php echo e(number_format($jumlah ?? 0, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <tr>
                        <td class="px-2 py-1 text-red-600" colspan="2">
                            <strong>Catatan:</strong> Tidak ada data penggunaan hak amil untuk periode ini.
                        </td>
                    </tr>
                <?php endif; ?>
                <tr class="font-semibold">
                    <td class="px-2 py-2">Surplus (defisit) Hak Amil</td>
                    <td class="px-2 py-2 text-right"><?php echo e(number_format($surplusDefisitHakAmil ?? 0, 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div><?php /**PATH /home/simadior/public_html/resources/views/filament/pages/laporan/hak-amil-section.blade.php ENDPATH**/ ?>