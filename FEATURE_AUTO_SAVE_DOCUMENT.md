# Automatic Attendance Document Saving

## Implementasi Selesai ✓

Fitur automatic saving untuk attendance_document telah diimplementasikan dengan flow sebagai berikut:

### 1. **Service Layer** 
File: `app/Services/AttendanceDocumentService.php`

- `saveDocument()` - Menyimpan metadata dokumen ke database
- `generateDocumentId()` - Generate ID unik untuk dokumen

### 2. **Model**
File: `app/Models/AttendanceDocument.php`

- Relasi ke `Internship` dan `User` (yang generate)
- Auto casting untuk date fields

### 3. **Controller Updates**

#### AdminController::exportRekapPdf()
- PDF disimpan ke: `storage/app/attendance_documents/YYYY/MM/DD/`
- Database record dibuat dengan:
  - internship_id
  - file_path
  - qr_hash
  - period_start & period_end (jika ada bulan spesifik)
  - generated_by (Auth::id())
  - generated_at (sekarang)

**Logic Penyimpanan:**
- Jika filter 1 peserta → simpan 1 record
- Jika filter institusi tanpa search → simpan untuk setiap peserta
- Jika ada search → hanya download, tidak simpan ke DB

#### UserController::exportRekapPdf()
- User export laporan pribadi mereka
- Otomatis simpan ke DB dengan periode yang sesuai

### 4. **Database Structure**
```sql
attendance_document {
  document_id: string (PK)
  internship_id: string (FK)
  generated_by: bigint (FK to users)
  period_start: date (nullable)
  period_end: date (nullable)
  file_path: string
  qr_hash: string
  generated_at: timestamp
}
```

## Flow Sekarang

```
User klik "Export PDF"
    ↓
Generate PDF & QR Code
    ↓
Simpan PDF ke storage/app/attendance_documents/
    ↓
Simpan metadata ke attendance_document table
    ↓
Download PDF ke browser user
```

## Testing

### Test Admin Export (Single Peserta)
1. Login sebagai Admin
2. Rekap → Cari 1 peserta
3. Export PDF
4. Check: `storage/app/attendance_documents/2026/01/21/` memiliki file
5. Check DB: `attendance_document` memiliki 1 record baru

### Test User Export
1. Login sebagai User (peserta)
2. Rekap → Pilih bulan
3. Export PDF
4. Check storage dan database

### Test Queryable dari Database
```php
// Di tinker atau code
$docs = \App\Models\AttendanceDocument::with(['internship', 'generatedBy'])->latest()->get();

foreach ($docs as $doc) {
    echo $doc->internship->user->name;  // Nama peserta
    echo $doc->generatedBy->name;       // Siapa yang generate
    echo $doc->file_path;               // Path file
    echo $doc->generated_at;            // Kapan dibuat
}
```

## Next Steps (Fase 2)
- [ ] Create menu "Dokumen Saya" untuk user view history
- [ ] Implement QR Verification endpoint yang ketat
- [ ] Add download button untuk dokumen yang sudah tersimpan
- [ ] Cleanup/Archive untuk dokumen lama
- [ ] Permission check untuk download
