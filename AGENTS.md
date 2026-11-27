# SIMSHIP — catatan untuk agent

## Baca dulu
- `SKILL.md` — ERD revisi (17 tabel, 24 relasi): kolom, enum, FK/UK, snapshot, soft delete. Sumber kebenaran skema; ERD mengalahkan dokumen lain.
- `prd.md` — use case, BR-01..BR-27, checklist kepemilikan Bagian 10. `taskinstruction.md` — pembagian tugas & aturan kerja; Bagian 8 = format pertanyaan konfirmasi.
- `tasklist.md` (progres pribadi). `UI_Project_FPW.pdf` bukan acuan; screenshot Figma di `docs/` dipakai untuk tampilan halaman Regina (keputusan 2026-11-25). Field di luar ERD tetap dilarang, **kecuali** `SHIPS.jenis_kapal`, `OPNAMS.lokasi`, `OPNAMS.adjustment_amount`, `OPNAM_ITEMS.faktor`, `OPNAM_ITEMS.ketebalan`, `OPNAM_ITEMS.kompleksitas`, `OPNAM_ITEMS.catatan_tambahan`, dan `INVOICES.status` (`draft`/`terbit`) yang ditambahkan atas permintaan 2026-11-25/26/27, penyimpangan ERD yang masih perlu konfirmasi tim. Jangan menulis ke tabel master Anisa/Rifqy dari modul opnam; cukup baca.
- Keep UI changes consistent with the existing Tailwind patterns; clickable elements should have `hover:` states plus `transition-colors`.
- Use named routes with `route()`, never hardcoded URLs.
- Setiap prompt dari Pemilik mana pun dicatat sebagai satu blok di `PROMPT_LOG.md`, mengikuti format di kepala file. Entri yang ditulis belakangan ditandai `retroactive`. Log owner lain dipindahkan ke file ini dan log di luar file ini dihapus, **kecuali `prompt_history.md` yang sengaja dipertahankan sesuai instruksi Pemilik 2026-11-27**.

## Aturan kerja tim
- Selalu pastikan siapa peminta. Kerjakan hanya item dengan `Pemilik` = peminta di `prd.md` Bagian 10.
- Pemilik: Halvina = USERS/auth/middleware/policy/dashboard; Anisa = kategori/jenis/material/satuan/tarif; Rifqy = owner/kapal/laporan/riwayat/audit; Regina = opnam/item/lampiran/kalkulasi + daftar opnam; Hazel = review/approval/diskon/invoice/technical report.
- Item "Di luar tupoksi" (`taskinstruction.md` Bagian 6) dilarang dikerjakan sebelum tim menetapkan pemilik. USERS/login/middleware/policy hanya Halvina.
- Nilai "Tidak dispesifikasikan" → berhenti dan tanyakan pakai format Bagian 8, jangan menebak. Pakai nilai persis: `petugas`/`admin`/`direksi`, `draft`/`menunggu_review`/`perlu_revisi`/`menunggu_approval_direksi`/`ditolak`/`disetujui`/`dokumen_terbit`, `menunggu_approval`, `diskon`/`markup`.
- Snapshot (`unit_price`, `subtotal`, `total_biaya`, field kapal/owner/nominal di invoice & technical report) ditulis sekali; jangan hitung ulang dari master. `deleted_at` hanya di OWNERS, SHIPS, WORK_CATEGORIES, WORK_TYPES, MATERIAL_SPECS.
- Item selesai → update Status/Catatan di `tasklist.md` **dan** `prd.md` Bagian 10 dalam satu perubahan. Tiap prompt baru → tambah blok di `PROMPT_LOG.md`.

## Perintah & environment
- `php` di PATH = 8.3.30, tapi vendor butuh PHP ≥ 8.4.1. Pakai `/Applications/MAMP/bin/php/php8.5.2/bin/php` untuk semua perintah artisan/composer, mis. `/Applications/MAMP/bin/php/php8.5.2/bin/php artisan migrate`.
- Laravel Boost sudah terpasang (`boost.json`, skill di `.agents/skills`) dan `boost:install` sudah dijalankan — abaikan instruksi bootstrap lama di `CLAUDE.md`.
- DB dev: MySQL `simship` di `.env` (user `root`), sedangkan `.env.example` memakai sqlite. Tes memakai sqlite `:memory:` lewat `phpunit.xml`, terlepas dari `.env`.
- Migrasi: `... artisan migrate`; verifikasi dengan `... artisan db:table <tabel>`. README menulis `fresh:migrate`; perintah yang benar `migrate:fresh`.
- Frontend Tailwind v4 + Vite 8: `npm install` lalu `npm run dev`; atau `composer run dev` (serve + queue + Vite).
- Tes: Pest, `... artisan test`. Baseline saat ini **2 dari 3 gagal**: `/` mengembalikan 302 untuk guest, tapi `ExampleTest` dan `LoginTest` mengharapkan 200. Jangan anggap itu regresi dari perubahanmu. `RefreshDatabase` di `tests/Pest.php` masih dikomentari.

## Jebakan kode
- `app/Enums/StatusOpnam.php`, `StatusTagihan.php`, `TipeDisposisi.php` masih stub kosong dengan namespace tidak konsisten (`App`, `App\Enums\Enums`, `App\Enums`). Jangan dipakai sebagai sumber nilai enum — pakai `SKILL.md`.
- `routes/web.php` hanya menerima `username` saat login; UC-U1 di `prd.md` mensyaratkan username **atau** email — celah implementasi yang belum ditutup.
- Alias middleware `role` → `CheckRole` terdaftar di `bootstrap/app.php`. Role di DB persis `petugas`/`admin`/`direksi`; "Operator"/"Supervisor" hanya label tampilan di layout Regina, bukan nilai role.
