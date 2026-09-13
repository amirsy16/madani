<?php if (isset($component)) { $__componentOriginalf45da69382bf4ac45a50b496dc82aa9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.simple','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page.simple'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div class="mx-auto w-full max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-900/5 dark:border-gray-700 dark:bg-gray-900">

        <div class="grid md:grid-cols-[1.05fr_1fr]">

            
            <div class="relative hidden flex-col justify-between overflow-hidden bg-[#3d0a15] p-8 md:flex">
                
                <div
                    class="absolute inset-0 opacity-[0.14]"
                    style="background-image: radial-gradient(rgba(255,255,255,0.55) 1px, transparent 1px); background-size: 22px 22px;"
                ></div>
                <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-[#800020]/70 blur-2xl"></div>

                <div class="relative">
                    <div class="inline-flex items-center rounded-lg bg-white/95 px-3.5 py-2.5 shadow-sm">
                        <img src="<?php echo e(asset('images/LOGOIM.png')); ?>" alt="LAZ Insan Madani" class="h-9 w-auto">
                    </div>

                    <h2 class="mt-8 text-3xl font-bold tracking-tight text-white">
                        SIMADI
                        <span class="ml-1 align-middle text-[11px] font-semibold uppercase tracking-widest text-amber-300/90">v2.0</span>
                    </h2>
                    <p class="mt-2 max-w-xs text-sm leading-relaxed text-white/70">
                        Sistem Informasi Pengelolaan Dana Zakat, Infaq &amp; Sedekah — LAZ Insan Madani.
                    </p>
                </div>

                <ul class="relative mt-10 space-y-3.5">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = [
                        ['icon' => 'heroicon-o-check-badge', 'text' => 'Verifikasi donasi & penyaluran teraudit'],
                        ['icon' => 'heroicon-o-chart-bar', 'text' => 'Laporan dana real-time dengan detail per kategori'],
                        ['icon' => 'heroicon-o-shield-check', 'text' => 'Data dokumen sensitif tersimpan privat'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-3 text-sm text-white/80">
                            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $item['icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 shrink-0 text-amber-300/90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                            <span><?php echo e($item['text']); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>

                <p class="relative mt-10 text-[11px] leading-relaxed text-white/45">
                    &copy; <?php echo e(date('Y')); ?> LAZ Insan Madani &middot; Sistem internal — akses terbatas
                    untuk pengelola yang berwenang.
                </p>
            </div>

            
            <div class="p-8 sm:p-10">
                <div class="mb-8 flex items-center justify-between md:hidden">
                    <img src="<?php echo e(asset('images/LOGOIM.png')); ?>" alt="LAZ Insan Madani" class="h-8 w-auto">
                </div>

                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Masuk ke akun Anda
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gunakan akun pengelola yang telah diberikan administrator.
                </p>

                <?php if (isset($component)) { $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.index','data' => ['id' => 'form','wire:submit' => 'authenticate','class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'form','wire:submit' => 'authenticate','class' => 'mt-6']); ?>
                    <?php echo e($this->form); ?>


                    <?php if (isset($component)) { $__componentOriginal742ef35d02cb00943edd9ad8ebf61966 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal742ef35d02cb00943edd9ad8ebf61966 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.actions','data' => ['actions' => $this->getCachedFormActions(),'fullWidth' => $this->hasFullWidthFormActions()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getCachedFormActions()),'full-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->hasFullWidthFormActions())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal742ef35d02cb00943edd9ad8ebf61966)): ?>
<?php $attributes = $__attributesOriginal742ef35d02cb00943edd9ad8ebf61966; ?>
<?php unset($__attributesOriginal742ef35d02cb00943edd9ad8ebf61966); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal742ef35d02cb00943edd9ad8ebf61966)): ?>
<?php $component = $__componentOriginal742ef35d02cb00943edd9ad8ebf61966; ?>
<?php unset($__componentOriginal742ef35d02cb00943edd9ad8ebf61966); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3)): ?>
<?php $attributes = $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3; ?>
<?php unset($__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3)): ?>
<?php $component = $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3; ?>
<?php unset($__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3); ?>
<?php endif; ?>

                <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 text-xs dark:border-gray-800">
                    <a
                        href="/admin/password-reset"
                        class="font-medium text-primary-700 hover:underline dark:text-primary-400"
                    >
                        Lupa kata sandi?
                    </a>
                    <span class="text-gray-400 dark:text-gray-500">SIMADI v2.0 &middot; LAZ Insan Madani</span>
                </div>
            </div>

        </div>
    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a)): ?>
<?php $attributes = $__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a; ?>
<?php unset($__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf45da69382bf4ac45a50b496dc82aa9a)): ?>
<?php $component = $__componentOriginalf45da69382bf4ac45a50b496dc82aa9a; ?>
<?php unset($__componentOriginalf45da69382bf4ac45a50b496dc82aa9a); ?>
<?php endif; ?>
<?php /**PATH D:\laragon\www\madaniX\resources\views/filament/pages/auth/login.blade.php ENDPATH**/ ?>