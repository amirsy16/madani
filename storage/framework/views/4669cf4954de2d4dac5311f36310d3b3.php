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
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight">
                    Tren Donasi <?php echo e($this->timeRange === '1_year' ? '(1 Tahun Terakhir)' : '(6 Bulan Terakhir)'); ?>

                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan total donasi (uang dan nilai barang) terverifikasi per bulan
                </p>
            </div>
            
            <div class="flex space-x-2">
                <select
                    wire:model.live="jenisDonasi"
                    wire:change="$refresh"
                    class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                >
                    <option value="">Semua Jenis Donasi</option>
                    <?php $__currentLoopData = $this->jenisDonasiOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nama): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>"><?php echo e($nama); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                
                <select
                    wire:model.live="timeRange"
                    wire:change="$refresh"
                    class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                >
                    <?php $__currentLoopData = $this->timeRangeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        
        <div
            x-data="{
                chartData: <?php echo e(json_encode($this->getChartData())); ?>,
                chartOptions: <?php echo e(json_encode($this->getChartOptions())); ?>,
                chartType: <?php echo e(json_encode($this->getChartType())); ?>,
                chart: null,
                
                init() {
                    this.initChart();
                    
                    // Re-render chart when Livewire component is updated
                    this.$watch('chartData', () => {
                        this.updateChart();
                    });
                },
                
                initChart() {
                    // Pastikan Chart.js tersedia
                    if (typeof Chart === 'undefined') {
                        // Jika Chart.js belum dimuat, muat dari CDN
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        script.onload = () => this.createChart();
                        document.head.appendChild(script);
                    } else {
                        this.createChart();
                    }
                },
                
                createChart() {
                    // Parse function strings in options
                    this.parseOptionFunctions(this.chartOptions);
                    
                    // Destroy existing chart if it exists
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    
                    // Create new chart
                    this.chart = new Chart(
                        this.$refs.canvas,
                        {
                            type: this.chartType,
                            data: this.chartData,
                            options: this.chartOptions
                        }
                    );
                },
                
                updateChart() {
                    if (this.chart) {
                        this.chart.data = this.chartData;
                        this.chart.update();
                    }
                },
                
                parseOptionFunctions(obj) {
                    for (const key in obj) {
                        if (typeof obj[key] === 'string' && obj[key].includes('function(')) {
                            obj[key] = new Function('return ' + obj[key])();
                        } else if (typeof obj[key] === 'object' && obj[key] !== null) {
                            this.parseOptionFunctions(obj[key]);
                        }
                    }
                }
            }"
            wire:key="<?php echo e($this->timeRange); ?>-<?php echo e($this->jenisDonasi); ?>"
        >
            <div class="h-80">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
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



<?php /**PATH /home/simadior/public_html/resources/views/filament/widgets/tren-donasi-chart.blade.php ENDPATH**/ ?>