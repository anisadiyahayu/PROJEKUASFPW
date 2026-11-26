# Log Prompt — Regina

File ini mencatat **setiap prompt yang diberikan Regina** beserta timestamp (WIB / Asia/Jakarta).

## Aturan pencatatan

- Setiap ada prompt baru, tambahkan satu baris di bawah tabel ini.
- Format timestamp: `YYYY-MM-DD HH:MM WIB`.
- Kolom **Prompt** ditulis verbatim (apa adanya).
- Kolom **Item/Modul** diisi jika prompt terkait pengerjaan SIMSHIP; kosongkan jika tidak.
- Pencatatan hanya berjalan pada agent/sesi yang membaca file ini. Jika prompt diberikan di sesi atau agent lain, agent itu perlu menambahkan entrinya sendiri.

## Daftar prompt

| No | Timestamp | Prompt | Item/Modul terkait | Catatan |
|---|---|---|---|---|
| 1 | 2026-11-25 18:28 WIB | `baca dan pahami file @prd.md dan @taskinstruction.md` | prd.md, taskinstruction.md | Kedua file dibaca lengkap (888 + 415 baris). |
| 2 | 2026-11-25 18:28 WIB | `saya regina` | Identitas peminta | Scope ditetapkan ke `taskinstruction.md` Bagian 5.4. |
| 3 | 2026-11-25 18:28 WIB | `aku ingin kamu buatkan juga file prompt.md untuk mencatat setiap prompt yang aku berikan dengan timestamp juga, dan buat juga tasklist.md` | prompt.md, tasklist.md | Permintaan pembuatan dua file. |
| 4 | 2026-11-25 18:28 WIB | `kerjakan` | prompt.md, tasklist.md | Eksekusi setelah keluar dari Plan mode. |
| 5 | 2026-11-25 19:22 WIB | `menurutmu untuk migrasi sebaiknya apakah dilakukan semuanya?` | Migrasi | Analisis pendekatan pengerjaan migrasi. |
| 6 | 2026-11-25 19:22 WIB | `analisis semua migrition yang ada apakah sudah sesuai dengan prd yang ada` | Migrasi (semua tabel) | Audit 19 file migrasi terhadap ERD `prd.md` Bagian 6. |
| 7 | 2026-11-25 19:22 WIB | `iya, fokus ke bagian saya saja` | DB-09, DB-10, DB-11 | Setuju fokus hanya pada 3 tabel Regina. |
| 8 | 2026-11-25 19:22 WIB | `iya, belum pernah dijalankan` | DB-09, DB-10, DB-11 | Konfirmasi migrasi belum dijalankan → edit file existing. |
| 9 | 2026-11-25 19:30 WIB | `Create or update AGENTS.md for this repository...` | AGENTS.md | Investigasi config/instruksi, lalu tulis ulang AGENTS.md. |
| 10 | 2026-11-25 20:06 WIB | `jalankan sesuai dengan rekomendasi mu` | DB-09, DB-10, DB-11 | Sinkronkan 3 model Eloquent dengan ERD. |
| 11 | 2026-11-25 20:12 WIB | `untuk jawaban pertanyaan, samakan dengan yang ada di prd` | AD-05 | Pakai nilai/kolom yang ada di PRD-ERD; jangan menambah field baru. |
| 12 | 2026-11-25 20:32 WIB | `bisa bantu update php ke php yang di rekomendasikan ga?` + `jalankan` | Environment | Arahkan `php` ke MAMP 8.5.2 lewat `~/.zshrc`. |
| 13 | 2026-11-25 21:12 WIB | `baca folder docs ... samakan tampilan ui halaman` + 4 jawaban + `iya, pakai usulanmu` | AD-05, PT-01–PT-06 | UI mengikuti screenshot Figma; role hanya label; field di luar ERD dilarang. |
| 14 | 2026-11-25 21:37 WIB | `kolom untuk jenis kapal dan lokasi pekerjaan belum ada` + `kerjakan sesuai uinya` | PT-01, DB-03, DB-09 | Tambah `SHIPS.jenis_kapal` & `OPNAMS.lokasi` serta tampilkan di langkah 1. |
| 15 | 2026-11-25 21:52 WIB | `lokasi pekerjaan bukannya kolom select ya?` + `jalankan sesuai ui` | PT-01 | Ubah input teks + datalist menjadi `<select>` dua opsi sesuai screenshot. |
| 16 | 2026-11-25 22:05 WIB | `1.B dan 2.A` | PT-01, DB-09, DB-10 | Faktor tambahan dihitung & disimpan; lokasi jadi select placeholder tanpa data. |
| 17 | 2026-11-25 23:04 WIB | `bagian "total etimasi" di bawah garis dan di atas RP` | PT-01 | Pindahkan label Total Estimasi ke bawah garis pemisah, di atas angka. |
| 18 | 2026-11-26 05:53 WIB | `periksa apakah semua file bukti masuk ke item pertama...` | PT-04 | Audit: semua lampiran masih dihubungkan ke item pertama. |
| 19 | 2026-11-26 05:53 WIB | `jadi apa yang dikerjakan?` | PT-04 | Penegasan rencana perbaikan lampiran per item. |
| 20 | 2026-11-26 05:53 WIB | `coba fokus ke bagian 1 ... lokasi pekerjaan, ini sebaiknya gimana?` | PT-01 | Diskusi penempatan lokasi di langkah 1. |
| 21 | 2026-11-26 05:53 WIB | `OPNAMS.lokasi. baru di bikin atau dari temen saya?` | DB-09 | Konfirmasi asal kolom (`OPNAMS.lokasi` buatan kita). |
| 22 | 2026-11-26 05:53 WIB | `kalo bikin 2 pilihan sesuai ui bisa?` | PT-01 | Diskusi 2 opsi dock. |
| 23 | 2026-11-26 05:53 WIB | `yang option volue nya ga usah pakai pilih lokasi langsung dock 1 atau dock 2` | PT-01 | Select tanpa placeholder. |
| 24 | 2026-11-26 05:53 WIB | `catatan wajib kah? kalo di hapus bagaimana?` | PT-01 | Diskusi field Catatan. |
| 25 | 2026-11-26 05:53 WIB | `kalo catatan di hapus OPNAMS.catatan untuk apa?` | DB-09 | Diskusi tujuan kolom catatan. |
| 26 | 2026-11-26 05:53 WIB | `kata temen saya yang lokasi pekerjaan 2 dock itu sebenernya di pake tapi bisa lain juga...` | PT-01 | Diskusi datalist vs select. |
| 27 | 2026-11-26 05:53 WIB | `eh ga jadi selact pake 2 dock itu dulu` | PT-01 | Kembali ke select 2 dock. |
| 28 | 2026-11-26 05:53 WIB | `di bagian 2 kan ada kolom "catatan tambahan" ya bisa di isi itu di simpen dimana?` | PT-03 | Diskusi "Catatan Tambahan" vs `description`. |
| 29 | 2026-11-26 05:53 WIB | `fokus ke bagian 1 dulu deh, selain pilih kapan dan tanggal opnam itu sisanya di buat wajib...` | PT-01 | Diskusi field wajib langkah 1. |
| 30 | 2026-11-26 05:53 WIB | `jangan dulu deh yang wajib di isi pilihan kapal, lokasi pekerjaa dan tanggal opnam aj` | PT-01 | Keputusan: hanya 3 field wajib. |
| 31 | 2026-11-26 05:53 WIB | `selain yang 3 itu ui catatan juga hapus aja` | PT-01 | Keputusan: hapus textarea Catatan. |
| 32 | 2026-11-26 05:53 WIB | `jalankan` | PT-01 | Eksekusi perubahan langkah 1. |
| 33 | 2026-11-26 06:06 WIB | `jalankan opsi b` | Data master (lokal) | Isi data contoh via tinker di DB `simship`; tanpa perubahan file kode. |
| 34 | 2026-11-26 06:10 WIB | `logo ga bisa di sebelah simship?` + `pakai inline svg` | Layout | Tambah ikon kapal inline SVG di samping teks SIMSHIP. |

> **Catatan timestamp:** entri 1-17 dicatat pada 2026-11-25 (18:28–23:04 WIB); entri 18-32 dicatat pukul 05:53 WIB; entri 33 pukul 06:06 WIB; entri 34 pukul 06:10 WIB pada 2026-11-26. Waktu di atas adalah waktu pencatatan, bukan waktu pengiriman persis tiap prompt.
