<x-filament-panels::page>
    @php($stat = $this->statistikLog())

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card
            label="Total Aktivitas"
            :value="number_format($stat['total'], 0, ',', '.')"
            description="Seluruh jejak input data"
            icon="heroicon-o-clock"
            color="primary"
        />
        <x-metric-card
            label="Hari Ini"
            :value="number_format($stat['hari_ini'], 0, ',', '.')"
            icon="heroicon-o-calendar-days"
            color="info"
        />
        <x-metric-card
            label="Minggu Ini"
            :value="number_format($stat['minggu_ini'], 0, ',', '.')"
            icon="heroicon-o-arrow-trending-up"
            color="warning"
        />
        <x-metric-card
            label="Pengguna Terlibat"
            :value="number_format($stat['pengguna'], 0, ',', '.')"
            description="Pengguna yang tercatat"
            icon="heroicon-o-users"
            color="success"
        />
    </div>

    <p class="mt-4 mb-2 text-sm text-gray-600 dark:text-gray-300">
        Jejak audit input data: siapa menambahkan, memperbarui, atau menghapus data apa dan kapan.
        Centang baris untuk menghapus log terpilih. Log dimulai sejak fitur ini aktif.
    </p>

    {{ $this->table }}
</x-filament-panels::page>
