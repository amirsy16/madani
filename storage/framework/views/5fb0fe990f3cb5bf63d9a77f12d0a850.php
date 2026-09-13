<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        
         <?php $__env->slot('heading', null, []); ?> 
            <div class="w-full">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            📊 Ringkasan Statistik Utama
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            <?php if($this->currentPeriod === 'custom' && $this->startDate && $this->endDate): ?>
                                Periode: <?php echo e(\Carbon\Carbon::parse($this->startDate)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($this->endDate)->format('d/m/Y')); ?>

                            <?php else: ?>
                                Periode: <?php echo e($this->timePeriodLabel); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        
                        <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                            <?php $__currentLoopData = $this->timePeriodOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    wire:click="setTimePeriod('<?php echo e($value); ?>')"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200
                                           <?php echo e($this->currentPeriod === $value 
                                              ? 'bg-primary-600 text-white shadow-sm' 
                                              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700'); ?>"
                                >
                                    <?php echo e($label); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
                
                
                <?php if($showDatePicker): ?>
                    <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal:</label>
                                <input 
                                    type="date" 
                                    wire:model="startDate"
                                    class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal:</label>
                                <input 
                                    type="date" 
                                    wire:model="endDate"
                                    class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="applyDateFilter"
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md shadow-sm transition-colors duration-200"
                                >
                                    ✓ Terapkan Filter
                                </button>
                                
                                <button
                                    wire:click="resetDateFilter"
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm transition-colors duration-200"
                                >
                                    ✕ Reset
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>

        
        <div class="relative">
            
            <div wire:loading.flex wire:target="setTimePeriod" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 items-center justify-center rounded-lg z-10">
                <div class="flex items-center space-x-2 text-primary-600">
                    <?php if (isset($component)) { $__componentOriginalbef7c2371a870b1887ec3741fe311a10 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbef7c2371a870b1887ec3741fe311a10 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.loading-indicator','data' => ['class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbef7c2371a870b1887ec3741fe311a10)): ?>
<?php $attributes = $__attributesOriginalbef7c2371a870b1887ec3741fe311a10; ?>
<?php unset($__attributesOriginalbef7c2371a870b1887ec3741fe311a10); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbef7c2371a870b1887ec3741fe311a10)): ?>
<?php $component = $__componentOriginalbef7c2371a870b1887ec3741fe311a10; ?>
<?php unset($__componentOriginalbef7c2371a870b1887ec3741fe311a10); ?>
<?php endif; ?>
                    <span class="text-sm font-medium">Memuat data...</span>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" wire:loading.class="opacity-50" wire:target="setTimePeriod">
                <?php $__empty_1 = true; $__currentLoopData = $this->stats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div 
                        class="stat-item"
                        wire:key="stat-<?php echo e($index); ?>"
                    >
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <?php if($stat['icon'] ?? null): ?>
                                            <span class="text-sm"><?php echo e($stat['icon']); ?></span>
                                        <?php endif; ?>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <?php echo e($stat['label'] ?? 'Unknown'); ?>

                                        </h3>
                                    </div>
                                    
                                    <div class="text-2xl font-bold text-<?php echo e($stat['color'] ?? 'gray'); ?>-600 dark:text-<?php echo e($stat['color'] ?? 'gray'); ?>-400 mb-1">
                                        <?php echo e($stat['value'] ?? 'Rp 0'); ?>

                                    </div>
                                    
                                    <?php if($stat['description'] ?? null): ?>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span><?php echo e($stat['description']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    
                                    <?php if(isset($stat['label']) && str_contains($stat['label'], 'INFAQ TERIKAT')): ?>
                                        <button
                                            wire:click="toggleInfaqTerikatDetail"
                                            type="button"
                                            class="mt-2 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-all duration-200 flex items-center gap-1 shadow-sm"
                                        >
                                            <span><?php echo e($showInfaqTerikatDetail ? 'Sembunyikan Detail' : 'Lihat Selengkapnya'); ?></span>
                                            <svg class="w-3 h-3 transform transition-transform duration-200 <?php echo e($showInfaqTerikatDetail ? 'rotate-180' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    
                                    
                                    <?php if(isset($stat['label']) && $stat['label'] === 'DANA ZAKAT'): ?>
                                        <button
                                            wire:click="toggleZakatDetail"
                                            type="button"
                                            class="mt-2 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-all duration-200 flex items-center gap-1 shadow-sm"
                                        >
                                            <span><?php echo e($showZakatDetail ? 'Sembunyikan Detail' : 'Lihat Selengkapnya'); ?></span>
                                            <svg class="w-3 h-3 transform transition-transform duration-200 <?php echo e($showZakatDetail ? 'rotate-180' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    
                                    
                                    <?php if(isset($stat['label']) && str_contains($stat['label'], 'DONASI BARANG')): ?>
                                        <button
                                            wire:click="toggleBarangDetail"
                                            type="button"
                                            class="mt-2 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-md transition-all duration-200 flex items-center gap-1 shadow-sm"
                                        >
                                            <span><?php echo e($showBarangDetail ? 'Sembunyikan Detail' : 'Lihat Selengkapnya'); ?></span>
                                            <svg class="w-3 h-3 transform transition-transform duration-200 <?php echo e($showBarangDetail ? 'rotate-180' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    
                    <div class="col-span-full">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6 text-center">
                            <div class="text-yellow-600 dark:text-yellow-400 text-2xl mb-2">⚠️</div>
                            <h3 class="text-yellow-800 dark:text-yellow-200 font-semibold mb-1">Tidak Ada Data</h3>
                            <p class="text-yellow-700 dark:text-yellow-300 text-sm">
                                Belum ada data statistik yang tersedia untuk periode ini.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            
            <?php if($showInfaqTerikatDetail && count($this->infaqTerikatDetail ?? []) > 0): ?>
                <div 
                    class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border-2 border-blue-200 dark:border-blue-800 p-6"
                    wire:loading.class="opacity-50"
                    wire:target="toggleInfaqTerikatDetail"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 flex items-center gap-2">
                            <span class="text-xl">📊</span>
                            Detail Infaq Terikat per Kategori
                        </h3>
                        <span class="text-sm text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/40 px-3 py-1 rounded-full">
                            <?php echo e(count($this->infaqTerikatDetail)); ?> Kategori • v2.0
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $debugCount = 0; ?>
                        <?php $__currentLoopData = $this->infaqTerikatDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $debugCount++; ?>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-blue-200 dark:border-blue-700/50 p-4 hover:shadow-md transition-all duration-200 hover:border-blue-400 dark:hover:border-blue-500">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                                        <span class="text-lg">🎯</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate" title="<?php echo e($detail['kategori']); ?>">
                                            <?php echo e($detail['kategori']); ?>

                                        </h4>
                                        <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                                            Rp <?php echo e(number_format($detail['total'], 0, ',', '.')); ?>

                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><?php echo e($detail['jumlah_transaksi']); ?> transaksi</span>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="border-t border-blue-200 dark:border-blue-700 pt-3 mt-3">
                                    <button
                                        wire:click="showInfaqTerikatDonasi(<?php echo e(\Illuminate\Support\Js::from($detail['kategori'])); ?>)"
                                        type="button"
                                        style="display: block !important; width: 100% !important; background-color: #2563eb !important; color: white !important; padding: 0.5rem !important; border-radius: 0.375rem !important; font-size: 0.875rem !important; font-weight: 600 !important; cursor: pointer !important;"
                                        class="hover:bg-blue-700"
                                    >
                                        🔍 LIHAT DETAIL DONASI (Card #<?php echo e($debugCount); ?>)
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-blue-700 dark:text-blue-300">
                                Total <?php echo e(array_sum(array_column($this->infaqTerikatDetail, 'jumlah_transaksi'))); ?> transaksi dari <?php echo e(count($this->infaqTerikatDetail)); ?> kategori
                            </span>
                            <span class="text-blue-900 dark:text-blue-100 font-bold">
                                Total: Rp <?php echo e(number_format(array_sum(array_column($this->infaqTerikatDetail, 'total')), 0, ',', '.')); ?>

                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            
            <?php if($showInfaqTerikatDetail && count($this->infaqTerikatDetail ?? []) === 0): ?>
                <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <div class="text-gray-400 dark:text-gray-500 text-2xl mb-2">📭</div>
                    <h3 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Detail Kategori</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data infaq terikat dengan kategori untuk periode ini.
                    </p>
                </div>
            <?php endif; ?>
            
            
            <?php if($showZakatDetail && count($this->zakatDetail ?? []) > 0): ?>
                <div 
                    class="mt-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border-2 border-green-200 dark:border-green-800 p-6"
                    wire:loading.class="opacity-50"
                    wire:target="toggleZakatDetail"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-green-900 dark:text-green-100 flex items-center gap-2">
                            <span class="text-xl">📈</span>
                            Detail Dana Zakat per Jenis
                        </h3>
                        <span class="text-sm text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/40 px-3 py-1 rounded-full">
                            <?php echo e(count($this->zakatDetail)); ?> Jenis
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $__currentLoopData = $this->zakatDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-green-200 dark:border-green-700/50 p-4 hover:shadow-md transition-all duration-200 hover:border-green-400 dark:hover:border-green-500">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center">
                                        <span class="text-lg">💰</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate" title="<?php echo e($detail['jenis']); ?>">
                                            <?php echo e($detail['jenis']); ?>

                                        </h4>
                                        <div class="text-xl font-bold text-green-600 dark:text-green-400 mb-1">
                                            Rp <?php echo e(number_format($detail['total'], 0, ',', '.')); ?>

                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><?php echo e($detail['jumlah_transaksi']); ?> transaksi</span>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div style="border-top: 1px solid rgb(209 213 219); margin-top: 12px; padding-top: 12px;">
                                    <button
                                        wire:click="showZakatDonasi(<?php echo e(\Illuminate\Support\Js::from($detail['jenis'])); ?>)"
                                        type="button"
                                        style="width: 100% !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 0.5rem !important; padding: 10px 16px !important; font-size: 13px !important; font-weight: 600 !important; color: white !important; background-color: rgb(22 163 74) !important; border-radius: 6px !important; border: none !important; cursor: pointer !important; transition: all 0.2s !important;"
                                        onmouseover="this.style.backgroundColor='rgb(21 128 61)'"
                                        onmouseout="this.style.backgroundColor='rgb(22 163 74)'"
                                    >
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span>🔍 LIHAT DETAIL DONASI</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-green-700 dark:text-green-300">
                                Total <?php echo e(array_sum(array_column($this->zakatDetail, 'jumlah_transaksi'))); ?> transaksi dari <?php echo e(count($this->zakatDetail)); ?> jenis
                            </span>
                            <span class="text-green-900 dark:text-green-100 font-bold">
                                Total: Rp <?php echo e(number_format(array_sum(array_column($this->zakatDetail, 'total')), 0, ',', '.')); ?>

                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            
            <?php if($showZakatDetail && count($this->zakatDetail ?? []) === 0): ?>
                <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <div class="text-gray-400 dark:text-gray-500 text-2xl mb-2">📭</div>
                    <h3 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Detail Jenis</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data zakat dengan jenis untuk periode ini.
                    </p>
                </div>
            <?php endif; ?>
            
            
            <?php if($showBarangDetail && count($this->barangDetail ?? []) > 0): ?>
                <div 
                    class="mt-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-lg border-2 border-orange-200 dark:border-orange-800 p-6"
                    wire:loading.class="opacity-50"
                    wire:target="toggleBarangDetail"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-orange-900 dark:text-orange-100 flex items-center gap-2">
                            <span class="text-xl">📦</span>
                            Detail Donasi Barang per Jenis
                        </h3>
                        <span class="text-sm text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/40 px-3 py-1 rounded-full">
                            <?php echo e(count($this->barangDetail)); ?> Jenis
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $__currentLoopData = $this->barangDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-orange-200 dark:border-orange-700/50 p-4 hover:shadow-md transition-all duration-200 hover:border-orange-400 dark:hover:border-orange-500">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-orange-100 dark:bg-orange-900/40 rounded-lg flex items-center justify-center">
                                        <span class="text-lg">📦</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1 truncate" title="<?php echo e($detail['jenis']); ?>">
                                            <?php echo e($detail['jenis']); ?>

                                        </h4>
                                        <div class="text-xl font-bold text-orange-600 dark:text-orange-400 mb-1">
                                            Rp <?php echo e(number_format($detail['total'], 0, ',', '.')); ?>

                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><?php echo e($detail['jumlah_transaksi']); ?> transaksi</span>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div style="border-top: 1px solid rgb(209 213 219); margin-top: 12px; padding-top: 12px;">
                                    <button
                                        wire:click="showBarangDonasiByJenis(<?php echo e(\Illuminate\Support\Js::from($detail['jenis'])); ?>)"
                                        type="button"
                                        style="width: 100% !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 0.5rem !important; padding: 10px 16px !important; font-size: 13px !important; font-weight: 600 !important; color: white !important; background-color: rgb(234 88 12) !important; border-radius: 6px !important; border: none !important; cursor: pointer !important; transition: all 0.2s !important;"
                                        onmouseover="this.style.backgroundColor='rgb(154 52 18)'"
                                        onmouseout="this.style.backgroundColor='rgb(234 88 12)'"
                                    >
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span>🔍 LIHAT DETAIL DONASI</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-orange-700 dark:text-orange-300">
                                Total <?php echo e(array_sum(array_column($this->barangDetail, 'jumlah_transaksi'))); ?> transaksi dari <?php echo e(count($this->barangDetail)); ?> jenis
                            </span>
                            <span class="text-orange-900 dark:text-orange-100 font-bold">
                                Total: Rp <?php echo e(number_format(array_sum(array_column($this->barangDetail, 'total')), 0, ',', '.')); ?>

                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            
            <?php if($showBarangDetail && count($this->barangDetail ?? []) === 0): ?>
                <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <div class="text-gray-400 dark:text-gray-500 text-2xl mb-2">📭</div>
                    <h3 class="text-gray-700 dark:text-gray-300 font-semibold mb-1">Belum Ada Detail Jenis</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data donasi barang dengan jenis untuk periode ini.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($this->stats && count($this->stats) > 0): ?>
            <div class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400">
                Menampilkan <?php echo e(count($this->stats)); ?> statistik untuk periode <?php echo e(strtolower($this->timePeriodLabel)); ?>

            </div>
        <?php endif; ?>
        
        
        <?php if($showDetailDonasiModal): ?>
            <div 
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" 
                role="dialog" 
                aria-modal="true"
                wire:key="detail-modal-<?php echo e($detailDonasiType); ?>-<?php echo e($selectedKategori ?? $selectedJenisZakat); ?>"
            >
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    
                    <div 
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                        aria-hidden="true"
                        wire:click="closeDetailDonasiModal"
                    ></div>

                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                        
                        <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        <?php if($detailDonasiType === 'infaq'): ?>
                                            📋 Detail Donasi Infaq Terikat - <?php echo e($selectedKategori); ?>

                                        <?php elseif($detailDonasiType === 'zakat'): ?>
                                            📋 Detail Donasi Zakat - <?php echo e($selectedJenisZakat); ?>

                                        <?php elseif($detailDonasiType === 'barang_by_jenis'): ?>
                                            📦 Detail Donasi Barang - <?php echo e($selectedJenisBarang); ?>

                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Total <?php echo e(count($this->detailDonasiData)); ?> transaksi • Periode: <?php echo e($this->timePeriodLabel); ?>

                                    </p>
                                </div>
                                <button
                                    type="button"
                                    wire:click="closeDetailDonasiModal"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                >
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        
                        <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                            <?php echo $__env->make('filament.widgets.ringkasan-statistik-utama.detail-donasi-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>

                        
                        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-3 flex justify-end border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                wire:click="closeDetailDonasiModal"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH /home/simadior/public_html/resources/views/filament/widgets/ringkasan-statistik-utama.blade.php ENDPATH**/ ?>