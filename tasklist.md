# Tasklist SIMSHIP

Daftar pekerjaan dan status pengerjaan. Acuan persyaratan ada di `prd.md`, dan pembagian tugas serta item tanpa pemilik ada di `tupoksi.md`. Penomoran use case mengikuti PRD revisi 2 (50 use case; DR-16 Riwayat Perubahan, DR-17 Riwayat Aktivitas, DR-18 Ekspor Riwayat Aktivitas).

## Aturan untuk AI agent
- Sebelum mengerjakan apa pun, cari item di file ini. Kerjakan hanya item yang Pemiliknya sama dengan orang yang meminta.
- Item tanpa pemilik ("Di luar tupoksi"), atau yang menyentuh modul lintas tim (USERS, login, tabel bersama), jangan dikerjakan. Tanyakan dulu.
- Setelah item selesai, ubah Status dan isi Catatan dalam perubahan yang sama.
- Nilai Status: Belum, Dikerjakan, Selesai, Perlu konfirmasi.

## 1. Checklist basis data (17 tabel)

| ID | Item | Pemilik | Status | Blokir / pertanyaan terbuka | Catatan |
|---|---|---|---|---|---|
| DB-09 | `OPNAMS` | Regina | Selesai | 9 no. 32 (`opnam_number`), 34/39 (`catatan`, `tanggal_opnam`), 38 (`total_biaya`) | Migrasi diperbaiki + model disinkronkan (7 konstanta status ERD, fillable `tanggal_opnam`/`catatan`/`total_biaya`). Terverifikasi via `db:table`. Logika aplikasi menyusul di PT-01/PT-06. |
| DB-10 | `OPNAM_ITEMS` | Regina | Selesai | — | Migrasi + model: `lokasi`, `faktor`, `ketebalan`, `kompleksitas`, `catatan_tambahan`. 4 kolom terakhir di luar ERD (disetujui 2026-11-26). |
| DB-11 | `OPNAM_ATTACHMENTS` | Regina | Selesai | 9 no. 18 (format, ukuran, lokasi file) | Migrasi: tambah `keterangan` nullable, `file_size` = `integer` (byte); model: fillable `keterangan`. Terverifikasi via `db:table`. |
| PT-01 | Mencatat Opnam Pekerjaan | Regina | Selesai | — | Wizard 5 langkah. Langkah 1: wajib Pilih Kapal / Tanggal Opname / `Lokasi Pekerjaan` (`<select>` 2 dock tanpa placeholder); field auto-fill readonly, **Catatan dihapus dari form** (kolom DB tetap). Faktor 1x/1,25x dihitung & disimpan. PT-07 tetap parkir. |
| PT-02 | Memilih Data Kapal | Regina | Selesai | — | Wajib (BR-11); pilih `SHIPS` + `OWNERS`, data kapal terisi otomatis. |
| PT-03 | Menambahkan Detail Pekerjaan | Regina | Selesai | — | Item dinamis; kategori→jenis→material→satuan (baca master Anisa). Tambah `Ketebalan`, `Kompleksitas` (Low/Medium/High → `faktor`), `Catatan Tambahan`. Hapus UI Faktor & Lokasi. Spesifikasi material auto dari master. |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Regina | Selesai | — | Upload **per item** lewat UID (`bukti[uid][]`); `saveDraft` memetakan uid→item_id (tidak lagi semua ke item pertama). JPG/PNG/PDF ≤10 MB; validasi BR-26. Tes 2 item + 2 file lulus. |
| PT-05 | Melihat Perhitungan Biaya | Regina | Selesai | — | Tarif terbaru per material+satuan; `subtotal = volume × unit_price`; `total_biaya` = jumlah subtotal (disetujui); snapshot beku. |
| PT-06 | Menyimpan Draft Opnam | Regina | Selesai | — | Tombol Simpan Draft tersedia di Final Review (sesuai screenshot); nomor `OPN-{tahun}-{3 digit}` (disetujui); status persis `draft`. |
| PT-08 | Melihat Status Pengajuan (Opname Saya) | Regina | Selesai | Tombol Detail masih placeholder (PT-09 parkir) | Halaman `/petugas/opnams`: hanya opnam `created_by = auth()->id()`, pencarian, filter status/tanggal, paginasi (BR-27). 3 tes Pest lulus. |
| AD-05 | Melihat Daftar Opnam | Regina | Selesai | — | Tampilan Supervisor sesuai screenshot: breadcrumb, badge status, kolom Operator, paginasi. Filter `q`, status, rentang `tanggal_opnam`. 5 tes Pest lulus (BR-27). |
| ID | Item | Pemilik | Status | Catatan |
|---|---|---|---|---|
| DB-01 | USERS | Halvina | Belum | |
| DB-02 | OWNERS | Rifqy | Belum | |
| DB-03 | SHIPS | Rifqy | Belum | |
| DB-04 | UNITS | Anisa | Belum | |
| DB-05 | WORK_CATEGORIES | Anisa | Belum | Tambah `is_active` (PRD 6.7), perlu konfirmasi |
| DB-06 | WORK_TYPES | Anisa | Belum | |
| DB-07 | MATERIAL_SPECS | Anisa | Belum | |
| DB-08 | TARIFFS | Anisa | Belum | |
| DB-09 | OPNAMS | Regina | Selesai | `jenis_kapal` ditulis sebagai salinan dari `SHIPS` lewat migrasi aditif (rekomendasi default, masih menunggu konfirmasi tim). Tetap tidak menulis `lokasi`/`adjustment_amount`. |
| DB-10 | OPNAM_ITEMS | Regina | Selesai | Snapshot `unit_price`/`subtotal` diuji kebal perubahan tarif; `faktor`, `ketebalan`, `kompleksitas`, dan `catatan_tambahan` ikut tersimpan. `faktor_ketebalan` dan `faktor_kompleksitas` ditambah lewat migrasi aditif default 1 (rekomendasi default, nilai faktor masih menunggu konfirmasi); kolom `faktor` lama dipertahankan. |
| DB-11 | OPNAM_ATTACHMENTS | Regina | Selesai | Migrasi/model sesuai ERD; `file_size` dalam byte. |
| DB-12 | STATUS_HISTORIES | Hazel | Selesai | Pemilik tunggal diputuskan 2026-11-26, struktur tidak diubah, dipakai opnam dan tagihan |
| DB-13 | BILLINGS | Hazel | Selesai | Dibuat otomatis saat AD-08, nomor `BLG-{id}` |
| DB-14 | BILLING_ADJUSTMENTS | Hazel | Selesai | Satu baris penyesuaian per tagihan, catatan disposisi wajib |
| DB-15 | INVOICES | Hazel | Selesai | Penanda `draft`/`terbit`, revisi hanya sebelum terbit, PRD 6.7 no. 2 |
| DB-16 | TECHNICAL_REPORTS | Hazel | Selesai | Dibuat saat persetujuan, tanpa harga |
| DB-17 | ACTIVITY_LOGS | Rifqy | Belum | Audit log |

| DB-09 | OPNAMS | Regina | Selesai | `jenis_kapal` ditulis sebagai salinan dari `SHIPS` lewat migrasi aditif (rekomendasi default, masih menunggu konfirmasi tim). Tetap tidak menulis `lokasi`/`adjustment_amount`. |
| DB-10 | OPNAM_ITEMS | Regina | Selesai | Snapshot `unit_price`/`subtotal` diuji kebal perubahan tarif; `faktor`, `ketebalan`, `kompleksitas`, dan `catatan_tambahan` ikut tersimpan. `faktor_ketebalan` dan `faktor_kompleksitas` ditambah lewat migrasi aditif default 1 (rekomendasi default, nilai faktor masih menunggu konfirmasi); kolom `faktor` lama dipertahankan. |
| DB-11 | OPNAM_ATTACHMENTS | Regina | Selesai | Migrasi/model sesuai ERD; `file_size` dalam byte. |
| DB-12 | STATUS_HISTORIES | Di luar tupoksi | Belum | Dipakai Regina dan Hazel, lihat Tupoksi bagian 3 |
| DB-13 | BILLINGS | Hazel | Belum | |
| DB-14 | BILLING_ADJUSTMENTS | Hazel | Belum | |
| DB-15 | INVOICES | Hazel | Belum | Penanda draft/terbit, PRD 6.7 no. 2 |
| DB-16 | TECHNICAL_REPORTS | Hazel | Belum | |
| DB-17 | ACTIVITY_LOGS | Rifqy | Belum | Audit log |

## 2. Checklist use case (50)

1. **Fondasi data** — migrasi + model DB-09, DB-10, DB-11. Menunggu tabel induk dari tim lain: `USERS` (Halvina) → `OWNERS`/`SHIPS` (Rifqy) → master tarif (Anisa). Migrasi tidak boleh merujuk tabel yang belum ada (`taskinstruction.md` Bagian 4).
2. **Pencatatan opnam** — PT-01, PT-02, PT-06.
3. **Item & kalkulasi** — PT-03, PT-05. Snapshot ditulis sekali, tidak dihitung ulang dari tarif master.
4. **Unggah bukti** — PT-04, setelah batas file diputuskan.
5. **Daftar opnam** — AD-05 (pencarian, filter status/tanggal, paginasi).
| ID | Use case | Pemilik | Status | Catatan |
|---|---|---|---|---|
| UC-U1 | Login | Halvina | Belum | |
| UC-U2 | Logout | Halvina | Belum | |
| UC-U3 | Mengubah Password | Halvina | Belum | |
| UC-U4 | Melihat Dashboard | Halvina | Belum | |
| UC-U5 | Melihat dan Mengubah Profil | Halvina | Belum | Profil untuk ketiga role |
| PT-01 | Mencatat Opnam Pekerjaan | Regina | Dikerjakan | Wizard 5 langkah berjalan (langkah 4 "Bukti Pekerjaan", langkah 5 "Final Review"). Input `catatan` opsional tersedia dan `jenis_kapal` tersalin sebagai snapshot. Penamaan tahapan akhir & daftar jenis kapal masih menunggu PRD 9 no. 16, 18. |
| PT-02 | Memilih Data Kapal | Regina | Selesai | Kapal wajib, pemilik & nomor lambung tampil otomatis (BR-11). |
| PT-03 | Menambahkan Detail Pekerjaan | Regina | Selesai | Item pada draft tersimpan dapat ditambah, diubah, dan dihapus hanya oleh pemiliknya (PRD 4.6). `total_biaya` dihitung ulang lewat `OpnamCostCalculator`; `unit_price` hanya dicari ulang bila material/satuan diubah (BR-35). Status `perlu_revisi` tidak disentuh (PT-09 parkir). Seluruh satuan dikirim ke wizard dan disaring di JS berdasarkan material/tarif, termasuk satuan duplikat agar opsi tidak kosong. |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Regina | Perlu konfirmasi | Validasi format/ukuran dan disk/path kini dibaca dari `config/opnam.php` (default JPG/PNG/PDF, 10 MB, disk public). Format final menunggu PRD 9 no. 5. |
| PT-05 | Melihat Perhitungan Biaya | Regina | Perlu konfirmasi | `OpnamCostCalculator` terpasang di form dan halaman edit draft; kedua faktor tersimpan terpisah sebagai snapshot di `OPNAM_ITEMS`. Kompleksitas mengikuti keputusan Regina: low 1x, medium 1,10x, high 1,15x. Faktor ketebalan tetap 1x karena aturan rentang belum diberikan. Satuan di wizard dibatasi m²/m dan mengikuti tarif material. Aturan pembulatan masih menunggu PRD 9 no. 19, 21. |
| PT-06 | Menyimpan Draft Opnam | Regina | Dikerjakan | Tombol Simpan Draft berjalan; nomor dibuat `OpnamNumberGenerator` dalam transaksi dengan kunci baris (tidak ada duplikat). Prefix/panjang seq dari `config/opnam.php`; format final menunggu PRD 9 no. 8. |
| PT-07 | Mengajukan Opnam untuk Review | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| PT-08 | Melihat Status Pengajuan | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| PT-09 | Memperbaiki Opnam yang Dikembalikan | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| PT-10 | Melihat Riwayat Aktivitas | Rifqy | Belum | Cakupan: seluruh aktivitas (PRD 3.3) |
| PT-11 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-01 | Mengelola Pengguna | Halvina | Belum | |
| AD-02 | Mengelola Data Owner dan Kapal | Rifqy | Belum | |
| AD-03 | Mengelola Kategori Pekerjaan | Anisa | Belum | |
| AD-04 | Mengelola Jenis Pekerjaan, Material, Satuan, Tarif | Anisa | Belum | |
| AD-05 | Melihat Daftar Opnam | Regina | Selesai | Pencarian, filter status, operator (dari `USERS.role=petugas`), tanggal satu input (Tanggal Opname), dan paginasi berjalan (BR-27). Tombol mata membuka halaman detail baca-saja (bukan review/approval). |
| AD-06 | Memeriksa Opnam | Hazel | Selesai | Daftar dan detail review dengan pencarian, filter tanggal, rincian pekerjaan, biaya, dan lampiran |
| AD-07 | Merevisi Data Opnam | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-08 | Menyetujui Hasil Review | Hazel | Selesai | Meneruskan ke Direksi, mencatat riwayat, dan membuat tagihan otomatis (ST-08) |
| AD-09 | Mengembalikan Opnam untuk Revisi | Hazel | Selesai | Catatan wajib tersimpan di `STATUS_HISTORIES.note` |
| AD-10 | Melihat Riwayat Perubahan | Rifqy | Belum | Gabungan Riwayat Revisi, konfirmasi |
| AD-11 | Melihat Riwayat Pekerjaan Kapal | Rifqy | Belum | Satu fitur dengan DR-15 |
| AD-12 | Merevisi Invoice | Hazel | Selesai | Admin, hanya saat `draft`, field teks snapshot dan tanggal |
| AD-13 | Mengekspor Invoice ke PDF | Hazel | Selesai | Ekspor pertama mempublikasikan invoice |
| AD-14 | Melihat Riwayat Aktivitas | Rifqy | Belum | Audit log |
| AD-15 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| AD-16 | Melihat dan Mengekspor Laporan Rekapitulasi | Rifqy | Belum | Halaman rekapitulasi |
| DR-01 | Melihat Draft Menunggu Approval | Hazel | Selesai | Sumber `BILLINGS.status = menunggu_approval` |
| DR-02 | Memeriksa Draft Tagihan | Hazel | Selesai | Rincian opnam, penyesuaian, dan total |
| DR-03 | Merevisi Data Opnam | Di luar tupoksi | Belum | Lihat Tupoksi bagian 3 |
| DR-04 | Memberikan Diskon atau Markup | Hazel | Selesai | Nominal, satu baris per tagihan |
| DR-05 | Mencatat Disposisi | Hazel | Selesai | `disposition_note` wajib |
| DR-06 | Menyetujui Tagihan | Hazel | Selesai | Sinkron `BILLINGS` dan `OPNAMS`, buat draf dokumen |
| DR-07 | Menolak Tagihan | Hazel | Selesai | Alasan di `STATUS_HISTORIES.note`, tanpa pengajuan ulang |
| DR-08 | Menerbitkan Invoice PDF | Hazel | Selesai | Dibuat saat DR-06, dipublikasikan saat ekspor pertama |
| DR-09 | Menerbitkan Technical Report PDF | Hazel | Selesai | Dibuat saat DR-06, tanpa harga |
| DR-10 | Merevisi Invoice | Hazel | Selesai | Direksi, hanya saat `draft` |
| DR-11 | Mengekspor Invoice ke PDF | Hazel | Selesai | Terpisah dari DR-12 |
| DR-12 | Mengunduh atau Mencetak Dokumen | Hazel | Selesai | Invoice dan technical report terbit |
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

- PT-07, PT-09 — item parkir (`taskinstruction.md` Bagian 6, no. 1). PT-08 sejak 2026-11-26 menjadi milik Regina.
- Menghitung ulang `unit_price`, `subtotal`, atau `total_biaya` dari tarif master setelah tersimpan (BR-10).
- Tabel/middleware/policy milik anggota lain, serta field dari mockup (`contact person`, telepon, `Berlaku hingga`, kuantitas minimum, faktor tarif).

## Sinkronisasi

Saat sebuah item selesai, lakukan dalam **satu perubahan yang sama**:

1. Update Status menjadi `Selesai` dan isi Catatan di `tasklist.md` ini.
2. Update Status dan Catatan di checklist `prd.md` Bagian 10 (checklist resmi sesuai Definition of Done).

Jika item masih tertahan, tandai `Perlu konfirmasi` dan ajukan pertanyaan memakai format `taskinstruction.md` Bagian 8.
=======
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
