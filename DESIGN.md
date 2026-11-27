---
name: simship-design
description: Instruksi wajib untuk membangun UI/website SIMSHIP (dashboard Operator, Supervisor, Direksi) agar identik dengan desain Figma. Gunakan setiap kali membuat, mengubah, atau me-review halaman, komponen, layout, atau styling pada proyek SIMSHIP.
---

# DESIGN.md — SIMSHIP UI/UX Implementation Skill

Dokumen ini adalah **aturan baku** bagi Agent/model apa pun (Claude, GPT, Gemini, Cursor, Copilot, dll.) yang membangun frontend SIMSHIP. Tujuannya: hasil kode **sesuai desain Figma**, konsisten antar role, dan tidak mengarang tampilan sendiri.

---

## 1. Sumber Kebenaran (Source of Truth)

Desain Figma adalah satu-satunya acuan visual. Jika instruksi pengguna bertentangan dengan Figma, **tanyakan dulu**, jangan menebak.

| Role | Figma File | Prefix Route |
|---|---|---|
| **Operator** | https://www.figma.com/make/JOa597PBkAP0j1x1tThsXr/SIMSHIP-Dashboard-Design-System?t=5NkXHTzYXsDO3ymC-1 | `/operator` |
| **Supervisor** | https://www.figma.com/make/ncYDgNmiZAaf3OVZxuokV8/Build-SIMSHIP-Supervisor-Admin?t=bm1kPsoSxrQdzrB8-1 | `/supervisor` |
| **Direksi** | https://www.figma.com/make/wuOAjKsraStfw1vsAJnLsv/SIMSHIP-Director-Approval-Dashboard?t=41x2a7HvyMzq1F0w-1 | `/direksi` |

> Catatan: file Operator ("SIMSHIP Dashboard Design System") diperlakukan sebagai **Design System induk**. Token, komponen dasar, dan pola layout dari file ini dipakai ulang oleh Supervisor dan Direksi, kecuali Figma role tersebut secara eksplisit berbeda.

---

## 2. Aturan Emas

1. **Jangan mengarang desain.** Warna, spacing, font, radius, shadow, ikon, dan copy harus berasal dari Figma atau dari token di Bagian 5.
2. **Jangan memakai nilai hardcode** (`#3b82f6`, `padding: 13px`) di komponen. Semua lewat design token / CSS variable / Tailwind theme.
3. **Komponen dulu, halaman kemudian.** Bangun komponen reusable, lalu rakit halaman.
4. **Satu design system untuk tiga role.** Jangan menduplikasi komponen per role; gunakan variant/props.
5. **Pixel-faithful, bukan pixel-perfect buta.** Samakan hierarki visual, spacing, dan perilaku; boleh menyesuaikan responsivitas selama tetap setia pada intent desain.
6. **Jika data desain tidak tersedia, tulis `TODO(design):` dan tanya pengguna.** Jangan diam-diam memilih sendiri.

---

## 3. Alur Kerja Agent (Wajib Berurutan)

### Langkah 0 — Pastikan akses ke Figma
Link `figma.com/make/...` adalah file **Figma Make** (prototipe berbasis kode) dan sering **tidak bisa dibaca lewat web fetch/crawler** (diblokir robots / butuh login). Gunakan urutan fallback ini:

1. **Figma MCP Server** (`get_design_context`, `get_variable_defs`, `get_screenshot`, `get_metadata`) jika tersedia.
2. **Export kode Figma Make** (Download code / copy source) yang diberikan pengguna.
3. **Screenshot / export frame** yang ditempel pengguna.
4. **Jika semuanya tidak ada → BERHENTI.** Minta pengguna menyediakan salah satu di atas. Jangan membangun UI dari imajinasi.

### Langkah 1 — Inventarisasi
Untuk role yang dikerjakan, catat:
- Daftar halaman/frame dan navigasi antar halaman
- Komponen yang dipakai (tombol, tabel, kartu, form, modal, badge status, chart, dll.)
- Token (warna, tipografi, spacing, radius, shadow)
- Semua state: default, hover, focus, active, disabled, loading, empty, error, success

### Langkah 2 — Isi/Perbarui token
Salin nilai dari Figma ke Bagian 5 dan ke file token proyek (`tokens.css` / `tailwind.config` / `theme.ts`). Jangan mulai membuat halaman sebelum token terisi.

### Langkah 3 — Bangun komponen dasar
Mulai dari atom → molekul → organisme (Bagian 6). Dokumentasikan props dan variant.

### Langkah 4 — Rakit halaman per role
Gunakan layout shell bersama (Bagian 7), lalu isi konten tiap halaman sesuai frame Figma.

### Langkah 5 — Verifikasi
Jalankan checklist Bagian 11. Bandingkan hasil render dengan screenshot Figma, berdampingan.

---

## 4. Tech Stack & Struktur Proyek

> Sesuaikan dengan stack proyek yang sudah ada. Jika Figma Make menghasilkan kode (umumnya React + Tailwind + shadcn/ui), **pertahankan stack tersebut** agar konversi minimal. Jangan mengganti framework tanpa persetujuan pengguna.

Struktur yang disarankan:

```
src/
├── styles/
│   ├── tokens.css          # CSS variables dari Figma
│   └── globals.css
├── components/
│   ├── ui/                 # atom & molekul (Button, Input, Badge, Table, Modal)
│   ├── layout/             # AppShell, Sidebar, Topbar, PageHeader
│   └── features/           # komponen domain (ShipmentCard, ApprovalPanel, StatusTimeline)
├── pages/ atau app/
│   ├── operator/
│   ├── supervisor/
│   └── direksi/
├── lib/                    # util, formatter (tanggal, angka, mata uang)
└── types/
```

Aturan penamaan:
- Komponen: `PascalCase` (`StatusBadge.tsx`)
- Token CSS: `--color-primary-500`, `--space-4`, `--radius-md`
- File halaman mengikuti nama frame di Figma bila memungkinkan

---

## 5. Design Tokens

> **Status: perlu diisi dari Figma.** Nilai di bawah adalah placeholder. Agent wajib menggantinya dengan nilai asli dari file Figma (lewat Variables / Styles / kode Figma Make). Hapus penanda `TODO` setelah terisi.

### 5.1 Warna

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `--color-primary` | `TODO(design)` | Aksi utama, link aktif, item navigasi aktif |
| `--color-primary-hover` | `TODO(design)` | Hover tombol utama |
| `--color-secondary` | `TODO(design)` | Aksi sekunder |
| `--color-bg` | `TODO(design)` | Latar halaman |
| `--color-surface` | `TODO(design)` | Kartu, panel, modal |
| `--color-border` | `TODO(design)` | Garis pemisah, border input |
| `--color-text` | `TODO(design)` | Teks utama |
| `--color-text-muted` | `TODO(design)` | Teks sekunder, placeholder |
| `--color-success` | `TODO(design)` | Disetujui / selesai |
| `--color-warning` | `TODO(design)` | Menunggu / perlu perhatian |
| `--color-danger` | `TODO(design)` | Ditolak / error / hapus |
| `--color-info` | `TODO(design)` | Informasi / dalam proses |

Dark mode: **ikuti Figma**. Jika Figma tidak memiliki varian dark, jangan menambahkannya.

### 5.2 Tipografi

| Token | Font / Ukuran / Berat / Line-height |
|---|---|
| `--font-family` | `TODO(design)` |
| `--text-h1` … `--text-h4` | `TODO(design)` |
| `--text-body` | `TODO(design)` |
| `--text-small` / `--text-caption` | `TODO(design)` |

### 5.3 Spacing, Radius, Shadow, Breakpoint

| Kategori | Nilai |
|---|---|
| Skala spacing | `TODO(design)` (contoh pola: kelipatan 4px) |
| Radius (sm/md/lg/full) | `TODO(design)` |
| Shadow (sm/md/lg) | `TODO(design)` |
| Breakpoint | `TODO(design)` |
| Lebar sidebar / tinggi topbar | `TODO(design)` |
| Lebar maksimum konten | `TODO(design)` |

### 5.4 Ikon & Aset
- Gunakan **library ikon yang sama dengan Figma**. Jika tidak jelas, tanyakan.
- Logo, ilustrasi, dan gambar diekspor dari Figma (SVG bila memungkinkan). Jangan mengganti dengan placeholder acak.
- Ukuran ikon konsisten (ikuti skala di Figma).

---

## 6. Inventaris Komponen

Setiap komponen **harus** punya semua state yang ada di Figma.

| Komponen | Variant utama | State wajib |
|---|---|---|
| Button | primary, secondary, ghost, danger; ukuran sm/md/lg; ikon kiri/kanan | default, hover, focus, active, disabled, loading |
| Input / Select / Textarea / Datepicker | normal, dengan ikon, dengan helper | default, focus, error, disabled |
| Badge / StatusChip | success, warning, danger, info, neutral | — |
| Card / StatCard (KPI) | dengan tren, dengan ikon | loading (skeleton) |
| Table | sortable, selectable, dengan aksi baris, pagination | loading, empty, error |
| Modal / Drawer / Dialog konfirmasi | ukuran sm/md/lg | open/close, loading |
| Tabs / Stepper / Timeline | horizontal/vertikal | aktif, selesai, tertunda |
| Toast / Alert | success, warning, error, info | — |
| Chart | sesuai Figma (line, bar, donut, dll.) | loading, empty |
| Sidebar / Topbar / Breadcrumb | collapsed/expanded | item aktif, hover |
| Form Approval (khusus Direksi) | setuju, tolak, minta revisi | wajib catatan saat tolak/revisi |

> Daftar di atas adalah **kerangka**. Tambahkan atau hapus baris sesuai komponen yang benar-benar ada di Figma.

---

## 7. Layout & Perilaku per Role

### 7.1 Shell Bersama
- Struktur: **Sidebar + Topbar + Area Konten** (konfirmasi dengan Figma).
- Sidebar menampilkan menu **hanya sesuai role** yang login.
- Topbar: judul halaman/breadcrumb, notifikasi, profil pengguna.
- Responsif: sidebar menjadi drawer pada layar kecil (sesuaikan dengan Figma).

### 7.2 Operator — `/operator`
Fokus: **input dan pemantauan operasional harian** (pengiriman/shipment, status, dokumen).
- Halaman: `TODO(design)` — isi dari daftar frame Figma Operator (contoh pola: Dashboard, Daftar Shipment, Form Input/Edit, Detail, Riwayat).
- Pola dominan: tabel data + form + filter + status chip.
- Aksi utama biasanya: tambah, ubah, kirim untuk review/approval.

### 7.3 Supervisor — `/supervisor`
Fokus: **pengawasan, verifikasi, dan administrasi**.
- Halaman: `TODO(design)` — isi dari frame Figma Supervisor (contoh pola: Dashboard ringkasan, Review/Verifikasi, Manajemen Pengguna/Master Data, Laporan).
- Pola dominan: antrean review, filter lanjutan, aksi massal, manajemen data.
- Menggunakan komponen yang sama dengan Operator; perbedaan hanya pada menu, data, dan aksi.

### 7.4 Direksi — `/direksi`
Fokus: **ringkasan eksekutif dan persetujuan (approval)**.
- Halaman: `TODO(design)` — isi dari frame Figma Direksi (contoh pola: Dashboard eksekutif, Daftar Approval, Detail & Keputusan).
- Pola dominan: KPI card, chart ringkas, panel keputusan yang jelas.
- Aksi approval (setuju / tolak / revisi) harus **menonjol, tidak ambigu**, dan memerlukan konfirmasi sebelum final.

### 7.5 Kontrol Akses
- UI role-based: menu & tombol yang tidak diizinkan **tidak dirender** (bukan sekadar disabled), kecuali Figma menunjukkan sebaliknya.
- Guard route per role di sisi routing; jangan hanya mengandalkan penyembunyian UI.

---

## 8. Aturan Implementasi Visual

- **Grid & spacing:** gunakan skala spacing token, bukan angka acak.
- **Tipografi:** gunakan kelas/token tipografi; jangan mengatur `font-size` langsung di komponen.
- **Warna status harus konsisten** di seluruh role (mis. "Disetujui" selalu memakai warna success yang sama).
- **Tabel:** header, zebra/hover, alignment angka (rata kanan), truncate teks panjang dengan tooltip.
- **Form:** label selalu terlihat, pesan error di bawah field, tanda wajib sesuai Figma.
- **Chart:** warna dari token, legenda dan tooltip sesuai Figma.
- **Animasi/transisi:** hanya jika ada di Figma atau halus (≤200ms) untuk hover/fokus. Jangan menambah animasi dekoratif.

---

## 9. Konten, Bahasa & Format Data

- **Bahasa UI: Bahasa Indonesia** (kecuali Figma memakai bahasa lain). Salin copy/label persis dari Figma.
- Format tanggal: `DD MMM YYYY` / `DD/MM/YYYY` sesuai Figma; zona waktu **WIB (Asia/Jakarta)**.
- Angka: pemisah ribuan titik, desimal koma (`1.250,50`); mata uang `Rp 1.250.000`.
- Gunakan data dummy yang realistis untuk logistik/pelayaran saat API belum tersedia; tandai jelas sebagai mock.

---

## 10. Aksesibilitas & Kualitas

- Kontras teks minimal WCAG AA.
- Semua elemen interaktif bisa diakses keyboard dengan focus ring terlihat.
- Gunakan elemen semantik (`button`, `nav`, `table`, `label`), `aria-*` seperlunya.
- Target sentuh minimal ~40px pada mobile.
- Tidak ada teks kecil di bawah ukuran caption desain.
- Tidak ada console error/warning pada halaman yang dibangun.

---

## 11. Checklist Fidelity (Definition of Done)

Sebelum menyatakan selesai, Agent **wajib** memeriksa:

- [ ] Semua warna, font, spacing, radius, shadow berasal dari token (tidak ada nilai hardcode)
- [ ] Layout, hierarki, dan urutan elemen sama dengan frame Figma
- [ ] Semua state komponen (hover, focus, disabled, loading, empty, error) ada
- [ ] Copy/label identik dengan Figma
- [ ] Ikon & aset sesuai Figma
- [ ] Responsif: desktop, tablet, mobile berfungsi (sesuai Figma bila ada frame-nya)
- [ ] Menu & aksi sesuai role
- [ ] Komponen dipakai ulang antar role, tanpa duplikasi
- [ ] Aksesibilitas dasar terpenuhi (Bagian 10)
- [ ] Screenshot hasil vs Figma dibandingkan dan selisih dijelaskan
- [ ] Semua `TODO(design)` yang tersisa dilaporkan ke pengguna

---

## 12. Format Laporan Agent

Setelah setiap pekerjaan, Agent melaporkan secara ringkas:

1. **Yang dibuat/diubah** (daftar file)
2. **Frame Figma yang dirujuk** (nama frame/halaman)
3. **Token baru** yang ditambahkan
4. **Deviasi dari Figma** dan alasannya (jika ada)
5. **Hal yang belum jelas** (`TODO(design)`) dan pertanyaan untuk pengguna

---

## 13. Larangan

- ❌ Membangun UI tanpa akses ke desain, hanya berdasarkan asumsi
- ❌ Mengubah palet, font, atau layout "agar lebih bagus" tanpa izin
- ❌ Mencampur library UI baru yang tidak ada di stack
- ❌ Menyalin warna dengan menebak dari screenshot bila token tersedia
- ❌ Membuat komponen terpisah per role untuk elemen yang sama
- ❌ Menghapus state (empty/error/loading) demi menyederhanakan
- ❌ Mengklaim "sudah sesuai Figma" tanpa menjalankan checklist Bagian 11

---

## 14. Cara Memakai Dokumen Ini

- **Claude Code / Cursor / Windsurf:** letakkan sebagai `DESIGN.md` di root repo (dan rujuk dari `CLAUDE.md` / `AGENTS.md` / `.cursorrules`: *"Ikuti DESIGN.md untuk semua pekerjaan UI."*).
- **Claude Skill:** simpan di `skills/simship-design/SKILL.md` (frontmatter di atas sudah siap pakai).
- **Chat biasa (model apa pun):** tempel isi dokumen ini di awal percakapan, lalu lampirkan screenshot/kode Figma per role.

Perintah contoh untuk Agent:

> "Baca DESIGN.md. Kerjakan halaman *Daftar Approval* untuk role Direksi sesuai Figma Direksi. Ikuti alur kerja Bagian 3 dan laporkan sesuai Bagian 12."

---

*Versi dokumen: 1.0 — Perbarui Bagian 5–7 setelah token dan daftar halaman diambil dari Figma.*
