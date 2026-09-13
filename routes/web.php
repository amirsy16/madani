<?php

use App\Models\Donasi;
use App\Services\PdfService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Redirect to admin panel
Route::redirect('/', '/admin');

// Custom password reset routes that bypass signature validation
Route::get('password/reset/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('password/reset', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
    ->name('password.store');

Route::get('password/reset', function () {
    return redirect('/admin/password-reset');
})->name('password.request');

// Route untuk download invoice PDF — wajib login + punya izin lihat donasi
Route::get('/invoice/download/{donasi}', function (Donasi $donasi) {
    try {
        // Pastikan donasi sudah terverifikasi
        if ($donasi->status_konfirmasi !== 'verified') {
            abort(403, 'Invoice hanya tersedia untuk donasi yang sudah terverifikasi');
        }

        $pdfService = app(PdfService::class);
        return $pdfService->streamInvoicePDF($donasi);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Invoice download failed', [
            'donasi_id' => $donasi->id,
            'error' => $e->getMessage()
        ]);
        abort(500, 'Gagal menggenerate invoice PDF');
    }
})->middleware(['auth', 'can:view,donasi'])->name('invoice.download');


// Serve file lain dari disk private (bukti pembayaran/penyaluran) — hanya untuk user login
Route::get('/private-file/{path}', function (string $path) {
    // Sanitasi: buang traversal & karakter berbahaya
    $path = str_replace(['..', "\0", '\\'], '', $path);
    $path = trim(preg_replace('#/+#', '/', $path), '/');

    $allowedPrefixes = ['bukti-pembayaran/', 'bukti-penyaluran/'];

    foreach ($allowedPrefixes as $prefix) {
        if (str_starts_with($path, $prefix) && Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->response($path);
        }
    }

    abort(404);
})->middleware('auth')->where('path', '.*')->name('private.file');
