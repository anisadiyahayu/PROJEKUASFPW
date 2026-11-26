# Tupoksi SIMSHIP

Pembagian tugas dan tanggung jawab anggota tim. Acuan persyaratan produk ada di `prd.md`, dan status pengerjaan ada di `tasklist.md`.

## 1. Pembagian tugas

| Anggota | Modul | Tugas |
|---|---|---|
| Regina | Opnam dan Kalkulasi Biaya | Halaman daftar opnam; form pencatatan opnam; tambah, edit, hapus detail pekerjaan sesuai status transaksi; hubungkan opnam dengan data kapal; kalkulasi biaya otomatis berdasarkan tarif dan spesifikasi; unggah foto/bukti pekerjaan |
| Anisa | Master Data Pekerjaan dan Tarif | CRUD kategori pekerjaan; CRUD jenis pekerjaan; CRUD material dan spesifikasi; CRUD satuan pekerjaan; CRUD tarif dasar; faktor tambahan tarif sesuai spesifikasi teknis (**belum ada di PRD, lihat PRD Bagian 9 no. 14**) |
| Rifqy | Master Data Owner dan Kapal serta Laporan | CRUD owner kapal; CRUD kapal; hubungkan kapal dengan owner; pencarian riwayat perbaikan kapal; halaman rekapitulasi pekerjaan; membantu ekspor laporan PDF/Excel; riwayat perubahan dan audit log |
| Halvina | Autentikasi, Pengguna, dan Dashboard | Login dan logout; ubah password; CRUD pengguna; tiga role (Admin, Petugas, Direksi); middleware dan policy; dashboard per role; lihat dan ubah profil |
| Hazel | Review, Approval, Disposisi, dan Dokumen Tagihan | Halaman review opnam; setujui hasil review atau kembalikan opnam untuk revisi; proses approval tagihan Direksi; tolak tagihan; diskon dan markup; catat disposisi; Invoice PDF; Technical Report PDF; unduh dan cetak dokumen |

## 2. Aturan khusus
- Middleware dan policy dipakai oleh semua modul, jadi perubahannya hanya dilakukan oleh Halvina.
- Tugas Rifqy untuk ekspor laporan berstatus membantu, sehingga pemilik mesin ekspor bersama perlu dikonfirmasi (lihat bagian 3 no. 8).
- Modul yang dipakai sebagai dasar pembagian: Umum (UC-U), Petugas (PT), Admin (AD), Direksi (DR), dan Basis data (17 tabel).

## 3. Item yang belum punya pemilik (parkir)

Item ini tidak boleh dikerjakan sebelum tim memutuskan pemiliknya.

| No | Item | Alasan belum ada di tupoksi | Keputusan yang dibutuhkan |
|---|---|---|---|
| 1 | PT-07, PT-08, PT-09, ST-01, ST-04 | Alur pengajuan dan perbaikan opnam oleh Petugas belum masuk tugas siapa pun | Tentukan pemilik |
| 2 | AD-07, DR-03 | Revisi data opnam oleh Admin dan Direksi belum masuk tugas siapa pun | Tentukan pemilik |
| 3 | AD-12, DR-10 | Revisi invoice belum masuk tugas siapa pun | Tentukan pemilik |
| 4 | PT-11, AD-15, DR-18 | Ekspor riwayat aktivitas belum masuk tugas siapa pun | Tentukan pemilik |
| 5 | ST-08 | Pembuatan tagihan dari opnam yang diteruskan belum punya pemilik (PRD punya rekomendasi di 4.7) | Tentukan pemilik dan konfirmasi cara |
| 6 | DB-12 STATUS_HISTORIES | Dipakai Regina (opnam) dan Hazel (tagihan) | Tentukan pemilik tunggal |
| 7 | AD-10, AD-11, AD-14, PT-10, DR-13, DR-16, DR-17 | Cakupan riwayat dan audit per role belum dipisah ke pemilik | Konfirmasi pemilik masing-masing |
| 8 | Mesin ekspor PDF/Excel | Modul ekspor bersama belum punya pemilik | Tentukan pemilik |
| 9 | Middleware dan policy | Dipakai semua modul | Sudah ke Halvina, konfirmasi tim |
| 10 | Faktor tambahan tarif (Anisa) | Tidak ada di use case, ERD, atau PRD | Putuskan dipakai atau dihapus dari tupoksi |
