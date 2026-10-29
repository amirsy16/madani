<?php

namespace App\Observers;

use App\Models\Donasi;
use App\Services\NotifikasiWa;

/**
 * Notifikasi WA (Fonnte) setiap donasi baru tercatat dari panel admin.
 *
 * Tidak mengirim bila dibuat dari console (seeder demo/tinker) dan bila
 * impor massal sedang berlangsung — penahanan lewat NotifikasiWa::$ditahan
 * yang disetel ImporTransaksional (keputusan ops: impor tanpa notif).
 */
class NotifikasiDonasiBaru
{
    public function created(Donasi $donasi): void
    {
        if (app()->runningInConsole() && ! app()->environment('testing')) {
            return;
        }

        $donasi->loadMissing(['donatur', 'metodePembayaran', 'dicatatOleh']);

        $nilai = $donasi->perkiraan_nilai_barang !== null
            ? 'Barang ± Rp '.number_format((float) $donasi->perkiraan_nilai_barang, 0, ',', '.')
            : 'Rp '.number_format((float) $donasi->jumlah, 0, ',', '.');

        $baris = [
            '🔔 *Donasi Baru*',
            $nilai.' — '.($donasi->donatur->nama ?? '(tanpa donatur)'),
        ];

        if ($donasi->metodePembayaran !== null) {
            $baris[] = $donasi->metodePembayaran->nama;
        }

        if ($donasi->dicatatOleh !== null) {
            $baris[] = 'dicatat oleh '.$donasi->dicatatOleh->name;
        }

        $baris[] = NotifikasiWa::stempelWaktu();

        NotifikasiWa::kirim(implode("\n", $baris));
    }
}
