# Deploy ke Hostinger (Shared Hosting + SSH) — untuk Pemula

Panduan ini untuk orang awam. Ikuti **berurutan dari nomor 1 sampai 8**.
Tidak perlu paham semua teori, cukup ikuti perintahnya.

> Syarat project ini: **PHP 8.4+** (`composer.json` butuh `php ^8.4`,
> Laravel 13 + Symfony v8 butuh `>= 8.4.1`).
> Kalau paket Hostinger kamu tidak menyediakan PHP 8.4, panduan ini tidak bisa dipakai.

---

## 0. Analogi 1 menit (penting!)

Bayangkan rumah:

- `kepandean/` = **isi rumah** (kamar, dapur, brankas `.env`). Browser **tidak boleh** masuk sini.
- `kepandean/public/` = **teras depan**. Hanya ini yang boleh dilihat browser.
- `public_html` = **papan penunjuk** ke teras depan.

```
~/domains/namadomain.com/
  kepandean/              <- PROJECT, tempat composer.json, artisan, .env
    public/               <- WEB, cuma index.php, .htaccess, build/
  public_html -> kepandean/public
```

**Salah (penyebab 403 + .env bisa bocor):**

```
~/domains/namadomain.com/
  public_html/
    artisan
    composer.json
    .env        <- bahaya!
    app/
    public/
```

Kalau `public_html` berisi `artisan` / `composer.json`, kamu salah. Perbaikannya ada di **Langkah 3**.

---

## 1. Siapkan 3 hal di hPanel

1. **SSH Access:** Websites > pilih domain > SSH Access > Enable.
   Catat `SSH Host`, `Port` (biasanya `65002`), `Username` (contoh `u889473689`).
2. **Database:** Databases > buat DB + user + password.
   Catat `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Host biasanya `localhost`.
3. **PHP:** Websites > PHP Configuration > pilih **8.4** (atau 8.5 kalau ada).
   Centang ekstensi: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `bcmath`, `intl`, `exif`, `fileinfo`.

---

## 2. Masuk lewat SSH + temukan PHP 8.4 yang benar

PHP di hPanel **beda** dengan PHP di SSH. hPanel 8.5 belum tentu SSH ikut 8.5.

```bash
ssh -p 65002 u889473689@xx.hostinger.com

php -v
# kalau keluar 8.3.x, JANGAN lanjut pakai php polos. Cari dulu:
which -a php
ls /opt/alt/php*/usr/bin/php
/opt/alt/php84/usr/bin/php -v
```

Sesuaikan path di bawah dengan yang ketemu di server kamu.
Contoh di panduan ini pakai `/opt/alt/php84/usr/bin/php`.
Kalau ketemunya `php85`, ganti semua `php84` jadi `php85`.

Biar pendek, bikin alias sekali tiap login:

```bash
alias php84='/opt/alt/php84/usr/bin/php'
php84 -v
# wajib 8.4.1+
```

Cari composer:

```bash
which composer
# contoh: /usr/local/bin/composer
```

---

## 3. Clone project (struktur folder yang benar)

```bash
cd ~/domains/namadomain.com
ls -la

# Hapus/rename public_html bawaan yang kosong (jangan hapus kalau sudah ada isinya!)
# mv public_html public_html_old

git clone https://github.com/rifqieali/kepandean-laravel.git kepandean
# kalau repo private:
# git clone https://<USERNAME>:<PAT_TOKEN>@github.com/rifqieali/kepandean-laravel.git kepandean

ls kepandean/composer.json
```

**Kalau terlanjur salah** (isi `public_html` = isi Laravel: ada `artisan`, `composer.json`):

```bash
cd ~/domains/namadomain.com
ls public_html/artisan public_html/composer.json public_html/public/index.php
mv public_html kepandean
ln -s kepandean/public public_html
ls -l
# harusnya: kepandean (folder) + public_html -> kepandean/public
```

Lalu arahkan domain ke teras depan. Pilih **salah satu**:

- **A (disarankan):** hPanel > Document Root > ubah ke `kepandean/public`, atau
- **B:** lewat SSH seperti di atas:

```bash
cd ~/domains/namadomain.com
ln -s kepandean/public public_html
```

Cek:

```bash
ls -l ~/domains/namadomain.com/
ls ~/domains/namadomain.com/kepandean/public/index.php ~/domains/namadomain.com/kepandean/public/.htaccess
```

---

## 4. Build frontend di LAPTOP, jangan di server

Project ini pakai Inertia React + Wayfinder. `npm run build` butuh PHP + `vendor/`
dan berat untuk Shared Hosting. Cara aman untuk pemula:

Di laptop:

```bash
npm ci
npm run build
php artisan filament:assets
git add public/build -f
git commit -m "build assets for hostinger"
git push origin main
```

Di server tinggal `git pull`, tidak perlu `npm` sama sekali.

---

## 5. Isi `.env` production

```bash
cd ~/domains/namadomain.com/kepandean
cp .env.example .env
nano .env
```

Ubah minimal ini (contoh):

```env
APP_NAME="Kepandean"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://namadomain.com
LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u889473689_kepandean
DB_USERNAME=u889473689_user
DB_PASSWORD=isi_dari_hpanel

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

> `QUEUE_CONNECTION=sync` paling aman di Shared Hosting.
> Pakai `database` hanya kalau kamu siap menjalankan worker/cron sendiri.
> `DB_HOST` di Hostinger biasanya `localhost`, bukan `127.0.0.1`. Coba `localhost` dulu.

Simpan: `Ctrl+O`, `Enter`, `Ctrl+X`.

---

## 6. Install + migrate (perintah wajib berurutan)

**Harus** dijalankan dari folder project (`.../kepandean`), bukan dari home.
Kalau error `Composer could not find a composer.json file in /home/...`,
artinya kamu lupa `cd`.

```bash
cd ~/domains/namadomain.com/kepandean
pwd
# harus berakhiran .../kepandean
ls composer.json

/opt/alt/php84/usr/bin/php $(which composer) install --no-dev --optimize-autoloader
/opt/alt/php84/usr/bin/php artisan key:generate --force

chmod -R 775 storage bootstrap/cache
rm -f public/storage
/opt/alt/php84/usr/bin/php artisan storage:link

/opt/alt/php84/usr/bin/php artisan migrate --force
# kalau butuh data awal dan kamu yakin ada seedernya:
# /opt/alt/php84/usr/bin/php artisan db:seed --force

/opt/alt/php84/usr/bin/php artisan filament:assets
/opt/alt/php84/usr/bin/php artisan optimize
```

Kalau RAM kecil dan composer kehabisan memori, tambah `memory_limit`:

```bash
/opt/alt/php84/usr/bin/php -d memory_limit=512M $(which composer) install --no-dev --optimize-autoloader
```

---

## 7. Rapikan permission + cron (opsional)

```bash
cd ~/domains/namadomain.com/kepandean
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
```

Cron hanya kalau project memakai scheduler Laravel.
hPanel > Cron Jobs > tiap menit:

```
/usr/bin/php /home/u889473689/domains/namadomain.com/kepandean/artisan schedule:run >> /dev/null 2>&1
```

Ganti `/usr/bin/php` dengan path PHP 8.4 yang ketemu di Langkah 2,
dan ganti username + domain dengan milikmu. Cek path dengan `which php`.

---

## 8. Update berikutnya (deploy ulang)

```bash
cd ~/domains/namadomain.com/kepandean
git pull origin main
/opt/alt/php84/usr/bin/php $(which composer) install --no-dev --optimize-autoloader
/opt/alt/php84/usr/bin/php artisan migrate --force
/opt/alt/php84/usr/bin/php artisan filament:assets
/opt/alt/php84/usr/bin/php artisan optimize
```

Habis edit `.env`:

```bash
/opt/alt/php84/usr/bin/php artisan optimize:clear
/opt/alt/php84/usr/bin/php artisan optimize
```

---

## Troubleshooting (pesan error -> artinya -> obatnya)

| Pesan | Artinya | Obat |
|---|---|---|
| `Composer could not find a composer.json file in /home/...` | Salah folder, belum `cd` ke project | `cd ~/domains/namadomain.com/kepandean` lalu ulangi. `pwd` harus berakhiran `kepandean` |
| `Root composer.json requires php ^8.4 but your php version (8.3.x)...` + 20 baris `symfony/... requires php >=8.4.1` | `php` polos masih 8.3, hPanel tidak mengubah CLI | Jangan pakai `php` polos. Pakai `/opt/alt/php84/usr/bin/php $(which composer) ...` dan `.../php artisan ...` |
| `Could not open input file: composer.phar` | `composer.phar` tidak ada di folder itu | Itu nama file, bukan perintah. Pakai `$(which composer)`: `/opt/alt/php84/usr/bin/php $(which composer) install ...` |
| Browser `403 Forbidden` | Document root salah (browser buka project root, bukan `public/`) | Pastikan `public_html -> kepandean/public`, dan `kepandean/public/index.php` ada. Lihat Langkah 3 |
| Browser `500` / halaman putih | `.env` / DB / key / permission | `tail -n 100 storage/logs/laravel.log`, cek `APP_KEY` sudah ada (`grep APP_KEY .env`), cek `chmod -R 775 storage bootstrap/cache` |
| `SQLSTATE[HY000] [2002]` | `DB_HOST` / user / password salah | Coba `DB_HOST=localhost` dulu, cek ulang user + password dari hPanel Databases |
| `vite manifest not found` / CSS hilang | Belum build frontend | Build di laptop (`npm run build`) lalu push `public/build`, `git pull` di server |
| Filament tanpa CSS | Lupa assets Filament | `/opt/alt/php84/usr/bin/php artisan filament:assets` |
| Gambar/upload 404 | Symlink storage putus (habis pindah folder) | `rm -f public/storage` lalu `php artisan storage:link` pakai PHP 8.4 |
| `Allowed memory size exhausted` saat composer | RAM Shared kecil | Tambah `-d memory_limit=512M` seperti Langkah 6 |

Masih buntu? Kirim 3 output ini:

```bash
pwd; ls -l ~/domains/namadomain.com/ | head -20
ls ~/domains/namadomain.com/kepandean/public/index.php ~/domains/namadomain.com/kepandean/public/.htaccess
/opt/alt/php84/usr/bin/php -v; /opt/alt/php84/usr/bin/php $(which composer) --version
```
