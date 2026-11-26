# Tasklist Regina — Opnam dan Kalkulasi Biaya

Working copy untuk memantau item yang menjadi tanggung jawab Regina. Sumber kebenaran tetap **checklist `prd.md` Bagian 10**; file ini mengikuti format `taskinstruction.md` Bagian 5.4.

- **Peminta:** Regina
- **Modul:** Opnam dan Kalkulasi Biaya
- **Tabel:** DB-09 `OPNAMS`, DB-10 `OPNAM_ITEMS`, DB-11 `OPNAM_ATTACHMENTS`
- **Use case:** PT-01, PT-02, PT-03, PT-04, PT-05, PT-06, AD-05
- **Aturan bisnis:** BR-11, BR-12, BR-13, BR-14, BR-26, BR-27
- **Status yang sah:** `Belum`, `Dikerjakan`, `Selesai`, `Perlu konfirmasi`

## Daftar tugas

| ID | Item | Pemilik | Status | Blokir / pertanyaan terbuka | Catatan |
|---|---|---|---|---|---|
| DB-09 | `OPNAMS` | Regina | Selesai | 9 no. 32 (`opnam_number`), 34/39 (`catatan`, `tanggal_opnam`), 38 (`total_biaya`) | Migrasi diperbaiki + model disinkronkan (7 konstanta status ERD, fillable `tanggal_opnam`/`catatan`/`total_biaya`). Terverifikasi via `db:table`. Logika aplikasi menyusul di PT-01/PT-06. |
| DB-10 | `OPNAM_ITEMS` | Regina | Selesai | 9 no. 34 (`lokasi`); status mana yang mengizinkan ubah/hapus item | Migrasi baru `2026_11_23_145200_create_opnam_items_table.php` + model: fillable `lokasi`, cast `volume` decimal:2 mengikuti migrasi. Terverifikasi via `db:table`. |
| DB-11 | `OPNAM_ATTACHMENTS` | Regina | Selesai | 9 no. 18 (format, ukuran, lokasi file) | Migrasi: tambah `keterangan` nullable, `file_size` = `integer` (byte); model: fillable `keterangan`. Terverifikasi via `db:table`. |
| PT-01 | Mencatat Opnam Pekerjaan | Regina | Selesai | — | Wizard 5 langkah. Langkah 1: wajib Pilih Kapal / Tanggal Opname / `Lokasi Pekerjaan` (`<select>` 2 dock tanpa placeholder); field auto-fill readonly, **Catatan dihapus dari form** (kolom DB tetap). Faktor 1x/1,25x dihitung & disimpan. PT-07 tetap parkir. |
| PT-02 | Memilih Data Kapal | Regina | Selesai | — | Wajib (BR-11); pilih `SHIPS` + `OWNERS`, data kapal terisi otomatis. |
| PT-03 | Menambahkan Detail Pekerjaan | Regina | Selesai | — | Item dinamis (tambah/hapus/ubah lewat step 2); kategori→jenis→material→satuan; `lokasi` per item sesuai ERD. Field mockup di luar ERD tidak dibuat. |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Regina | Selesai | — | JPG/PNG/PDF maks 10 MB (sesuai screenshot, disetujui); simpan di `storage/app/public/opnam-attachments`. Opsional (BR-13); validasi aktif (BR-26). |
| PT-05 | Melihat Perhitungan Biaya | Regina | Selesai | — | Tarif terbaru per material+satuan; `subtotal = volume × unit_price`; `total_biaya` = jumlah subtotal (disetujui); snapshot beku. |
| PT-06 | Menyimpan Draft Opnam | Regina | Selesai | — | Tombol Simpan Draft tersedia di Final Review (sesuai screenshot); nomor `OPN-{tahun}-{3 digit}` (disetujui); status persis `draft`. |
| AD-05 | Melihat Daftar Opnam | Regina | Selesai | — | Tampilan Supervisor sesuai screenshot: breadcrumb, badge status, kolom Operator, paginasi. Filter `q`, status, rentang `tanggal_opnam`. 5 tes Pest lulus (BR-27). |

## Rencana pengerjaan

1. **Fondasi data** — migrasi + model DB-09, DB-10, DB-11. Menunggu tabel induk dari tim lain: `USERS` (Halvina) → `OWNERS`/`SHIPS` (Rifqy) → master tarif (Anisa). Migrasi tidak boleh merujuk tabel yang belum ada (`taskinstruction.md` Bagian 4).
2. **Pencatatan opnam** — PT-01, PT-02, PT-06.
3. **Item & kalkulasi** — PT-03, PT-05. Snapshot ditulis sekali, tidak dihitung ulang dari tarif master.
4. **Unggah bukti** — PT-04, setelah batas file diputuskan.
5. **Daftar opnam** — AD-05 (pencarian, filter status/tanggal, paginasi).

## Batasan (jangan dikerjakan)

- PT-07, PT-08, PT-09 — item parkir (`taskinstruction.md` Bagian 6, no. 1).
- Menghitung ulang `unit_price`, `subtotal`, atau `total_biaya` dari tarif master setelah tersimpan (BR-10).
- Tabel/middleware/policy milik anggota lain, serta field dari mockup (`contact person`, telepon, `Berlaku hingga`, kuantitas minimum, faktor tarif).

## Sinkronisasi

Saat sebuah item selesai, lakukan dalam **satu perubahan yang sama**:

1. Update Status menjadi `Selesai` dan isi Catatan di `tasklist.md` ini.
2. Update Status dan Catatan di checklist `prd.md` Bagian 10 (checklist resmi sesuai Definition of Done).

Jika item masih tertahan, tandai `Perlu konfirmasi` dan ajukan pertanyaan memakai format `taskinstruction.md` Bagian 8.
