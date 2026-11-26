# Tasklist SIMSHIP

Daftar pekerjaan dan status pengerjaan. Acuan persyaratan ada di `prd.md`, dan pembagian tugas serta item tanpa pemilik ada di `tupoksi.md`. Penomoran use case mengikuti PRD revisi 2 (50 use case; DR-16 Riwayat Perubahan, DR-17 Riwayat Aktivitas, DR-18 Ekspor Riwayat Aktivitas).

## Aturan untuk AI agent
- Sebelum mengerjakan apa pun, cari item di file ini. Kerjakan hanya item yang Pemiliknya sama dengan orang yang meminta.
- Item tanpa pemilik ("Di luar tupoksi"), atau yang menyentuh modul lintas tim (USERS, login, tabel bersama), jangan dikerjakan. Tanyakan dulu.
- Setelah item selesai, ubah Status dan isi Catatan dalam perubahan yang sama.
- Nilai Status: Belum, Dikerjakan, Selesai, Perlu konfirmasi.

## 1. Checklist basis data (17 tabel)

| ID | Item | Pemilik | Status | Catatan |
|---|---|---|---|---|
| DB-01 | USERS | Halvina | Dikerjakan | |
| DB-02 | OWNERS | Rifqy | Belum | |
| DB-03 | SHIPS | Rifqy | Belum | |
| DB-04 | UNITS | Anisa | Belum | |
| DB-05 | WORK_CATEGORIES | Anisa | Belum | Tambah `is_active` (PRD 6.7), perlu konfirmasi |
| DB-06 | WORK_TYPES | Anisa | Belum | |
| DB-07 | MATERIAL_SPECS | Anisa | Belum | |
| DB-08 | TARIFFS | Anisa | Belum | |
| DB-09 | OPNAMS | Regina | Belum | |
| DB-10 | OPNAM_ITEMS | Regina | Belum | |
| DB-11 | OPNAM_ATTACHMENTS | Regina | Belum | |
| DB-12 | STATUS_HISTORIES | Di luar tupoksi | Belum | Dipakai Regina dan Hazel, lihat Tupoksi bagian 3 |
| DB-13 | BILLINGS | Hazel | Belum | |
| DB-14 | BILLING_ADJUSTMENTS | Hazel | Belum | |
| DB-15 | INVOICES | Hazel | Belum | Penanda draft/terbit, PRD 6.7 no. 2 |
| DB-16 | TECHNICAL_REPORTS | Hazel | Belum | |
| DB-17 | ACTIVITY_LOGS | Rifqy | Belum | Audit log |

## 2. Checklist use case (50)

| ID | Use case | Pemilik | Status | Catatan |
|---|---|---|---|---|
| UC-U1 | Login | Halvina | Dikerjakan | |
| UC-U2 | Logout | Halvina | Dikerjakan | |
| UC-U3 | Mengubah Password | Halvina | Belum | |
| UC-U4 | Melihat Dashboard | Halvina | Belum | |
| UC-U5 | Melihat dan Mengubah Profil | Halvina | Belum | Profil untuk ketiga role |
| PT-01 | Mencatat Opnam Pekerjaan | Regina | Belum | |
| PT-02 | Memilih Data Kapal | Regina | Belum | |
| PT-03 | Menambahkan Detail Pekerjaan | Regina | Belum | |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Regina | Belum | Batas file belum ditetapkan |
| PT-05 | Melihat Perhitungan Biaya | Regina | Belum | |
| PT-06 | Menyimpan Draft Opnam | Regina | Belum | Sudah diputuskan: tersedia di tahapan pencatatan |
| PT-07 | Mengajukan Opnam untuk Review | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| PT-08 | Melihat Status Pengajuan | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| PT-09 | Memperbaiki Opnam yang Dikembalikan | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| PT-10 | Melihat Riwayat Aktivitas | Rifqy | Belum | Cakupan: seluruh aktivitas (PRD 3.3) |
| PT-11 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-01 | Mengelola Pengguna | Halvina | Belum | |
| AD-02 | Mengelola Data Owner dan Kapal | Rifqy | Belum | |
| AD-03 | Mengelola Kategori Pekerjaan | Anisa | Belum | |
| AD-04 | Mengelola Jenis Pekerjaan, Material, Satuan, Tarif | Anisa | Belum | |
| AD-05 | Melihat Daftar Opnam | Regina | Belum | Halaman daftar opnam |
| AD-06 | Memeriksa Opnam | Hazel | Belum | Halaman review opnam |
| AD-07 | Merevisi Data Opnam | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-08 | Menyetujui Hasil Review | Hazel | Belum | |
| AD-09 | Mengembalikan Opnam untuk Revisi | Hazel | Belum | |
| AD-10 | Melihat Riwayat Perubahan | Rifqy | Belum | Gabungan Riwayat Revisi, konfirmasi |
| AD-11 | Melihat Riwayat Pekerjaan Kapal | Rifqy | Belum | Satu fitur dengan DR-15 |
| AD-12 | Merevisi Invoice | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-13 | Mengekspor Invoice ke PDF | Hazel | Belum | Konfirmasi |
| AD-14 | Melihat Riwayat Aktivitas | Rifqy | Belum | Audit log |
| AD-15 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-16 | Melihat dan Mengekspor Laporan Rekapitulasi | Rifqy | Belum | Halaman rekapitulasi |
| DR-01 | Melihat Draft Menunggu Approval | Hazel | Belum | |
| DR-02 | Memeriksa Draft Tagihan | Hazel | Belum | |
| DR-03 | Merevisi Data Opnam | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| DR-04 | Memberikan Diskon atau Markup | Hazel | Belum | |
| DR-05 | Mencatat Disposisi | Hazel | Belum | |
| DR-06 | Menyetujui Tagihan | Hazel | Belum | |
| DR-07 | Menolak Tagihan | Hazel | Belum | |
| DR-08 | Menerbitkan Invoice PDF | Hazel | Belum | Diterbitkan sistem |
| DR-09 | Menerbitkan Technical Report PDF | Hazel | Belum | Diterbitkan sistem |
| DR-10 | Merevisi Invoice | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| DR-11 | Mengekspor Invoice ke PDF | Hazel | Belum | Konfirmasi |
| DR-12 | Mengunduh atau Mencetak Dokumen | Hazel | Belum | |
| DR-13 | Melihat Rekap Tagihan | Rifqy | Belum | Konfirmasi |
| DR-14 | Mengekspor Laporan PDF/Excel | Rifqy | Belum | Membantu ekspor laporan |
| DR-15 | Mencari dan Melihat Riwayat Perbaikan Kapal | Rifqy | Belum | |
| DR-16 | Melihat Riwayat Perubahan | Rifqy | Belum | Penomoran baru |
| DR-17 | Melihat Riwayat Aktivitas | Rifqy | Belum | Audit log |
| DR-18 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |

## 3. Checklist transisi status (10)

| ID | Transisi | Pemilik | Status | Catatan |
|---|---|---|---|---|
| ST-01 | Opnam: draft → menunggu_review | Di luar tupoksi | Belum | PT-07 |
| ST-02 | Opnam: menunggu_review → perlu_revisi | Hazel | Belum | AD-09 |
| ST-03 | Opnam: menunggu_review → menunggu_approval_direksi | Hazel | Belum | AD-08 |
| ST-04 | Opnam: perlu_revisi → menunggu_review | Di luar tupoksi | Belum | PT-09, sudah diputuskan |
| ST-05 | Opnam: menunggu_approval_direksi → disetujui | Hazel | Belum | DR-06 |
| ST-06 | Opnam: menunggu_approval_direksi → ditolak | Hazel | Belum | DR-07 |
| ST-07 | Opnam: disetujui → dokumen_terbit | Hazel | Belum | Pemicu: rekomendasi PRD 4.6, konfirmasi |
| ST-08 | Tagihan: (dibuat) → menunggu_approval | Di luar tupoksi | Belum | Rekomendasi PRD 4.7 |
| ST-09 | Tagihan: menunggu_approval → disetujui | Hazel | Belum | DR-06 |
| ST-10 | Tagihan: menunggu_approval → ditolak | Hazel | Belum | DR-07 |