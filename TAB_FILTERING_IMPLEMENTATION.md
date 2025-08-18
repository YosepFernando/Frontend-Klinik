# Tab-Specific Filtering Implementation

## Overview
Implementasi filtering berdasarkan tab untuk sistem rekrutmen agar status/progres seleksi lamaran tampil benar dan informatif di setiap tab.

## Changes Made

### 1. Filter Logic Implementation
Menambahkan logic filtering di `applications-table.blade.php` untuk menyembunyikan aplikasi yang sudah lulus ke tahap berikutnya:

#### Tab "Seleksi Berkas" (`$stage === 'document'`)
- **HIDE**: Aplikasi dengan `document_status = 'accepted'` atau `'diterima'`
- **SHOW**: Aplikasi dengan `document_status = 'pending'`, `'menunggu'`, `'rejected'`, atau `'ditolak'`
- **Logic**: Yang sudah diterima dokumennya pindah ke tab Interview

#### Tab "Interview" (`$stage === 'interview'`)
- **HIDE**: 
  - Aplikasi dengan `interview_status = 'lulus'` atau `'passed'` (sudah lulus ke final)
  - Aplikasi yang dokumennya belum diterima (`document_status != 'accepted'/'diterima'`)
- **SHOW**: Aplikasi yang dokumennya sudah diterima dan belum lulus interview
- **Logic**: Hanya yang lolos seleksi berkas dan belum selesai interview

#### Tab "Hasil Seleksi/Final" (`$stage === 'final'`)
- **SHOW**: Aplikasi yang sudah lulus interview ATAU sudah memiliki status final
- **HIDE**: Aplikasi yang belum lulus interview dan belum ada status final
- **Logic**: Hanya yang sudah melalui tahap interview

### 2. Implementation Details

```php
// Filter logic berdasarkan stage
$shouldSkip = false;

// Tab Seleksi Berkas: Sembunyikan yang sudah diterima (sudah lulus ke tahap selanjutnya)
if (isset($stage) && $stage === 'document') {
    if ($docStatus === 'accepted' || $docStatus === 'diterima') {
        $shouldSkip = true; // Jangan tampilkan yang sudah diterima di tab berkas
    }
}

// Tab Interview: Sembunyikan yang sudah lulus interview (sudah lulus ke tahap final)
if (isset($stage) && $stage === 'interview') {
    if ($intStatus === 'lulus' || $intStatus === 'passed') {
        $shouldSkip = true; // Jangan tampilkan yang sudah lulus interview di tab interview
    }
    // Juga jangan tampilkan yang dokumennya belum diterima
    if ($docStatus !== 'accepted' && $docStatus !== 'diterima') {
        $shouldSkip = true; // Hanya tampilkan yang dokumennya sudah diterima
    }
}

// Tab Final: Hanya tampilkan yang sudah lulus interview atau memiliki status final
if (isset($stage) && $stage === 'final') {
    // Hanya tampilkan jika sudah lulus interview ATAU sudah ada status final
    $hasValidInterviewStatus = ($intStatus === 'lulus' || $intStatus === 'passed');
    $hasValidFinalStatus = ($finalStatus !== 'pending' && $finalStatus !== 'menunggu');
    
    if (!$hasValidInterviewStatus && !$hasValidFinalStatus) {
        $shouldSkip = true; // Jangan tampilkan yang belum lulus interview dan belum ada status final
    }
}
```

### 3. Enhanced Empty State Messages
Menambahkan pesan kosong yang lebih kontekstual untuk setiap tab:

- **Tab Seleksi Berkas**: "Tidak ada aplikasi yang perlu direview dokumennya. Aplikasi yang sudah diterima dokumennya akan pindah ke tab Interview."
- **Tab Interview**: "Tidak ada aplikasi yang siap untuk tahap interview. Hanya aplikasi yang dokumennya sudah diterima dan belum lulus interview yang tampil di sini."
- **Tab Final**: "Tidak ada aplikasi yang siap untuk keputusan final. Hanya aplikasi yang sudah lulus interview yang tampil di sini."

## Benefits

### 1. Progressive Workflow
- Setiap tab hanya menampilkan aplikasi yang relevan untuk tahap tersebut
- Mengurangi kebingungan dan meningkatkan efisiensi workflow

### 2. Clear Status Visibility
- Status yang sudah selesai tidak mengganggu view
- Fokus pada tindakan yang masih perlu dilakukan

### 3. Improved UX
- Interface lebih bersih dan mudah dipahami
- Workflow yang lebih natural dan intuitif

## Status Mapping

### Document Status
- `pending`, `menunggu` → Perlu direview
- `accepted`, `diterima` → Sudah diterima (pindah ke Interview)
- `rejected`, `ditolak` → Ditolak (tetap di tab Berkas)

### Interview Status
- `not_scheduled`, `belum_dijadwal` → Belum dijadwal (show jika doc accepted)
- `scheduled`, `terjadwal`, `pending` → Sudah dijadwal (show)
- `lulus`, `passed` → Sudah lulus (pindah ke Final)
- `tidak_lulus`, `ditolak`, `failed` → Tidak lulus (tetap di Interview)

### Final Status
- `pending`, `menunggu` → Menunggu keputusan
- `accepted`, `diterima` → Diterima bekerja
- `rejected`, `ditolak` → Ditolak final
- `waiting_list` → Waiting list

## Files Modified
- `/resources/views/recruitments/partials/applications-table.blade.php`

## Testing Recommendations
1. Test dengan data aplikasi di berbagai status
2. Verifikasi perpindahan aplikasi antar tab
3. Test empty state untuk setiap tab
4. Verifikasi bahwa tab "Semua" tetap menampilkan semua aplikasi

## Notes
- Tab "Semua" (`$stage === 'all'` atau `isset($showAll)`) tetap menampilkan semua aplikasi tanpa filtering
- Logic filtering hanya berlaku untuk tab spesifik
- Filtering dilakukan di level view untuk performa yang baik
