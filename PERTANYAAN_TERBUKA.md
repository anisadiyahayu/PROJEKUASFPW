# Pertanyaan Terbuka — Modul Regina (Opnam & Kalkulasi Biaya)

Daftar ini disusun memakai format `taskinstruction.md` Bagian 8. Tujuannya: membuka blokir item Regina yang masih berstatus `Dikerjakan` / `Perlu konfirmasi` di `tasklist.md`. Belum ada nilai yang diputuskan; agent tidak boleh menebak.

Dikelompokkan berdasarkan pihak yang perlu menjawab. Kirim blok yang relevan ke masing-masing, lalu catatkan jawabannya di sini dan di `tasklist.md` sebelum implementasi.

---

## A. Kepada Client / Pemilik Master Tarif (Anisa)

```
Item: PT-05 — Faktor Ketebalan
Tertahan karena: PRD 9 no. 19 dan no. 23; 3.10.4; 6.7 no. 4
Pertanyaan: Apakah Petugas mengisi angka ketebalan lalu faktor dihitung dari rentang, atau Petugas memilih faktor langsung? Berapa rentang/satuannya?
Opsi (jika ada):
  A. Isi ketebalan (mis. mm), lalu faktor diambil dari peta rentang di config/opnam.php
  B. Petugas memilih nilai faktor langsung dari pilihan tetap
  C. Faktor bersumber dari master tarif Anisa (perlu penambahan kolom; menyimpang dari ERD)
Dampak jika menebak: faktor salah membuat subtotal dan total_biaya salah; konfigurasi saat ini sengaja kosong sehingga semua faktor = 1.
```

```
Item: PT-05 — Faktor Kompleksitas
Tertahan karena: PRD 9 no. 20 dan no. 23; 3.10.4; 6.7 no. 4
Pertanyaan: Apa saja tingkat kompleksitas dan berapa nilai faktor untuk masing-masing tingkat?
Opsi (jika ada):
  A. Daftar tetap dari client (sebutkan tingkat dan nilainya)
  B. Tetap low/medium/high dengan nilai yang ditentukan client
  C. Semua bernilai 1 sampai ada keputusan (default saat ini)
Dampak jika menebak: estimasi biaya tidak sesuai tarif teknis; nilai saat ini low/medium/high hanyalah label UI.
```

```
Item: PT-05 — Pembulatan
Tertahan karena: PRD 9 no. 21; 3.10.4
Pertanyaan: Bagaimana aturan pembulatan subtotal item dan total_biaya?
Opsi (jika ada):
  A. Subtotal dibulatkan 2 desimal per item, lalu dijumlah (default dokumen saat ini)
  B. Semua subtotal dijumlah dulu, baru dibulatkan
  C. Tanpa pembulatan
Dampak jika menebak: selisih nominal pada total_biaya dan dokumen tagihan.
```

```
Item: PT-01 — Daftar Jenis Kapal
Tertahan karena: PRD 9 no. 18; 3.10.2; 6.7 no. 3
Pertanyaan: Nilai OPNAMS.jenis_kapal berupa teks bebas atau pilihan tetap? Jika pilihan tetap, sebutkan daftarnya.
Opsi (jika ada):
  A. Teks bebas (disalin dari SHIPS.jenis_kapal)
  B. Pilihan tetap dengan daftar nilai tertentu
Dampak jika menebak: validasi dan snapshot jenis kapal tidak sesuai keputusan; kolom sudah ada sebagai salinan dari SHIPS.
```

```
Item: PT-04 — Unggahan Bukti Pekerjaan
Tertahan karena: PRD 9 no. 5; BR-26
Pertanyaan: Format file apa saja yang diizinkan, berapa ukuran maksimum per file, dan di mana file disimpan?
Opsi (jika ada):
  A. JPG/PNG/PDF, maks. 10 MB, storage public (default sementara saat ini)
  B. Format/ukuran/lokasi lain sesuai keputusan client
Dampak jika menebak: file yang seharusnya diterima ditolak atau sebaliknya; lokasi storage sulit dipindah setelah data tersimpan.
```

---

## B. Kepada Tim / Regina (internal)

```
Item: PT-06 — Format Nomor Opnam
Tertahan karena: PRD 9 no. 8; 6.6
Pertanyaan: Format final opnam_number dan cara pembuatannya?
Opsi (jika ada):
  A. OPN-{tahun}-{seq} dengan sequence yang dijamin unik (disarankan; saat ini hanya count+1)
  B. Format lain (sebutkan)
Dampak jika menebak: bentuk nomor tidak sesuai keputusan; implementasi count+1 berisiko duplikat saat dua draft dibuat bersamaan.
```

```
Item: PT-01 — Nama Tahap Akhir
Tertahan karena: PRD 9 no. 16
Pertanyaan: Tahap kelima pencatatan bernama "Bukti Pekerjaan" atau "Tinjauan Akhir"?
Opsi (jika ada):
  A. Bukti Pekerjaan (label wizard saat ini)
  B. Tinjauan Akhir
Dampak jika menebak: hanya label antarmuka; tidak mengubah data atau alur.
```

```
Item: PT-01 — Field Catatan
Tertahan karena: PRD 3.10.2 menyebut OPNAMS.catatan opsional, tetapi test yang ada justru menolak keberadaan input catatan
Pertanyaan: Apakah input catatan opsional tetap ditampilkan di langkah 1, atau sengaja dihilangkan?
Opsi (jika ada):
  A. Ditampilkan sebagai textarea opsional; test lama diperbarui
  B. Dihilangkan dari form; kolom DB tetap nullable dan tidak pernah diisi
Dampak jika menebak: test yang ada gagal atau field yang disepakati tidak tersedia.
```

---

## C. Kepada Tim (item parkir — bukan milik Regina, tapi memblokir alur)

```
Item: PT-07, PT-09, ST-01, ST-04, ST-08, DB-12 STATUS_HISTORIES
Tertahan karena: tupoksi.md Bagian 3 no. 1, 5, 6; taskinstruction.md Bagian 6
Pertanyaan: Siapa pemilik alur pengajuan/perbaikan opnam, pembuatan tagihan dari opnam yang diteruskan, dan STATUS_HISTORIES? Bagaimana cara pembuatan tagihannya?
Opsi (jika ada):
  A. Tetap parkir sampai tim menetapkan pemilik
  B. Ditugaskan ke pemilik tertentu (sebutkan)
Dampak jika menebak: alur review → approval → dokumen tidak bisa diuji dari ujung ke ujung; modul Hazel menunggu opnam berstatus menunggu_review.
```

---

## Catatan penggunaan

- Jawaban yang sudah ada di `prd.md` Bagian 10 tidak perlu ditanyakan ulang.
- Setiap jawaban wajib dicatat di `PROMPT_LOG.md` (blok prompt baru) dan `tasklist.md` (Status/Catatan) sebelum item diimplementasikan.
- Item yang masih `[TERBUKA]` tetap berhenti sampai ada jawaban; agent tidak boleh memilih sendiri.
