# Kepandean Vibe

Portal resmi Desa Kepandean (Kecamatan Dukuhturi, Kabupaten Tegal), hasil
redesain dari
[kepandean-laravel](https://github.com/rifqieali/kepandean-laravel) memakai
arah desain `DESIGN.md` (Studio.Design: permukaan kanvas terang, tipografi
editorial Inter, hero asimetris, panel showcase hitam).

Stack sama persis dengan sumbernya: Laravel 13 + Inertia.js 3 + React 19 +
Vite + Tailwind CSS 4. Panel admin memakai Filament di `/admin`. Backend,
route, dan database tidak diubah, yang berubah hanya tampilan halaman publik
(`resources/js/layouts/public-layout.tsx` dan
`resources/js/pages/welcome.tsx`) plus token desain di
`resources/css/app.css`.

Panduan ini ditulis untuk pemula. Ikuti langkah demi langkah, jangan
dilewati.

## 1. Yang perlu disiapkan

Unduh dan pasang empat hal ini dulu:

| Kebutuhan  | Versi minimal | Cek dengan              |
| ---------- | ------------- | ----------------------- |
| PHP        | 8.4.1         | `php -v`                |
| Composer   | 2             | `composer --version`    |
| Node.js    | 20 (pakai npm)| `node -v` dan `npm -v`  |
| Git        | bebas         | `git --version`         |

Ekstensi PHP yang wajib aktif: `mbstring`, `openssl`, `fileinfo`,
`tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `intl`, `exif`, `zip`,
plus `pdo_sqlite` dan `sqlite3` (untuk database bawaan) atau `pdo_mysql`
(kalau pakai MySQL). Kalau `composer install` protes soal `ext-iconv`
atau `ext-exif`, aktifkan dulu di `php.ini` (cari baris
`;extension=iconv`, hapus titik komanya).

## 2. Instalasi di laptop (Windows, Mac, Linux)

Buka terminal, lalu jalankan satu per satu:

```bash
# 1. Unduh kode
git clone https://github.com/rifqieali/kepandean-vibe.git
cd kepandean-vibe

# 2. Salin contoh konfigurasi
cp .env.example .env

# 3. Pasang paket PHP (agak lama, tunggu sampai selesai)
composer install

# 4. Buat kunci aplikasi
php artisan key:generate

# 5. Siapkan database bawaan (SQLite, tanpa setting apa pun)
touch database/database.sqlite
php artisan migrate --seed

# 6. Sambungkan folder upload agar gambar bisa tampil
php artisan storage:link

# 7. Pasang paket JavaScript lalu build tampilan
npm install
npm run build
```

Windows (PowerShell): ganti langkah 2 dengan `copy .env.example .env`
dan langkah 5 dengan `New-Item database/database.sqlite -ItemType File
-Force`. Sisanya sama.

## 3. Menjalankan aplikasi

```bash
composer dev
```

Perintah itu menyalakan server + antrian + Vite sekaligus. Buka di
browser:

- Halaman publik: http://localhost:8000
- Panel admin: http://localhost:8000/admin

Kalau port 8000 sudah dipakai aplikasi lain, jalankan
`php artisan serve --port=8001` di satu terminal dan `npm run dev` di
terminal lain, lalu buka http://localhost:8001.

Akun bawaan hasil seeder (ganti passwordnya setelah login pertama):

| Peran     | Email                 | Password   |
| --------- | --------------------- | ---------- |
| Admin     | `admin@kepandean.id`  | `password` |
| Editor    | `editor@kepandean.id` | `password` |
| Developer | `admin@developer.dev` | `password` |

## 4. Perintah sehari-hari

```bash
composer dev        # kerja harian (server + antrian + vite)
npm run dev         # hanya vite, untuk utak-atik tampilan
npm run build       # build tampilan untuk produksi
npm run types:check # cek tipe TypeScript, harus bersih tanpa error
php artisan migrate --seed   # isi ulang database dari nol
php artisan storage:link     # perbaiki gambar 404
```

Setiap mengubah file di `resources/js` atau `resources/css`, simpan lalu
lihat hasilnya di browser (dengan `npm run dev` halaman me-refresh
sendiri). Sebelum push, jalankan `npm run build` dan `npm run
types:check`, keduanya harus lolos.

## 5. Deploy untuk pemula

Ada dua jalan. Pilih yang sesuai hosting Anda.

### Jalan A: Hosting dengan akses SSH penuh (disarankan)

Berlaku untuk VPS atau hosting yang memberi terminal SSH dan pilihan
versi PHP.

1. Di server, pasang PHP 8.4, Composer, dan Node.js 20.
2. Clone repo ini ke folder aplikasi, contoh `/var/www/kepandean`.
3. Salin `.env.example` menjadi `.env`, lalu isi:
   `APP_URL` dengan domain asli (contoh `https://kepandean.desa.id`),
   `APP_ENV=production`, `APP_DEBUG=false`, dan kredensial database.
4. Jalankan: `composer install --no-dev`, `php artisan key:generate`,
   `php artisan migrate --force`, `php artisan storage:link`,
   `npm install`, `npm run build`.
5. Arahkan web server (Nginx/Apache) ke folder `public/` repo ini,
   bukan ke akarnya. Beri hak tulis ke `storage/` dan
   `bootstrap/cache/`.
6. Pasang cron untuk scheduler Laravel:
   `* * * * * php /var/www/kepandean/artisan schedule:run`.

### Jalan B: Shared hosting seperti Hostinger (tanpa root)

Ikuti panduan langkah demi langkah yang sudah ada di
[DEPLOY-HOSTINGER.md](DEPLOY-HOSTINGER.md). Intinya: upload file
project, pastikan versi PHP di panel sama dengan SSH (8.4), folder
`public/` yang diakses publik, `.env` diisi manual, lalu
`php artisan migrate --force` dan `php artisan storage:link` lewat SSH.
Kalau mentok di error 403 atau halaman putih, bab troubleshooting di
file itu menjelaskannya satu per satu.

File `Dockerfile`, `render.yaml`, dan folder `docker/` tersedia kalau
Anda deploy ke layanan container.

## 6. Struktur project (biar tidak tersesat)

- `app/`, `routes/`, `config/`, `database/` : backend Laravel, sama
  seperti sumbernya, jangan diubah kecuali paham risikonya.
- `resources/js/pages/welcome.tsx` : halaman beranda (hero, layanan,
  sekilas desa, lokasi, aduan, statistik).
- `resources/js/layouts/public-layout.tsx` : header, navigasi, footer
  halaman publik.
- `resources/css/app.css` : token desain (warna Studio, font Inter)
  di dalam blok `@theme`.
- `DESIGN.md` : arah desain yang dipakai redesign ini.
- `prototype-static/` : prototipe HTML statis awal, hanya arsip.
- `DEPLOY-HOSTINGER.md` : panduan deploy shared hosting yang detail.

## 7. Troubleshooting

| Gejala | Obatnya |
| ------ | ------- |
| `/admin` 404 atau halaman putih | pastikan docroot ke folder `public/`, lalu `php artisan route:clear` dan `php artisan config:clear` |
| `/admin` tampil tanpa gaya (polos) | `php artisan filament:assets`, lalu hard refresh (Ctrl+Shift+R) |
| `/admin` 403 setelah login | akun harus berrole `developer`, `admin_desa`, atau `editor` dan email terverifikasi |
| Mau ganti jalur `/admin` | isi `FILAMENT_PATH` di `.env` (mis. `ruang-admin`), lalu `php artisan config:clear` dan `php artisan route:clear`; robots.txt ikut otomatis |
| `No application encryption key` | `php artisan key:generate` |
| `unable to open database file` | `touch database/database.sqlite` lalu `php artisan migrate` |
| Gambar upload 404 | `php artisan storage:link` |
| Halaman putih setelah pull | `npm run build` (atau `npm run dev` saat ngoding) |
| `composer install` gagal soal ext | aktifkan ekstensi di `php.ini`, lihat Bab 1 |
| `npm run build` gagal soal wayfinder | pastikan `composer install` sudah sukses dulu |
| Port 8000 dipakai | `php artisan serve --port=8001`, sesuaikan `APP_URL` |

Masih buntu? Buka issue di tab Issues repo ini, sertakan pesan error
lengkap dan langkah yang sudah dicoba.
