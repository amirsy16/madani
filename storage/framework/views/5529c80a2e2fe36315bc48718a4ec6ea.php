<div class="space-y-4">
    <?php
        $totalAmount = collect($this->detailDonasiData)->sum(function($d) { 
            return ($d['jumlah'] ?? 0) + ($d['perkiraan_nilai_barang'] ?? 0); 
        });
        $uniqueDonors = collect($this->detailDonasiData)->pluck('donatur_id')->filter()->unique()->count();
    ?>


<div class="fi-ta-ctn divide-y divide-gray-200 dark:divide-white/10 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    
    <div class="p-4">
        <div class="grid grid-cols-3 gap-4">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Total Transaksi</span>
                <span class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    <?php echo e(count($this->detailDonasiData)); ?>

                </span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Total Donatur Unik</span>
                <span class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    <?php echo e(collect($this->detailDonasiData)->pluck('donatur_nama')->unique()->count()); ?>

                </span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">Total Nominal</span>
                <span class="text-2xl font-semibold tracking-tight text-success-600 dark:text-success-400">
                    Rp <?php echo e(number_format($detailDonasiType === 'barang_by_jenis' ? collect($this->detailDonasiData)->sum('nilai_barang') : collect($this->detailDonasiData)->sum('nominal'), 0, ',', '.')); ?>

                </span>
            </div>
        </div>
    </div>

    
    <div class="fi-ta-content relative divide-y divide-gray-200 dark:divide-white/10 overflow-x-auto">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                No
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Tanggal
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                                    Nomor Transaksi
                                <?php else: ?>
                                    Kode
                                <?php endif; ?>
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Donatur
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Jenis Donasi
                            </span>
                        </span>
                    </th>
                    <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Deskripsi Barang
                            </span>
                        </span>
                    </th>
                    <?php endif; ?>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-end">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Nominal
                            </span>
                        </span>
                    </th>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-center">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Metode
                            </span>
                        </span>
                    </th>
                    <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                    <th class="fi-ta-header-cell px-2 py-2 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <span class="group flex items-center gap-x-1 whitespace-nowrap justify-start">
                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white">
                                Fundraiser
                            </span>
                        </span>
                    </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                <?php $__empty_1 = true; $__currentLoopData = $this->detailDonasiData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $donasi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                <?php echo e($index + 1); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                <?php echo e(\Carbon\Carbon::parse($donasi['tanggal'])->format('d/m/Y')); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30" style="--c-50:var(--gray-50);--c-400:var(--gray-400);--c-600:var(--gray-600);">
                                                <span class="font-mono text-xs">
                                                    <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                                                        <?php echo e($donasi['nomor_transaksi']); ?>

                                                    <?php else: ?>
                                                        <?php echo e($donasi['kode']); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max flex-col gap-y-1">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-medium text-xs leading-5 text-gray-950 dark:text-white">
                                                <?php echo e($donasi['donatur_nama']); ?>

                                            </div>
                                            <?php if($donasi['donatur_hp']): ?>
                                                <div class="fi-ta-text-item-description text-xs leading-4 text-gray-500 dark:text-gray-400">
                                                    <?php echo e($donasi['donatur_hp']); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                <?php echo e($donasi['jenis_donasi']); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white" title="<?php echo e($donasi['deskripsi_barang']); ?>">
                                                <?php echo e(Str::limit($donasi['deskripsi_barang'], 25)); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex justify-end">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-semibold text-xs leading-5 fi-color-custom text-custom-600 dark:text-custom-400" style="--c-400:var(--success-400);--c-600:var(--success-600);">
                                                Rp <?php echo e(number_format($detailDonasiType === 'barang_by_jenis' ? $donasi['nilai_barang'] : $donasi['nominal'], 0, ',', '.')); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex justify-center">
                                        <div class="flex max-w-max">
                                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight fi-color-primary bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                                <?php echo e($donasi['metode_pembayaran']); ?>

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-col-wrp">
                                <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                    <div class="flex">
                                        <div class="flex max-w-max">
                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-xs leading-5 text-gray-950 dark:text-white">
                                                <?php echo e($donasi['fundraiser']); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e($detailDonasiType === 'barang_by_jenis' ? '9' : '7'); ?>" class="fi-ta-empty-state px-6 py-12">
                            <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                                <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                                    <svg class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                    Tidak ada data donasi
                                </h4>
                                <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada transaksi donasi untuk kategori ini.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if(count($this->detailDonasiData) > 0): ?>
                <tfoot class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <td colspan="<?php echo e($detailDonasiType === 'barang_by_jenis' ? '7' : '5'); ?>" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                <div class="flex justify-end">
                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-semibold text-xs leading-5 text-gray-950 dark:text-white">
                                        Total Keseluruhan:
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-2 sm:last-of-type:pe-2">
                            <div class="fi-ta-text grid w-full gap-y-1 px-2 py-2">
                                <div class="flex justify-end">
                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5 font-bold text-sm leading-5 fi-color-custom text-custom-600 dark:text-custom-400" style="--c-400:var(--success-400);--c-600:var(--success-600);">
                                        Rp <?php echo e(number_format($detailDonasiType === 'barang_by_jenis' ? collect($this->detailDonasiData)->sum('nilai_barang') : collect($this->detailDonasiData)->sum('nominal'), 0, ',', '.')); ?>

                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php if($detailDonasiType === 'barang_by_jenis'): ?>
                        <td></td>
                        <?php endif; ?>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php /**PATH /home/simadior/public_html/resources/views/filament/widgets/ringkasan-statistik-utama/detail-donasi-modal.blade.php ENDPATH**/ ?>