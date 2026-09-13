<?php

namespace App\Filament\Resources\DonaturResource\Pages;

use App\Filament\Resources\DonaturResource;
use App\Models\Donatur;
use App\Models\Pekerjaan;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListDonaturs extends ListRecords
{
    protected static string $resource = DonaturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->label('Import Donatur')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->slideOver()
                ->validateUsing([
                    'gender' => 'required|in:male,female,organization',
                    'nama' => 'required|string|max:255',
                    'nomor_hp' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:255',
                    'alamat_detail' => 'nullable|string|max:500',
                    'pekerjaan' => 'nullable|string|max:100',
                ])
                ->mutateAfterValidationUsing(function (array $data): array {
                    // Generate kode donatur
                    $data['kode_donatur'] = Donatur::generateNewKodeDonatur();
                    
                    // Normalize gender
                    $data['gender'] = strtolower(trim($data['gender']));
                    
                    // Resolve pekerjaan jika ada
                    if (!empty($data['pekerjaan'])) {
                        $pekerjaan = Pekerjaan::where('nama', 'LIKE', '%' . trim($data['pekerjaan']) . '%')
                            ->where('aktif', true)->first();
                        $data['pekerjaan_id'] = $pekerjaan?->id;
                    }
                    unset($data['pekerjaan']);
                    
                    // Set alamat_lengkap dari alamat_detail
                    if (!empty($data['alamat_detail'])) {
                        $data['alamat_lengkap'] = trim($data['alamat_detail']);
                    }
                    
                    return $data;
                })
                ->sampleExcel(
                    sampleData: [
                        [
                            'gender' => 'male',
                            'nama' => 'Ahmad Donatur',
                            'nomor_hp' => '081234567890',
                            'email' => 'ahmad@example.com',
                            'alamat_detail' => 'Jl. Contoh No. 123, RT 01/RW 02',
                            'pekerjaan' => 'Karyawan Swasta',
                        ],
                        [
                            'gender' => 'female',
                            'nama' => 'Siti Donatur',
                            'nomor_hp' => '081987654321',
                            'email' => 'siti@example.com',
                            'alamat_detail' => 'Jl. Merdeka No. 45',
                            'pekerjaan' => 'Mengurus Rumah Tangga',
                        ],
                        [
                            'gender' => 'organization',
                            'nama' => 'PT Amanah Sejahtera',
                            'nomor_hp' => '02112345678',
                            'email' => 'info@amanah.com',
                            'alamat_detail' => 'Jl. Industri No. 100',
                            'pekerjaan' => '',
                        ],
                    ],
                    fileName: 'template_import_donatur.xlsx',
                    sampleButtonLabel: 'Download Template',
                    customiseActionUsing: fn(Action $action) => $action
                        ->color('gray')
                        ->icon('heroicon-o-document-arrow-down'),
                ),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DonaturResource\Widgets\DonaturStats::class,
        ];
    }
}
