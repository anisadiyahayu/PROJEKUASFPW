<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sumber nilai faktor kalkulasi opnam
    |--------------------------------------------------------------------------
    |
    | PRD 3.10.4: sampai sumber nilai faktor diputuskan tim, modul Opnam
    | memakai konfigurasi ini. Isi awal sengaja kosong sehingga semua faktor
    | bernilai 1. Pertanyaan terkait: PRD 9 no. 19, 20, dan 23.
    |
    | Bentuk peta: nilai input => faktor. Contoh setelah ada keputusan:
    |   'faktor_ketebalan' => [12 => 1.2],
    |   'faktor_kompleksitas' => ['tinggi' => 1.1],
    |
    */

    /*
     * Faktor ketebalan masih 1x untuk semua input karena aturan rentang
     * belum ditetapkan (PRD 9 no. 19). Nilai faktor ketebalan tidak diubah
     * di sini mengikuti keputusan Regina.
     */
    'faktor_ketebalan' => [],

    /*
     * Sesuai keputusan Regina: low = 1x, medium = 1,10x, high = 1,15x.
     */
    'faktor_kompleksitas' => [
        'low' => 1.0,
        'medium' => 1.10,
        'high' => 1.15,
    ],

    /*
     * PRD 3.10.4 memberi default sementara pembulatan 2 desimal untuk
     * subtotal_item; total_biaya adalah jumlah nilai yang sudah dibulatkan.
     * Pertanyaan terkait: PRD 9 no. 21.
     */
    'pembulatan' => 2,

    /*
     |--------------------------------------------------------------------------
     | Validasi dan penyimpanan bukti pekerjaan (PT-04, BR-26)
     |--------------------------------------------------------------------------
     |
     | Nilai awal mengikuti default sementara: JPG/PNG/PDF, maks. 10 MB,
     | disimpan di disk public. Pertanyaan terkait: PRD 9 no. 5.
     | Diubah di sini saat client menetapkan format, ukuran, atau lokasi.
     |
     */
    'unggahan' => [
        'mimes' => 'jpg,jpeg,png,pdf',
        'max_kb' => 10240,
        'disk' => 'public',
        'path' => 'opnam-attachments',
    ],

    /*
     |--------------------------------------------------------------------------
     | Format nomor opnam (PT-06)
     |--------------------------------------------------------------------------
     |
     | Default sementara: OPN-{tahun}-{seq}. Pertanyaan terkait: PRD 9 no. 8.
     | Generator memakai transaksi dan kunci baris agar seq tetap unik.
     |
     */
    'nomor' => [
        'prefix' => 'OPN',
        'panjang_seq' => 3,
    ],

    /*
      |--------------------------------------------------------------------------
      | Daftar jenis kapal (DB-03 / PT-01)
      |--------------------------------------------------------------------------
      |
      | Keputusan Regina 2026-11-27: jenis kapal memakai pilihan tetap. Rifqy
      | selaku pemilik SHIPS tidak keberatan. Pertanyaan terkait: PRD 9 no. 18.
      | Nilai lama di SHIPS/OPNAMS yang tidak ada dalam daftar tetap dibaca
      | sebagai snapshot dan tidak dinormalisasi.
      |
      */
    'jenis_kapal' => [
        'Tugboat',
        'Barge',
        'Ferry',
        'Kapal Kargo',
    ],

];
