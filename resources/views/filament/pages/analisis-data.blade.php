<x-filament-panels::page>
    <style>
        /* Custom heading style untuk semua widget table di halaman Analisis Data */
        .fi-ta-header-heading {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            text-align: left !important;
        }
        
        @media (min-width: 768px) {
            .fi-ta-header-heading {
                font-size: 1.75rem !important;
            }
        }
    </style>
    
    {{-- Header widgets akan otomatis dirender oleh Filament --}}
    
    {{-- Konten utama halaman --}}
    <div class="space-y-6">
        {{-- Konten custom jika diperlukan dapat ditambahkan di sini --}}
    </div>
    
    {{-- Footer widgets akan otomatis dirender oleh Filament --}}
</x-filament-panels::page>