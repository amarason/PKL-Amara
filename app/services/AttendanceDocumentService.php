<?php

namespace App\Services;

use App\Models\AttendanceDocument;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AttendanceDocumentService
{
    /**
     * Simpan dokumen attendance ke database
     * 
     * @param string $internshipId - ID peserta magang
     * @param string $filePath - Path file PDF yang disimpan
     * @param string $qrHash - Hash encrypted untuk QR verification
     * @param mixed $periodStart - Tanggal mulai periode (nullable)
     * @param mixed $periodEnd - Tanggal akhir periode (nullable)
     * @return AttendanceDocument
     */
    public function saveDocument(
        string $internshipId,
        string $filePath,
        string $qrHash,
        $periodStart = null,
        $periodEnd = null
    ): AttendanceDocument {
        return AttendanceDocument::create([
            'document_id' => Str::uuid()->toString(),
            'internship_id' => $internshipId,
            'file_path' => $filePath,
            'qr_hash' => $qrHash,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'generated_by' => Auth::id(),
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate document_id yang unik
     */
    public static function generateDocumentId(): string
    {
        return 'DOC-' . date('YmdHis') . '-' . strtoupper(Str::random(6));
    }
}
