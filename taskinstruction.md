# TASK INSTRUCTION SIMSHIP

Dokumen ini adalah instruksi kerja untuk AI coding agent (Laravel) dan anggota tim. Dokumen ini diturunkan dari `prd.md`. Jika ada pertentangan, `prd.md` dan ERD yang menang. Dokumen ini tidak menambah persyaratan baru.

---

## 1. Cara Memakai Dokumen Ini

1. Tanyakan atau pastikan siapa yang meminta pekerjaan (nama anggota tim).
2. Cari modul anggota tersebut di Bagian 5. Kerjakan **hanya** item yang Pemiliknya sama dengan peminta.
3. Sebelum menulis kode, baca Bagian 2 (aturan umum) dan Bagian 3 (kontrak data).
4. Jika item tergolong "Perlu konfirmasi" atau "Tidak dispesifikasikan", **berhenti dan tanyakan** memakai format di Bagian 8. Jangan menebak.
5. Setelah item selesai, ubah Status dan isi Catatan di checklist `prd.md` Bagian 10 dalam perubahan yang sama.

Nilai Status yang sah: `Belum`, `Dikerjakan`, `Selesai`, `Perlu konfirmasi`.

---

## 2. Aturan Umum (Guardrails)

### 2.1 Sumber kebenaran

| Urutan | Sumber | Dipakai untuk |
|---|---|---|
| 1 | ERD revisi (`Perancangan_database_Laravel_rev.pdf`) | Nama tabel, kolom, tipe, kunci, nilai enum |
| 2 | `Daftar_Use_Case_SIMSHIP.docx`, `Usecase.pdf` | Perilaku, alur, aturan bisnis |
| 3 | `UI_Project_FPW.pdf` | **Bukan acuan.** Jangan membangun layar dari mockup |

### 2.2 Larangan

- Jangan membuat tabel, kolom, nilai enum, role, atau status yang tidak ada di ERD.
- Jangan menambah fitur, validasi, atau hak akses di luar PRD Bagian 2 sampai 5.
- Jangan menambah field dari mockup: contact person owner, nomor telepon, tanggal berakhir tarif ("Berlaku hingga"), kuantitas minimum, faktor tarif ("Faktor Tambahan").
- Jangan menerjemahkan nilai status atau role. Pakai persis: `petugas`, `admin`, `direksi`; `draft`, `menunggu_review`, `perlu_revisi`, `menunggu_approval_direksi`, `ditolak`, `disetujui`, `dokumen_terbit`; `menunggu_approval`; `diskon`, `markup`.
- Jangan berasumsi soft delete di tabel yang tidak punya `deleted_at`.
- Jangan mengubah middleware dan policy kecuali diminta Halvina.
- Jangan mengerjakan item "Di luar tupoksi" (Bagian 6).
- Jangan mengerjakan item lintas tim (USERS, login, tabel bersama) kecuali peminta adalah pemiliknya.

### 2.3 Nilai snapshot

Kolom berikut ditulis **sekali** saat transaksi terjadi dan tidak pernah dihitung ulang dari data master:

`OPNAM_ITEMS.unit_price`, `OPNAM_ITEMS.subtotal`, `OPNAMS.total_biaya`, `INVOICES` dan `TECHNICAL_REPORTS` (`ship_name`, `hull_number`, `owner_name`, `owner_address`), `INVOICES.subtotal_amount`, `INVOICES.adjustment_amount`, `INVOICES.total_amount`.

Konsekuensinya (BR-10): perubahan tarif tidak boleh mengubah transaksi yang sudah tercatat.

### 2.4 Soft delete

Hanya lima tabel yang punya `deleted_at`: `OWNERS`, `SHIPS`, `WORK_CATEGORIES`, `WORK_TYPES`, `MATERIAL_SPECS`.

Apakah operasi "hapus" pada tabel tersebut soft atau hard **tidak dispesifikasikan** (PRD 9, no. 21). Tanyakan sebelum membuat aksi hapus. Tabel lain, termasuk `UNITS` dan `TARIFFS`, tidak punya `deleted_at`: tanyakan juga sebelum membuat aksi hapus untuk keduanya.

### 2.5 Hak akses ringkas

| Kemampuan | Petugas | Admin | Direksi |
|---|---|---|---|
| Fitur umum (login, logout, password, dashboard, profil) | Ya | Ya | Ya |
| Catat, simpan draft, ajukan, perbaiki opnam | Ya | - | - |
| Kelola pengguna, owner, kapal, master data, tarif | Tidak | Ya | - |
| Daftar opnam, periksa, revisi opnam, teruskan atau kembalikan | - | Ya | - |
| Diskon, markup, disposisi, setujui, tolak, terbitkan dokumen | Tidak | - | Ya |
| Ekspor invoice PDF | Tidak | Ya | Ya |
| Rekap tagihan | - | - | Ya |
| Laporan PDF atau Excel | - | Ya | Ya |

Tanda "-" berarti sumber tidak mencantumkan, bukan melarang. Jangan menambahkan akses di sel "-" tanpa konfirmasi.

---

## 3. Kontrak Data (Ringkas dari ERD)

Detail lengkap ada di `prd.md` Bagian 6. Berikut yang paling sering dipakai.

### 3.1 Enum

| Kolom | Nilai |
|---|---|
| `USERS.role` | `petugas`, `admin`, `direksi` |
| `OPNAMS.status` | `draft`, `menunggu_review`, `perlu_revisi`, `menunggu_approval_direksi`, `ditolak`, `disetujui`, `dokumen_terbit` |
| `BILLINGS.status` | `menunggu_approval`, `disetujui`, `ditolak` |
| `BILLING_ADJUSTMENTS.adjustment_type` | `diskon`, `markup` |

### 3.2 Kolom unik

`USERS.username`, `USERS.email`, `SHIPS.hull_number`, `UNITS.code`, `WORK_CATEGORIES.name`, `OPNAMS.opnam_number`, `BILLINGS.billing_number` (nullable), `INVOICES.invoice_number`, `TECHNICAL_REPORTS.report_number`.

Cara pembuatan nomor dokumen **tidak dispesifikasikan** (PRD 9, no. 32). Tanyakan sebelum membuat generator nomor.

### 3.3 Transisi status yang diizinkan

Selain tabel ini, **tidak diizinkan** sampai pengguna mengonfirmasi.

**Opnam**

| ID | Dari | Ke | Pemicu | Pemilik | Catatan |
|---|---|---|---|---|---|
| ST-01 | (baru) / `draft` | `menunggu_review` | PT-07 (isi `submitted_at`) | Di luar tupoksi | Parkir |
| ST-02 | `menunggu_review` | `perlu_revisi` | AD-09 (catatan wajib) | Hazel | |
| ST-03 | `menunggu_review` | `menunggu_approval_direksi` | AD-08 (isi `forwarded_at`) | Hazel | Konfirmasi nama status |
| ST-04 | `perlu_revisi` | `menunggu_review` | PT-09 | Di luar tupoksi | Parkir, tersirat |
| ST-05 | `menunggu_approval_direksi` | `disetujui` | DR-06 | Hazel | Tersirat, konfirmasi |
| ST-06 | `menunggu_approval_direksi` | `ditolak` | DR-07 | Hazel | Tersirat, konfirmasi |
| ST-07 | `disetujui` | `dokumen_terbit` | DR-08 / DR-09 | Hazel | Tersirat, pemicu belum jelas |

Pembuatan opnam baru berstatus `draft` (PT-06) dimiliki Regina.

**Tagihan**

| ID | Dari | Ke | Pemicu | Pemilik | Catatan |
|---|---|---|---|---|---|
| ST-08 | (dibuat) | `menunggu_approval` | Langkah pembuatan tidak dispesifikasikan | Di luar tupoksi | Parkir |
| ST-09 | `menunggu_approval` | `disetujui` | DR-06 | Hazel | |
| ST-10 | `menunggu_approval` | `ditolak` | DR-07 | Hazel | |

### 3.4 Pencatatan riwayat

Setiap perubahan status opnam yang ada di tabel 3.3 dicatat ke `STATUS_HISTORIES` (`from_status`, `to_status`, `note`, `changed_by`). Pemilik tunggal tabel ini belum ditetapkan (Bagian 6, no. 6). Sebelum diputuskan, jangan mengubah struktur tabelnya.

---

## 4. Urutan Pengerjaan yang Disarankan

Urutan ini hanya mengikuti dependensi foreign key di ERD dan bukan penugasan baru.

| Tahap | Isi | Alasan dependensi |
|---|---|---|
| 1 | `USERS` + autentikasi (Halvina) | Banyak tabel punya FK `created_by`, `uploaded_by`, `issued_by`, `decided_by` ke USERS |
| 2 | `OWNERS`, `SHIPS` (Rifqy); `UNITS`, `WORK_CATEGORIES`, `WORK_TYPES`, `MATERIAL_SPECS`, `TARIFFS` (Anisa) | Dibutuhkan sebelum opnam bisa dibuat |
| 3 | `OPNAMS`, `OPNAM_ITEMS`, `OPNAM_ATTACHMENTS` (Regina) | Bergantung pada kapal dan tarif |
| 4 | Review opnam (Hazel) | Bergantung pada opnam berstatus `menunggu_review` |
| 5 | `BILLINGS`, `BILLING_ADJUSTMENTS`, `INVOICES`, `TECHNICAL_REPORTS` (Hazel) | Bergantung pada opnam yang diteruskan |
| 6 | `ACTIVITY_LOGS`, laporan, riwayat (Rifqy) | Bergantung pada data transaksi |

Migrasi harus mengikuti urutan FK. Jangan membuat migrasi yang merujuk tabel yang belum ada.

---

## 5. Instruksi per Anggota

### 5.1 Halvina: Autentikasi, Pengguna, dan Dashboard

**Tabel:** `USERS` (DB-01).

**Use case:** UC-U1, UC-U2, UC-U3, UC-U4, UC-U5, AD-01.

**Aturan bisnis:** BR-01.

**Tugas konkret**

- [ ] Migrasi dan model `USERS` sesuai ERD (`name`, `username` UK, `email` UK, `password`, `role`, `is_active`, `last_login_at`).
- [ ] Login dengan username **atau** email + password (UC-U1).
- [ ] Tolak login jika `is_active` = false (BR-01).
- [ ] Logout dan akhiri sesi (UC-U2).
- [ ] Ubah password akun yang sedang login (UC-U3).
- [ ] Dashboard per role (UC-U4).
- [ ] Lihat dan ubah profil (UC-U5).
- [ ] CRUD pengguna oleh Admin: tambah, ubah data, tentukan role, nonaktifkan (AD-01).
- [ ] Middleware dan policy untuk tiga role. Ini dipakai semua modul; hanya Halvina yang boleh mengubahnya.

**Yang harus ditanyakan dulu**

| Hal | Rujukan PRD |
|---|---|
| Apakah password lama wajib saat ubah password, dan aturan password | 9 no. 24 |
| Field profil mana yang boleh diubah | 9 no. 25 |
| Isi dashboard tiap role | 9 no. 19 |
| Apakah pengguna boleh dihapus, atau hanya dinonaktifkan | 9 no. 22 |
| Batas waktu sesi | 5 (tidak dispesifikasikan) |

**Jangan**

- Jangan menambah role di luar `petugas`, `admin`, `direksi`.
- Jangan memakai istilah "Operator" atau "Supervisor" sebagai nilai role (itu istilah mockup).

---

### 5.2 Anisa: Master Data Pekerjaan dan Tarif

**Tabel:** `UNITS` (DB-04), `WORK_CATEGORIES` (DB-05), `WORK_TYPES` (DB-06), `MATERIAL_SPECS` (DB-07), `TARIFFS` (DB-08).

**Use case:** AD-03, AD-04.

**Aturan bisnis:** BR-02, BR-06, BR-10.

**Tugas konkret**

- [ ] Migrasi dan model kelima tabel sesuai ERD, termasuk FK: `WORK_TYPES.work_category_id`, `MATERIAL_SPECS.work_type_id`, `TARIFFS.material_spec_id`, `TARIFFS.unit_id`, `TARIFFS.created_by`.
- [ ] `deleted_at` hanya pada `WORK_CATEGORIES`, `WORK_TYPES`, `MATERIAL_SPECS`.
- [ ] CRUD kategori pekerjaan (AD-03).
- [ ] CRUD jenis pekerjaan, material/spesifikasi, satuan, dan tarif dasar (AD-04).
- [ ] Hanya Admin yang dapat mengelola tarif. Petugas tidak boleh mengubah tarif dasar (BR-02).
- [ ] Setiap tarif baru menyimpan `price`, `effective_from`, `created_by`, `created_at`.
- [ ] Pastikan perubahan tarif tidak menyentuh `OPNAM_ITEMS` yang sudah ada (BR-10).

**Yang harus ditanyakan dulu**

| Hal | Rujukan PRD |
|---|---|
| Perilaku hapus (soft atau hard) | 9 no. 21 |
| Apakah tarif punya tanggal berakhir (ERD hanya `effective_from`) | 9 no. 17 |
| Bagaimana memilih tarif yang "berlaku" jika ada beberapa `effective_from` | 5, BR-14 |
| "Faktor tambahan tarif" di tugas tim: tidak ada di ERD | 9 no. 30 |

**Jangan**

- Jangan menambah kolom faktor tambahan, kuantitas minimum, atau "berlaku hingga" ke `TARIFFS`.

---

### 5.3 Rifqy: Owner, Kapal, Laporan, dan Riwayat

**Tabel:** `OWNERS` (DB-02), `SHIPS` (DB-03), `ACTIVITY_LOGS` (DB-17).

**Use case:** AD-02, AD-10, AD-11, AD-14, AD-16, PT-10, DR-13, DR-14, DR-15, DR-16.

**Aturan bisnis:** BR-25, BR-27.

**Tugas konkret**

- [ ] Migrasi dan model `OWNERS` (`name`, `address` nullable, `deleted_at`) dan `SHIPS` (`owner_id` FK, `name`, `hull_number` UK, `deleted_at`).
- [ ] CRUD owner dan kapal, hubungkan kapal dengan owner (AD-02).
- [ ] Migrasi dan model `ACTIVITY_LOGS` (`user_id` nullable, `event`, `subject_type`, `subject_id`, `description`, `old_values` json, `new_values` json, `ip_address` nullable, `created_at`).
- [ ] Tampilan riwayat aktivitas untuk Admin (AD-14) dan Direksi (DR-16). Untuk Direksi **wajib** mencakup perubahan harga dan diskon.
- [ ] Pencarian riwayat perbaikan kapal berdasarkan nama atau nomor lambung, lewat halaman Transaksi Disetujui (DR-15, BR-25).
- [ ] Halaman rekapitulasi pekerjaan (AD-16) dan rekap tagihan (DR-13).
- [ ] Bantu ekspor laporan PDF/Excel (DR-14).

**Yang harus ditanyakan dulu**

| Hal | Rujukan PRD |
|---|---|
| Cakupan riwayat aktivitas Petugas (sendiri atau seluruhnya), PT-10 | 9 no. 2 |
| Apakah Riwayat Perubahan, Audit Log, dan Riwayat Aktivitas satu fitur | 9 no. 1 |
| Apakah AD-11 dan DR-15 satu fitur atau dua | 9 no. 7 |
| Isi dan kolom laporan rekap, serta periodenya (tidak ada tabel laporan di ERD) | 9 no. 20 |
| Pemetaan tabel AD-10 (`STATUS_HISTORIES`, `INVOICES`, `ACTIVITY_LOGS`) | 3.3 |
| Perilaku hapus owner dan kapal | 9 no. 21 |
| Mesin ekspor PDF/Excel bersama belum punya pemilik | Bagian 6, no. 8 |

**Jangan**

- Jangan membuat tabel laporan baru.
- Jangan menambah field owner (contact person, telepon) dari mockup.
- Jangan membangun mesin ekspor bersama sebelum pemiliknya ditetapkan.

---

### 5.4 Regina: Opnam dan Kalkulasi Biaya

**Tabel:** `OPNAMS` (DB-09), `OPNAM_ITEMS` (DB-10), `OPNAM_ATTACHMENTS` (DB-11).

**Use case:** PT-01, PT-02, PT-03, PT-04, PT-05, PT-06, AD-05.

**Aturan bisnis:** BR-11, BR-12, BR-13, BR-14, BR-26, BR-27.

**Tugas konkret**

- [ ] Migrasi dan model tiga tabel sesuai ERD, termasuk `tanggal_opnam`, `catatan`, `total_biaya`, `lokasi`, `keterangan`, `sort_order`.
- [ ] Alur pencatatan: pilih kapal (wajib, BR-11) → detail pekerjaan (opsional, BR-12) → estimasi biaya → tinjauan akhir (PT-01).
- [ ] Pilih kapal dari data `SHIPS` beserta `OWNERS` (PT-02).
- [ ] Tambah, ubah, hapus item pekerjaan: material, volume, keterangan (PT-03).
- [ ] Hitung biaya otomatis dari tarif yang berlaku (PT-05, BR-14). Saat item dibuat, salin harga tarif ke `unit_price` dan hitung `subtotal = volume × unit_price`; nilai ini beku (snapshot).
- [ ] Isi `OPNAMS.total_biaya` sebagai snapshot.
- [ ] Unggah foto atau bukti per item, simpan `file_path`, `original_name`, `mime_type`, `file_size` (byte), `uploaded_by` (PT-04). Unggah opsional (BR-13). Validasi format dan ukuran wajib (BR-26).
- [ ] Simpan draft dengan status `draft` (PT-06).
- [ ] Halaman daftar opnam untuk Admin dengan pencarian, filter status dan tanggal, paginasi (AD-05, BR-27).
- [ ] Item dapat ditambah, diubah, dan dihapus hanya sesuai status transaksi.

**Yang harus ditanyakan dulu**

| Hal | Rujukan PRD |
|---|---|
| Format file yang diizinkan, ukuran maksimum, lokasi penyimpanan | 9 no. 18 |
| Apakah tombol Simpan Draft tersedia di alur pencatatan | 9 no. 8 |
| Aturan perhitungan `total_biaya` dan kapan snapshot diambil | 9 no. 38 |
| Arti dan cara input `catatan` dan `tanggal_opnam` | 9 no. 34, 39 |
| Arti dan cara input `lokasi`, `keterangan` | 9 no. 34 |
| Aturan pembuatan `opnam_number` | 9 no. 32 |
| Status transaksi mana yang mengizinkan ubah atau hapus item | "sesuai status transaksi" tidak dirinci |
| Pemilihan tarif yang berlaku | 9 no. 17 |

**Jangan**

- Jangan mengerjakan PT-07, PT-08, PT-09 (Bagian 6).
- Jangan menghitung ulang `unit_price`, `subtotal`, atau `total_biaya` dari tarif master setelah tersimpan.

---

### 5.5 Hazel: Review, Approval, Disposisi, dan Dokumen Tagihan

**Tabel:** `BILLINGS` (DB-13), `BILLING_ADJUSTMENTS` (DB-14), `INVOICES` (DB-15), `TECHNICAL_REPORTS` (DB-16).

**Use case:** AD-06, AD-08, AD-09, AD-13, DR-01, DR-02, DR-04, DR-05, DR-06, DR-07, DR-08, DR-09, DR-11, DR-12.

**Aturan bisnis:** BR-07, BR-08, BR-09, BR-16, BR-17, BR-18, BR-22, BR-23, BR-24.

**Tugas konkret**

- [ ] Migrasi dan model keempat tabel sesuai ERD.
- [ ] Halaman review opnam untuk Admin: rincian pekerjaan, estimasi biaya, lampiran (AD-06).
- [ ] Setujui hasil review → status `menunggu_approval_direksi`, isi `forwarded_at`, catat `STATUS_HISTORIES` (AD-08, ST-03). Pemeriksaan selalu mendahului keputusan (BR-16).
- [ ] Kembalikan untuk revisi → status `perlu_revisi`, **catatan wajib** (AD-09, BR-18, ST-02). Catat ke `STATUS_HISTORIES.note`.
- [ ] Daftar draft menunggu approval untuk Direksi (DR-01).
- [ ] Pemeriksaan draft tagihan: rincian dan total (DR-02). Selalu mendahului setujui atau tolak (BR-22).
- [ ] Diskon atau markup (DR-04): `adjustment_type` ∈ {`diskon`, `markup`}, `adjustment_value`, `adjustment_amount`. Opsional (BR-24).
- [ ] Catatan disposisi **wajib** untuk setiap diskon atau markup (DR-05, BR-08), simpan di `BILLING_ADJUSTMENTS.disposition_note`.
- [ ] Setujui tagihan: isi `decided_by`, `decided_at`, status `disetujui` (DR-06, ST-09).
- [ ] Tolak tagihan disertai alasan: isi `decided_by`, `decided_at`, status `ditolak` (DR-07, ST-10).
- [ ] Terbitkan invoice PDF setelah tagihan disetujui. Invoice **menampilkan harga** (DR-08, BR-09, BR-23). Isi snapshot kapal, lambung, owner, dan nominal.
- [ ] Terbitkan technical report PDF setelah tagihan disetujui. Technical report **tidak menampilkan harga** (DR-09, BR-09, BR-23). Isi snapshot kapal, lambung, owner.
- [ ] Ekspor invoice ke PDF untuk Admin (AD-13) dan Direksi (DR-11).
- [ ] Unduh atau cetak dokumen yang sudah terbit (DR-12).
- [ ] Hanya Direksi yang dapat memberi persetujuan akhir dan penyesuaian harga (BR-07).

**Yang harus ditanyakan dulu**

| Hal | Rujukan PRD |
|---|---|
| Bagaimana record `BILLINGS` dibuat dari opnam yang diteruskan (ST-08) | 9 no. 15 |
| Tempat menyimpan alasan penolakan (BILLINGS tidak punya kolom alasan; `STATUS_HISTORIES.note` hanya kandidat) | 9 no. 14 |
| Apakah tagihan yang ditolak final atau bisa diperbaiki dan diajukan ulang | 9 no. 6 |
| Apakah DR-01 membaca `OPNAMS.status` atau `BILLINGS.status`; siapa yang berwenang atas keputusan | 9 no. 36 |
| Aksi mana (DR-08, DR-09, atau keduanya) yang mengubah opnam menjadi `dokumen_terbit` | 9 no. 37 |
| Apakah DR-08 dan DR-11 aksi yang sama | 9 no. 4 |
| Aturan pembuatan `billing_number`, `invoice_number`, `report_number` | 9 no. 32 |
| Pemetaan nama status `menunggu_approval_direksi` menggantikan `diteruskan_ke_direksi` | 9 no. 35 |
| Hubungan `subtotal_amount`, `adjustment_amount`, `total_amount` dengan `OPNAMS.total_biaya` | tidak dispesifikasikan |

**Jangan**

- Jangan menampilkan harga di technical report.
- Jangan menerbitkan dokumen sebelum tagihan berstatus `disetujui`.
- Jangan mengerjakan AD-07, DR-03, AD-12, DR-10 (Bagian 6).
- Jangan membuat transisi status di luar Bagian 3.3.

---

## 6. Item Di Luar Tupoksi (Parkir)

**Agent tidak boleh mengerjakan item ini sebelum tim menetapkan pemiliknya.** Jika diminta, jawab bahwa item ini belum punya pemilik dan minta keputusan tim.

| No | Item | Keputusan yang dibutuhkan |
|---|---|---|
| 1 | PT-07, PT-08, PT-09, ST-01, ST-04 (alur pengajuan dan perbaikan opnam oleh Petugas) | Tentukan pemilik |
| 2 | AD-07, DR-03 (revisi data opnam oleh Admin dan Direksi) | Tentukan pemilik |
| 3 | AD-12, DR-10 (revisi invoice) | Tentukan pemilik |
| 4 | PT-11, AD-15, DR-17 (ekspor riwayat aktivitas ke PDF) | Tentukan pemilik |
| 5 | ST-08 (pembuatan tagihan dari opnam yang diteruskan) | Tentukan pemilik dan cara pembuatannya |
| 6 | DB-12 `STATUS_HISTORIES` (dipakai Regina dan Hazel) | Tentukan pemilik tunggal |
| 7 | AD-10, AD-11, AD-14, PT-10, DR-13, DR-16 (cakupan riwayat dan audit) | Konfirmasi cakupan masing-masing |
| 8 | Mesin ekspor PDF/Excel bersama | Tentukan pemilik |
| 9 | Middleware dan policy | Sudah ditetapkan ke Halvina, konfirmasi tim |

Catatan: item 1 membuat Petugas belum bisa mengajukan opnam di sistem. Akibatnya alur Hazel (review) belum bisa diuji dari ujung ke ujung sampai item 1 dan item 5 diputuskan.

---

## 7. Definition of Done (per Item)

Sebuah item boleh ditandai `Selesai` hanya jika semua poin berikut terpenuhi:

- [ ] Nama tabel, kolom, tipe, dan nilai enum sama persis dengan ERD.
- [ ] Tidak ada tabel, kolom, enum, role, status, atau fitur baru di luar PRD.
- [ ] Aturan bisnis yang tercantum di item tersebut diterapkan dan diuji (lihat kolom Aturan di PRD Bagian 3).
- [ ] Hak akses mengikuti PRD Bagian 2: aktor yang tidak berhak ditolak.
- [ ] Kolom snapshot ditulis sekali dan tidak dihitung ulang.
- [ ] Transisi status hanya yang ada di Bagian 3.3.
- [ ] Setiap pertanyaan terbuka yang menyentuh item sudah dijawab pengguna, atau item ditandai `Perlu konfirmasi`.
- [ ] Status dan Catatan di checklist `prd.md` Bagian 10 sudah diperbarui dalam perubahan yang sama.

Spesifikasi sumber tidak memuat kriteria penerimaan per fitur (PRD 9, no. 11). Satu-satunya poin penerimaan adalah aturan eksplisit di kolom "Aturan dan poin penerimaan" PRD Bagian 3.

---

## 8. Format Pertanyaan Konfirmasi

Gunakan format ini saat harus berhenti dan bertanya.

```
Item: <ID, mis. DR-07>
Tertahan karena: <rujukan PRD, mis. 9 no. 14>
Pertanyaan: <satu pertanyaan spesifik>
Opsi (jika ada):
  A. <opsi>
  B. <opsi>
Dampak jika menebak: <mis. kolom baru di ERD / transisi status baru>
```

Contoh:

```
Item: DR-07 Menolak Tagihan
Tertahan karena: PRD 9 no. 14 (alasan penolakan tidak punya kolom di BILLINGS)
Pertanyaan: Di mana alasan penolakan disimpan?
Opsi:
  A. STATUS_HISTORIES.note
  B. Tambah kolom di BILLINGS (perlu revisi ERD)
Dampak jika menebak: opsi B membuat kolom yang tidak ada di ERD.
```

---

## 9. Daftar Pertanyaan Terbuka yang Memblokir Pekerjaan

Ringkasan dari PRD Bagian 9, dikelompokkan menurut pemilik yang terdampak. Selesaikan sebelum item terkait dikerjakan.

| Pemilik | Pertanyaan yang memblokir |
|---|---|
| Halvina | Aturan password; field profil; isi dashboard; hapus vs nonaktif pengguna |
| Anisa | Hapus soft vs hard; tanggal berakhir tarif; pemilihan tarif berlaku; faktor tambahan tarif |
| Rifqy | Cakupan riwayat Petugas; penggabungan riwayat; AD-11 vs DR-15; isi laporan dan ekspor; hapus owner dan kapal |
| Regina | Batas unggahan; Simpan Draft; rumus `total_biaya`; makna `catatan`, `tanggal_opnam`, `lokasi`, `keterangan`; format `opnam_number` |
| Hazel | Pembuatan tagihan; penyimpanan alasan tolak; alur setelah tolak; otoritas status opnam vs tagihan; pemicu `dokumen_terbit`; DR-08 vs DR-11; format nomor dokumen |
| Tim | Pemilik item parkir (Bagian 6); nama status `menunggu_approval_direksi`; apakah Direksi boleh merevisi opnam dan invoice; waktu revisi invoice |
