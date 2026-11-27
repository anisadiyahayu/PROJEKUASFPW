# PRD SIMSHIP (Versi Bahasa Indonesia, Revisi 2)

Sistem Pencatatan Opnam Pekerjaan Kapal.

Dokumen ini hanya berisi persyaratan produk. Tasklist, pembagian tugas, dan status pengerjaan ada di `tasklist.md` dan `tupoksi.md`.

**Perubahan dari versi sebelumnya**
- Bagian Checklist Progres dan Kepemilikan dipindah ke file terpisah.
- Keputusan yang sudah disepakati di Analisis Fitur v3 dimasukkan ke PRD. Daftarnya ada di Bagian 10.
- Penomoran use case mengikuti Analisis Fitur v3 (DR-16 Riwayat Perubahan, DR-17 Riwayat Aktivitas, DR-18 Ekspor Riwayat Aktivitas). Total use case jadi 50.
- Hal yang ada di PRD tetapi belum ada di ERD ditambahkan sebagai penambahan resmi (Bagian 6.7).

---

## 0. Panduan untuk AI Agent (baca dahulu)

### Urutan sumber
1. **PRD ini** adalah sumber utama. Jika PRD lebih lengkap dari ERD, ikuti PRD.
2. **ERD revisi** (`Perancangan_database_Laravel_rev.pdf`) adalah dasar struktur basis data. Penambahan di atas ERD hanya yang tercantum di Bagian 6.7.
3. **Use case dan diagram** (`Usecase.pdf`) dan **Analisis Fitur v3** adalah acuan perilaku. Jika bertentangan dengan PRD ini, PRD ini yang berlaku, dan pertentangannya dicatat di Bagian 9 atau 10.
4. **Mockup UI** bukan acuan. Jangan membangun layar dari mockup.

### Aturan wajib
- Jangan membuat tabel, kolom, nilai enum, role, atau status di luar ERD dan Bagian 6.7.
- Jangan menambah fitur, validasi, atau hak akses di luar Bagian 2 sampai 5.
- Item bertanda **[TERBUKA]** atau tercantum di Bagian 9: berhenti dan tanyakan. Item bertanda **[REKOMENDASI]** boleh diimplementasikan sebagai default, tetapi harus mudah diubah.
- Gunakan nilai enum persis seperti di ERD (misalnya `menunggu_review`).
- Kolom snapshot (`unit_price`, `subtotal`, `total_biaya`, `ship_name`, `hull_number`, `owner_name`, `owner_address`, `subtotal_amount`, `adjustment_amount`, `total_amount`) ditulis sekali saat transaksi terjadi dan tidak dihitung ulang dari data master setelahnya.
- Soft delete (`deleted_at`) hanya pada tabel yang mendefinisikannya di ERD.

---

## 1. Ringkasan Produk

SIMSHIP mencatat opnam (inspeksi pekerjaan kapal) di lapangan, memeriksanya, lalu mengubah pekerjaan yang disetujui menjadi tagihan, invoice, dan technical report.

**Tujuan:** mencatat pekerjaan kapal beserta estimasi biayanya, memeriksa catatan tersebut, dan menyelesaikannya lewat keputusan Direksi.

**Pengguna:** Petugas (pencatat lapangan), Admin (pengelola data dan pemeriksa), Direksi (pengambil keputusan akhir).

**Lingkup:**
- 50 use case: 5 umum (UC-U1 sampai UC-U5), 11 Petugas (PT-01 sampai PT-11), 16 Admin (AD-01 sampai AD-16), 18 Direksi (DR-01 sampai DR-18).
- 17 tabel dan 24 relasi dari ERD, ditambah penambahan di Bagian 6.7.
- Pencarian, filter, dan paginasi adalah fitur pendukung di dalam use case, bukan use case tersendiri.

**Di luar cakupan:** desain antarmuka (sedang direvisi).

**Istilah:** Operator menjadi Petugas, Supervisor menjadi Admin. Rekap Aktivitas dan Audit Log menjadi Riwayat Aktivitas. Riwayat Revisi (Admin) digabung ke Riwayat Perubahan.

---

## 2. Aktor dan Hak Akses

Fitur umum (login, logout, ubah password, dashboard, profil) tersedia untuk ketiga aktor. Akun nonaktif tidak dapat masuk.

| Fitur | Petugas | Admin | Direksi |
|---|:-:|:-:|:-:|
| Akun dan akses (UC-U1 sampai UC-U5) | Ya | Ya | Ya |
| Mencatat, menyimpan draft, mengajukan, memantau, memperbaiki opnam (PT-01 sampai PT-09) | Ya | - | - |
| Melihat daftar, memeriksa, meneruskan atau mengembalikan opnam (AD-05, AD-06, AD-08, AD-09) | - | Ya | - |
| Kelola pengguna dan master data (AD-01 sampai AD-04) | - | Ya | - |
| Memeriksa dan memutuskan tagihan, diskon atau markup, disposisi (DR-01, DR-02, DR-04 sampai DR-07) | - | - | Ya |
| Revisi data opnam (AD-07, DR-03) | - | Ya | Ya |
| Revisi invoice (AD-12, DR-10) | - | Ya | Ya |
| Ekspor invoice ke PDF (AD-13, DR-11) | - | Ya | Ya |
| Unduh atau cetak dokumen terbit (DR-12) | - | - | Ya |
| Riwayat Aktivitas dan ekspor PDF (PT-10, PT-11, AD-14, AD-15, DR-17, DR-18) | Ya | Ya | Ya |
| Riwayat Perubahan (AD-10, DR-16) | - | Ya | Ya |
| Riwayat Pekerjaan Kapal (AD-11, DR-15) | - | Ya | Ya |
| Laporan rekapitulasi dan rekap tagihan (AD-16, DR-13, DR-14) | - | Ya | Ya |

Batasan eksplisit Petugas: tidak boleh mengubah tarif dasar, memberi diskon atau markup, memberi persetujuan akhir, atau mengekspor invoice.

---

## 3. Persyaratan Fungsional

Kolom **Tabel** merujuk ke ERD.

### 3.1 Modul Akun dan Akses (semua aktor)

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| UC-U1 | Login | Masuk dengan username atau email dan password. Setelah berhasil, diarahkan ke dashboard sesuai role. Mengisi `last_login_at`. | BR-01 | USERS |
| UC-U2 | Logout | Mengakhiri sesi, kembali ke halaman login. | - | USERS |
| UC-U3 | Ubah Password | Mengganti password akun yang sedang login. | Aturan password **[TERBUKA]** | USERS |
| UC-U4 | Dashboard | Ringkasan sesuai role. | Isi per role **[TERBUKA]** | - |
| UC-U5 | Lihat dan Ubah Profil | Melihat dan mengubah data diri. Halaman Profil harus ada untuk ketiga role. | Field yang boleh diubah **[TERBUKA]** | USERS |

### 3.2 Modul Pencatatan Opnam (Petugas)

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| PT-01 | Mencatat Opnam | Membuat opnam melalui tahapan berurutan: informasi kapal, detail pekerjaan, estimasi biaya, dan bukti pekerjaan. Dapat disimpan sebagai draft atau dikirim. Menyertakan PT-02. PT-03 dan PT-04 opsional. | BR-11, BR-14 | OPNAMS, OPNAM_ITEMS, SHIPS, TARIFFS, STATUS_HISTORIES |
| PT-02 | Pilih Data Kapal | Memilih kapal dari daftar. Pemilik dan nomor lambung ditampilkan. | BR-11: wajib sebelum tahapan berikutnya | SHIPS, OWNERS |
| PT-03 | Tambah Detail Pekerjaan | Menambah satu atau lebih item: kategori, jenis pekerjaan, material, volume, keterangan, lokasi. Item bisa ditambah, diubah, dan dihapus selama status opnam `draft` atau `perlu_revisi`. | BR-12, BR-28 | OPNAM_ITEMS, TARIFFS, MATERIAL_SPECS, WORK_TYPES, UNITS |
| PT-04 | Unggah Foto/Bukti | Mengunggah file bukti pada item pekerjaan, dengan validasi format dan ukuran. | BR-13, BR-26. Batas format dan ukuran **[TERBUKA]** | OPNAM_ATTACHMENTS |
| PT-05 | Lihat Perhitungan Biaya | Menampilkan estimasi biaya otomatis dari item dan tarif berlaku. Hanya tampilan. | BR-14 | OPNAM_ITEMS, TARIFFS |
| PT-06 | Simpan Draft | Menyimpan opnam yang belum dikirim agar bisa dilanjutkan. Tersedia di tahapan pencatatan. Status `draft`. | - | OPNAMS |
| PT-07 | Ajukan untuk Review | Tombol "Kirim Opnam" di akhir pencatatan. Status menjadi `menunggu_review`, `submitted_at` terisi, opnam masuk ke Status Pengajuan. | BR-15 | OPNAMS, STATUS_HISTORIES |
| PT-08 | Lihat Status Pengajuan | Daftar opnam beserta status dan riwayat prosesnya. | - | OPNAMS, STATUS_HISTORIES |
| PT-09 | Perbaiki Opnam yang Dikembalikan | Membuka opnam `perlu_revisi`, memperbaiki kesalahan input, lalu mengajukan ulang. Status kembali ke `menunggu_review`. Berbeda dari revisi oleh Admin dan Direksi. | BR-21 | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS, STATUS_HISTORIES |

### 3.3 Modul Riwayat dan Ekspor

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| PT-10, AD-14, DR-17 | Riwayat Aktivitas | Menampilkan seluruh aktivitas di sistem dengan kolom: pengguna, role, aktivitas, modul, deskripsi, waktu. Dilengkapi pencarian dan filter pengguna, role, modul, tanggal. | Semua role melihat seluruh aktivitas | ACTIVITY_LOGS |
| PT-11, AD-15, DR-18 | Ekspor Riwayat Aktivitas ke PDF | Mengunduh PDF sesuai filter yang sedang aktif. | Semua role | ACTIVITY_LOGS |
| AD-10, DR-16 | Riwayat Perubahan | Menampilkan perubahan data opnam dan invoice: nilai sebelum dan sesudah, pelaku, waktu. Wajib mencakup perubahan harga dan diskon. Menggantikan Riwayat Revisi (Admin). | Hanya Admin dan Direksi | ACTIVITY_LOGS (`old_values`, `new_values`), STATUS_HISTORIES |
| AD-11, DR-15 | Riwayat Pekerjaan Kapal | Mencari dan melihat riwayat pekerjaan dan perbaikan kapal berdasarkan nama atau nomor lambung. Direksi menjangkaunya lewat halaman Transaksi Disetujui. Satu fitur yang sama. | BR-25 | SHIPS, OPNAMS, OPNAM_ITEMS |

### 3.4 Modul Master Data (Admin)

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| AD-01 | Kelola Pengguna | Tambah akun, ubah data, tentukan role (petugas, admin, direksi), nonaktifkan akun. Pengguna tidak dihapus, hanya dinonaktifkan. | BR-01 | USERS |
| AD-02 | Kelola Owner dan Kapal | Tambah, ubah, hapus. Hapus memakai soft delete. Nomor lambung unik. | - | OWNERS, SHIPS |
| AD-03 | Kelola Kategori Pekerjaan | Tambah, ubah, hapus (soft delete), beserta status aktif. Nama kategori unik. | Penambahan `is_active`, lihat 6.7 | WORK_CATEGORIES |
| AD-04 | Kelola Jenis Pekerjaan, Material/Spesifikasi, Satuan, Tarif | Mengelola keempat master. Tarif dicatat dengan tanggal berlaku (`effective_from`). Hapus jenis pekerjaan dan material memakai soft delete. | BR-02, BR-06, BR-10 | WORK_TYPES, MATERIAL_SPECS, UNITS, TARIFFS |

### 3.5 Modul Pemeriksaan Opnam (Admin)

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| AD-05 | Daftar Opnam | Seluruh opnam dengan pencarian, filter status, operator, tanggal, dan paginasi. | BR-27 | OPNAMS, SHIPS |
| AD-06 | Periksa Opnam | Meninjau detail pekerjaan, bukti, dan estimasi biaya sebelum keputusan. Use case dasar untuk AD-07, AD-08, AD-09. | BR-16, BR-17 | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS |
| AD-07 | Revisi Data Opnam | Mengubah data opnam langsung saat pemeriksaan (status `menunggu_review`). Setiap perubahan tercatat di Riwayat Perubahan. | BR-19 | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS, ACTIVITY_LOGS |
| AD-08 | Setujui Hasil Review | Menyetujui dan meneruskan ke Direksi. Status `menunggu_approval_direksi`, `forwarded_at` terisi. Menyertakan AD-06. | BR-16 | OPNAMS, STATUS_HISTORIES |
| AD-09 | Kembalikan untuk Revisi | Mengembalikan ke Petugas dengan status `perlu_revisi`. Catatan wajib dan disimpan di riwayat status. Menyertakan AD-06. | BR-17, BR-18 | OPNAMS, STATUS_HISTORIES |

### 3.6 Modul Keputusan Tagihan (Direksi)

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| DR-01 | Daftar Draft Menunggu Approval | Menampilkan opnam atau tagihan yang diteruskan Admin dan menunggu keputusan. | Daftar dibaca dari `BILLINGS.status = menunggu_approval` **[REKOMENDASI]** | BILLINGS, OPNAMS |
| DR-02 | Periksa Draft Tagihan | Meninjau rincian pekerjaan, subtotal, dan total. Use case dasar untuk DR-03, DR-04, DR-06, DR-07. | BR-22 | BILLINGS, BILLING_ADJUSTMENTS, OPNAM_ITEMS |
| DR-03 | Revisi Data Opnam | Mengubah data opnam langsung saat pemeriksaan tagihan. Tercatat di Riwayat Perubahan. | BR-20 | OPNAMS, OPNAM_ITEMS, ACTIVITY_LOGS |
| DR-04 | Diskon atau Markup | Menentukan jenis (diskon atau markup), nilai, dan total akhir. Opsional. Menyertakan DR-05. Perubahan harga dan diskon wajib tercatat di log. | BR-07, BR-08, BR-24 | BILLING_ADJUSTMENTS, BILLINGS |
| DR-05 | Catat Disposisi | Alasan atas penyesuaian harga. Wajib untuk setiap diskon atau markup. | BR-08 | BILLING_ADJUSTMENTS (`disposition_note`) |
| DR-06 | Setujui Tagihan | Persetujuan akhir. Mengisi `decided_by` dan `decided_at`. Menyertakan DR-02. | BR-07, BR-22, BR-23 | BILLINGS, STATUS_HISTORIES |
| DR-07 | Tolak Tagihan | Menolak disertai alasan. Alasan disimpan di `STATUS_HISTORIES.note` **[REKOMENDASI]**. Tagihan yang ditolak dapat diajukan ulang. Menyertakan DR-02. | BR-22, BR-29 | BILLINGS, STATUS_HISTORIES |
| DR-08 | Terbitkan Invoice PDF | Setelah tagihan disetujui, sistem menerbitkan invoice. Invoice menampilkan harga. Diterbitkan sistem, bukan dipicu manual. | BR-09, BR-23 | INVOICES |
| DR-09 | Terbitkan Technical Report PDF | Setelah tagihan disetujui, sistem menerbitkan technical report tanpa harga. | BR-09, BR-23 | TECHNICAL_REPORTS |

### 3.7 Modul Invoice dan Dokumen

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| AD-12, DR-10 | Revisi Invoice | Mengubah data invoice sebelum invoice diterbitkan. Menambah `revision_count`. Perubahan dicatat di log. Admin dan Direksi. | **[TERBUKA]** hubungan dengan penerbitan otomatis, lihat Bagian 9 no. 2 | INVOICES, ACTIVITY_LOGS |
| AD-13, DR-11 | Ekspor Invoice ke PDF | Mengunduh invoice yang sudah terbit. Admin dan Direksi. Petugas tidak boleh. | BR-05, BR-09 | INVOICES |
| DR-12 | Unduh atau Cetak Dokumen | Mengunduh atau mencetak dokumen terbit (invoice dan technical report). | Digabung dengan DR-11 atau tidak **[TERBUKA]** | INVOICES, TECHNICAL_REPORTS |

### 3.8 Modul Laporan

| ID | Fitur | Perilaku | Aturan | Tabel |
|---|---|---|---|---|
| AD-16 | Lihat dan Ekspor Laporan Rekapitulasi | Laporan rekap per periode, ekspor PDF atau Excel. Admin. | Terpisah dari Riwayat Aktivitas. Isi dan kolom **[TERBUKA]** | Agregasi dari tabel transaksi |
| DR-13 | Lihat Rekap Tagihan | Rekap tagihan yang sudah diproses. Direksi. | - | BILLINGS |
| DR-14 | Ekspor Laporan PDF/Excel | Ekspor laporan. Direksi. | Terpisah dari Riwayat Aktivitas | Agregasi |

### 3.9 Fitur Pendukung (berlaku di dalam use case)

| Fitur | Berlaku di | Keterangan |
|---|---|---|
| Pencarian, filter status dan tanggal, paginasi | Semua daftar transaksi dan riwayat | BR-27 |
| Validasi format dan ukuran file | PT-04 | BR-26 |
| Estimasi biaya otomatis | PT-01, PT-05 | BR-14 |
| Pencatatan log aktivitas | Semua aksi ubah data | Lihat 4.5 |

### 3.10 Rincian Modul Opnam dan Kalkulasi Biaya

Bagian ini merinci PT-01 sampai PT-06 dan AD-05, melengkapi 3.2 dan 4.1 tanpa mengubahnya. Baris yang merujuk 6.7 berstatus perlu konfirmasi: agent mengimplementasikannya setelah tim mengonfirmasi.

#### 3.10.1 Batas pekerjaan dan antarmuka dengan modul lain

Modul Opnam hanya mengerjakan tabel `OPNAMS`, `OPNAM_ITEMS`, `OPNAM_ATTACHMENTS`, use case PT-01 sampai PT-06 dan AD-05, serta service kalkulasi biaya (3.10.4). Tidak ada perubahan yang diminta di modul lain.

| Modul | Yang dibaca modul Opnam | Perubahan di modul itu | Antarmuka yang tetap |
|---|---|---|---|
| Owner, kapal, log aktivitas | `SHIPS.name`, `hull_number`, `OWNERS.name` | Tidak ada | `SHIPS` tetap sesuai ERD. Jenis kapal disimpan di `OPNAMS` |
| Master pekerjaan dan tarif | `TARIFFS.price`, `unit_id`, `effective_from`, `MATERIAL_SPECS`, `WORK_TYPES`, `WORK_CATEGORIES`, `UNITS` | Tidak ada | `TARIFFS` tidak diberi kolom faktor. Faktor disimpan di item opnam |
| Review, approval, dokumen tagihan | Modul tagihan membaca `OPNAMS.total_biaya` dan `OPNAM_ITEMS.volume`, `unit_price`, `subtotal`, `description`, `lokasi` | Tidak ada | Nama, tipe, dan arti kolom ini tidak berubah. `subtotal` tetap hasil akhir per item dan `total_biaya` tetap jumlah `subtotal` |
| Autentikasi, pengguna, policy | Role dan policy yang sudah ada | Tidak ada | Aturan siapa mengubah apa di status apa tetap mengikuti 4.6 |

Aturan pelaksanaan:
- Modul lain dibaca saja. Jangan mengubah migrasi, model, atau policy milik modul lain, dan jangan mengubah struktur `STATUS_HISTORIES`.
- Perubahan basis data hanya lewat migrasi baru yang aditif (kolom baru nullable atau punya default). Jangan mengubah nama, tipe, atau constraint kolom ERD. Migrasi memeriksa keberadaan kolom (`hasColumn`) sebelum menambah, supaya tidak ada kolom ganda.
- Item yang belum punya pemilik di `tupoksi.md` (PT-07, PT-08, PT-09, AD-07, DR-03, ST-01, ST-04) tidak dikerjakan di sini. Modul Opnam hanya menyediakan service kalkulasi yang dapat dipakai pemiliknya kelak.

#### 3.10.2 Informasi kapal dan opnam (langkah 1)

| Field | Sumber | Aturan |
|---|---|---|
| Kapal | Pilih dari `SHIPS` | Wajib (BR-11) |
| Pemilik | `OWNERS.name` lewat `SHIPS` | Tampil otomatis, tidak dapat diubah |
| Nomor lambung | `SHIPS.hull_number` | Tampil otomatis, tidak dapat diubah |
| Jenis kapal | Input Petugas, disimpan di `OPNAMS.jenis_kapal` | 6.7 no. 3. Daftar nilai **[TERBUKA]**, Bagian 9 no. 18 |
| Tanggal opnam | `OPNAMS.tanggal_opnam` | Wajib. Menentukan tarif berlaku (3.10.3) |
| Catatan | `OPNAMS.catatan` | Opsional |

`jenis_kapal` disimpan di `OPNAMS` sebagai nilai pada saat opnam, mengikuti pola snapshot. Jika `SHIPS` kelak memiliki jenis kapal, form mengisinya otomatis dari sana dan `OPNAMS.jenis_kapal` tetap menjadi salinan. Opnam yang sudah ada tidak berubah.

Kolom pekerjaan tambahan dari client (6.7 no. 5, **[TERBUKA]**, Bagian 9 no. 22) dicatat di tabel berikut setelah daftarnya diterima. Semua kolom nullable dan tidak ikut rumus kecuali 3.10.4 menyebutnya.

| Nama kolom | Tipe | Tempat | Wajib | Dipakai di rumus |
|---|---|---|---|---|
| (diisi setelah client memberi daftar) | | | | |

#### 3.10.3 Detail pekerjaan (langkah 2)

Alur pengisian satu item:
1. Petugas memilih kategori, lalu jenis pekerjaan, lalu material atau spesifikasi.
2. Sistem mencari tarif: baris `TARIFFS` untuk material itu dengan `effective_from` terbaru yang tidak melewati `tanggal_opnam` (BR-31).
3. Sistem menampilkan satuan dan harga dari tarif tersebut.
4. Petugas mengisi volume, ketebalan, kompleksitas, lokasi, dan keterangan.
5. Sistem menampilkan pratinjau faktor dan subtotal. Item disimpan dengan `tariff_id` dan snapshot harga.

Aturan: BR-33, BR-34, BR-35 dan BR-28 berlaku. Jika satu material punya tarif berlaku dengan lebih dari satu satuan, Petugas memilih satuan **[REKOMENDASI]**. Mengubah tarif di master setelah item tersimpan tidak mengubah item tersebut (BR-10).

#### 3.10.4 Rumus kalkulasi (PT-05)

```
subtotal_item = volume x unit_price x faktor_ketebalan x faktor_kompleksitas
total_biaya   = jumlah subtotal_item semua item
```

- `faktor_ketebalan` dan `faktor_kompleksitas` bernilai 1 bila input terkait tidak diisi. Item lama dan item tanpa faktor menghasilkan `volume x unit_price`, sama seperti sebelum penambahan ini.
- Pembulatan **[TERBUKA]**, Bagian 9 no. 21. Default sementara: `subtotal_item` dibulatkan 2 desimal dan `total_biaya` adalah jumlah dari nilai yang sudah dibulatkan.
- Contoh (angka ilustrasi): volume 10, `unit_price` 100.000, faktor ketebalan 1,2, faktor kompleksitas 1,1 menghasilkan `subtotal` 1.320.000.
- Sumber nilai faktor **[TERBUKA]**, Bagian 9 no. 14, 19, 20, 23. Sampai diputuskan, modul Opnam memakai konfigurasi milik sendiri (`config/opnam.php`) yang memetakan input ketebalan dan tingkat kompleksitas ke faktor. Isi awal konfigurasi kosong, sehingga semua faktor bernilai 1. Jika master faktor disediakan kemudian, sumber nilai berpindah ke master itu tanpa mengubah kolom di `OPNAM_ITEMS`.
- Logika rumus berada di satu service (`OpnamCostCalculator`) yang tidak bergantung pada halaman. Form pencatatan memanggilnya. Pemilik AD-07 dan DR-03 memanggil service yang sama saat mengerjakan revisi opnam, supaya hasil hitung identik di semua jalur.
- `total_biaya` dihitung ulang selama opnam masih boleh diubah dan dibekukan saat diteruskan ke Direksi **[REKOMENDASI]**, mengikuti 6.6.

Kolom yang ditambahkan (6.7 no. 3 dan 4), lewat satu migrasi aditif:

| Tabel | Kolom | Tipe | Aturan |
|---|---|---|---|
| `OPNAMS` | `jenis_kapal` | string, nullable | Nilai saat opnam dibuat |
| `OPNAM_ITEMS` | `ketebalan` | decimal, nullable | Input Petugas, satuan **[TERBUKA]** |
| `OPNAM_ITEMS` | `kompleksitas` | string, nullable | Tingkat pilihan Petugas, daftar **[TERBUKA]** |
| `OPNAM_ITEMS` | `faktor_ketebalan` | decimal, default 1 | Snapshot, ditulis saat item disimpan |
| `OPNAM_ITEMS` | `faktor_kompleksitas` | decimal, default 1 | Snapshot, ditulis saat item disimpan |

Baris yang sudah ada mendapat default 1, sehingga `subtotal` lama tidak berubah.

#### 3.10.5 Tampilan estimasi biaya (PT-05)

Tabel per item menampilkan jenis pekerjaan, material, volume dan satuan, harga satuan, faktor ketebalan, faktor kompleksitas, dan subtotal. Di bawahnya tampil `total_biaya`. Tampilan hanya baca.

#### 3.10.6 Kriteria penerimaan

1. Item dengan semua faktor bernilai 1 menghasilkan `subtotal = volume x unit_price`.
2. Item pada contoh 3.10.4 menghasilkan `subtotal` 1.320.000.
3. `total_biaya` sama dengan jumlah `subtotal` semua item pada opnam.
4. Mengubah harga di `TARIFFS` setelah item tersimpan tidak mengubah `unit_price` maupun `subtotal` item itu.
5. Material tanpa tarif berlaku pada `tanggal_opnam` menolak penyimpanan item dengan pesan yang jelas.
6. Migrasi berjalan pada basis data yang sudah berisi opnam tanpa error, dan `subtotal` serta `total_biaya` lama tidak berubah.
7. Diff perubahan tidak memuat file milik modul lain.
8. Halaman tagihan yang membaca `total_biaya` dan item opnam tetap menampilkan nilai yang benar setelah migrasi.

---

## 4. Alur Kerja

### 4.1 Mencatat opnam (Petugas)
1. Login (UC-U1).
2. Pilih kapal (PT-02, wajib).
3. Isi informasi kapal dan tanggal opnam.
4. Tambah item pekerjaan beserta material, volume, keterangan (PT-03, opsional).
5. Unggah bukti pada item (PT-04, opsional, divalidasi).
6. Sistem menghitung estimasi biaya dari tarif berlaku (PT-05).
7. Simpan sebagai draft (PT-06) atau kirim (PT-07).
8. Saat dikirim, status `menunggu_review` dan masuk ke Admin.
9. Petugas memantau status dan riwayat (PT-08).

### 4.2 Pemeriksaan opnam (Admin)
1. Periksa rincian (AD-06, selalu dijalankan sebelum keputusan).
2. Jika perlu, revisi data (AD-07).
3. Pilih salah satu:
   - Setujui dan teruskan (AD-08). Status `menunggu_approval_direksi`. Sistem membuat draft tagihan **[REKOMENDASI, lihat 4.7]**.
   - Kembalikan dengan catatan wajib (AD-09). Status `perlu_revisi`.
4. Jika dikembalikan, Petugas memperbaiki (PT-09) dan mengajukan ulang. Status `menunggu_review`.

### 4.3 Keputusan tagihan (Direksi)
1. Lihat daftar draft menunggu approval (DR-01).
2. Periksa draft tagihan (DR-02, selalu sebelum keputusan).
3. Jika perlu, revisi data opnam (DR-03).
4. Jika perlu, beri diskon atau markup dengan disposisi wajib (DR-04, DR-05).
5. Pilih salah satu:
   - Setujui (DR-06). Status `disetujui`.
   - Tolak dengan alasan (DR-07). Status `ditolak`. Tagihan dapat diajukan ulang. Tujuan status setelah pengajuan ulang **[TERBUKA]**.
6. Setelah disetujui, sistem menerbitkan invoice (DR-08) dan technical report (DR-09). Invoice menampilkan harga, technical report tidak.

### 4.4 Revisi dan ekspor dokumen
Admin dan Direksi dapat merevisi invoice sebelum terbit (AD-12, DR-10) dan mengekspor invoice terbit (AD-13, DR-11). Direksi dapat mengunduh atau mencetak dokumen terbit (DR-12).

### 4.5 Pencatatan log aktivitas
Setiap aksi yang mengubah data harus menulis `ACTIVITY_LOGS` dengan `user_id`, `event`, subjek, `description`, `ip_address`. Aksi berikut wajib menyimpan `old_values` dan `new_values`:
- revisi data opnam dan item (AD-07, DR-03)
- perubahan tarif (AD-04)
- diskon dan markup (DR-04)
- revisi invoice (AD-12, DR-10)

Perubahan status ditulis ke `STATUS_HISTORIES` (`from_status`, `to_status`, `note`, `changed_by`).

### 4.6 Transisi status

**Opnam (`OPNAMS.status`)**

| Dari | Ke | Pemicu | Catatan |
|---|---|---|---|
| (baru) | `draft` | PT-06 | |
| `draft` | `menunggu_review` | PT-07 | isi `submitted_at` |
| `menunggu_review` | `perlu_revisi` | AD-09 | catatan wajib |
| `menunggu_review` | `menunggu_approval_direksi` | AD-08 | isi `forwarded_at` |
| `perlu_revisi` | `menunggu_review` | PT-09 | diputuskan di Analisis Fitur v3 |
| `menunggu_approval_direksi` | `disetujui` | DR-06 | |
| `menunggu_approval_direksi` | `ditolak` | DR-07 | |
| `disetujui` | `dokumen_terbit` | penerbitan dokumen | pemicu **[REKOMENDASI]**: setelah invoice dan technical report terbit |
| `ditolak` | (diajukan ulang) | - | tujuan **[TERBUKA]** |

**Tagihan (`BILLINGS.status`)**

| Dari | Ke | Pemicu |
|---|---|---|
| (dibuat) | `menunggu_approval` | otomatis saat AD-08 **[REKOMENDASI]** |
| `menunggu_approval` | `disetujui` | DR-06 |
| `menunggu_approval` | `ditolak` | DR-07 |
| `ditolak` | (diajukan ulang) | **[TERBUKA]** |

Transisi yang tidak tercantum tidak diizinkan.

**Kapan data boleh diubah**

| Status opnam | Yang boleh mengubah item dan data opnam |
|---|---|
| `draft`, `perlu_revisi` | Petugas pemilik opnam |
| `menunggu_review` | Admin (AD-07) |
| `menunggu_approval_direksi` | Direksi (DR-03) |
| Status lain | Tidak ada |

### 4.7 Pembuatan tagihan dan penyelarasan status **[REKOMENDASI]**
- Saat AD-08, sistem membuat satu `BILLINGS` dengan `status = menunggu_approval`, `subtotal_amount = OPNAMS.total_biaya`, `adjustment_amount = 0`, `total_amount = subtotal_amount`.
- Saat DR-04, `BILLINGS.adjustment_amount` diisi dari total `BILLING_ADJUSTMENTS` dan `total_amount` dihitung ulang.
- Keputusan Direksi (DR-06, DR-07) ditulis ke `BILLINGS` dan dicerminkan ke `OPNAMS.status` dalam satu transaksi basis data.

---

## 5. Aturan Bisnis

| ID | Aturan |
|---|---|
| BR-01 | Akun nonaktif tidak dapat masuk. |
| BR-02 | Petugas tidak dapat mengubah tarif dasar. |
| BR-03 | Petugas tidak dapat memberi diskon atau markup. |
| BR-04 | Petugas tidak dapat memberi persetujuan akhir. |
| BR-05 | Petugas tidak dapat mengekspor invoice. |
| BR-06 | Admin mengelola tarif dasar serta memeriksa dan merevisi opnam. |
| BR-07 | Direksi memberi persetujuan akhir dan penyesuaian harga. |
| BR-08 | Setiap diskon atau markup wajib punya catatan disposisi. |
| BR-09 | Invoice menampilkan harga. Technical report tidak. |
| BR-10 | Perubahan tarif tidak mengubah transaksi yang sudah tercatat (snapshot). |
| BR-11 | Pemilihan kapal wajib sebelum opnam dibuat. |
| BR-12 | Penambahan detail pekerjaan opsional. |
| BR-13 | Unggah bukti opsional. |
| BR-14 | Estimasi biaya dihitung otomatis dari tarif berlaku. |
| BR-15 | Pengajuan opnam mengubah status menjadi `menunggu_review`. |
| BR-16 | Persetujuan hasil review selalu didahului pemeriksaan. |
| BR-17 | Pengembalian opnam selalu didahului pemeriksaan. |
| BR-18 | Pengembalian opnam wajib disertai catatan. |
| BR-19 | Admin mengubah data opnam hanya sesuai kebutuhan pemeriksaan. |
| BR-20 | Direksi mengubah data opnam hanya jika perlu saat pemeriksaan tagihan. |
| BR-21 | Petugas hanya dapat memperbaiki opnam berstatus `perlu_revisi`. |
| BR-22 | Keputusan tagihan selalu didahului pemeriksaan draft tagihan. |
| BR-23 | Invoice dan technical report hanya terbit setelah tagihan disetujui. |
| BR-24 | Penyesuaian harga bersifat opsional. |
| BR-25 | Riwayat perbaikan dicari berdasarkan nama kapal atau nomor lambung, lewat halaman Transaksi Disetujui (untuk Direksi). |
| BR-26 | Unggahan file divalidasi format dan ukurannya. |
| BR-27 | Daftar transaksi mendukung pencarian, filter status dan tanggal, dan paginasi. |
| BR-28 | Item opnam hanya dapat ditambah, diubah, atau dihapus sesuai tabel "Kapan data boleh diubah" (4.6). |
| BR-29 | Tagihan yang ditolak dapat diajukan ulang. |
| BR-30 | Riwayat Perubahan hanya dapat diakses Admin dan Direksi. Riwayat Aktivitas dapat diakses semua role. |
| BR-31 | Tarif yang dipakai item opnam adalah tarif berlaku pada saat item dibuat, lalu dibekukan di `unit_price` **[REKOMENDASI]**: tarif dengan `effective_from` terbaru yang tidak lebih dari tanggal opnam. |
| BR-32 | `subtotal` item = `volume` x `unit_price` x `faktor_ketebalan` x `faktor_kompleksitas`. Faktor bernilai 1 bila tidak diisi. `total_biaya` = jumlah `subtotal` semua item **[REKOMENDASI]**. |
| BR-33 | Material tanpa tarif berlaku pada `tanggal_opnam`: item tidak dapat disimpan dan harga tidak diisi nol **[REKOMENDASI]**. |
| BR-34 | Kategori, jenis pekerjaan, dan material pada item ditelusuri dari `tariff_id` dan tidak disimpan ulang di `OPNAM_ITEMS`. |
| BR-35 | Mengubah material pada item mencari ulang tarif dan memperbarui `unit_price`. Mengubah hanya volume atau faktor tidak mengubah `unit_price` **[REKOMENDASI]**. |

---

## 6. Spesifikasi Basis Data

Sumber: ERD revisi (17 tabel, 24 relasi) ditambah Bagian 6.7. Panjang string dan presisi decimal belum ditetapkan **[TERBUKA]**.

### 6.1 Identitas dan master data

**USERS**: `id` PK; `name`; `username` UK; `email` UK; `password`; `role` (petugas, admin, direksi); `is_active` boolean; `last_login_at` datetime.

**OWNERS**: `id` PK; `name`; `address` text nullable; `deleted_at`.

**SHIPS**: `id` PK; `owner_id` FK OWNERS; `name`; `hull_number` UK; `deleted_at`.

**UNITS**: `id` PK; `code` UK; `name`.

**WORK_CATEGORIES**: `id` PK; `name` UK; `description` text nullable; `deleted_at`; `is_active` (penambahan, 6.7).

**WORK_TYPES**: `id` PK; `work_category_id` FK; `name`; `deleted_at`.

**MATERIAL_SPECS**: `id` PK; `work_type_id` FK; `name`; `deleted_at`.

**TARIFFS**: `id` PK; `material_spec_id` FK; `unit_id` FK; `created_by` FK USERS; `price` decimal; `effective_from` date; `created_at`.

### 6.2 Opnam

**OPNAMS**: `id` PK; `opnam_number` UK; `ship_id` FK; `created_by` FK USERS; `status` (draft, menunggu_review, perlu_revisi, menunggu_approval_direksi, ditolak, disetujui, dokumen_terbit); `submitted_at` nullable; `forwarded_at` nullable; `tanggal_opnam` date; `catatan` text nullable; `total_biaya` decimal (snapshot); `created_at`; `updated_at`.

**OPNAM_ITEMS**: `id` PK; `opnam_id` FK; `tariff_id` FK; `volume` decimal; `unit_price` decimal (snapshot); `subtotal` decimal (snapshot); `description` text nullable; `sort_order` int; `lokasi` string nullable; `created_at`; `updated_at`.

**OPNAM_ATTACHMENTS**: `id` PK; `opnam_item_id` FK; `uploaded_by` FK USERS; `file_path`; `original_name`; `mime_type`; `file_size` int (byte); `keterangan` text nullable; `created_at`; `updated_at`.

**STATUS_HISTORIES** (polimorfik, dipakai opnam dan tagihan): `id` PK; `statusable_type`; `statusable_id`; `from_status` nullable; `to_status`; `note` text nullable; `changed_by` FK USERS nullable; `created_at`.

### 6.3 Tagihan, invoice, laporan

**BILLINGS**: `id` PK; `opnam_id` FK; `billing_number` UK nullable; `status` (menunggu_approval, disetujui, ditolak); `subtotal_amount`; `adjustment_amount`; `total_amount`; `decided_by` FK USERS nullable; `decided_at` nullable.

**BILLING_ADJUSTMENTS**: `id` PK; `billing_id` FK; `created_by` FK USERS; `adjustment_type` (diskon, markup); `adjustment_value`; `adjustment_amount`; `disposition_note` text.

**INVOICES**: `id` PK; `billing_id` FK; `issued_by` FK USERS; `invoice_number` UK; `issue_date`; snapshot: `ship_name`, `hull_number`, `owner_name`, `owner_address` (nullable), `subtotal_amount`, `adjustment_amount`, `total_amount`; `revision_count` int.

**TECHNICAL_REPORTS**: `id` PK; `billing_id` FK; `issued_by` FK USERS; `report_number` UK; `issue_date`; snapshot: `ship_name`, `hull_number`, `owner_name`, `owner_address` (nullable). Tidak memuat harga.

### 6.4 Log aktivitas

**ACTIVITY_LOGS**: `id` PK; `user_id` FK nullable; `event`; `subject_type` dan `subject_id` (polimorfik); `description`; `old_values` json nullable; `new_values` json nullable; `ip_address` nullable; `created_at`.

Kolom "role" di tampilan diambil dari `USERS.role`. Kolom "modul" diturunkan dari `subject_type`.

### 6.5 Relasi

| Anak | Induk |
|---|---|
| SHIPS.owner_id | OWNERS |
| WORK_TYPES.work_category_id | WORK_CATEGORIES |
| MATERIAL_SPECS.work_type_id | WORK_TYPES |
| TARIFFS.material_spec_id, unit_id, created_by | MATERIAL_SPECS, UNITS, USERS |
| OPNAMS.ship_id, created_by | SHIPS, USERS |
| OPNAM_ITEMS.opnam_id, tariff_id | OPNAMS, TARIFFS |
| OPNAM_ATTACHMENTS.opnam_item_id, uploaded_by | OPNAM_ITEMS, USERS |
| STATUS_HISTORIES.changed_by | USERS (nullable) |
| STATUS_HISTORIES.statusable_* | polimorfik |
| BILLINGS.opnam_id, decided_by | OPNAMS, USERS (nullable) |
| BILLING_ADJUSTMENTS.billing_id, created_by | BILLINGS, USERS |
| INVOICES.billing_id, issued_by | BILLINGS, USERS |
| TECHNICAL_REPORTS.billing_id, issued_by | BILLINGS, USERS |
| ACTIVITY_LOGS.user_id | USERS (nullable) |
| ACTIVITY_LOGS.subject_* | polimorfik |

### 6.6 Aturan data
- Snapshot: lihat Bagian 0. `OPNAMS.total_biaya` adalah jumlah `subtotal` semua item. Dihitung ulang selama opnam masih boleh diubah, dibekukan saat diteruskan ke Direksi **[REKOMENDASI]**.
- Nomor dokumen (`opnam_number`, `billing_number`, `invoice_number`, `report_number`) unik. Format dan cara pembuatan **[TERBUKA]**.
- Soft delete hanya pada OWNERS, SHIPS, WORK_CATEGORIES, WORK_TYPES, MATERIAL_SPECS. USERS tidak dihapus, hanya dinonaktifkan.
- UNITS dan TARIFFS tidak punya `deleted_at`. Penghapusan ditolak bila sudah dipakai transaksi **[REKOMENDASI]**.
- Riwayat revisi invoice disimpan di `ACTIVITY_LOGS` (`old_values`, `new_values`), dan `INVOICES.revision_count` menghitung jumlah revisi **[REKOMENDASI]**.

### 6.7 Penambahan terhadap ERD
Berikut hal yang dibutuhkan PRD tetapi belum ada di ERD. Menurut keputusan, PRD yang dipakai.

| No | Penambahan | Alasan | Status |
|---|---|---|---|
| 1 | `WORK_CATEGORIES.is_active` boolean | AD-03 mengelola kategori beserta status aktifnya (Analisis Fitur v3) | Perlu konfirmasi |
| 2 | Penandaan invoice belum terbit atau sudah terbit pada `INVOICES` (misalnya `status` atau `issued_at`) | AD-12 dan DR-10 hanya boleh merevisi sebelum terbit, tetapi ERD tidak bisa membedakan | **[TERBUKA]**, lihat Bagian 9 no. 2 |
| 3 | `OPNAMS.jenis_kapal` string nullable | Opnam perlu mencatat jenis kapal. Disimpan di `OPNAMS` sebagai nilai saat opnam supaya tabel `SHIPS` tidak berubah | Perlu konfirmasi, daftar nilai **[TERBUKA]**, lihat Bagian 9 no. 18 |
| 4 | `OPNAM_ITEMS.ketebalan` decimal nullable; `OPNAM_ITEMS.kompleksitas` string nullable; `OPNAM_ITEMS.faktor_ketebalan` dan `faktor_kompleksitas` decimal default 1 (snapshot) | Perhitungan ketebalan pelat dan kompleksitas pekerjaan butuh tempat input dan hasil di item opnam, tanpa mengubah `TARIFFS` | **[TERBUKA]**, lihat Bagian 9 no. 14, 19, 20, 23 |
| 5 | Kolom pekerjaan tambahan permintaan client di `OPNAM_ITEMS` atau `OPNAMS` | Permintaan client | **[TERBUKA]**, lihat Bagian 9 no. 22 |

Tidak ada tabel baru. Alasan penolakan, catatan pengembalian, dan riwayat revisi invoice memakai tabel yang sudah ada.

---

## 7. Persyaratan Sistem

**Dari sumber:**
- Login dengan username atau email; akun nonaktif diblokir.
- Output PDF: riwayat aktivitas, invoice, technical report.
- Output PDF atau Excel: laporan rekapitulasi.
- Dokumen terbit dapat diunduh atau dicetak.
- Unggah file dengan validasi format dan ukuran.
- Pencarian, filter, dan paginasi pada daftar transaksi dan riwayat.
- Perhitungan biaya otomatis dari tarif.
- Snapshot nilai pada transaksi.
- Pencatatan IP, nilai lama, dan nilai baru pada log aktivitas.

**Konteks teknis:** aplikasi dikembangkan dengan Laravel dan AI coding agent (konteks permintaan, bukan persyaratan dari dokumen desain).

**Tidak dispesifikasikan (jangan ditebak):** versi framework, mesin basis data, hosting, browser yang didukung, performa, kebijakan password dan batas waktu sesi, backup, batas unggah dan lokasi penyimpanan file, penjadwalan laporan, notifikasi atau email, integrasi eksternal, aksesibilitas.

---

## 8. Ketertelusuran

| ID | Use case | Aktor | Modul | Aturan | Tabel utama |
|---|---|---|---|---|---|
| UC-U1 sampai UC-U5 | Akun dan akses | Semua | 3.1 | BR-01 | USERS |
| PT-01 sampai PT-05 | Pencatatan, pilih kapal, detail, bukti, biaya | Petugas | 3.2 | BR-11 sampai BR-14, BR-26, BR-28 | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS |
| PT-01, PT-03, PT-05 (rincian) | Jenis kapal, faktor, dan kalkulasi biaya | Petugas | 3.10 | BR-32 sampai BR-35 | OPNAMS, OPNAM_ITEMS |
| PT-06, PT-07 | Draft dan ajukan | Petugas | 3.2 | BR-15 | OPNAMS, STATUS_HISTORIES |
| PT-08, PT-09 | Status dan perbaikan | Petugas | 3.2 | BR-21 | OPNAMS, STATUS_HISTORIES |
| PT-10, PT-11 | Riwayat aktivitas dan ekspor | Petugas | 3.3 | BR-30 | ACTIVITY_LOGS |
| AD-01 sampai AD-04 | Pengguna dan master data | Admin | 3.4 | BR-01, BR-02, BR-06, BR-10 | USERS, OWNERS, SHIPS, WORK_*, MATERIAL_SPECS, UNITS, TARIFFS |
| AD-05 sampai AD-09 | Daftar, periksa, revisi, teruskan, kembalikan | Admin | 3.5 | BR-16 sampai BR-19, BR-27 | OPNAMS, OPNAM_ITEMS, STATUS_HISTORIES |
| AD-10, AD-11 | Riwayat perubahan, riwayat kapal | Admin | 3.3 | BR-25, BR-30 | ACTIVITY_LOGS, SHIPS, OPNAMS |
| AD-12, AD-13 | Revisi dan ekspor invoice | Admin | 3.7 | BR-05, BR-09 | INVOICES |
| AD-14, AD-15 | Riwayat aktivitas dan ekspor | Admin | 3.3 | BR-30 | ACTIVITY_LOGS |
| AD-16 | Laporan rekapitulasi | Admin | 3.8 | - | agregasi |
| DR-01, DR-02 | Draft dan periksa tagihan | Direksi | 3.6 | BR-22 | BILLINGS, BILLING_ADJUSTMENTS |
| DR-03 | Revisi data opnam | Direksi | 3.6 | BR-20 | OPNAMS, OPNAM_ITEMS |
| DR-04, DR-05 | Diskon, markup, disposisi | Direksi | 3.6 | BR-07, BR-08, BR-24 | BILLING_ADJUSTMENTS |
| DR-06, DR-07 | Setujui, tolak | Direksi | 3.6 | BR-22, BR-23, BR-29 | BILLINGS, STATUS_HISTORIES |
| DR-08, DR-09 | Terbit invoice, technical report | Sistem (setelah DR-06) | 3.6 | BR-09, BR-23 | INVOICES, TECHNICAL_REPORTS |
| DR-10 sampai DR-12 | Revisi, ekspor, unduh dokumen | Direksi | 3.7 | BR-09 | INVOICES, TECHNICAL_REPORTS |
| DR-13, DR-14 | Rekap tagihan, laporan | Direksi | 3.8 | - | BILLINGS |
| DR-15 | Riwayat pekerjaan kapal | Direksi | 3.3 | BR-25 | SHIPS, OPNAMS |
| DR-16 | Riwayat perubahan | Direksi | 3.3 | BR-30 | ACTIVITY_LOGS |
| DR-17, DR-18 | Riwayat aktivitas dan ekspor | Direksi | 3.3 | BR-30 | ACTIVITY_LOGS |

Cakupan: 50 use case, 3 aktor, 17 tabel ERD, semuanya tercakup.

---

## 9. Hal yang Masih Terbuka

Agent harus bertanya sebelum mengerjakan bagian terkait.

| No | Pertanyaan | Terkait |
|---|---|---|
| 1 | Tagihan atau opnam yang ditolak lalu diajukan ulang: kembali ke status apa (`menunggu_review` atau `perlu_revisi`), dan siapa yang mengajukan ulang? | DR-07, BR-29 |
| 2 | Invoice dibuat otomatis saat disetujui (DR-08), tetapi revisi hanya boleh "sebelum terbit". Bagaimana status invoice draft dan terbit dibedakan, dan kapan revisi dilakukan? | AD-12, DR-10, 6.7 no. 2 |
| 3 | DR-11 (ekspor invoice) dan DR-12 (unduh atau cetak dokumen): digabung atau terpisah? | DR-11, DR-12 |
| 4 | Konfirmasi bahwa Riwayat Revisi benar-benar digabung ke Riwayat Perubahan. | AD-10, DR-16 |
| 5 | Format file dan batas ukuran unggahan, serta lokasi penyimpanan. | PT-04 |
| 6 | Isi dashboard per role. | UC-U4 |
| 7 | Isi, kolom, dan periode laporan rekapitulasi. | AD-16, DR-13, DR-14 |
| 8 | Format penomoran opnam, tagihan, invoice, technical report. | 6.6 |
| 9 | Aturan password, apakah password lama wajib, batas sesi. | UC-U3 |
| 10 | Field profil yang boleh diubah. | UC-U5 |
| 11 | `adjustment_value` berupa persen atau nominal? Boleh lebih dari satu penyesuaian per tagihan? | DR-04 |
| 12 | Konfirmasi rekomendasi pembuatan tagihan, perhitungan `total_biaya`, dan pemicu `dokumen_terbit`. | 4.6, 4.7 |
| 13 | `issued_by` saat dokumen diterbitkan sistem: diisi Direksi yang menyetujui? | DR-08, DR-09 |
| 14 | "Faktor tambahan tarif sesuai spesifikasi teknis" (muncul di pembagian tugas, tidak ada di use case atau ERD): dipakai atau tidak, dan bagaimana kolomnya? | AD-04 |
| 15 | Konfirmasi `WORK_CATEGORIES.is_active`. | AD-03 |
| 16 | Tahapan akhir pencatatan: Analisis Fitur menyebut "bukti pekerjaan", dokumen use case menyebut "tinjauan akhir". Mana yang dipakai? | PT-01 |
| 17 | Tarif tidak punya tanggal berakhir. Apakah perlu? | TARIFFS |
| 18 | Daftar jenis kapal untuk `OPNAMS.jenis_kapal`: pilihan tetap atau teks bebas, dan apa nilainya? (ditanyakan ke client dan pemilik SHIPS) | 3.10.2, 6.7 no. 3 |
| 19 | Faktor ketebalan: Petugas mengisi angka ketebalan lalu faktor dihitung dari rentang, atau Petugas memilih faktor langsung? Apa aturan rentang dan satuannya? (ditanyakan ke pemilik master tarif dan client) | 3.10.4, 6.7 no. 4, no. 14 |
| 20 | Faktor kompleksitas: apa saja tingkatnya dan berapa nilai tiap tingkat? (ditanyakan ke pemilik master tarif dan client) | 3.10.4, 6.7 no. 4, no. 14 |
| 21 | Aturan pembulatan `subtotal` dan `total_biaya`. | 3.10.4 |
| 22 | Daftar kolom pekerjaan tambahan dari client, dan apakah masing-masing per item atau per opnam. | 3.10.2, 6.7 no. 5 |
| 23 | Faktor tambahan tetap dipakai atau dihapus dari tupoksi (`tupoksi.md` bagian 3 no. 10). Keputusan ini menentukan 6.7 no. 4. | 3.10.4, no. 14 |

---

## 10. Keputusan yang Sudah Dimasukkan dari Analisis Fitur v3

| Keputusan | Dampak di PRD |
|---|---|
| Riwayat Aktivitas dilihat semua role atas seluruh aktivitas | PT-10, AD-14, DR-17 |
| Riwayat Revisi digabung ke Riwayat Perubahan (Admin dan Direksi) | AD-10, DR-16 |
| Riwayat Pekerjaan Kapal dan Riwayat Perbaikan Kapal adalah satu fitur | AD-11, DR-15 |
| Direksi boleh merevisi data opnam dan invoice | DR-03, DR-10 |
| Revisi invoice hanya sebelum terbit | AD-12, DR-10 |
| Invoice dan technical report diterbitkan oleh sistem setelah persetujuan | DR-08, DR-09 |
| Tombol Simpan Draft tersedia di tahapan pencatatan | PT-06 |
| Pengajuan ulang setelah perbaikan Petugas kembali ke `menunggu_review` | PT-09 |
| Tagihan yang ditolak dapat diajukan ulang (tujuan status terbuka) | DR-07 |
| Catatan pengembalian disimpan di riwayat status | AD-09 |
| Hapus pada owner dan kapal memakai soft delete | AD-02 |
| Profil harus ada untuk Admin dan Direksi | UC-U5 |
| Laporan rekap dan Riwayat Aktivitas terpisah | AD-16, DR-14 |
