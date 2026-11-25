---
name: simship-erd
description: Sumber kebenaran skema basis data SIMSHIP (Sistem Pencatatan Opnam Pekerjaan Kapal) sesuai ERD revisi (17 tabel, 24 relasi). Gunakan skill ini setiap kali membuat atau mengubah migration, model Eloquent, relasi, seeder, factory, validasi, enum status/role, atau query yang menyentuh tabel SIMSHIP (USERS, OWNERS, SHIPS, UNITS, WORK_CATEGORIES, WORK_TYPES, MATERIAL_SPECS, TARIFFS, OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS, STATUS_HISTORIES, BILLINGS, BILLING_ADJUSTMENTS, INVOICES, TECHNICAL_REPORTS, ACTIVITY_LOGS). Pakai juga ketika pengguna menyebut opnam, tagihan, invoice, technical report, tarif, snapshot, atau audit log pada proyek ini, walaupun tidak menyebut kata "ERD".
---

# SIMSHIP: Skema Basis Data (ERD Revisi)

Sumber: `Perancangan_database_Laravel_rev.pdf` (ERD revisi, **17 tabel, 24 relasi**), sebagaimana direproduksi di PRD SIMSHIP Bagian 6.
Jika dokumen lain bertentangan dengan ERD, **ikuti ERD**.

## Aturan wajib untuk agent

1. Jangan membuat tabel, kolom, nilai enum, role, atau status yang tidak ada di dokumen ini.
2. Gunakan nama tabel, nama kolom, dan nilai enum **persis** seperti tertulis (huruf kecil, snake_case, bahasa Indonesia untuk nilai status/role). Jangan diterjemahkan.
3. Jika suatu hal ditandai **TIDAK DISPESIFIKASIKAN**, berhenti dan tanyakan kepada pengguna. Jangan menebak (termasuk panjang string, presisi decimal, default, index, on-delete).
4. Soft delete (`deleted_at`) hanya pada 5 tabel: OWNERS, SHIPS, WORK_CATEGORIES, WORK_TYPES, MATERIAL_SPECS. Jangan berasumsi soft delete di tabel lain.
5. Kolom **Snapshot** ditulis sekali saat transaksi terjadi dan tidak dihitung ulang dari data master setelahnya.
6. Jangan menambahkan `created_at`/`updated_at` di tabel yang ERD-nya tidak mencantumkannya (lihat 3.).
7. Jangan menambah fitur, validasi, atau hak akses di luar apa yang dinyatakan eksplisit.

Legenda: PK = primary key, FK = foreign key, UK = unique key. "nullable" hanya ditulis jika ERD menyebutkannya; selain itu nullability = TIDAK DISPESIFIKASIKAN kecuali kolom itu PK/FK/UK yang wajar, dan tetap tanyakan bila ragu.

---

## 1. Tabel

### 1.1 Identitas dan data master

#### USERS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| name | string | |
| username | string | UK |
| email | string | UK |
| password | string | |
| role | string | Nilai: `petugas`, `admin`, `direksi` |
| is_active | boolean | |
| last_login_at | datetime | |

#### OWNERS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| name | string | |
| address | text | nullable |
| deleted_at | datetime | Nullability: TIDAK DISPESIFIKASIKAN |

#### SHIPS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| owner_id | bigint | FK ke OWNERS.id |
| name | string | |
| hull_number | string | UK |
| deleted_at | datetime | Nullability: TIDAK DISPESIFIKASIKAN |

#### UNITS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| code | string | UK |
| name | string | |

(UNITS tidak memiliki `deleted_at`.)

#### WORK_CATEGORIES
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| name | string | UK |
| description | text | nullable |
| deleted_at | datetime | Nullability: TIDAK DISPESIFIKASIKAN |

#### WORK_TYPES
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| work_category_id | bigint | FK ke WORK_CATEGORIES.id |
| name | string | |
| deleted_at | datetime | Nullability: TIDAK DISPESIFIKASIKAN |

#### MATERIAL_SPECS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| work_type_id | bigint | FK ke WORK_TYPES.id |
| name | string | |
| deleted_at | datetime | Nullability: TIDAK DISPESIFIKASIKAN |

#### TARIFFS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| material_spec_id | bigint | FK ke MATERIAL_SPECS.id |
| unit_id | bigint | FK ke UNITS.id |
| created_by | bigint | FK ke USERS.id |
| price | decimal | |
| effective_from | date | |
| created_at | datetime | |

(Hanya `effective_from`; tidak ada tanggal berakhir. Tidak ada `updated_at` dan tidak ada `deleted_at`.)

### 1.2 Opnam

#### OPNAMS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| opnam_number | string | UK |
| ship_id | bigint | FK ke SHIPS.id |
| created_by | bigint | FK ke USERS.id |
| status | string | Nilai: `draft`, `menunggu_review`, `perlu_revisi`, `menunggu_approval_direksi`, `ditolak`, `disetujui`, `dokumen_terbit` |
| submitted_at | datetime | nullable |
| forwarded_at | datetime | nullable |
| tanggal_opnam | date | |
| catatan | text | nullable |
| total_biaya | decimal | **Snapshot** |
| created_at | datetime | |
| updated_at | datetime | |

#### OPNAM_ITEMS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| opnam_id | bigint | FK ke OPNAMS.id |
| tariff_id | bigint | FK ke TARIFFS.id |
| volume | decimal | |
| unit_price | decimal | **Snapshot**: dibekukan saat transaksi terjadi |
| subtotal | decimal | **Snapshot**: dibekukan saat transaksi terjadi |
| description | text | nullable |
| sort_order | int | |
| lokasi | string | nullable |
| created_at | datetime | |
| updated_at | datetime | |

#### OPNAM_ATTACHMENTS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| opnam_item_id | bigint | FK ke OPNAM_ITEMS.id |
| uploaded_by | bigint | FK ke USERS.id |
| file_path | string | |
| original_name | string | |
| mime_type | string | |
| file_size | int | Satuan: byte |
| keterangan | text | nullable |
| created_at | datetime | |
| updated_at | datetime | |

#### STATUS_HISTORIES
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| statusable_type | string | Polimorfik |
| statusable_id | bigint | Polimorfik |
| from_status | string | nullable |
| to_status | string | |
| note | text | nullable |
| changed_by | bigint | FK ke USERS.id; nullable |
| created_at | datetime | |

(Tidak ada `updated_at`.)

### 1.3 Tagihan, invoice, laporan

#### BILLINGS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| opnam_id | bigint | FK ke OPNAMS.id |
| billing_number | string | UK; nullable |
| status | string | Nilai: `menunggu_approval`, `disetujui`, `ditolak` |
| subtotal_amount | decimal | |
| adjustment_amount | decimal | |
| total_amount | decimal | |
| decided_by | bigint | FK ke USERS.id; nullable |
| decided_at | datetime | nullable |

#### BILLING_ADJUSTMENTS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| billing_id | bigint | FK ke BILLINGS.id |
| created_by | bigint | FK ke USERS.id |
| adjustment_type | string | Nilai: `diskon`, `markup` |
| adjustment_value | decimal | |
| adjustment_amount | decimal | |
| disposition_note | text | |

#### INVOICES
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| billing_id | bigint | FK ke BILLINGS.id |
| issued_by | bigint | FK ke USERS.id |
| invoice_number | string | UK |
| issue_date | date | |
| ship_name | string | **Snapshot** |
| hull_number | string | **Snapshot** |
| owner_name | string | **Snapshot** |
| owner_address | text | **Snapshot**; nullable |
| subtotal_amount | decimal | **Snapshot** |
| adjustment_amount | decimal | **Snapshot** |
| total_amount | decimal | **Snapshot** |
| revision_count | int | |

#### TECHNICAL_REPORTS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| billing_id | bigint | FK ke BILLINGS.id |
| issued_by | bigint | FK ke USERS.id |
| report_number | string | UK |
| issue_date | date | |
| ship_name | string | **Snapshot** |
| hull_number | string | **Snapshot** |
| owner_name | string | **Snapshot** |
| owner_address | text | **Snapshot**; nullable |

(Technical report tidak memiliki kolom nominal/harga.)

### 1.4 Log aktivitas

#### ACTIVITY_LOGS
| Kolom | Tipe | Kunci / constraint |
|---|---|---|
| id | bigint | PK |
| user_id | bigint | FK ke USERS.id; nullable |
| event | string | |
| subject_type | string | Polimorfik |
| subject_id | bigint | Polimorfik |
| description | string | |
| old_values | json | nullable |
| new_values | json | nullable |
| ip_address | string | nullable |
| created_at | datetime | |

(Tidak ada `updated_at`.)

---

## 2. Relasi (24, sesuai ERD)

Dibaca dari arah foreign key.

| # | Anak.kolom | Induk | Makna |
|---|---|---|---|
| 1 | SHIPS.owner_id | OWNERS.id | Satu owner memiliki banyak kapal |
| 2 | WORK_TYPES.work_category_id | WORK_CATEGORIES.id | Satu kategori memiliki banyak jenis pekerjaan |
| 3 | MATERIAL_SPECS.work_type_id | WORK_TYPES.id | Satu jenis pekerjaan memiliki banyak spesifikasi material |
| 4 | TARIFFS.material_spec_id | MATERIAL_SPECS.id | Satu spesifikasi material memiliki banyak tarif |
| 5 | TARIFFS.unit_id | UNITS.id | Satu satuan dipakai banyak tarif |
| 6 | TARIFFS.created_by | USERS.id | Satu user membuat banyak tarif |
| 7 | OPNAMS.ship_id | SHIPS.id | Satu kapal memiliki banyak opnam |
| 8 | OPNAMS.created_by | USERS.id | Satu user membuat banyak opnam |
| 9 | OPNAM_ITEMS.opnam_id | OPNAMS.id | Satu opnam memiliki banyak item |
| 10 | OPNAM_ITEMS.tariff_id | TARIFFS.id | Satu tarif dipakai banyak item |
| 11 | OPNAM_ATTACHMENTS.opnam_item_id | OPNAM_ITEMS.id | Satu item memiliki banyak lampiran |
| 12 | OPNAM_ATTACHMENTS.uploaded_by | USERS.id | Satu user mengunggah banyak lampiran |
| 13 | STATUS_HISTORIES.changed_by | USERS.id | Nullable |
| 14 | STATUS_HISTORIES.statusable_type + statusable_id | (polimorfik) | Riwayat status untuk record statusable apa pun |
| 15 | BILLINGS.opnam_id | OPNAMS.id | Satu opnam memiliki tagihan |
| 16 | BILLINGS.decided_by | USERS.id | Nullable |
| 17 | BILLING_ADJUSTMENTS.billing_id | BILLINGS.id | Satu tagihan memiliki banyak penyesuaian |
| 18 | BILLING_ADJUSTMENTS.created_by | USERS.id | |
| 19 | INVOICES.billing_id | BILLINGS.id | |
| 20 | INVOICES.issued_by | USERS.id | |
| 21 | TECHNICAL_REPORTS.billing_id | BILLINGS.id | |
| 22 | TECHNICAL_REPORTS.issued_by | USERS.id | |
| 23 | ACTIVITY_LOGS.user_id | USERS.id | Nullable |
| 24 | ACTIVITY_LOGS.subject_type + subject_id | (polimorfik) | Aktivitas pada record subjek apa pun |

Kardinalitas pada tabel ini diturunkan dari arah FK dan label ERD; simbol kardinalitas pasti per relasi tidak terkonfirmasi dari ekstraksi teks (mis. `BILLINGS.opnam_id` dilabeli "satu opnam memiliki tagihan"; apakah 1-ke-1 atau 1-ke-banyak: tanyakan sebelum menambah unique constraint).

---

## 3. Ringkasan lintas tabel

**Soft delete (`deleted_at`)**: OWNERS, SHIPS, WORK_CATEGORIES, WORK_TYPES, MATERIAL_SPECS. Tidak ada di tabel lain, termasuk UNITS dan TARIFFS.

**Timestamp yang tercantum di ERD**
| Tabel | created_at | updated_at |
|---|---|---|
| TARIFFS | ya | tidak |
| OPNAMS | ya | ya |
| OPNAM_ITEMS | ya | ya |
| OPNAM_ATTACHMENTS | ya | ya |
| STATUS_HISTORIES | ya | tidak |
| ACTIVITY_LOGS | ya | tidak |
| Semua tabel lain | tidak | tidak |

**Kolom snapshot** (ditulis sekali, tidak dihitung ulang dari master)
- OPNAM_ITEMS: `unit_price`, `subtotal`
- OPNAMS: `total_biaya`
- INVOICES: `ship_name`, `hull_number`, `owner_name`, `owner_address`, `subtotal_amount`, `adjustment_amount`, `total_amount`
- TECHNICAL_REPORTS: `ship_name`, `hull_number`, `owner_name`, `owner_address`

**Kolom unique**: USERS.username, USERS.email, SHIPS.hull_number, UNITS.code, WORK_CATEGORIES.name, OPNAMS.opnam_number, BILLINGS.billing_number (nullable), INVOICES.invoice_number, TECHNICAL_REPORTS.report_number.

**Kolom polimorfik**: STATUS_HISTORIES (`statusable_type`, `statusable_id`) dan ACTIVITY_LOGS (`subject_type`, `subject_id`).

**Nilai enum (disimpan sebagai string)**
| Kolom | Nilai |
|---|---|
| USERS.role | `petugas`, `admin`, `direksi` |
| OPNAMS.status | `draft`, `menunggu_review`, `perlu_revisi`, `menunggu_approval_direksi`, `ditolak`, `disetujui`, `dokumen_terbit` |
| BILLINGS.status | `menunggu_approval`, `disetujui`, `ditolak` |
| BILLING_ADJUSTMENTS.adjustment_type | `diskon`, `markup` |

Catatan: `diteruskan_ke_direksi` (istilah lama dokumen use case) sudah **digantikan** `menunggu_approval_direksi` di ERD revisi. Jangan memakai istilah lama. Istilah antarmuka "Operator"/"Supervisor" bukan nilai role; nilai role di DB tetap `petugas`/`admin`.

---

## 4. TIDAK DISPESIFIKASIKAN (berhenti dan tanyakan)

ERD tidak menetapkan hal-hal berikut. Jangan mengisinya sendiri:

- Panjang kolom string/text, presisi dan skala semua kolom decimal.
- Nullability kolom yang tidak ditandai nullable (termasuk `deleted_at` pada 5 tabel soft delete).
- Index, nilai default, aturan on-delete / on-update.
- Apakah status ditegakkan oleh basis data (check/enum) atau kode aplikasi.
- Daftar lengkap nilai role selain tiga nilai di atas.
- Cara pembuatan nomor dokumen (`opnam_number`, `billing_number`, `invoice_number`, `report_number`).
- Tempat penyimpanan alasan penolakan tagihan (BILLINGS tidak punya kolom alasan; `STATUS_HISTORIES.note` hanya kandidat, belum dikonfirmasi).
- Tempat penyimpanan riwayat revisi invoice di luar `revision_count`.
- Aturan perhitungan dan saat pengambilan snapshot `OPNAMS.total_biaya` (BR-14 hanya mencakup estimasi per item).
- Makna dan aturan input `OPNAMS.tanggal_opnam`, `OPNAMS.catatan`, `OPNAM_ITEMS.lokasi`, `OPNAM_ATTACHMENTS.keterangan`.
- Tabel mana yang berwenang atas keputusan `disetujui`/`ditolak` (OPNAMS vs BILLINGS), dan aksi mana (DR-08/DR-09) yang mengubah OPNAMS.status ke `dokumen_terbit`.
- Cara tagihan (BILLINGS) dibuat dari opnam berstatus `menunggu_approval_direksi`.
- Perilaku hapus (soft atau hard) di luar kolom `deleted_at` yang ada; apakah USERS dapat dihapus (hanya `is_active` untuk menonaktifkan).
- Batas unggahan (format, ukuran maksimum, lokasi penyimpanan) untuk OPNAM_ATTACHMENTS.
- Laporan rekapitulasi: tidak ada tabel di ERD.
- File `Database_Design_SIMSHIP.md` Bagian 4 (dirujuk ERD) tidak tersedia; constraint dan asumsi di dalamnya tidak diketahui.

---

## 5. Konteks perilaku yang memengaruhi skema (dari use case, bukan bagian ERD)

Hanya yang dinyatakan eksplisit di sumber; gunakan sebagai konteks, jangan memperluasnya.

**Transisi status yang dinyatakan di use case**

| Entitas | Dari | Ke | Pemicu |
|---|---|---|---|
| OPNAMS | (baru) | `draft` | PT-06 simpan draft |
| OPNAMS | `draft` | `menunggu_review` | PT-07 ajukan (mengisi `submitted_at`) |
| OPNAMS | `menunggu_review` | `perlu_revisi` | AD-09 kembalikan, catatan wajib |
| OPNAMS | `menunggu_review` | `menunggu_approval_direksi` | AD-08 setujui dan teruskan (mengisi `forwarded_at`) |
| BILLINGS | `menunggu_approval` | `disetujui` | DR-06 setujui |
| BILLINGS | `menunggu_approval` | `ditolak` | DR-07 tolak |

**Transisi tersirat, belum dikonfirmasi** (jangan implementasikan sebelum pengguna mengonfirmasi): `perlu_revisi` → `menunggu_review` (PT-09), `menunggu_approval_direksi` → `disetujui`/`ditolak`, `disetujui` → `dokumen_terbit`, dan pembuatan BILLINGS (status awal `menunggu_approval`). Transisi lain tidak diizinkan.

**Aturan bisnis yang menyentuh data**
- BR-01: akun dengan `is_active` false tidak dapat login.
- BR-08: setiap diskon/markup wajib punya catatan disposisi (`BILLING_ADJUSTMENTS.disposition_note`).
- BR-09: invoice menampilkan harga; technical report tidak.
- BR-10: perubahan tarif tidak mengubah transaksi yang sudah tercatat (karena snapshot).
- BR-18: pengembalian opnam wajib disertai catatan.
- BR-23: invoice dan technical report hanya diterbitkan setelah tagihan `disetujui`.
- BR-21: Petugas hanya dapat memperbaiki opnam berstatus `perlu_revisi`.
- DR-16 / AD-14 / PT-10: riwayat aktivitas membaca ACTIVITY_LOGS (DR-16 wajib mencakup perubahan harga dan diskon).

---

## 6. Checklist verifikasi sebelum menyelesaikan pekerjaan

- [ ] Jumlah tabel = 17, jumlah relasi = 24.
- [ ] Setiap nama tabel/kolom/enum identik dengan dokumen ini.
- [ ] `deleted_at` hanya di OWNERS, SHIPS, WORK_CATEGORIES, WORK_TYPES, MATERIAL_SPECS.
- [ ] Timestamp sesuai tabel di bagian 3 (tidak ada tambahan).
- [ ] Semua kolom UK dan FK terpasang; kolom nullable persis seperti ditandai.
- [ ] Tidak ada kolom, status, atau role karangan (contoh yang **tidak** ada di ERD: contact person owner, nomor telepon, tanggal berakhir tarif, kuantitas minimum, faktor tarif, kolom alasan penolakan).
- [ ] Item di bagian 4 yang tersentuh sudah ditanyakan ke pengguna, bukan ditebak.
