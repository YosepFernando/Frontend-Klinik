<?php

// Test the new filtering logic with actual API structure
$apiResponse = json_decode('{
  "status": "success",
  "message": "Data hasil seleksi berhasil diambil",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id_hasil_seleksi": 6,
        "id_user": 11,
        "id_lamaran_pekerjaan": 7,
        "status": "diterima",
        "catatan": "Keputusan: Diterima. Mulai kerja: 2025-08-25",
        "created_at": "2025-08-18T07:19:35.000000Z",
        "updated_at": "2025-08-18T07:33:29.000000Z",
        "user": {
          "id_user": 11,
          "nama_user": "adhim",
          "no_telp": "082340619880",
          "email": "adhim@gmail.com",
          "tanggal_lahir": "2003-02-23T00:00:00.000000Z",
          "foto_profil": null,
          "role": "kasir",
          "created_at": "2025-08-18T04:41:47.000000Z",
          "updated_at": "2025-08-18T07:33:29.000000Z"
        },
        "lamaran_pekerjaan": {
          "id_lamaran_pekerjaan": 7,
          "id_lowongan_pekerjaan": 3,
          "id_user": 11,
          "nama_pelamar": "adhim@gmail.com",
          "email_pelamar": "adgim@gmail.com",
          "NIK_pelamar": "2222222222222222",
          "telepon_pelamar": "082340619880",
          "alamat_pelamar": "adhim",
          "pendidikan_terakhir": "D3",
          "status": "diterima",
          "created_at": "2025-08-18T04:43:13.000000Z",
          "updated_at": "2025-08-18T04:47:14.000000Z",
          "lowongan_pekerjaan": {
            "id_lowongan_pekerjaan": 3,
            "judul_pekerjaan": "Kasir - Part Time/Full Time",
            "id_posisi": 5,
            "jumlah_lowongan": 1,
            "pengalaman_minimal": "6 bulan",
            "gaji_minimal": "4000000.00",
            "gaji_maksimal": "5000000.00",
            "status": "aktif"
          }
        }
      },
      {
        "id_hasil_seleksi": 3,
        "id_user": 10,
        "id_lamaran_pekerjaan": 5,
        "status": "pending",
        "catatan": "Lulus tes praktik kasir dan wawancara. Siap untuk onboarding.",
        "created_at": "2025-08-17T18:04:32.000000Z",
        "updated_at": "2025-08-17T18:04:32.000000Z",
        "user": {
          "id_user": 10,
          "nama_user": "Sinta Pelanggan",
          "no_telp": "081234567899",
          "email": "pelanggan2@gmail.com",
          "tanggal_lahir": "1991-06-20T00:00:00.000000Z",
          "foto_profil": "pelanggan2.jpg",
          "role": "pelanggan",
          "created_at": "2025-08-17T18:04:32.000000Z",
          "updated_at": "2025-08-17T18:04:32.000000Z"
        },
        "lamaran_pekerjaan": {
          "id_lamaran_pekerjaan": 5,
          "id_lowongan_pekerjaan": 3,
          "id_user": 9,
          "nama_pelamar": "Rudi Hermawan",
          "email_pelamar": "rudi.hermawan@gmail.com",
          "NIK_pelamar": "3201015212920001",
          "telepon_pelamar": "081234567905",
          "alamat_pelamar": "Jl. Mawar No. 654, Jakarta Utara",
          "pendidikan_terakhir": "SMA",
          "status": "diterima",
          "created_at": "2025-08-17T18:04:32.000000Z",
          "updated_at": "2025-08-18T07:34:04.000000Z",
          "lowongan_pekerjaan": {
            "id_lowongan_pekerjaan": 3,
            "judul_pekerjaan": "Kasir - Part Time/Full Time",
            "id_posisi": 5,
            "jumlah_lowongan": 1,
            "pengalaman_minimal": "6 bulan",
            "gaji_minimal": "4000000.00",
            "gaji_maksimal": "5000000.00",
            "status": "aktif"
          }
        }
      }
    ]
  }
}', true);

echo "=== TESTING NEW FILTERING LOGIC ===\n\n";

$targetId = 3;
$hasilSeleksiData = $apiResponse['data']['data'] ?? [];

echo "API returned " . count($hasilSeleksiData) . " hasil seleksi entries\n";
echo "Target lowongan ID: {$targetId}\n\n";

$filtered = array_filter($hasilSeleksiData, function($hasilSeleksi) use ($targetId) {
    echo "Processing hasil seleksi ID: " . $hasilSeleksi['id_hasil_seleksi'] . "\n";
    
    // PERBAIKAN: Ambil lowongan ID dari path yang benar
    // API struktur: hasil_seleksi.lamaran_pekerjaan.lowongan_pekerjaan.id_lowongan_pekerjaan
    $hasilLowonganId = null;
    
    if (isset($hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan']['id_lowongan_pekerjaan'])) {
        $hasilLowonganId = $hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan']['id_lowongan_pekerjaan'];
        echo "  Found lowongan ID in nested path: {$hasilLowonganId}\n";
    } elseif (isset($hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'])) {
        // Fallback ke direct field dalam lamaran_pekerjaan
        $hasilLowonganId = $hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'];
        echo "  Found lowongan ID in direct field: {$hasilLowonganId}\n";
    } else {
        echo "  No lowongan ID found!\n";
    }
    
    if (empty($hasilLowonganId) || $hasilLowonganId != $targetId) {
        echo "  FILTERED OUT: ID {$hasilLowonganId} != target {$targetId}\n";
        return false;
    }
    
    echo "  PASSED: ID {$hasilLowonganId} matches target {$targetId}\n";
    return true;
});

echo "\n=== RESULTS ===\n";
echo "Original count: " . count($hasilSeleksiData) . "\n";
echo "Filtered count: " . count($filtered) . "\n";

if (count($filtered) > 0) {
    echo "\nFiltered entries:\n";
    foreach ($filtered as $item) {
        echo "- ID: " . $item['id_hasil_seleksi'] . 
             ", User: " . $item['user']['nama_user'] . 
             ", Status: " . $item['status'] . "\n";
    }
} else {
    echo "\nERROR: No entries passed the filter!\n";
}

echo "\n=== Test completed ===\n";
