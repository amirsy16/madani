<?php

namespace App\Services;

use App\Models\Donasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Unified PDF Service - Menggabungkan semua fungsi PDF
 * Menggantikan: InvoicePdfService, HybridPdfService, ExternalPdfService
 */
class PdfService
{
    /**
     * Generate PDF invoice untuk donasi
     */
    public function generateInvoicePDF(Donasi $donasi): string
    {
        try {
            // Ensure relationships are loaded
            $donasi->load(['donatur', 'jenisDonasi', 'metodePembayaran']);
            
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($donasi);
            
            // Prepare data untuk PDF
            $data = $this->prepareInvoiceData($donasi, $invoiceNumber);
            
            // Generate PDF using donation form template
            $pdf = Pdf::loadView('invoices.donation-form-invoice', $data)
                ->setPaper('a5', 'landscape')
                ->setOptions($this->getDefaultPdfOptions());
            
            // Generate filename
            $filename = 'invoice-' . $donasi->nomor_transaksi_unik . '.pdf';
            $path = 'invoices/' . $filename;
            
            // Ensure directory exists
            Storage::disk('private')->makeDirectory('invoices');
            
            // Save PDF to storage
            Storage::disk('private')->put($path, $pdf->output());
            
            $fullPath = Storage::disk('private')->path($path);
            
            
            return $fullPath;
            
        } catch (Exception $e) {
            Log::error('Invoice PDF generation failed', [
                'donasi_id' => $donasi->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate nomor invoice dari nomor transaksi unik donasi
     */
    protected function generateInvoiceNumber(Donasi $donasi): string
    {
        return 'INV-' . $donasi->nomor_transaksi_unik;
    }

    /**
     * Stream PDF invoice
     */
    public function streamInvoicePDF(Donasi $donasi): \Illuminate\Http\Response
    {
        try {
            // Ensure relationships are loaded
            $donasi->load(['donatur', 'jenisDonasi', 'metodePembayaran']);
            
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($donasi);
            
            // Prepare data untuk PDF
            $data = $this->prepareInvoiceData($donasi, $invoiceNumber);
            
            // Generate PDF using donation form template
            $pdf = Pdf::loadView('invoices.donation-form-invoice', $data)
                ->setPaper('a5', 'landscape')
                ->setOptions($this->getDefaultPdfOptions());
            
            $filename = 'invoice-' . $donasi->nomor_transaksi_unik . '.pdf';
            
            return $pdf->stream($filename);
            
        } catch (Exception $e) {
            Log::error('Invoice PDF streaming failed', [
                'donasi_id' => $donasi->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate PDF from HTML (Unified method)
     */
    public function generatePdfFromHtml(string $html, array $options = []): string
    {
        try {
            $orientation = $options['orientation'] ?? 'portrait';
            $paper = $options['paper'] ?? 'A4';
            
            $pdf = Pdf::loadHTML($html)
                ->setPaper($paper, $orientation)
                ->setOptions($this->getDefaultPdfOptions());
            
            return $pdf->output();
            
        } catch (Exception $e) {
            Log::error('PDF generation from HTML failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate PDF from view
     */
    public function generatePdfFromView(string $view, array $data = [], array $options = []): string
    {
        try {
            $orientation = $options['orientation'] ?? 'portrait';
            $paper = $options['paper'] ?? 'A4';
            
            $pdf = Pdf::loadView($view, $data)
                ->setPaper($paper, $orientation)
                ->setOptions($this->getDefaultPdfOptions());
            
            return $pdf->output();
            
        } catch (Exception $e) {
            Log::error('PDF generation from view failed', [
                'view' => $view,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Stream PDF from view
     */
    public function streamPdfFromView(string $view, array $data = [], array $options = []): \Illuminate\Http\Response
    {
        try {
            $orientation = $options['orientation'] ?? 'portrait';
            $paper = $options['paper'] ?? 'A4';
            $filename = $options['filename'] ?? 'document.pdf';
            
            $pdf = Pdf::loadView($view, $data)
                ->setPaper($paper, $orientation)
                ->setOptions($this->getDefaultPdfOptions());
            
            return $pdf->stream($filename);
            
        } catch (Exception $e) {
            Log::error('PDF streaming from view failed', [
                'view' => $view,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate invoice dengan template yang berbeda
     */
    public function generateInvoiceWithTemplate(Donasi $donasi, string $templateName = 'donation-form-invoice'): string
    {
        try {
            // Ensure relationships are loaded
            $donasi->load(['donatur', 'jenisDonasi', 'metodePembayaran']);
            
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($donasi);
            
            // Prepare data untuk PDF
            $data = $this->prepareInvoiceData($donasi, $invoiceNumber);
            
            // Tentukan template dan paper size
            $templatePath = 'invoices.' . $templateName;
            $paperSize = 'a5';
            $orientation = 'landscape';
            
            if (str_contains($templateName, 'simple-invoice')) {
                $paperSize = 'a4';
                $orientation = 'portrait';
            } elseif (str_contains($templateName, 'compact') || str_contains($templateName, 'form')) {
                $paperSize = 'a5';
                $orientation = 'landscape';
            }
            
            try {
                // Generate PDF using specified template
                $pdf = Pdf::loadView($templatePath, $data)
                    ->setPaper($paperSize, $orientation)
                    ->setOptions($this->getDefaultPdfOptions());
                
                
            } catch (Exception $e) {
                Log::warning('Template failed, falling back to simple template', [
                    'template' => $templateName,
                    'error' => $e->getMessage()
                ]);
                
                // Fallback ke template sederhana
                $pdf = Pdf::loadView('invoices.donation-form-invoice', $data)
                    ->setPaper('a5', 'landscape')
                    ->setOptions($this->getDefaultPdfOptions());
            }
            
            // Generate filename
            $filename = 'invoice-' . $donasi->nomor_transaksi_unik . '.pdf';
            $path = 'invoices/' . $filename;
            
            // Ensure directory exists
            Storage::disk('private')->makeDirectory('invoices');
            
            // Save PDF to storage
            Storage::disk('private')->put($path, $pdf->output());
            
            $fullPath = Storage::disk('private')->path($path);
            
            
            return $fullPath;
            
        } catch (Exception $e) {
            Log::error('Invoice PDF generation failed', [
                'donasi_id' => $donasi->id,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Prepare data untuk invoice PDF
     */
    private function prepareInvoiceData(Donasi $donasi, string $invoiceNumber): array
    {
        return [
            'invoice_number' => $invoiceNumber,
            'donasi' => $donasi,
            'donatur' => $donasi->donatur,
            'jenis_donasi' => $donasi->jenisDonasi?->nama ?? 'Donasi Umum',
            'catatan' => $donasi->keterangan_infak_khusus ?? $donasi->catatan_donatur ?? '',
            
            // Data untuk kompatibilitas template lama
            'nama_donatur' => $donasi->donatur->nama ?? 'N/A',
            'telepon_donatur' => $donasi->donatur->nomor_hp ?? '',
            'alamat_donatur' => $donasi->donatur->alamat_lengkap ?? '',
            'email_donatur' => $donasi->donatur->email ?? '',
            'jumlah' => $donasi->jumlah,
            'nomor_transaksi' => $donasi->nomor_transaksi_unik,
        ];
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): array
    {
        return [
            'donation-form-invoice' => 'Template Form Donasi (Rekomendasi)',
            'simple-invoice' => 'Template Sederhana (Legacy)',
            'professional-donation-form' => 'Template Professional (Seperti Formulir Asli)',
            'donasi-form' => 'Form Donasi Lengkap (Mungkin bermasalah)',
            'donasi-form-compact' => 'Form Donasi Kompak (Mungkin bermasalah)',
        ];
    }

    /**
     * Validate if template exists
     */
    public function templateExists(string $templateName): bool
    {
        $templatePath = resource_path('views/invoices/' . $templateName . '.blade.php');
        return file_exists($templatePath);
    }

    /**
     * Get default PDF options yang aman untuk shared hosting
     */
    private function getDefaultPdfOptions(): array
    {
        return [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,        // Disable remote content untuk avoid GD issues
            'isPhpEnabled' => false,           // Disable PHP untuk security
            'defaultFont' => 'DejaVu Sans',
            'enable_font_subsetting' => false,
            'disable_font_subsetting' => true,
            'dpi' => 96,
            'defaultPaperSize' => 'a4',
            'tempDir' => storage_path('app/temp/'),
            // Avoid GD extension requirements
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'enable_remote' => false,
            'enable_html5_parser' => true,
        ];
    }
}
