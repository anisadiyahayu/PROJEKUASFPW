# PRD SIMSHIP (Versi Bahasa Indonesia)

## Panduan untuk AI Agent (baca dahulu)

### Urutan sumber

1. **Basis data** (nama, tipe, kolom, kunci, nilai enum): ERD revisi, `Perancangan_database_Laravel_rev.pdf`. Jika dokumen lain bertentangan dengan ERD, ikuti ERD.
2. **Perilaku** (use case, alur kerja, aturan bisnis): `Daftar_Use_Case_SIMSHIP.docx` dan `Usecase.pdf`. Analisis fitur hanya gambaran umum, bukan spesifikasi yang mengikat.
3. **Mockup UI** (`UI_Project_FPW.pdf`): bukan acuan. Jangan membangun layar dari mockup ini.

### Aturan wajib bagi agent implementasi

- Jangan membuat tabel, kolom, nilai enum, role, atau status yang tidak ada di ERD.
- Jangan menambah fitur, validasi, atau hak akses di luar Bagian 2 sampai 5.
- Jika nilai ditandai "Tidak dispesifikasikan" atau tercantum di Bagian 9, **berhenti dan tanyakan**. Jangan menebak.
- Gunakan nilai ERD yang persis untuk status dan role (misalnya `menunggu_review`, bukan terjemahan).
- Kolom snapshot (`unit_price`, `subtotal`, `total_biaya`, `ship_name`, `hull_number`, `owner_name`, `owner_address`, `subtotal_amount`, `adjustment_amount`, `total_amount`) ditulis sekali saat transaksi terjadi dan tidak dihitung ulang dari data master setelahnya.
- Data master yang dihapus memakai `deleted_at` hanya jika ERD mendefinisikannya. Jangan berasumsi soft delete di tempat lain.

### Transisi status opnam

| Dari | Ke | Pemicu | Dasar |
|---|---|---|---|
| (baru) | `draft` | PT-06 simpan draft | Use case |
| `draft` | `menunggu_review` | PT-07 ajukan (mengisi `submitted_at`) | Use case, BR-15 |
| `menunggu_review` | `perlu_revisi` | AD-09 kembalikan dengan catatan | Use case |
| `menunggu_review` | `menunggu_approval_direksi` | AD-08 setujui review dan teruskan (mengisi `forwarded_at`) | Use case; nilai ERD mengganti `diteruskan_ke_direksi`, perlu konfirmasi |
| `perlu_revisi` | `menunggu_review` | PT-09 perbaiki dan ajukan ulang | Tersirat, tidak dinyatakan. Konfirmasi |
| `menunggu_approval_direksi` | `disetujui` | Tagihan disetujui (DR-06) | Tersirat, konfirmasi |
| `menunggu_approval_direksi` | `ditolak` | Tagihan ditolak (DR-07) | Tersirat, konfirmasi |
| `disetujui` | `dokumen_terbit` | Invoice atau technical report diterbitkan (DR-08, DR-09) | Tersirat, konfirmasi |

### Transisi status tagihan

Transisi yang tidak ada di tabel ini tidak diizinkan sampai pengguna mengonfirmasinya.

| Dari | Ke | Pemicu | Dasar |
|---|---|---|---|
| (dibuat) | `menunggu_approval` | Pembuatan tagihan dari opnam yang diteruskan | Langkah pembuatan tidak dispesifikasikan (Bagian 9) |
| `menunggu_approval` | `disetujui` | DR-06 setujui | Use case |
| `menunggu_approval` | `ditolak` | DR-07 tolak | Use case |

---

## 1. Ringkasan Produk

SIMSHIP (Sistem Pencatatan Opnam Pekerjaan Kapal) mencatat opnam atau inspeksi pekerjaan kapal yang dilakukan di lapangan, memeriksanya, lalu mengubah pekerjaan yang disetujui menjadi tagihan, invoice, dan technical report.

- **Tujuan:** Mencatat pekerjaan kapal beserta estimasi biayanya, memeriksa catatan tersebut, dan menyelesaikannya melalui keputusan Direksi.
- **Latar belakang:** Tidak dispesifikasikan dalam dokumen sumber.
- **Sasaran:** Tidak dinyatakan sebagai pernyataan tersendiri. Lingkup fungsional di bawah ini adalah satu-satunya uraian maksud dalam dokumen sumber.
- **Pengguna:** Petugas (pencatat lapangan), Admin (pengelola data dan pemeriksa), dan Direksi (pengambil keputusan akhir). Lihat Bagian 2.

### Lingkup dalam dokumen sumber

- 49 use case: 5 umum (UC-U1 sampai UC-U5), 11 Petugas (PT-01 sampai PT-11), 16 Admin (AD-01 sampai AD-16), dan 17 Direksi (DR-01 sampai DR-17).
- 17 tabel basis data dan 24 relasi dalam ERD.
- Pencarian, filter, dan paginasi dijelaskan di dalam use case yang memuatnya, bukan sebagai use case tersendiri.

### Di luar cakupan PRD ini

Antarmuka pengguna. Dokumen use case sumber menyatakan bahwa antarmuka sedang direvisi dan belum menjadi acuan. Mockup `UI_Project_FPW.pdf` hanya dicatat sebagai konteks (lihat Bagian 9).

### Dokumen sumber

- `Daftar_Use_Case_SIMSHIP.docx` (daftar use case dan fitur utama)
- `Usecase.pdf` (diagram use case untuk Petugas, Admin, dan Direksi)
- `Perancangan_database_Laravel_rev.pdf` (ERD revisi)

---

## 2. Aktor dan Hak Akses

SIMSHIP memiliki tiga aktor. Fitur umum (login, logout, ubah password, dashboard, profil) tersedia untuk ketiganya.

| Aktor | Peran (sumber) | Tanggung jawab utama (sumber) |
|---|---|---|
| Petugas | Pencatat lapangan untuk inspeksi pekerjaan kapal | Mencatat dan mengajukan opnam, memperbaiki opnam yang dikembalikan untuk revisi. |
| Admin | Pengelola data dan pemeriksa opnam | Mengelola pengguna dan master data, memeriksa opnam, merevisi data opnam, meneruskan opnam ke Direksi, serta merevisi dan mengekspor invoice. |
| Direksi | Pengambil keputusan akhir | Memeriksa draft tagihan, memberikan diskon atau markup dengan catatan disposisi, menyetujui atau menolak tagihan, serta menerbitkan dan mengekspor dokumen. |

### Akses dan batasan per aktor (sesuai dokumen sumber)

| Kemampuan | Petugas | Admin | Direksi |
|---|---|---|---|
| Fitur umum UC-U1 sampai UC-U5 | Ya | Ya | Ya |
| Memilih kapal, mencatat, menyimpan draft, mengajukan opnam (PT-01, PT-02, PT-06, PT-07) | Ya | Tidak tercantum | Tidak tercantum |
| Memperbaiki opnam yang dikembalikan (PT-09) | Ya | Tidak tercantum | Tidak tercantum |
| Mengelola pengguna (AD-01) | Tidak | Ya | Tidak tercantum |
| Mengelola owner dan kapal (AD-02) | Tidak tercantum | Ya | Tidak tercantum |
| Mengelola kategori, jenis pekerjaan, material, satuan, tarif dasar (AD-03, AD-04) | Tidak (tidak boleh mengubah tarif dasar) | Ya | Tidak tercantum |
| Melihat seluruh opnam (AD-05), memeriksa (AD-06), meneruskan atau mengembalikan (AD-08, AD-09) | Tidak tercantum | Ya | Tidak tercantum |
| Merevisi data opnam (AD-07) | Tidak tercantum | Ya | Ya, sesuai kebutuhan saat pemeriksaan tagihan (DR-03) |
| Diskon atau markup dengan disposisi (DR-04, DR-05) | Tidak | Tidak tercantum | Ya |
| Menyetujui atau menolak tagihan (DR-06, DR-07) | Tidak (tidak dapat memberi persetujuan akhir) | Tidak tercantum | Ya |
| Menerbitkan invoice PDF dan technical report PDF (DR-08, DR-09) | Tidak tercantum | Tidak tercantum | Ya |
| Merevisi invoice (AD-12, DR-10) | Tidak tercantum | Ya (AD-12) | Ya (DR-10) |
| Mengekspor invoice ke PDF (AD-13, DR-11) | Tidak (tidak dapat mengekspor invoice) | Ya | Ya |
| Melihat rekap tagihan (DR-13) | Tidak tercantum | Tidak tercantum | Ya |
| Laporan PDF atau Excel (AD-16, DR-14) | Tidak tercantum | Ya (AD-16) | Ya (DR-14) |
| Melihat dan mengekspor riwayat aktivitas ke PDF (PT-10, PT-11, AD-14, AD-15, DR-16, DR-17) | Ya (cakupan masih terbuka, lihat Bagian 9) | Ya | Ya |

"Tidak tercantum" berarti dokumen sumber tidak memberikan kemampuan tersebut kepada aktor itu. Hal ini tidak secara eksplisit melarangnya.

**Peran akun:** Tabel `USERS` menyimpan nilai role `petugas`, `admin`, dan `direksi`. Akun memiliki penanda `is_active`. Akun yang tidak aktif tidak dapat masuk (UC-U1).

---

## 3. Fitur dan Persyaratan Fungsional

Semua 49 use case dari dokumen sumber tercantum di bawah ini, dikelompokkan per aktor. Setiap baris memuat deskripsi dan relasi dari sumber. Kolom aturan merujuk ke daftar bernomor di Bagian 5.

Hal yang tidak dispesifikasikan untuk fitur mana pun dalam dokumen sumber: field input dan output, alur kerja langkah demi langkah yang detail, dan kriteria penerimaan. Satu-satunya poin penerimaan adalah pernyataan eksplisit pada kolom "Aturan dan poin penerimaan". Jika sumber tidak menyebutkan, sel tersebut menyatakannya. Tabel basis data terkait dipetakan berdasarkan nama dari ERD; pemetaan ini tidak dinyatakan langsung oleh dokumen sumber.

### 3.1 Fitur umum (semua aktor)

| ID | Use case | Deskripsi (sumber) | Relasi | Aturan dan poin penerimaan | Tabel DB terkait |
|---|---|---|---|---|---|
| UC-U1 | Login | Masuk menggunakan username atau email dan password. | Tidak ada | BR-01: Akun nonaktif tidak dapat masuk. | USERS |
| UC-U2 | Logout | Keluar dari sistem dan mengakhiri sesi. | Tidak ada | Tidak dispesifikasikan selain deskripsi. | USERS |
| UC-U3 | Mengubah Password | Mengganti password akun yang sedang digunakan. | Tidak ada | Tidak dispesifikasikan (misalnya pengecekan password lama atau aturan password). | USERS |
| UC-U4 | Melihat Dashboard | Melihat ringkasan sesuai role yang login. | Tidak ada | Isi dashboard per role: tidak dispesifikasikan. | Tidak dispesifikasikan |
| UC-U5 | Melihat dan Mengubah Profil | Melihat data profil dan mengubah data diri, lalu menyimpan perubahan. | Tidak ada | Field yang dapat diubah: tidak dispesifikasikan. | USERS |

### 3.2 Use case Petugas

| ID | Use case | Deskripsi (sumber) | Relasi | Aturan dan poin penerimaan | Tabel DB terkait |
|---|---|---|---|---|---|
| PT-01 | Mencatat Opnam Pekerjaan | Membuat transaksi opnam baru melalui langkah-langkah: informasi kapal, detail pekerjaan, estimasi biaya, dan tinjauan akhir. | Menyertakan PT-02 | BR-11, BR-14. Langkah-langkahnya ada di Bagian 4. | OPNAMS, OPNAM_ITEMS, SHIPS, TARIFFS, STATUS_HISTORIES |
| PT-02 | Memilih Data Kapal | Memilih kapal yang akan diopnam sebagai langkah awal pencatatan. | Disertakan oleh PT-01 | BR-11: Pemilihan kapal selalu wajib sebelum transaksi dibuat. | SHIPS, OWNERS |
| PT-03 | Menambahkan Detail Pekerjaan | Menambahkan satu atau beberapa item pekerjaan beserta material, volume, dan keterangan. | Memperluas PT-01 (opsional) | BR-12: Penambahan detail bersifat opsional. | OPNAM_ITEMS, TARIFFS, MATERIAL_SPECS, WORK_TYPES, UNITS |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Mengunggah foto atau dokumen bukti pekerjaan, dengan validasi format dan ukuran file. | Memperluas PT-03 (opsional) | BR-13: Unggah bersifat opsional. BR-26: Validasi format dan ukuran wajib. Batas format dan ukuran yang diizinkan: tidak dispesifikasikan. | OPNAM_ATTACHMENTS |
| PT-05 | Melihat Perhitungan Biaya | Melihat estimasi biaya yang dihitung otomatis berdasarkan tarif yang berlaku. | Tidak ada | BR-14. | OPNAM_ITEMS, TARIFFS |
| PT-06 | Menyimpan Draft Opnam | Menyimpan opnam yang belum diajukan agar dapat dilanjutkan nanti. | Tidak ada | Nilai status: `draft`. Apakah tombol Simpan Draft tersedia di proses pencatatan masih menjadi pertanyaan terbuka (Bagian 9). | OPNAMS |
| PT-07 | Mengajukan Opnam untuk Review | Mengirim opnam ke Admin untuk diperiksa. Status berubah menjadi Menunggu Review. | Tidak ada | BR-15: Status berubah menjadi `menunggu_review`. | OPNAMS, STATUS_HISTORIES |
| PT-08 | Melihat Status Pengajuan | Melihat status setiap opnam beserta riwayat prosesnya. | Tidak ada | Tidak dispesifikasikan selain deskripsi. | OPNAMS, STATUS_HISTORIES |
| PT-09 | Memperbaiki Opnam yang Dikembalikan | Memperbaiki kesalahan input pada opnam berstatus Perlu Revisi, lalu mengajukannya kembali. Berbeda dari revisi oleh Admin dan Direksi. | Tidak ada | BR-21: Hanya opnam berstatus `perlu_revisi` yang dapat diperbaiki di sini. Transisi status saat pengajuan ulang tersirat tetapi tidak dinyatakan secara eksplisit (Bagian 9). | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS, STATUS_HISTORIES |
| PT-10 | Melihat Riwayat Aktivitas | Melihat catatan aktivitas dan perubahan data di sistem. | Tidak ada | Cakupan untuk Petugas (aktivitas sendiri atau seluruhnya): pertanyaan terbuka (Bagian 9). | ACTIVITY_LOGS |
| PT-11 | Mengekspor Riwayat Aktivitas ke PDF | Mengunduh riwayat aktivitas dalam format PDF. | Tidak ada | Pertanyaan cakupan yang sama dengan PT-10. | ACTIVITY_LOGS |

### 3.3 Use case Admin

| ID | Use case | Deskripsi (sumber) | Relasi | Aturan dan poin penerimaan | Tabel DB terkait |
|---|---|---|---|---|---|
| AD-01 | Mengelola Pengguna | Menambah akun, mengubah data, menentukan role, dan menonaktifkan akun. | Tidak ada | Aturan penonaktifan: BR-01 (akun nonaktif tidak dapat masuk). | USERS |
| AD-02 | Mengelola Data Owner dan Kapal | Menambah, mengubah, dan menghapus data pemilik dan kapal. | Tidak ada | Perilaku hapus (soft atau hard): tidak dispesifikasikan; ERD memiliki `deleted_at` pada OWNERS dan SHIPS. | OWNERS, SHIPS |
| AD-03 | Mengelola Kategori Pekerjaan | Menambah, mengubah, dan menghapus kategori pekerjaan. | Tidak ada | Perilaku hapus: tidak dispesifikasikan; `deleted_at` ada pada WORK_CATEGORIES. | WORK_CATEGORIES |
| AD-04 | Mengelola Jenis Pekerjaan, Material/Spesifikasi, Satuan, dan Tarif | Mengelola jenis pekerjaan, material, satuan, dan tarif dasar. | Tidak ada | BR-06: Admin mengelola tarif dasar. BR-10: Perubahan tarif tidak mengubah transaksi yang sudah tercatat. | WORK_TYPES, MATERIAL_SPECS, UNITS, TARIFFS |
| AD-05 | Melihat Daftar Opnam | Melihat seluruh opnam, dilengkapi pencarian, filter, dan paginasi. | Tidak ada | BR-27. | OPNAMS, SHIPS |
| AD-06 | Memeriksa Opnam | Memeriksa detail pekerjaan dan estimasi biaya sebelum memberi keputusan. | Disertakan oleh AD-08 dan AD-09; diperluas oleh AD-07 | BR-06, BR-16, BR-17. | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS |
| AD-07 | Merevisi Data Opnam | Mengubah data opnam secara langsung saat pemeriksaan, jika diperlukan. | Memperluas AD-06 (bersyarat) | BR-19. | OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS |
| AD-08 | Menyetujui Hasil Review | Menyetujui hasil pemeriksaan dan meneruskan opnam ke Direksi. | Menyertakan AD-06 | BR-16, BR-22: Status menjadi `menunggu_approval_direksi`, menggantikan `diteruskan_ke_direksi` pada ERD revisi (`forwarded_at` diisi). | OPNAMS, STATUS_HISTORIES |
| AD-09 | Mengembalikan Opnam untuk Revisi | Mengembalikan opnam ke Petugas dengan status Perlu Revisi, disertai catatan. | Menyertakan AD-06 | BR-17, BR-18: Catatan wajib diisi. | OPNAMS, STATUS_HISTORIES |
| AD-10 | Melihat Riwayat Revisi | Melihat riwayat perubahan pada opnam dan invoice. | Tidak ada | Tidak dispesifikasikan selain deskripsi. Apakah ini fitur yang sama dengan Riwayat Perubahan: pertanyaan terbuka (Bagian 9). | STATUS_HISTORIES, INVOICES, ACTIVITY_LOGS (pemetaan belum dikonfirmasi sumber) |
| AD-11 | Melihat Riwayat Pekerjaan Kapal | Melihat riwayat pekerjaan yang pernah dilakukan pada suatu kapal. | Tidak ada | Hubungan dengan DR-15: pertanyaan terbuka (Bagian 9). | SHIPS, OPNAMS, OPNAM_ITEMS |
| AD-12 | Merevisi Invoice | Mengubah data invoice. | Tidak ada | Waktu revisi (sebelum atau sesudah terbit): pertanyaan terbuka (Bagian 9). | INVOICES |
| AD-13 | Mengekspor Invoice ke PDF | Mengunduh invoice dalam format PDF. Invoice menampilkan harga. | Tidak ada | BR-09: Invoice menampilkan harga. | INVOICES |
| AD-14 | Melihat Riwayat Aktivitas | Melihat catatan aktivitas dan perubahan data di sistem. | Tidak ada | Penggabungan dengan Riwayat Perubahan dan Audit Log: pertanyaan terbuka (Bagian 9). | ACTIVITY_LOGS |
| AD-15 | Mengekspor Riwayat Aktivitas ke PDF | Mengunduh riwayat aktivitas dalam format PDF. | Tidak ada | Sama dengan AD-14. | ACTIVITY_LOGS |
| AD-16 | Melihat dan Mengekspor Laporan Rekapitulasi | Melihat laporan rekapitulasi per periode dan mengekspornya ke PDF atau Excel. | Tidak ada | Isi dan kolom laporan: tidak dispesifikasikan. Dipisahkan dari Riwayat Aktivitas (Bagian 10 sumber). | Tidak dispesifikasikan (tidak ada tabel laporan di ERD) |

### 3.4 Use case Direksi

| ID | Use case | Deskripsi (sumber) | Relasi | Aturan dan poin penerimaan | Tabel DB terkait |
|---|---|---|---|---|---|
| DR-01 | Melihat Draft Menunggu Approval | Melihat daftar opnam atau tagihan yang menunggu keputusan Direksi. | Tidak ada | Apakah daftar ini mencakup opnam atau hanya tagihan: tidak dispesifikasikan secara rinci. | BILLINGS (status `menunggu_approval`) |
| DR-02 | Memeriksa Draft Tagihan | Memeriksa rincian pekerjaan dan total tagihan sebelum memberi keputusan. | Disertakan oleh DR-06 dan DR-07; diperluas oleh DR-03 dan DR-04 | BR-22. | BILLINGS, BILLING_ADJUSTMENTS, OPNAM_ITEMS |
| DR-03 | Merevisi Data Opnam | Mengubah data opnam langsung di draft saat pemeriksaan, jika diperlukan. | Memperluas DR-02 (opsional) | BR-20. Apakah ini berlaku untuk Direksi juga masih terbuka (Bagian 9). | OPNAMS, OPNAM_ITEMS |
| DR-04 | Memberikan Diskon atau Markup | Memberi penyesuaian harga berupa diskon atau markup, wajib disertai catatan disposisi. | Menyertakan DR-05; memperluas DR-02 (opsional) | BR-07, BR-08, BR-24. | BILLING_ADJUSTMENTS, BILLINGS |
| DR-05 | Mencatat Disposisi | Mengisi alasan atau catatan atas penyesuaian harga yang diberikan. | Disertakan oleh DR-04 | BR-08. | BILLING_ADJUSTMENTS (`disposition_note`) |
| DR-06 | Menyetujui Tagihan | Memberikan persetujuan akhir atas tagihan. | Menyertakan DR-02; diperluas oleh DR-08 dan DR-09 | BR-07, BR-22, BR-23. | BILLINGS (status `disetujui`, `decided_by`, `decided_at`) |
| DR-07 | Menolak Tagihan | Menolak tagihan disertai alasan penolakan. | Menyertakan DR-02 | BR-22. Tempat penyimpanan alasan penolakan: tidak dispesifikasikan (Bagian 9). Apakah tagihan yang ditolak dapat direvisi: pertanyaan terbuka. | BILLINGS (status `ditolak`, `decided_by`, `decided_at`) |
| DR-08 | Menerbitkan Invoice PDF | Menerbitkan invoice setelah tagihan disetujui. Invoice menampilkan harga. | Memperluas DR-06 | BR-09, BR-23. | INVOICES |
| DR-09 | Menerbitkan Technical Report PDF | Menerbitkan technical report setelah tagihan disetujui. Technical report tidak menampilkan harga. | Memperluas DR-06 | BR-09, BR-23. | TECHNICAL_REPORTS |
| DR-10 | Merevisi Invoice | Mengubah data invoice. | Tidak ada | Waktu dan apakah Direksi boleh merevisi: pertanyaan terbuka (Bagian 9). | INVOICES |
| DR-11 | Mengekspor Invoice ke PDF | Mengunduh invoice dalam format PDF. | Tidak ada | Perbedaan dengan DR-08: pertanyaan terbuka (Bagian 9). | INVOICES |
| DR-12 | Mengunduh atau Mencetak Dokumen | Mengunduh atau mencetak dokumen yang sudah terbit. | Tidak ada | Tidak dispesifikasikan selain deskripsi. | INVOICES, TECHNICAL_REPORTS |
| DR-13 | Melihat Rekap Tagihan | Melihat rekap tagihan yang sudah diproses. | Tidak ada | Tidak dispesifikasikan selain deskripsi. | BILLINGS |
| DR-14 | Mengekspor Laporan PDF/Excel | Mengekspor laporan ke format PDF atau Excel. | Tidak ada | Isi laporan: tidak dispesifikasikan. Tidak tampil di Usecase.pdf (Bagian 9). | Tidak dispesifikasikan |
| DR-15 | Mencari dan Melihat Riwayat Perbaikan Kapal | Mencari riwayat perbaikan berdasarkan nama atau nomor lambung kapal, melalui halaman Transaksi Disetujui. | Tidak ada | BR-25. Apakah ini fitur yang sama dengan AD-11: pertanyaan terbuka (Bagian 9). | SHIPS, OPNAMS, OPNAM_ITEMS |
| DR-16 | Melihat Riwayat Aktivitas | Melihat catatan aktivitas dan perubahan data, termasuk perubahan harga dan diskon. | Tidak ada | Wajib mencakup perubahan harga dan diskon (eksplisit dalam sumber). | ACTIVITY_LOGS |
| DR-17 | Mengekspor Riwayat Aktivitas ke PDF | Mengunduh riwayat aktivitas dalam format PDF. | Tidak ada | Sama dengan DR-16. | ACTIVITY_LOGS |

### 3.5 Fitur pendukung (bukan use case tersendiri)

| Fitur | Berlaku di | Deskripsi (sumber) | Tabel DB terkait |
|---|---|---|---|
| Pencarian, filter status dan tanggal, paginasi | AD-05, DR-15, dan daftar transaksi lain | Pencarian, filter status dan tanggal, serta paginasi pada daftar transaksi. | OPNAMS, BILLINGS (pemetaan berdasarkan nama) |
| Validasi file | PT-04 | Validasi format dan ukuran file. Aturan spesifik: tidak dispesifikasikan. | OPNAM_ATTACHMENTS |
| Estimasi biaya otomatis | PT-01 | Pengisian otomatis estimasi biaya berdasarkan tarif. | OPNAM_ITEMS, TARIFFS |

---

## 4. Alur Kerja Sistem

Bagian ini memakai relasi include dan extend dari dokumen sumber. **Include** berarti use case yang disertakan selalu dijalankan. **Extend** berarti use case tambahan hanya dijalankan dalam kondisi tertentu. Urutan langkah diambil dari deskripsi sumber. Jika sumber tidak menyebutkan suatu langkah, hal itu dicatat.

### 4.1 Mencatat opnam (Petugas)

1. Petugas masuk ke sistem (UC-U1).
2. Petugas memilih kapal (PT-02). Langkah ini selalu wajib (include dari PT-01).
3. Petugas mencatat opnam (PT-01), yang terdiri dari informasi kapal, detail pekerjaan, estimasi biaya, dan tinjauan akhir.
4. Petugas dapat menambahkan satu atau beberapa item pekerjaan beserta material, volume, dan keterangan (PT-03, extend opsional dari PT-01).
5. Petugas dapat mengunggah foto atau dokumen sebagai bukti (PT-04, extend opsional dari PT-03). Format dan ukuran file divalidasi.
6. Estimasi biaya dihitung otomatis dari tarif yang berlaku (PT-05).
7. Petugas menyimpan opnam sebagai draft (PT-06, status `draft`) atau mengajukannya (PT-07).
8. Saat diajukan, status menjadi Menunggu Review (`menunggu_review`) dan opnam diteruskan ke Admin.
9. Petugas dapat memantau status dan riwayat proses (PT-08).

### 4.2 Memeriksa opnam (Admin)

1. Admin memeriksa rincian pekerjaan dan estimasi biaya (AD-06). Langkah ini selalu dijalankan sebelum keputusan, karena AD-08 dan AD-09 menyertakan AD-06.
2. Jika diperlukan, Admin mengubah data opnam secara langsung saat pemeriksaan (AD-07, extend bersyarat dari AD-06).
3. Admin mengambil salah satu dari dua keputusan:
   - a. Menyetujui hasil review dan meneruskan opnam ke Direksi (AD-08). Status menjadi `menunggu_approval_direksi`. Jalur alternatif: tidak ada yang dispesifikasikan.
   - b. Mengembalikan opnam ke Petugas dengan status Perlu Revisi dan catatan (AD-09).
4. Jika dikembalikan, Petugas memperbaiki kesalahan input (PT-09) dan mengajukan ulang. Sumber menyebutnya sebagai pengajuan ulang, tetapi tidak menyatakan perubahan status yang tepat. Lihat Bagian 9.

### 4.3 Pemeriksaan dan keputusan tagihan (Direksi)

1. Direksi melihat draft yang menunggu keputusan (DR-01).
2. Direksi memeriksa rincian pekerjaan dan total tagihan (DR-02). Langkah ini selalu dijalankan sebelum keputusan, karena DR-06 dan DR-07 menyertakan DR-02.
3. Jika diperlukan, Direksi mengubah data opnam di draft (DR-03, extend bersyarat dari DR-02).
4. Jika diperlukan, Direksi memberikan diskon atau markup (DR-04, extend opsional dari DR-02). Catatan disposisi wajib diisi (DR-05, selalu disertakan oleh DR-04).
5. Direksi mengambil salah satu dari dua keputusan:
   - a. Menyetujui tagihan (DR-06). Status menjadi `disetujui`.
   - b. Menolak tagihan disertai alasan (DR-07). Status menjadi `ditolak`. Alur setelah penolakan: pertanyaan terbuka (Bagian 9).
6. Hanya setelah disetujui, Direksi menerbitkan invoice PDF (DR-08, extend dari DR-06) dan/atau technical report PDF (DR-09, extend dari DR-06). Invoice menampilkan harga. Technical report tidak.

**Celah dalam sumber:** Sumber tidak menjelaskan bagaimana record tagihan dibuat dari opnam berstatus `menunggu_approval_direksi`. Tabel `BILLINGS` memiliki foreign key `opnam_id`, sehingga relasinya ada di basis data. Langkah pembuatannya tidak dispesifikasikan (Bagian 9).

### 4.4 Revisi dan ekspor dokumen (Admin dan Direksi)

- Admin dapat merevisi invoice (AD-12) dan mengekspornya ke PDF (AD-13).
- Direksi dapat merevisi invoice (DR-10) dan mengekspornya ke PDF (DR-11). Direksi juga dapat mengunduh atau mencetak dokumen yang sudah terbit (DR-12).
- Apakah revisi dilakukan sebelum atau sesudah terbit adalah pertanyaan terbuka.
- Apakah DR-08 (Menerbitkan Invoice PDF) dan DR-11 (Mengekspor Invoice ke PDF) adalah aksi yang sama adalah pertanyaan terbuka.

### 4.5 Riwayat aktivitas dan riwayat perbaikan

- Petugas dapat melihat dan mengekspor riwayat aktivitas (PT-10, PT-11).
- Admin dapat melihat riwayat aktivitas, mengekspornya ke PDF (AD-14, AD-15), melihat riwayat revisi (AD-10), dan melihat pekerjaan kapal di masa lalu (AD-11).
- Direksi dapat melihat riwayat aktivitas termasuk perubahan harga dan diskon, serta mengekspornya ke PDF (DR-16, DR-17).
- Direksi dapat mencari riwayat perbaikan berdasarkan nama kapal atau nomor lambung dari halaman Transaksi Disetujui (DR-15).

### 4.6 Nilai status opnam (dari desain basis data)

Sumber tidak menjelaskan diagram transisi yang lengkap. Transisi di atas hanya yang dinyatakan dalam deskripsi use case.

| Tabel dan kolom | Nilai |
|---|---|
| `OPNAMS.status` | `draft`, `menunggu_review`, `perlu_revisi`, `menunggu_approval_direksi`, `ditolak`, `disetujui`, `dokumen_terbit` |
| `BILLINGS.status` | `menunggu_approval`, `disetujui`, `ditolak` |

**Alur alternatif dan pengecualian:** Akun nonaktif tidak dapat masuk (UC-U1). Petugas tidak dapat menjalankan aksi Admin atau Direksi yang tercantum di Bagian 5. Alur pengecualian lainnya: tidak dispesifikasikan dalam dokumen sumber.

---

## 5. Aturan Bisnis

Setiap aturan di bawah ini dinyatakan secara eksplisit dalam dokumen sumber. Sumber mengaitkan setiap aturan dengan use case atau bagian yang tercantum. Tidak ada aturan yang ditambahkan. Celah dicantumkan di akhir bagian ini dan di Bagian 9.

| ID | Aturan bisnis | Sumber |
|---|---|---|
| BR-01 | Akun yang tidak aktif tidak dapat masuk. | UC-U1; AD-01 (penonaktifan) |
| BR-02 | Petugas tidak dapat mengubah tarif dasar. | Bagian 8; Bagian 2 |
| BR-03 | Petugas tidak dapat memberikan diskon atau markup. | Bagian 8 |
| BR-04 | Petugas tidak dapat memberikan persetujuan akhir atas tagihan. | Bagian 8 |
| BR-05 | Petugas tidak dapat mengekspor invoice. | Bagian 8 |
| BR-06 | Admin mengelola tarif dasar serta memeriksa dan merevisi opnam. | Bagian 8; AD-04, AD-06 |
| BR-07 | Direksi memberikan persetujuan akhir dan penyesuaian harga (diskon atau markup). | Bagian 8; DR-04, DR-06 |
| BR-08 | Setiap diskon atau markup wajib memiliki alasan atau catatan disposisi. | Bagian 8; DR-04, DR-05 |
| BR-09 | Invoice menampilkan harga. Technical report tidak menampilkan harga. | Bagian 8; AD-13, DR-08, DR-09 |
| BR-10 | Perubahan tarif tidak mengubah transaksi yang sudah tercatat. | Bagian 8 |
| BR-11 | Pemilihan kapal selalu wajib sebelum transaksi opnam dibuat. | Bagian 7 (PT-01 menyertakan PT-02) |
| BR-12 | Penambahan detail pekerjaan bersifat opsional. | Bagian 7 (PT-03 memperluas PT-01) |
| BR-13 | Unggah bukti bersifat opsional. | Bagian 7 (PT-04 memperluas PT-03) |
| BR-14 | Estimasi biaya dihitung otomatis dari tarif yang berlaku. | PT-05; Bagian 9 (estimasi biaya otomatis) |
| BR-15 | Pengajuan opnam mengubah status menjadi Menunggu Review. | PT-07 |
| BR-16 | Persetujuan hasil review selalu didahului oleh pemeriksaan. | Bagian 7 (AD-08 menyertakan AD-06) |
| BR-17 | Pengembalian opnam selalu didahului oleh pemeriksaan. | Bagian 7 (AD-09 menyertakan AD-06) |
| BR-18 | Pengembalian opnam wajib disertai catatan. | AD-09 |
| BR-19 | Admin mengubah data opnam hanya sesuai kebutuhan pemeriksaan. | AD-07; Bagian 7 |
| BR-20 | Direksi mengubah data opnam hanya jika diperlukan saat pemeriksaan tagihan. | DR-03; Bagian 7 (extend bersyarat) |
| BR-21 | Petugas hanya dapat memperbaiki opnam berstatus Perlu Revisi. | PT-09 |
| BR-22 | Keputusan tagihan (setujui atau tolak) selalu didahului oleh pemeriksaan draft tagihan. | Bagian 7 (DR-06 dan DR-07 menyertakan DR-02) |
| BR-23 | Invoice dan technical report hanya diterbitkan setelah tagihan disetujui. | Bagian 7 (DR-08 dan DR-09 memperluas DR-06) |
| BR-24 | Penyesuaian harga bersifat opsional. | Bagian 7 (DR-04 memperluas DR-02) |
| BR-25 | Riwayat perbaikan dicari berdasarkan nama kapal atau nomor lambung, melalui halaman Transaksi Disetujui. | DR-15 |
| BR-26 | Unggahan file divalidasi formatnya dan ukurannya. | PT-04; Bagian 9 |
| BR-27 | Daftar transaksi mendukung pencarian, filter status dan tanggal, serta paginasi. | Bagian 9 (fitur pendukung); AD-05 |

**Perubahan tarif dan transaksi:** BR-10 berarti tarif disalin ke setiap transaksi. Desain basis data menyimpan nilai snapshot di `OPNAM_ITEMS` (`unit_price` dan `subtotal`) dan di `OPNAMS` (`total_biaya`) serta di `INVOICES` dan `TECHNICAL_REPORTS` (detail kapal dan owner).

**Nilai status:** Nilai status opnam dan tagihan tercantum di Bagian 4.6.

### Aturan yang tidak dispesifikasikan dalam dokumen sumber

- Ukuran file maksimum dan jenis file yang diizinkan untuk unggahan.
- Apakah tarif memiliki tanggal berakhir. `TARIFFS` hanya menyimpan `effective_from`.
- Apakah tagihan yang ditolak dapat diperbaiki dan diajukan ulang.
- Tempat penyimpanan alasan penolakan tagihan.
- Aturan revisi invoice (sebelum atau sesudah terbit) dan apakah Direksi boleh merevisi opnam dan invoice.
- Aturan password dan batas waktu sesi.
- Aturan isi dan perhitungan laporan.

---

## 6. Spesifikasi Basis Data

Sumber: `Perancangan_database_Laravel_rev.pdf` (ERD revisi, 17 tabel, 24 relasi). Teks ERD merujuk ke `Database_Design_SIMSHIP.md`, Bagian 4, untuk detail kolom, constraint, dan asumsi. File tersebut tidak disediakan. Tabel di bawah ini mereproduksi ERD secara persis. Constraint yang tidak ditampilkan di ERD ditandai "Tidak dispesifikasikan".

**Catatan umum:** ERD tidak menampilkan kolom `created_at` atau `updated_at` pada sebagian besar tabel. Nullability hanya ditampilkan jika ERD menyebutkannya nullable. Panjang kolom string dan presisi kolom decimal: tidak dispesifikasikan.

### 6.1 Identitas dan data master

#### USERS

| Kolom | Tipe | Kunci dan constraint |
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

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| name | string | |
| address | text | nullable |
| deleted_at | datetime | Nullability: tidak dispesifikasikan |

#### SHIPS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| owner_id | bigint | FK ke OWNERS.id |
| name | string | |
| hull_number | string | UK |
| deleted_at | datetime | Nullability: tidak dispesifikasikan |

#### UNITS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| code | string | UK |
| name | string | |

#### WORK_CATEGORIES

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| name | string | UK |
| description | text | nullable |
| deleted_at | datetime | Nullability: tidak dispesifikasikan |

#### WORK_TYPES

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| work_category_id | bigint | FK ke WORK_CATEGORIES.id |
| name | string | |
| deleted_at | datetime | Nullability: tidak dispesifikasikan |

#### MATERIAL_SPECS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| work_type_id | bigint | FK ke WORK_TYPES.id |
| name | string | |
| deleted_at | datetime | Nullability: tidak dispesifikasikan |

#### TARIFFS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| material_spec_id | bigint | FK ke MATERIAL_SPECS.id |
| unit_id | bigint | FK ke UNITS.id |
| created_by | bigint | FK ke USERS.id |
| price | decimal | |
| effective_from | date | |
| created_at | datetime | |

### 6.2 Opnam

#### OPNAMS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| opnam_number | string | UK |
| ship_id | bigint | FK ke SHIPS.id |
| created_by | bigint | FK ke USERS.id |
| status | string | Nilai: `draft`, `menunggu_review`, `perlu_revisi`, `menunggu_approval_direksi`, `ditolak`, `disetujui`, `dokumen_terbit` |
| submitted_at | datetime | nullable |
| forwarded_at | datetime | nullable |
| tanggal_opnam | date | - |
| catatan | text | nullable |
| total_biaya | decimal | Snapshot |
| created_at | datetime | - |
| updated_at | datetime | - |

#### OPNAM_ITEMS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| opnam_id | bigint | FK ke OPNAMS.id |
| tariff_id | bigint | FK ke TARIFFS.id |
| volume | decimal | |
| unit_price | decimal | Snapshot: nilai dibekukan saat transaksi terjadi |
| subtotal | decimal | Snapshot: nilai dibekukan saat transaksi terjadi |
| description | text | nullable |
| sort_order | int | |
| lokasi | string | nullable |
| created_at | datetime | - |
| updated_at | datetime | - |

#### OPNAM_ATTACHMENTS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| opnam_item_id | bigint | FK ke OPNAM_ITEMS.id |
| uploaded_by | bigint | FK ke USERS.id |
| file_path | string | |
| original_name | string | |
| mime_type | string | |
| file_size | int | Satuan: byte |
| keterangan | text | nullable |
| created_at | datetime | - |
| updated_at | datetime | - |

#### STATUS_HISTORIES

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| statusable_type | string | Polimorfik |
| statusable_id | bigint | Polimorfik |
| from_status | string | nullable |
| to_status | string | |
| note | text | nullable |
| changed_by | bigint | FK ke USERS.id; nullable |
| created_at | datetime | |

### 6.3 Tagihan, invoice, dan laporan

#### BILLINGS

| Kolom | Tipe | Kunci dan constraint |
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

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| billing_id | bigint | FK ke BILLINGS.id |
| created_by | bigint | FK ke USERS.id |
| adjustment_type | string | Nilai: `diskon`, `markup` |
| adjustment_value | decimal | |
| adjustment_amount | decimal | |
| disposition_note | text | |

#### INVOICES

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| billing_id | bigint | FK ke BILLINGS.id |
| issued_by | bigint | FK ke USERS.id |
| invoice_number | string | UK |
| issue_date | date | |
| ship_name | string | Snapshot |
| hull_number | string | Snapshot |
| owner_name | string | Snapshot |
| owner_address | text | Snapshot; nullable |
| subtotal_amount | decimal | Snapshot |
| adjustment_amount | decimal | Snapshot |
| total_amount | decimal | Snapshot |
| revision_count | int | |

#### TECHNICAL_REPORTS

| Kolom | Tipe | Kunci dan constraint |
|---|---|---|
| id | bigint | PK |
| billing_id | bigint | FK ke BILLINGS.id |
| issued_by | bigint | FK ke USERS.id |
| report_number | string | UK |
| issue_date | date | |
| ship_name | string | Snapshot |
| hull_number | string | Snapshot |
| owner_name | string | Snapshot |
| owner_address | text | Snapshot; nullable |

### 6.4 Log aktivitas

#### ACTIVITY_LOGS

| Kolom | Tipe | Kunci dan constraint |
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

### 6.5 Relasi (foreign key sesuai ERD)

| Tabel.kolom anak | Induk | Relasi (dari label ERD dan FK) |
|---|---|---|
| SHIPS.owner_id | OWNERS.id | Satu owner memiliki banyak kapal |
| WORK_TYPES.work_category_id | WORK_CATEGORIES.id | Satu kategori memiliki banyak jenis pekerjaan |
| MATERIAL_SPECS.work_type_id | WORK_TYPES.id | Satu jenis pekerjaan memiliki banyak spesifikasi material |
| TARIFFS.material_spec_id | MATERIAL_SPECS.id | Satu spesifikasi material memiliki banyak tarif |
| TARIFFS.unit_id | UNITS.id | Satu satuan dipakai banyak tarif |
| TARIFFS.created_by | USERS.id | Satu user membuat banyak tarif |
| OPNAMS.ship_id | SHIPS.id | Satu kapal memiliki banyak opnam |
| OPNAMS.created_by | USERS.id | Satu user membuat banyak opnam |
| OPNAM_ITEMS.opnam_id | OPNAMS.id | Satu opnam memiliki banyak item |
| OPNAM_ITEMS.tariff_id | TARIFFS.id | Satu tarif dipakai banyak item |
| OPNAM_ATTACHMENTS.opnam_item_id | OPNAM_ITEMS.id | Satu item memiliki banyak lampiran |
| OPNAM_ATTACHMENTS.uploaded_by | USERS.id | Satu user mengunggah banyak lampiran |
| STATUS_HISTORIES.changed_by | USERS.id | Nullable |
| STATUS_HISTORIES.statusable_type dan statusable_id | Polimorfik | Riwayat status untuk record statusable apa pun |
| BILLINGS.opnam_id | OPNAMS.id | Satu opnam memiliki tagihan |
| BILLINGS.decided_by | USERS.id | Nullable |
| BILLING_ADJUSTMENTS.billing_id | BILLINGS.id | Satu tagihan memiliki banyak penyesuaian |
| BILLING_ADJUSTMENTS.created_by | USERS.id | |
| INVOICES.billing_id | BILLINGS.id | |
| INVOICES.issued_by | USERS.id | |
| TECHNICAL_REPORTS.billing_id | BILLINGS.id | |
| TECHNICAL_REPORTS.issued_by | USERS.id | |
| ACTIVITY_LOGS.user_id | USERS.id | Nullable |
| ACTIVITY_LOGS.subject_type dan subject_id | Polimorfik | Aktivitas pada record subjek apa pun |

Kardinalitas relasi untuk setiap baris dibaca dari arah foreign key. Diagram ERD menampilkan label satu-ke-banyak dan satu-ke-nol-atau-satu; simbol pasti per relasi tidak dikonfirmasi dari hasil ekstraksi teks. Kardinalitas karena itu ditandai sebagai turunan.

### 6.6 Elemen basis data yang tidak dispesifikasikan dalam dokumen sumber

- Timestamp (`created_at`, `updated_at`) pada tabel selain TARIFFS, OPNAMS, OPNAM_ITEMS, OPNAM_ATTACHMENTS, STATUS_HISTORIES, dan ACTIVITY_LOGS.
- Presisi dan skala kolom decimal; panjang kolom string dan text.
- Index, nilai default, dan aturan on-delete atau on-update.
- Apakah soft delete (`deleted_at`) berlaku untuk tabel yang tidak memilikinya, dan tabel mana yang memakai hard delete.
- Tempat penyimpanan alasan penolakan tagihan.
- Tempat penyimpanan riwayat revisi invoice di luar `revision_count`.
- Daftar lengkap nilai role, dan apakah status ditegakkan oleh basis data atau kode aplikasi.

**Catatan desain basis data:** ERD mencantumkan kolom snapshot (`unit_price`, `subtotal` di OPNAM_ITEMS; `total_biaya` di OPNAMS; field kapal, hull, owner, dan nominal di INVOICES dan TECHNICAL_REPORTS). Kolom ini menyimpan nilai yang dibekukan saat transaksi terjadi, sesuai legenda ERD.

---

## 7. Persyaratan Sistem

Bagian ini hanya memuat persyaratan teknis dan sistem yang muncul dalam dokumen sumber. Semua hal lain ditandai "Tidak dispesifikasikan".

### 7.1 Dinyatakan dalam dokumen sumber

| Persyaratan | Sumber | Detail |
|---|---|---|
| Login dengan username atau email dan password | UC-U1 | Akun diidentifikasi melalui username atau email. |
| Akun nonaktif diblokir dari login | UC-U1, AD-01 | Penanda `is_active` pada USERS. |
| Output PDF | PT-11, AD-13, AD-15, DR-11, DR-12, DR-08, DR-09, DR-17 | Riwayat aktivitas, invoice, technical report, dan dokumen diekspor atau diterbitkan dalam format PDF. |
| Output PDF dan Excel untuk laporan | AD-16, DR-14 | Laporan rekap diekspor ke PDF atau Excel. |
| Pencetakan | DR-12 | Dokumen yang sudah terbit dapat diunduh atau dicetak. |
| Unggah file dengan validasi format dan ukuran | PT-04 | Foto dan dokumen sebagai bukti. Aturan spesifiknya tidak dispesifikasikan. |
| Pencarian, filter, dan paginasi | AD-05, DR-15, Bagian 9 sumber | Berlaku pada daftar transaksi. |
| Perhitungan biaya otomatis dari tarif | PT-05, Bagian 9 sumber | Dihitung otomatis dari tarif yang berlaku. |
| Kolom soft delete | ERD | `deleted_at` ada pada OWNERS, SHIPS, WORK_CATEGORIES, WORK_TYPES, dan MATERIAL_SPECS. Cara penghapusan tidak dispesifikasikan. |
| Nilai snapshot pada transaksi | ERD | OPNAMS, OPNAM_ITEMS, INVOICES, dan TECHNICAL_REPORTS menyimpan nilai yang dibekukan. |
| IP klien pada catatan aktivitas | ERD | `ACTIVITY_LOGS.ip_address` (nullable). |
| Nilai lama dan baru pada catatan aktivitas | ERD | `ACTIVITY_LOGS.old_values` dan `new_values` bertipe json (nullable). |
| Nomor dokumen unik | ERD | `opnam_number`, `billing_number` (nullable), `invoice_number`, dan `report_number` bersifat unik. Cara pembuatan nomor tidak dispesifikasikan. |
| Referensi desain basis data | ERD | ERD merujuk ke `Database_Design_SIMSHIP.md`, Bagian 4. File tersebut tidak disediakan. |

### 7.2 Tidak dispesifikasikan dalam dokumen sumber

- Bahasa pemrograman, framework, dan versinya.
- Mesin basis data.
- Hosting, deployment, dan lingkungan.
- Browser dan perangkat yang didukung.
- Persyaratan performa, kapasitas, dan waktu respons.
- Persyaratan keamanan selain login dan ubah password, termasuk kebijakan password, batas waktu sesi, dan aturan pencatatan akses.
- Backup dan pemulihan.
- Ukuran unggah maksimum, jenis file yang diizinkan, dan lokasi penyimpanan unggahan.
- Aturan pembuatan laporan, penjadwalan, dan periodenya.
- Email atau notifikasi lain.
- Integrasi dengan sistem eksternal.
- Lokalisasi. Dokumen sumber ditulis dalam bahasa Indonesia.
- Aksesibilitas.

### 7.3 Konteks dari permintaan PRD ini

Permintaan PRD ini menyatakan bahwa PRD akan dipakai sebagai acuan pengembangan aplikasi Laravel dengan AI coding agent. Ini dicatat sebagai konteks saja. Dokumen desain sumber tidak menyatakan Laravel sebagai persyaratan. Tidak ada teknologi lain yang ditambahkan.

---

## 8. Ketertelusuran Persyaratan

Setiap use case di bawah ini dilacak ke sumbernya dan ke lokasi PRD tempat ia didokumentasikan. Referensi sumber: DOCX = `Daftar_Use_Case_SIMSHIP.docx`, nomor bagian. DIAG = `Usecase.pdf`, nomor halaman. ERD = `Design_Database_Project.pdf`.

### 8.1 Use case

| ID | Use case | Sumber | Lokasi PRD (tabel Bagian 3, alur, aturan, DB) |
|---|---|---|---|
| UC-U1 | Login | DOCX 3; DIAG 1, 2, 3 | 3.1; 4; BR-01; USERS |
| UC-U2 | Logout | DOCX 3; DIAG 1, 2, 3 | 3.1; USERS |
| UC-U3 | Mengubah Password | DOCX 3; DIAG 1, 2, 3 | 3.1; USERS |
| UC-U4 | Melihat Dashboard | DOCX 3; DIAG 1, 2, 3 | 3.1 |
| UC-U5 | Melihat dan Mengubah Profil | DOCX 3; DIAG 1, 2, 3 (Melihat Profil) | 3.1; USERS |
| PT-01 | Mencatat Opnam Pekerjaan | DOCX 4, 7, 8; DIAG 3 | 3.2; 4.1; BR-11, BR-14; OPNAMS, OPNAM_ITEMS |
| PT-02 | Memilih Data Kapal | DOCX 4, 7; DIAG 3 | 3.2; 4.1; BR-11; SHIPS, OWNERS |
| PT-03 | Menambahkan Detail Pekerjaan | DOCX 4, 7; DIAG 3 | 3.2; 4.1; BR-12; OPNAM_ITEMS |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | DOCX 4, 7, 9; DIAG 3 | 3.2; 4.1; BR-13, BR-26; OPNAM_ATTACHMENTS |
| PT-05 | Melihat Perhitungan Biaya | DOCX 4; DIAG 3 | 3.2; BR-14; OPNAM_ITEMS, TARIFFS |
| PT-06 | Menyimpan Draft Opnam | DOCX 4, 10; DIAG 3 | 3.2; 4.1; OPNAMS |
| PT-07 | Mengajukan Opnam untuk Review | DOCX 4; DIAG 3 | 3.2; 4.1; BR-15; OPNAMS |
| PT-08 | Melihat Status Pengajuan | DOCX 4; DIAG 3 | 3.2; OPNAMS, STATUS_HISTORIES |
| PT-09 | Memperbaiki Opnam yang Dikembalikan | DOCX 4; tidak ada di DIAG 3 | 3.2; 4.2; BR-21; OPNAMS |
| PT-10 | Melihat Riwayat Aktivitas | DOCX 4, 10; tidak ada di DIAG 3 | 3.2; ACTIVITY_LOGS |
| PT-11 | Mengekspor Riwayat Aktivitas ke PDF | DOCX 4; DIAG 3 (Mengekspor Rekap Aktivitas) | 3.2; ACTIVITY_LOGS |
| AD-01 | Mengelola Pengguna | DOCX 5; DIAG 2 | 3.3; BR-01; USERS |
| AD-02 | Mengelola Data Owner dan Kapal | DOCX 5; DIAG 2 | 3.3; OWNERS, SHIPS |
| AD-03 | Mengelola Kategori Pekerjaan | DOCX 5; DIAG 2 | 3.3; BR-06; WORK_CATEGORIES |
| AD-04 | Mengelola Jenis Pekerjaan, Material/Spesifikasi, Satuan, dan Tarif | DOCX 5; DIAG 2 | 3.3; BR-06, BR-10; WORK_TYPES, MATERIAL_SPECS, UNITS, TARIFFS |
| AD-05 | Melihat Daftar Opnam | DOCX 5, 9; DIAG 2 | 3.3; BR-27; OPNAMS |
| AD-06 | Memeriksa Opnam | DOCX 5, 7; DIAG 2 | 3.3; 4.2; BR-16, BR-17; OPNAMS, OPNAM_ITEMS |
| AD-07 | Merevisi Data Opnam | DOCX 5, 7; DIAG 2 | 3.3; 4.2; BR-19 |
| AD-08 | Menyetujui Hasil Review | DOCX 5, 7; DIAG 2 | 3.3; 4.2; BR-16, BR-22; OPNAMS |
| AD-09 | Mengembalikan Opnam untuk Revisi | DOCX 5, 7; DIAG 2 | 3.3; 4.2; BR-17, BR-18; OPNAMS |
| AD-10 | Melihat Riwayat Revisi | DOCX 5, 10; DIAG 2 | 3.3; STATUS_HISTORIES, INVOICES |
| AD-11 | Melihat Riwayat Pekerjaan Kapal | DOCX 5, 10; DIAG 2 | 3.3; SHIPS, OPNAMS, OPNAM_ITEMS |
| AD-12 | Merevisi Invoice | DOCX 5, 10; DIAG 2 | 3.3; 4.4; INVOICES |
| AD-13 | Mengekspor Invoice ke PDF | DOCX 5, 8; DIAG 2 | 3.3; BR-09; INVOICES |
| AD-14 | Melihat Riwayat Aktivitas | DOCX 5, 10; DIAG 2 (Melihat Audit Log) | 3.3; ACTIVITY_LOGS |
| AD-15 | Mengekspor Riwayat Aktivitas ke PDF | DOCX 5; DIAG 2 (Mengekspor Rekap Aktivitas) | 3.3; ACTIVITY_LOGS |
| AD-16 | Melihat dan Mengekspor Laporan Rekapitulasi | DOCX 5, 10; tidak ada di DIAG 2 | 3.3; 7.1 |
| DR-01 | Melihat Draft Menunggu Approval | DOCX 6; DIAG 1 | 3.4; BILLINGS |
| DR-02 | Memeriksa Draft Tagihan | DOCX 6, 7; DIAG 1 | 3.4; 4.3; BR-22; BILLINGS |
| DR-03 | Merevisi Data Opnam | DOCX 6, 7, 10; tidak ada di DIAG 1 | 3.4; 4.3; BR-20; OPNAMS |
| DR-04 | Memberikan Diskon atau Markup | DOCX 6, 7, 8; DIAG 1 | 3.4; 4.3; BR-07, BR-08, BR-24; BILLING_ADJUSTMENTS |
| DR-05 | Mencatat Disposisi | DOCX 6, 7; DIAG 1 | 3.4; BR-08; BILLING_ADJUSTMENTS |
| DR-06 | Menyetujui Tagihan | DOCX 6, 7; DIAG 1 | 3.4; 4.3; BR-07, BR-22, BR-23; BILLINGS |
| DR-07 | Menolak Tagihan | DOCX 6, 7, 10; DIAG 1 | 3.4; 4.3; BR-22; BILLINGS |
| DR-08 | Menerbitkan Invoice PDF | DOCX 6, 7, 8; DIAG 1 | 3.4; 4.3; BR-09, BR-23; INVOICES |
| DR-09 | Menerbitkan Technical Report PDF | DOCX 6, 7, 8; DIAG 1 | 3.4; 4.3; BR-09, BR-23; TECHNICAL_REPORTS |
| DR-10 | Merevisi Invoice | DOCX 6, 10; DIAG 1 | 3.4; 4.4; INVOICES |
| DR-11 | Mengekspor Invoice ke PDF | DOCX 6, 10; DIAG 1 | 3.4; 4.4; INVOICES |
| DR-12 | Mengunduh atau Mencetak Dokumen | DOCX 6; DIAG 1 | 3.4; INVOICES, TECHNICAL_REPORTS |
| DR-13 | Melihat Rekap Tagihan | DOCX 6; DIAG 1 | 3.4; BILLINGS |
| DR-14 | Mengekspor Laporan PDF/Excel | DOCX 6, 10; tidak ada di DIAG 1 | 3.4; 7.1 |
| DR-15 | Mencari dan Melihat Riwayat Perbaikan Kapal | DOCX 6, 9, 10; DIAG 1 | 3.4; 4.5; BR-25; SHIPS, OPNAMS |
| DR-16 | Melihat Riwayat Aktivitas | DOCX 6, 10; DIAG 1 (Melihat Riwayat Perubahan, Melihat Audit Log) | 3.4; ACTIVITY_LOGS |
| DR-17 | Mengekspor Riwayat Aktivitas ke PDF | DOCX 6; DIAG 1 (Mengekspor Rekap Aktivitas) | 3.4; ACTIVITY_LOGS |

### 8.2 Item sumber lainnya

| Item sumber | Sumber | Lokasi PRD |
|---|---|---|
| Ringkasan, tujuan, dan lingkup dokumen use case | DOCX 1 | Bagian 1 |
| Aktor dan tanggung jawab (Petugas, Admin, Direksi) | DOCX 2; DIAG 1, 2, 3 | Bagian 2 |
| Relasi include dan extend (Bagian 7 sumber) | DOCX 7 | Bagian 4; Bagian 5 (BR-11 sampai BR-24) |
| Aturan bisnis (Bagian 8 sumber) | DOCX 8 | Bagian 5 (BR-01 sampai BR-10) |
| Fitur pendukung (Bagian 9 sumber) | DOCX 9 | Bagian 3.5; BR-26, BR-27 |
| Pertanyaan terbuka (Bagian 10 sumber) | DOCX 10 | Bagian 9 |
| Basis data: 17 tabel, 24 relasi, relasi snapshot dan polimorfik | ERD | Bagian 6 (6.1 sampai 6.6) |
| Mockup antarmuka (operator, supervisor, direksi) | UI_Project_FPW.pdf | Tidak dipakai sebagai sumber persyaratan. Dicatat di Bagian 9. |

### 8.3 Pemeriksaan cakupan

- Use case dalam sumber: 49 (UC-U1 sampai UC-U5, PT-01 sampai PT-11, AD-01 sampai AD-16, DR-01 sampai DR-17). Semua 49 muncul di Bagian 3 dan di 8.1 di atas.
- Tabel basis data di ERD: 17. Semua 17 muncul di Bagian 6.
- Aktor: 3. Semua 3 muncul di Bagian 2.

---

## 9. Informasi yang Belum Lengkap

Daftar ini hanya memuat item yang tidak jelas, belum didefinisikan, atau bertentangan dalam dokumen sumber. Item 1 sampai 9 adalah pertanyaan terbuka dari Bagian 10 dokumen use case sumber. Item 10 dan seterusnya ditemukan selama analisis PRD ini. Tidak ada yang di sini yang sudah diselesaikan atau dijadikan persyaratan.

### 9.1 Pertanyaan terbuka dari dokumen sumber (Bagian 10)

| No. | Pertanyaan | Dampak pada PRD ini |
|---|---|---|
| 1 | Riwayat Perubahan dan Audit Log digabung menjadi Riwayat Aktivitas di dokumen sumber, tetapi diagram menampilkannya terpisah. | AD-10, AD-14, DR-16 |
| 2 | Cakupan Riwayat Aktivitas untuk Petugas: hanya aktivitas sendiri, atau seluruh aktivitas. | PT-10, PT-11 |
| 3 | Apakah Direksi boleh merevisi data opnam dan invoice. Sumber tidak mencantumkannya untuk Direksi. | DR-03, DR-10 |
| 4 | Apakah Menerbitkan Invoice PDF dan Mengekspor Invoice ke PDF berbeda untuk Direksi. Jika sama, salah satunya dihapus. | DR-08, DR-11 |
| 5 | Waktu revisi invoice: sebelum atau sesudah invoice terbit. | AD-12, DR-10 |
| 6 | Alur setelah tagihan ditolak: final, atau dapat diperbaiki lalu diajukan ulang. | DR-07 |
| 7 | Apakah Riwayat Pekerjaan Kapal (Admin) dan Mencari dan Melihat Riwayat Perbaikan Kapal (Direksi) adalah dua fitur atau satu. | AD-11, DR-15 |
| 8 | Apakah tombol Simpan Draft tersedia dalam proses pencatatan opnam. | PT-06 |
| 9 | Laporan Rekapitulasi tetap terpisah dari Riwayat Aktivitas (dinyatakan dalam sumber). Konfirmasi bahwa pemisahan ini final. | AD-16, DR-14, PT-11, AD-15, DR-17 |

### 9.2 Celah dan konflik tambahan yang ditemukan selama analisis

| No. | Item | Detail |
|---|---|---|
| 10 | Input, output, dan alur kerja rinci untuk setiap fitur | Tidak dispesifikasikan dalam dokumen sumber. Hanya deskripsi dan relasi yang tersedia. |
| 11 | Kriteria penerimaan untuk setiap fitur | Tidak dispesifikasikan dalam dokumen sumber. Hanya pernyataan eksplisit yang dicantumkan di Bagian 3. |
| 12 | File detail basis data | ERD merujuk ke `Database_Design_SIMSHIP.md`, Bagian 4, yang tidak disediakan. Constraint, detail kolom, dan asumsi di luar ERD tidak diketahui. |
| 13 | Panjang, presisi, dan nullability | Tidak dispesifikasikan untuk kolom string, text, dan decimal. Nullability tidak ditampilkan untuk beberapa kolom. |
| 14 | Tempat penyimpanan alasan penolakan | DR-07 mensyaratkan alasan. BILLINGS tidak memiliki kolom alasan di ERD. `STATUS_HISTORIES.note` mungkin kandidat, tetapi sumber tidak menyatakannya. |
| 15 | Cara tagihan dibuat dari opnam yang diteruskan | Sumber tidak menjelaskan langkah ini. `BILLINGS.opnam_id` ada. |
| 16 | Transisi status opnam saat diajukan ulang | PT-09 menyebut opnam diajukan kembali, tetapi status tujuan tidak dinyatakan secara eksplisit. |
| 17 | Tanggal berakhir tarif | TARIFFS hanya memiliki `effective_from`. Apakah perlu tanggal berakhir tidak dispesifikasikan. |
| 18 | Batas unggahan | Format yang diizinkan dan ukuran maksimum untuk PT-04 tidak dispesifikasikan. |
| 19 | Isi dashboard per role | Tidak dispesifikasikan untuk UC-U4. |
| 20 | Isi laporan dan format ekspor | Tidak dispesifikasikan untuk AD-16 dan DR-14. Laporan tidak memiliki tabel di ERD. |
| 21 | Perilaku hapus | AD-02 dan AD-03 menyebut hapus, tetapi sumber tidak menyatakan apakah penghapusan bersifat soft (`deleted_at`) atau hard. Kolom soft delete hanya ada pada sebagian tabel. |
| 22 | Nonaktifkan versus hapus untuk pengguna | AD-01 menyebut penonaktifan. Sumber tidak menyatakan apakah pengguna dapat dihapus. |
| 23 | Timestamp dan field audit | Tidak dispesifikasikan pada sebagian besar tabel. |
| 24 | Ubah password | Apakah password lama wajib diisi, dan aturan password: tidak dispesifikasikan. |
| 25 | Field profil | Field mana yang dapat diubah di UC-U5: tidak dispesifikasikan. |
| 26 | Penamaan role Admin | Sumber menggunakan Admin. Antarmuka menggunakan Supervisor untuk role yang sama. Nilai role di basis data adalah `admin`. |
| 27 | Penamaan role Petugas | Sumber menggunakan Petugas. Antarmuka menggunakan Operator. Nilai role di basis data adalah `petugas`. |
| 28 | Nama di diagram log aktivitas | Usecase.pdf menggunakan Rekap Aktivitas di beberapa tempat. Sumber menggunakan Riwayat Aktivitas. |
| 29 | Celah diagram use case | Usecase.pdf Petugas tidak menampilkan PT-09 atau PT-10. Admin tidak menampilkan AD-16. Direksi tidak menampilkan DR-03 atau DR-14. |
| 30 | Mockup antarmuka | UI_Project_FPW.pdf ditandai sedang direvisi di dokumen sumber, sehingga tidak dijadikan persyaratan. Mockup menampilkan field yang tidak ada di use case atau ERD, misalnya contact person owner, nomor telepon, tanggal berakhir tarif (Berlaku hingga), kuantitas minimum, dan faktor tarif (Faktor Tambahan). Field tersebut tidak dimasukkan ke PRD ini. |
| 31 | Nilai status pada mockup UI | Menampilkan status seperti Draft, Menunggu Review, Perlu Revisi, Diteruskan, Disetujui, Ditolak, dan Menunggu Approval Direksi. ERD dan sumber menggunakan kata yang berbeda untuk sebagian status. PRD ini memakai nilai dari ERD. |
| 32 | Penomoran dokumen | Aturan pembuatan `opnam_number`, `billing_number`, `invoice_number`, dan `report_number`: tidak dispesifikasikan. |
| 33 | Riwayat revisi invoice | ERD hanya menyimpan `revision_count` pada INVOICES. Tempat penyimpanan revisi sebelumnya: tidak dispesifikasikan. |
| 34 | Kolom ERD baru yang tidak dijelaskan di dokumen use case | ERD revisi menambahkan `tanggal_opnam`, `catatan`, `total_biaya` (snapshot), `created_at`, dan `updated_at` pada OPNAMS; `lokasi`, `created_at`, dan `updated_at` pada OPNAM_ITEMS; `keterangan`, `created_at`, dan `updated_at` pada OPNAM_ATTACHMENTS. Dokumen use case tidak menjelaskan kolom-kolom ini. Makna, cara input, dan aturannya: tidak dispesifikasikan. |
| 35 | Status opnam yang berganti nama | ERD revisi mengganti `diteruskan_ke_direksi` menjadi `menunggu_approval_direksi`, dan menambahkan `ditolak`, `disetujui`, dan `dokumen_terbit`. Dokumen use case (AD-08, Bagian 4) masih memakai istilah lama. Konfirmasi pemetaannya. |
| 36 | Himpunan status yang tumpang tindih | OPNAMS.status dan BILLINGS.status kini memakai nilai yang sama (`disetujui`, `ditolak`). Tabel mana yang berwenang untuk tiap keputusan, dan status mana yang dibaca DR-01: tidak dispesifikasikan. |
| 37 | Pemicu `dokumen_terbit` | Aksi mana (DR-08, DR-09, atau keduanya) yang mengubah OPNAMS.status menjadi `dokumen_terbit`: tidak dispesifikasikan. |
| 38 | Sumber `total_biaya` | Aturan perhitungan dan saat snapshot diambil: tidak dispesifikasikan. BR-14 hanya mencakup estimasi per item. |
| 39 | Penggunaan `catatan` dan `tanggal_opnam` | Hubungan dengan catatan pengembalian AD-09 dan tanggal opnam pada PT-01: tidak dispesifikasikan. |

---

## 10. Checklist Progres dan Kepemilikan (Progress Checklist and Ownership)

### Aturan untuk AI agent (AI agent rules)

- Sebelum mengerjakan apa pun, cari item di tabel ini. Kerjakan hanya item yang Pemiliknya sama dengan orang yang meminta.
- Item tanpa pemilik, atau yang menyentuh modul lintas tim (USERS, login, dan tabel bersama), jangan dikerjakan. Tanyakan dulu.
- Setelah item selesai, ubah Status dan isi Catatan dalam perubahan yang sama.
- Nilai Status: `Belum`, `Dikerjakan`, `Selesai`, `Perlu konfirmasi`.

### 10.1 Pembagian tugas (Tupoksi)

**Aturan khusus:** middleware dan policy (Halvina) dipakai oleh semua modul, jadi perubahannya hanya dilakukan oleh Halvina. Tugas Rifqy untuk ekspor laporan berstatus membantu, sehingga mesin ekspor bersama perlu dikonfirmasi (lihat 10.5).

Modul yang bisa dipakai sebagai dasar pembagian: Umum (UC-U), Petugas (PT), Admin (AD), Direksi (DR), dan Basis data (17 tabel). Pembagian ini sudah sesuai `Pembagian_Tugas_Tim.docx`.

| Anggota | Modul | Tugas (dari Pembagian_Tugas_Tim.docx) |
|---|---|---|
| Regina | Opnam dan Kalkulasi Biaya | Halaman daftar opnam; form pencatatan opnam; tambah, edit, hapus detail pekerjaan sesuai status transaksi; hubungkan opnam dengan data kapal; kalkulasi biaya otomatis berdasarkan tarif dan spesifikasi; unggah foto/bukti pekerjaan |
| Anisa | Master Data Pekerjaan dan Tarif | CRUD kategori pekerjaan; CRUD jenis pekerjaan; CRUD material dan spesifikasi; CRUD satuan pekerjaan; CRUD tarif dasar; faktor tambahan tarif sesuai spesifikasi teknis |
| Rifqy | Master Data Owner dan Kapal serta Laporan | CRUD owner kapal; CRUD kapal; hubungkan kapal dengan owner; pencarian riwayat perbaikan kapal; halaman rekapitulasi pekerjaan; membantu ekspor laporan PDF/Excel; riwayat perubahan dan audit log |
| Halvina | Autentikasi, Pengguna, dan Dashboard | Login dan logout; ubah password; CRUD pengguna; tiga role (Admin, Petugas, Direksi); middleware dan policy; dashboard per role; lihat dan ubah profil |
| Hazel | Review, Approval, Disposisi, dan Dokumen Tagihan | Halaman review opnam; setujui hasil review atau kembalikan opnam untuk revisi; proses approval tagihan Direksi; tolak tagihan; diskon dan markup; catat disposisi; Invoice PDF; Technical Report PDF; unduh dan cetak dokumen |

### 10.2 Checklist basis data (17 tabel ERD)

| ID | Item | Pemilik | Status | Catatan |
|---|---|---|---|---|
| DB-01 | USERS | Halvina | Belum | |
| DB-02 | OWNERS | Rifqy | Belum | |
| DB-03 | SHIPS | Rifqy | Belum | ⚠ Kolom `jenis_kapal` ditambahkan di luar ERD atas permintaan Regina 2026-11-25; perlu konfirmasi Rifqy. |
| DB-04 | UNITS | Anisa | Belum | |
| DB-05 | WORK_CATEGORIES | Anisa | Belum | |
| DB-06 | WORK_TYPES | Anisa | Belum | |
| DB-07 | MATERIAL_SPECS | Anisa | Belum | |
| DB-08 | TARIFFS | Anisa | Belum | |
| DB-09 | OPNAMS | Regina | Selesai | Migrasi & model: `tanggal_opnam`, `catatan`, `total_biaya`, 7 konstanta status; + `lokasi` & `adjustment_amount` (di luar ERD, permintaan 2026-11-25). |
| DB-10 | OPNAM_ITEMS | Regina | Selesai | `lokasi`, `faktor`, `ketebalan`, `kompleksitas`, `catatan_tambahan`; 4 kolom terakhir di luar ERD (disetujui 2026-11-26). |
| DB-11 | OPNAM_ATTACHMENTS | Regina | Selesai | Migrasi & model: `keterangan`, `file_size` = `integer` (byte); terverifikasi via `db:table`. |
| DB-12 | STATUS_HISTORIES | Di luar tupoksi | Belum | Dipakai Regina dan Hazel, lihat 10.5 |
| DB-13 | BILLINGS | Hazel | Belum | |
| DB-14 | BILLING_ADJUSTMENTS | Hazel | Belum | |
| DB-15 | INVOICES | Hazel | Belum | |
| DB-16 | TECHNICAL_REPORTS | Hazel | Belum | |
| DB-17 | ACTIVITY_LOGS | Rifqy | Belum | Audit log |

### 10.3 Checklist use case (49)

| ID | Use case | Pemilik | Status | Catatan |
|---|---|---|---|---|
| UC-U1 | Login | Halvina | Belum | |
| UC-U2 | Logout | Halvina | Belum | |
| UC-U3 | Mengubah Password | Halvina | Belum | |
| UC-U4 | Melihat Dashboard | Halvina | Belum | |
| UC-U5 | Melihat dan Mengubah Profil | Halvina | Belum | |
| PT-01 | Mencatat Opnam Pekerjaan | Regina | Selesai | Langkah 1: wajib kapal/tanggal/`lokasi` (select 2 dock); Catatan tidak ditampilkan (kolom tetap). Faktor 1x/1,25x dihitung & disimpan. |
| PT-02 | Memilih Data Kapal | Regina | Selesai | Pilih `SHIPS` + `OWNERS`, auto-fill (BR-11). |
| PT-03 | Menambahkan Detail Pekerjaan | Regina | Selesai | Item dinamis; `Ketebalan`, `Kompleksitas`→`faktor`, `Catatan Tambahan`; UI Faktor & Lokasi dihapus; spesifikasi auto dari master. |
| PT-04 | Mengunggah Foto/Bukti Pekerjaan | Regina | Selesai | Upload per item via UID (`bukti[uid][]`); tiap file ke `opnam_item_id` benar; JPG/PNG/PDF ≤10 MB. |
| PT-05 | Melihat Perhitungan Biaya | Regina | Selesai | Snapshot `unit_price`/`subtotal`; `total_biaya` = jumlah subtotal. |
| PT-06 | Menyimpan Draft Opnam | Regina | Selesai | Tombol Simpan Draft di Final Review; nomor `OPN-{tahun}-{3 digit}`. |
| PT-07 | Mengajukan Opnam untuk Review | Di luar tupoksi | Belum | Lihat 10.5 |
| PT-08 | Melihat Status Pengajuan | Regina | Selesai | Opname Saya: daftar milik petugas login; filter & paginasi; detail read-only (riwayat status); 5 tes lulus. |
| PT-09 | Memperbaiki Opnam yang Dikembalikan | Di luar tupoksi | Belum | Lihat 10.5 |
| PT-10 | Melihat Riwayat Aktivitas | Rifqy | Belum | Konfirmasi, cakupan belum jelas |
| PT-11 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat 10.5 |
| AD-01 | Mengelola Pengguna | Halvina | Belum | |
| AD-02 | Mengelola Data Owner dan Kapal | Rifqy | Belum | |
| AD-03 | Mengelola Kategori Pekerjaan | Anisa | Belum | |
| AD-04 | Mengelola Jenis Pekerjaan, Material, Satuan, dan Tarif | Anisa | Belum | |
| AD-05 | Melihat Daftar Opnam | Regina | Selesai | Filter `q`, status + operator, tanggal tunggal; aksi 👁 → detail read-only Admin; 7 tes lulus. |
| AD-06 | Memeriksa Opnam | Hazel | Belum | Halaman review opnam |
| AD-07 | Merevisi Data Opnam | Di luar tupoksi | Belum | Lihat 10.5 |
| AD-08 | Menyetujui Hasil Review | Hazel | Belum | |
| AD-09 | Mengembalikan Opnam untuk Revisi | Hazel | Belum | |
| AD-10 | Melihat Riwayat Revisi | Rifqy | Belum | Konfirmasi |
| AD-11 | Melihat Riwayat Pekerjaan Kapal | Rifqy | Belum | Konfirmasi, terkait pencarian riwayat perbaikan |
| AD-12 | Merevisi Invoice | Di luar tupoksi | Belum | Lihat 10.5 |
| AD-13 | Mengekspor Invoice ke PDF | Hazel | Belum | Konfirmasi |
| AD-14 | Melihat Riwayat Aktivitas | Rifqy | Belum | Audit log |
| AD-15 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat 10.5 |
| AD-16 | Melihat dan Mengekspor Laporan Rekapitulasi | Rifqy | Belum | Halaman rekapitulasi |
| DR-01 | Melihat Draft Menunggu Approval | Hazel | Belum | |
| DR-02 | Memeriksa Draft Tagihan | Hazel | Belum | |
| DR-03 | Merevisi Data Opnam | Di luar tupoksi | Belum | Lihat 10.5 |
| DR-04 | Memberikan Diskon atau Markup | Hazel | Belum | |
| DR-05 | Mencatat Disposisi | Hazel | Belum | |
| DR-06 | Menyetujui Tagihan | Hazel | Belum | |
| DR-07 | Menolak Tagihan | Hazel | Belum | |
| DR-08 | Menerbitkan Invoice PDF | Hazel | Belum | |
| DR-09 | Menerbitkan Technical Report PDF | Hazel | Belum | |
| DR-10 | Merevisi Invoice | Di luar tupoksi | Belum | Lihat 10.5 |
| DR-11 | Mengekspor Invoice ke PDF | Hazel | Belum | Konfirmasi |
| DR-12 | Mengunduh atau Mencetak Dokumen | Hazel | Belum | |
| DR-13 | Melihat Rekap Tagihan | Rifqy | Belum | Konfirmasi |
| DR-14 | Mengekspor Laporan PDF/Excel | Rifqy | Belum | Membantu ekspor laporan |
| DR-15 | Mencari dan Melihat Riwayat Perbaikan Kapal | Rifqy | Belum | |
| DR-16 | Melihat Riwayat Aktivitas | Rifqy | Belum | Audit log |
| DR-17 | Mengekspor Riwayat Aktivitas ke PDF | Di luar tupoksi | Belum | Lihat 10.5 |

### 10.4 Checklist transisi status (10)

| ID | Transisi | Pemilik | Status | Catatan |
|---|---|---|---|---|
| ST-01 | Opnam: `draft` → `menunggu_review` | Di luar tupoksi | Belum | PT-07, lihat 10.5 |
| ST-02 | Opnam: `menunggu_review` → `perlu_revisi` | Hazel | Belum | AD-09 |
| ST-03 | Opnam: `menunggu_review` → `menunggu_approval_direksi` | Hazel | Belum | AD-08, konfirmasi nama status |
| ST-04 | Opnam: `perlu_revisi` → `menunggu_review` | Di luar tupoksi | Belum | Tersirat, lihat 10.5 |
| ST-05 | Opnam: `menunggu_approval_direksi` → `disetujui` | Hazel | Belum | Tersirat, konfirmasi |
| ST-06 | Opnam: `menunggu_approval_direksi` → `ditolak` | Hazel | Belum | Tersirat, konfirmasi |
| ST-07 | Opnam: `disetujui` → `dokumen_terbit` | Hazel | Belum | Tersirat, konfirmasi |
| ST-08 | Tagihan: (dibuat) → `menunggu_approval` | Di luar tupoksi | Belum | Langkah pembuatan belum jelas, lihat 10.5 |
| ST-09 | Tagihan: `menunggu_approval` → `disetujui` | Hazel | Belum | DR-06 |
| ST-10 | Tagihan: `menunggu_approval` → `ditolak` | Hazel | Belum | DR-07 |

### 10.5 Item di luar tupoksi (parkir)

Item berikut tidak tercantum di tugas anggota mana pun, atau pemiliknya belum jelas. Item ini tetap ada di checklist dengan Pemilik "Di luar tupoksi". **Agent tidak boleh mengerjakannya sebelum tim memutuskan pemiliknya.**

| No | Item | Alasan belum ada di tupoksi | Keputusan yang dibutuhkan |
|---|---|---|---|
| 1 | PT-07, PT-09, ST-01, ST-04 | Alur pengajuan dan perbaikan opnam oleh Petugas belum masuk tugas siapa pun | Tentukan pemilik. PT-08 (Opname Saya) sudah menjadi milik Regina. |
| 2 | AD-07, DR-03 | Revisi data opnam oleh Admin dan Direksi belum masuk tugas siapa pun | Tentukan pemilik |
| 3 | AD-12, DR-10 | Revisi invoice belum masuk tugas siapa pun | Tentukan pemilik |
| 4 | PT-11, AD-15, DR-17 | Ekspor riwayat aktivitas belum masuk tugas siapa pun | Tentukan pemilik |
| 5 | ST-08 | Langkah pembuatan tagihan dari opnam yang diteruskan belum jelas | Tentukan pemilik dan cara pembuatannya |
| 6 | DB-12 STATUS_HISTORIES | Dipakai Regina (opnam) dan Hazel (tagihan) | Tentukan pemilik tunggal |
| 7 | AD-10, AD-11, AD-14, PT-10, DR-13, DR-16 | Cakupan riwayat dan audit untuk Petugas, Admin, dan Direksi belum dipisah | Konfirmasi cakupan masing-masing |
| 8 | Mesin ekspor PDF/Excel | Modul ekspor bersama belum punya pemilik | Tentukan pemilik |
| 9 | Middleware dan policy | Dipakai semua modul | Sudah ditetapkan ke Halvina, konfirmasi tim |
