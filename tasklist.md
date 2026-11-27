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
| DB-01 | USERS | Halvina | Belum | |
| DB-02 | OWNERS | Rifqy | Belum | |
| DB-03 | SHIPS | Rifqy | Belum | ⚠ Kolom `jenis_kapal` ditambahkan di luar ERD atas permintaan Regina 2026-11-25; perlu konfirmasi Rifqy. |
| DB-04 | UNITS | Anisa | Belum | |
| DB-05 | WORK_CATEGORIES | Anisa | Belum | Tambah `is_active` (PRD 6.7), perlu konfirmasi |
| DB-06 | WORK_TYPES | Anisa | Belum | |
| DB-07 | MATERIAL_SPECS | Anisa | Belum | |
| DB-08 | TARIFFS | Anisa | Belum | |
| DB-09 | OPNAMS | Regina | Selesai | Migrasi & model: `tanggal_opnam`, `catatan`, `total_biaya`, 7 konstanta status. `jenis_kapal` tersalin dari `SHIPS` saat simpan draft; `lokasi` & `adjustment_amount` di luar ERD (permintaan 2026-11-25). |
| DB-10 | OPNAM_ITEMS | Regina | Selesai | `lokasi`, `faktor`, `ketebalan`, `kompleksitas`, `catatan_tambahan`; snapshot `unit_price`/`subtotal` diuji kebal terhadap perubahan tarif. |
| DB-11 | OPNAM_ATTACHMENTS | Regina | Selesai | Migrasi & model: `keterangan`, `file_size` = `integer` (byte); terverifikasi via `db:table`. |
| DB-12 | STATUS_HISTORIES | Hazel | Selesai | Pemilik tunggal diputuskan 2026-11-26; struktur tidak diubah, dipakai opnam dan tagihan. |
| DB-13 | BILLINGS | Hazel | Selesai | Dibuat otomatis saat AD-08, nomor `BLG-{id}`. |
| DB-14 | BILLING_ADJUSTMENTS | Hazel | Selesai | Satu baris penyesuaian per tagihan, catatan disposisi wajib. |
| DB-15 | INVOICES | Hazel | Selesai | Penanda `draft`/`terbit`, revisi hanya sebelum terbit, PRD 6.7 no. 2. |
| DB-16 | TECHNICAL_REPORTS | Hazel | Selesai | Dibuat saat persetujuan, tanpa harga. |
| DB-17 | ACTIVITY_LOGS | Rifqy | Belum | Audit log. |

## 2. Checklist use case (50)

Urutan pengerjaan:

1. **Fondasi data** — DB-09, DB-10, DB-11 sudah bermigrasi; tabel induk (`USERS`, `OWNERS`, `SHIPS`, master tarif) tersedia.
2. **Pencatatan opnam** — PT-01, PT-02, PT-06.
3. **Item & kalkulasi** — PT-03, PT-05. Snapshot ditulis sekali, tidak dihitung ulang dari tarif master.
4. **Unggah bukti** — PT-04; batas format/ukuran dibaca dari `config/opnam.php`.
5. **Daftar opnam** — AD-05 (pencarian, filter status/tanggal, paginasi).

| ID | Use case | Pemilik | Status | Catatan |
|---|---|---|---|---|
| UC-U1 | Login | Halvina | Selesai | Username atau email (case-insensitive); pesan error persis `Username/email atau password salah, atau akun tidak aktif.`; `last_login_at` diisi; LoginTest lulus. |
| UC-U2 | Logout | Halvina | Selesai | Session diinvalidasi dan diarahkan ke `login` melalui `AuthController`; LoginTest lulus. |
| UC-U3 | Mengubah Password | Halvina | Belum | |
| UC-U4 | Melihat Dashboard | Halvina | Belum | |
| UC-U5 | Melihat dan Mengubah Profil | Halvina | Belum | Profil untuk ketiga role. |
| PT-01 | Mencatat Opnam Pekerjaan | Regina | Selesai | Wizard 5 langkah; langkah 1 wajib kapal/tanggal/`lokasi` (2 dock tanpa placeholder); catatan tidak ditampilkan (kolom tetap); `jenis_kapal` tersalin dari `SHIPS`. |
| PT-02 | Memilih Data Kapal | Regina | Selesai | Kapal wajib (BR-11); pemilik dan nomor lambung terisi otomatis. |
| PT-03 | Menambahkan Detail Pekerjaan | Regina | Selesai | Item draft dapat ditambah, diubah, dan dihapus oleh pemiliknya; `unit_price` dicari ulang hanya bila material/satuan diubah (BR-35). |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Regina | Selesai | Upload per item via UID (`bukti[uid][]`); JPG/PNG/PDF ≤10 MB; format, ukuran, dan disk dari `config/opnam.php`. |
| PT-05 | Melihat Perhitungan Biaya | Regina | Selesai | `subtotal = volume × unit_price × faktor`; ketebalan 1x, kompleksitas low 1,00x / medium 1,10x / high 1,15x; `total_biaya = Σsubtotal + adjustment_amount`; pembulatan 2 desimal; snapshot beku. |
| PT-06 | Menyimpan Draft Opnam | Regina | Selesai | Nomor `OPN-{tahun}-{3 digit}` via `OpnamNumberGenerator` dalam transaksi dengan kunci baris; status persis `draft`. |
| PT-07 | Mengajukan Opnam untuk Review | Di luar tupoksi | Belum | Lihat `tupoksi.md` bagian 3 |
| PT-08 | Melihat Status Pengajuan | Regina | Selesai | Opname Saya: hanya opnam milik petugas login; pencarian, filter status/tanggal, paginasi (BR-27). |
| PT-09 | Memperbaiki Opnam yang Dikembalikan | Di luar tupoksi | Belum | Lihat `tupoksi.md` bagian 3 |
| PT-10 | Melihat Riwayat Aktivitas | Rifqy | Belum | Cakupan: seluruh aktivitas (PRD 3.3) |
| PT-11 | Mengekspor Riwayat Aktivitas ke PDF | Hazel | Selesai | Ekspor PDF mengikuti filter aktif dan cakupan role; `ActivityLogPdfExporter` memakai `Dompdf\Dompdf`. |
| AD-01 | Mengelola Pengguna | Halvina | Belum | |
| AD-02 | Mengelola Data Owner dan Kapal | Rifqy | Belum | |
| AD-03 | Mengelola Kategori Pekerjaan | Anisa | Selesai | CRUD + soft delete di `admin.work-categories.*`; WorkCategoryTest lulus. |
| AD-04 | Mengelola Jenis Pekerjaan, Material, Satuan, Tarif | Anisa | Selesai | CRUD + soft delete di `admin.work-types.*`, `admin.material-specs.*`, `admin.units.*`; test terkait lulus. |
| AD-05 | Melihat Daftar Opnam | Regina | Selesai | Pencarian, filter status/operator/tanggal, paginasi (BR-27); tombol mata membuka detail baca-saja `admin.opnams.show`. |
| AD-06 | Memeriksa Opnam | Hazel | Selesai | Daftar dan detail review dengan pencarian, filter tanggal, rincian pekerjaan, biaya, dan lampiran. |
| AD-07 | Merevisi Data Opnam | Hazel | Selesai | Revisi saat `menunggu_review` oleh Admin; data, item, dan total dihitung ulang memakai `OpnamCostCalculator`, perubahan tercatat di ACTIVITY_LOGS. |
| AD-08 | Menyetujui Hasil Review | Hazel | Selesai | Meneruskan ke Direksi, mencatat riwayat, dan membuat tagihan otomatis (ST-08). |
| AD-09 | Mengembalikan Opnam untuk Revisi | Hazel | Selesai | Catatan wajib tersimpan di `STATUS_HISTORIES.note`. |
| AD-10 | Melihat Riwayat Perubahan | Rifqy | Belum | Gabungan Riwayat Revisi; konfirmasi cakupan. |
| AD-11 | Melihat Riwayat Pekerjaan Kapal | Rifqy | Selesai | Satu fitur dengan DR-15; RepairHistoryTest lulus. |
| AD-12 | Merevisi Invoice | Hazel | Selesai | Admin, hanya saat `draft`; field teks snapshot dan tanggal. |
| AD-13 | Mengekspor Invoice ke PDF | Hazel | Selesai | Ekspor pertama mempublikasikan invoice; memakai `Dompdf\Dompdf`. |
| AD-14 | Melihat Riwayat Aktivitas | Rifqy | Selesai | Audit log; ActivityLogTest lulus. |
| AD-15 | Mengekspor Riwayat Aktivitas ke PDF | Hazel | Selesai | Ekspor PDF mengikuti filter aktif dan cakupan role; `ActivityLogPdfExporter` memakai `Dompdf\Dompdf`. |
| AD-16 | Melihat dan Mengekspor Laporan Rekapitulasi | Rifqy | Selesai | Halaman rekapitulasi, ekspor PDF dan Excel; WorkRecapitulationTest lulus. |
| DR-01 | Melihat Draft Menunggu Approval | Hazel | Selesai | Sumber `BILLINGS.status = menunggu_approval`. |
| DR-02 | Memeriksa Draft Tagihan | Hazel | Selesai | Rincian opnam, penyesuaian, dan total. |
| DR-03 | Merevisi Data Opnam | Hazel | Selesai | Revisi saat `menunggu_approval_direksi` oleh Direksi; tagihan menunggu approval ikut diselaraskan dengan penyesuaian tetap dipertahankan. |
| DR-04 | Memberikan Diskon atau Markup | Hazel | Selesai | Nominal, satu baris penyesuaian per tagihan. |
| DR-05 | Mencatat Disposisi | Hazel | Selesai | `disposition_note` wajib. |
| DR-06 | Menyetujui Tagihan | Hazel | Selesai | Sinkron `BILLINGS` dan `OPNAMS`, buat draf dokumen. |
| DR-07 | Menolak Tagihan | Hazel | Selesai | Alasan di `STATUS_HISTORIES.note`, tanpa pengajuan ulang. |
| DR-08 | Menerbitkan Invoice PDF | Hazel | Selesai | Dibuat saat DR-06, dipublikasikan saat ekspor pertama. |
| DR-09 | Menerbitkan Technical Report PDF | Hazel | Selesai | Dibuat saat DR-06, tanpa harga. |
| DR-10 | Merevisi Invoice | Hazel | Selesai | Direksi, hanya saat `draft`. |
| DR-11 | Mengekspor Invoice ke PDF | Hazel | Selesai | Terpisah dari DR-12. |
| DR-12 | Mengunduh atau Mencetak Dokumen | Hazel | Selesai | Invoice dan technical report terbit. |
| DR-13 | Melihat Rekap Tagihan | Rifqy | Selesai | Rekap tagihan tersedia untuk Direksi. |
| DR-14 | Mengekspor Laporan PDF/Excel | Rifqy | Selesai | Memakai `RecapitulationExporter`. |
| DR-15 | Mencari dan Melihat Riwayat Perbaikan Kapal | Rifqy | Selesai | Satu fitur dengan AD-11. |
| DR-16 | Melihat Riwayat Perubahan | Rifqy | Selesai | Riwayat revisi digabung ke Riwayat Perubahan. |
| DR-17 | Melihat Riwayat Aktivitas | Rifqy | Selesai | Audit log; ActivityLogTest lulus. |
| DR-18 | Mengekspor Riwayat Aktivitas ke PDF | Hazel | Selesai | Ekspor PDF mengikuti filter aktif dan cakupan role; `ActivityLogPdfExporter` memakai `Dompdf\Dompdf`. |

## 3. Checklist transisi status (10)

| ID | Transisi | Pemilik | Status | Catatan |
|---|---|---|---|---|
| ST-01 | Opnam: `draft` → `menunggu_review` | Di luar tupoksi | Belum | PT-07 |
| ST-02 | Opnam: `menunggu_review` → `perlu_revisi` | Hazel | Selesai | AD-09 |
| ST-03 | Opnam: `menunggu_review` → `menunggu_approval_direksi` | Hazel | Selesai | AD-08 |
| ST-04 | Opnam: `perlu_revisi` → `menunggu_review` | Di luar tupoksi | Belum | PT-09, sudah diputuskan |
| ST-05 | Opnam: `menunggu_approval_direksi` → `disetujui` | Hazel | Selesai | DR-06 |
| ST-06 | Opnam: `menunggu_approval_direksi` → `ditolak` | Hazel | Selesai | DR-07 |
| ST-07 | Opnam: `disetujui` → `dokumen_terbit` | Hazel | Selesai | Pemicu: invoice dipublikasikan dan technical report tersedia |
| ST-08 | Tagihan: (dibuat) → `menunggu_approval` | Hazel | Selesai | Dibuat otomatis saat AD-08, nomor `BLG-%06d`; pemilik ditetapkan 2026-11-26 |
| ST-09 | Tagihan: `menunggu_approval` → `disetujui` | Hazel | Selesai | DR-06 |
| ST-10 | Tagihan: `menunggu_approval` → `ditolak` | Hazel | Selesai | DR-07 |

## Catatan parkir

- PT-07, PT-09, ST-01, ST-04 — alur pengajuan dan perbaikan opnam oleh Petugas belum masuk tugas siapa pun (`tupoksi.md` bagian 3). PT-08 sejak 2026-11-26 menjadi milik Regina.
- Menghitung ulang `unit_price`, `subtotal`, atau `total_biaya` dari tarif master setelah tersimpan (BR-10).
- Tabel/middleware/policy milik anggota lain, serta field dari mockup (`contact person`, telepon, `Berlaku hingga`, kuantitas minimum, faktor tarif).

## Sinkronisasi

Saat sebuah item selesai, lakukan dalam **satu perubahan yang sama**:

1. Update Status menjadi `Selesai` dan isi Catatan di `tasklist.md` ini.
2. Update Status dan Catatan di checklist `prd.md` Bagian 10 (checklist resmi sesuai Definition of Done).

Jika item masih tertahan, tandai `Perlu konfirmasi` dan ajukan pertanyaan memakai format `taskinstruction.md` Bagian 8.
