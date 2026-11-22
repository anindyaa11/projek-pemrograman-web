# 📖 Dokumentasi Fungsi - JogjaYuk!

Dokumen ini menjelaskan fungsi-fungsi yang dipakai di program JogjaYuk!, mulai dari fungsi bantu di `config/helper.php`, alur tiap halaman, sampai fungsi JavaScript dan fungsi bawaan PHP yang dipakai.

**Daftar isi**
1. [Cara fungsi dipakai](#1-cara-fungsi-dipakai)
2. [Fungsi di `config/helper.php`](#2-fungsi-di-confighelperphp)
3. [Alur tiap halaman](#3-alur-tiap-halaman)
4. [Fungsi JavaScript](#4-fungsi-javascript)
5. [Fungsi bawaan PHP yang dipakai](#5-fungsi-bawaan-php-yang-dipakai)

---

## 1. Cara fungsi dipakai

Setiap halaman di folder `pages/` memanggil dua file di bagian atas:

```php
include "../config/koneksi.php";   // membuat variabel $koneksi (koneksi MySQL)
include "../config/helper.php";    // memuat semua fungsi bantu + memulai session
```

`helper.php` otomatis menjalankan `session_start()` bila session belum aktif, sehingga `$_SESSION` bisa langsung dipakai.

---

## 2. Fungsi di `config/helper.php`

### 2.1 Fungsi umum

| Fungsi | Kegunaan | Mengembalikan |
|---|---|---|
| `e($s)` | Mengamankan teks sebelum dicetak ke HTML (`htmlspecialchars`), mencegah XSS | string aman |
| `rupiah($n)` | Memformat angka jadi mata uang, `350000` → `Rp 350.000` | string |
| `tanggalValid($t)` | Mengecek string berformat `YYYY-MM-DD` dan benar-benar tanggal yang ada (mis. `2026-02-30` ditolak) | `true`/`false` |
| `sudahLogin()` | Mengecek apakah `$_SESSION['user_id']` ada | `true`/`false` |
| `halamanSekarang()` | Nama file halaman + query string saat ini, mis. `detailHomestay.php?id=3` | string |
| `urlAman($url, $default)` | Memvalidasi alamat tujuan redirect. Hanya menerima pola `namafile.php` atau `namafile.php?query`. Jika tidak cocok, memakai `$default`. Mencegah *open redirect* | string |
| `wajibLogin()` | Jika belum login, mengalihkan ke `login.php?redirect=<halaman sekarang>` lalu `exit` | - |

Contoh:
```php
echo e($row['nama_homestay']);        // aman dari XSS
echo rupiah($row['harga_per_malam']); // Rp 350.000
wajibLogin();                         // letakkan di atas halaman yang butuh login
```

### 2.2 Notifikasi (flash message)

Flash message disimpan di session lalu ditampilkan **satu kali** pada halaman berikutnya (dipakai setelah redirect).

| Fungsi | Kegunaan |
|---|---|
| `setFlash($tipe, $pesan)` | Menyimpan pesan. `$tipe` = `'sukses'` atau `'gagal'` |
| `tampilFlash()` | Menampilkan pesan tersimpan dalam kotak `.total-harga` (✅ untuk sukses, ⚠️ untuk gagal), lalu menghapusnya dari session |
| `tampilPesan($pesan, $sukses = false)` | Menampilkan pesan langsung (tanpa session), dipakai untuk error validasi pada halaman yang sama |

Contoh alur:
```php
setFlash('sukses', 'Booking berhasil!');
header("Location: riwayatBooking.php");   // halaman tujuan memanggil tampilFlash()
exit;
```

### 2.3 Keamanan CSRF

CSRF adalah serangan yang membuat browser user mengirim form tanpa sepengetahuannya. Pencegahannya memakai token acak per session.

| Fungsi | Kegunaan |
|---|---|
| `csrfToken()` | Membuat (jika belum ada) dan mengembalikan token acak 64 karakter (`random_bytes`) |
| `csrfField()` | Mengembalikan `<input type="hidden" name="csrf" ...>` untuk ditaruh di dalam form |
| `csrfValid()` | Membandingkan `$_POST['csrf']` dengan token di session memakai `hash_equals` (aman dari timing attack) |

Pemakaian: setiap form `POST` memuat `<?php echo csrfField(); ?>`, dan penerimanya memeriksa `if (!csrfValid()) { ... }`.

### 2.4 Fungsi database (prepared statement)

Ketiganya membungkus `mysqli_prepare` + `bind_param` supaya aman dari SQL injection dan kodenya ringkas. Parameter `$types` memakai kode mysqli: `i` = integer, `s` = string, `d` = desimal.

| Fungsi | Kegunaan | Mengembalikan |
|---|---|---|
| `qAll($k, $sql, $types, $params)` | Menjalankan `SELECT` dan mengambil **semua baris** | array of array (kosong bila tidak ada) |
| `qOne($k, $sql, $types, $params)` | Menjalankan `SELECT` dan mengambil **satu baris pertama** | array atau `null` |
| `qExec($k, $sql, $types, $params)` | Menjalankan `INSERT` / `UPDATE` / `DELETE` | jumlah baris yang terpengaruh (`affected_rows`) |

Contoh:
```php
// satu baris
$h = qOne($koneksi, "SELECT * FROM homestay WHERE id = ?", 'i', [$id]);

// banyak baris
$daftar = qAll($koneksi, "SELECT * FROM rental WHERE harga_per_hari <= ?", 'd', [200000]);

// ubah data, hasilnya jumlah baris terpengaruh
$n = qExec($koneksi, "UPDATE users SET nama = ? WHERE id = ?", 'si', [$nama, $uid]);
```

> Jika query gagal (mis. tabel tidak ada), mysqli di PHP 8.1+ melempar exception. Karena itu proses simpan data dibungkus `try { ... } catch (Exception $ex) { ... }` agar user mendapat pesan yang ramah.

### 2.5 Navbar & footer

| Fungsi | Kegunaan |
|---|---|
| `navAkun($prefix = '')` | Mencetak area akun di navbar. Jika login: "Halo, *nama*!" (link ke profil) + tombol Logout. Jika belum: tombol Login. `$prefix` = `'pages/'` bila dipanggil dari `index.php` |
| `navbarHalaman($aktif, $logo, $extraMenu)` | Mencetak seluruh header navbar. `$aktif` salah satu dari `homestay`, `paket`, `rental`, `riwayat` untuk menandai menu yang sedang aktif |
| `footerHalaman()` | Mencetak footer standar |

Contoh: `navbarHalaman('homestay');`

### 2.6 Wishlist (favorit)

| Fungsi | Kegunaan | Mengembalikan |
|---|---|---|
| `ambilWishlist($k, $tipe)` | Mengambil id item yang difavoritkan user untuk satu tipe (`homestay`/`paket`/`rental`). Jika belum login → array kosong | array integer |
| `tombolWishlist($tipe, $id, $aktif)` | Membuat form kecil berisi tombol **❤️ Favorit** (jika sudah difavoritkan) atau **🤍 Simpan**. Dikirim ke `wishlistToggle.php` | string HTML |

### 2.7 Kartu produk

| Fungsi | Kegunaan |
|---|---|
| `emojiPaket($nama)` | Memilih emoji pengganti foto untuk paket (🏛️ candi, 🏖️ pantai, 🏞️ lainnya) berdasarkan kata di nama |
| `emojiRental($jenis)` | Memilih emoji untuk kendaraan (🛵 motor, 🚙 jeep, 🚗 lainnya) |
| `metaItem($tipe, $r)` | Menyeragamkan perbedaan tiap tipe menjadi satu array: `judul`, `harga`, `satuan` (malam/orang/hari), `detail` (nama file detail), `emoji`, `kelas` |
| `kartuProduk($tipe, $r, $favIds, $qs)` | Mencetak satu kartu lengkap: foto (atau emoji jika file foto tidak ada), label kategori, nama, lokasi, deskripsi, harga, tombol **Lihat Detail**, dan tombol favorit. `$favIds` = hasil `ambilWishlist`, `$qs` = query tambahan untuk link detail (mis. tanggal) |

Satu fungsi `kartuProduk` dipakai oleh halaman homestay, paket, rental, dan favorit, jadi tampilan kartunya seragam.

### 2.8 Pencarian, filter, dan urutan

| Fungsi | Kegunaan |
|---|---|
| `bangunFilter($kolomCari, $kolomHarga, $kolomJenis)` | Membaca `$_GET` lalu menyusun kondisi `WHERE` secara dinamis. Mengembalikan `[$where, $types, $params]` |
| `urutan($kolomHarga)` | Mengembalikan teks `ORDER BY` dari `$_GET['sort']` lewat daftar putih (whitelist): `termurah`, `termahal`, `terbaru`, default `id ASC` |
| `formFilter($lokasiList, $tanggal, $jenisList)` | Mencetak kotak **Cari & Filter** (kata kunci, lokasi, harga, tanggal opsional, ketersediaan, urutan, tombol Cari/Reset) |

Parameter GET yang dibaca `bangunFilter`:

| Parameter | Efek |
|---|---|
| `q` | Pencarian `LIKE` ke semua kolom di `$kolomCari` |
| `lokasi` | Cocok persis dengan kolom `lokasi` |
| `kategori` | Cocok dengan `$kolomJenis` (kolom `kategori`, atau `jenis_kendaraan` untuk rental) |
| `harga_min`, `harga_max` | Rentang harga (harus numerik) |
| `status` | Default hanya yang `tersedia`; `semua` menampilkan seluruhnya |

Contoh pemakaian (disederhanakan dari `homestay.php`):
```php
list($w, $t, $p) = bangunFilter(['nama_homestay','lokasi'], 'harga_per_malam', 'kategori');
$sql = "SELECT * FROM homestay" . ($w ? ' WHERE ' . implode(' AND ', $w) : '')
     . ' ORDER BY ' . urutan('harga_per_malam');
$daftar = qAll($koneksi, $sql, $t, $p);
```
Nilai dari user tidak pernah ditempel langsung ke SQL. Semuanya lewat tanda `?` dan `bind_param`. Hanya nama kolom (yang berasal dari kode, bukan input) yang disisipkan ke teks SQL.

### 2.9 Kategori (chip)

| Fungsi | Kegunaan |
|---|---|
| `emojiKategori($n)` | Mengembalikan emoji untuk nama kategori (🏙️ Pusat Kota, 🏛️ Budaya & Heritage, 👨‍👩‍👧 Keluarga, 🌿 Tenang & Santai, 🏛️ Candi & Budaya, 🏞️ Alam & Pemandangan, 🏖️ Pantai, 🛵 Motor, 🚗 Mobil, 🚙 Jeep). Kategori baru → 🏷️ |
| `barKategori($daftar)` | Mencetak baris chip kategori. `$daftar` berisi `['nama' => ..., 'n' => jumlah]`. Chip **Semua** + satu chip per kategori; chip yang sedang dipilih diberi kelas `aktif`. Link chip mempertahankan filter lain (`http_build_query`) |

### 2.10 Validasi akun

Keduanya mengembalikan **string kosong `''` jika valid**, atau **pesan error** jika tidak.

| Fungsi | Aturan |
|---|---|
| `validasiEmailGmail($email)` | Format email valid (`FILTER_VALIDATE_EMAIL`), maksimal 100 karakter, dan berakhiran `@gmail.com` |
| `validasiPassword($p)` | Minimal 8 karakter dan mengandung minimal 1 simbol (karakter selain huruf, angka, dan spasi) |

Contoh:
```php
if (($err = validasiPassword($password)) !== '') {
    $pesan = $err;   // tampilkan ke user
}
```

---

## 3. Alur tiap halaman

### `index.php`
Mengecek `$_SESSION['user_id']`. Jika belum login → redirect ke `pages/login.php?wajib=1`. Jika sudah → menampilkan beranda.

### `pages/login.php`
1. Jika sudah login → langsung dialihkan.
2. Membaca `?redirect=` (divalidasi `urlAman`) sebagai tujuan setelah login.
3. Saat `POST`: cari user berdasarkan email (prepared statement).
   - Email tidak ada → "Email belum terdaftar..."
   - `password_verify` gagal → "Password salah!"
   - Berhasil → `session_regenerate_id(true)` (cegah *session fixation*), simpan `user_id`, `nama`, `role` di session, lalu redirect.

### `pages/register.php`
Validasi berurutan: semua kolom terisi → panjang nama → `validasiEmailGmail` → `validasiPassword` → email belum dipakai. Password disimpan dengan `password_hash(..., PASSWORD_DEFAULT)`. Berhasil → redirect ke `login.php?daftar=1`.

### `pages/logout.php`
Mengosongkan `$_SESSION`, menghapus cookie session, `session_destroy()`, lalu kembali ke beranda (yang otomatis mengarah ke login).

### `pages/homestay.php`, `paketWisata.php`, `rental.php` (katalog)
`wajibLogin()` → `bangunFilter` → susun SQL + `urutan` → `qAll` → ambil daftar lokasi dan kategori untuk filter/chip → `ambilWishlist` → cetak `barKategori`, `formFilter`, lalu `kartuProduk` untuk tiap baris.
Khusus homestay: bila `checkin` & `checkout` valid, ditambah subquery `id NOT IN (SELECT homestay_id FROM booking WHERE status <> 'dibatalkan' AND tanggal_checkin < checkout AND tanggal_checkout > checkin)` sehingga hanya homestay yang kosong pada tanggal itu yang tampil.

### `pages/detailHomestay.php`
Saat `POST` melalui langkah-langkah:
1. `wajibLogin()` dan `csrfValid()`
2. Homestay berstatus `tersedia`
3. Validasi nama, tanggal (`tanggalValid`), jumlah tamu
4. `check-in` tidak boleh sebelum hari ini dan `check-out` harus setelah `check-in`
5. Hitung malam: `DateTime::diff()->days`; total = harga × malam
6. **Cek bentrok** (`COUNT(*)` booking aktif yang tanggalnya beririsan)
7. `INSERT` ke `booking` dengan `user_id` → `setFlash` → redirect ke riwayat

Syarat beririsan: `checkin_lama < checkout_baru` **dan** `checkout_lama > checkin_baru`.

### `pages/detailPaket.php`
Sama polanya, tetapi tanpa cek bentrok. Total = `harga_per_orang × jumlah_orang`, disimpan ke `booking_paket`.

### `pages/detailRental.php`
Sama polanya. Jumlah hari dihitung **inklusif** (`diff + 1`). Cek bentrok memakai `tanggal_mulai <= selesai_baru AND tanggal_selesai >= mulai_baru`. Disimpan ke `booking_rental`.

### `pages/riwayatBooking.php`
Mengambil pesanan user dari tiga tabel (`booking`, `booking_paket`, `booking_rental`) dengan `JOIN` ke tabel layanannya, menyeragamkan ke satu array (`jenis`, `kode`, `judul`, `info`, `total`, `status`), mengurutkan dengan `usort` (terbaru dulu), lalu menyaring dengan `array_filter` berdasarkan `?jenis=` dan `?status=`. Kode booking berformat `HS-0001` (homestay), `PW-0001` (paket), `RK-0001` (rental). Tombol **Batalkan Pesanan** hanya muncul bila status `menunggu`.

### `pages/batalBooking.php`
Hanya menerima `POST` + CSRF valid. Nama tabel dipilih dari **daftar putih** (`homestay→booking`, `paket→booking_paket`, `rental→booking_rental`), tidak pernah dari input langsung. Query:
`UPDATE ... SET status='dibatalkan' WHERE id=? AND user_id=? AND status='menunggu'`
Klausa `user_id` memastikan user hanya bisa membatalkan pesanan miliknya.

### `pages/wishlistToggle.php`
Fungsi *toggle*: mencoba `DELETE` dulu; jika tidak ada baris terhapus (belum difavoritkan) → `INSERT`. Tipe item divalidasi dengan daftar putih dan keberadaan item dicek sebelum disimpan. Setelah itu kembali ke halaman asal (`urlAman`).

### `pages/wishlist.php`
Tiga query `JOIN` antara `wishlist` dan tabel layanan, lalu menampilkan hasilnya per kategori layanan dengan `kartuProduk`.

### `pages/profil.php`
Satu halaman, dua form yang dibedakan oleh input tersembunyi `aksi`:
- `aksi=profil` → validasi nama + `validasiEmailGmail` + cek email tidak dipakai akun lain (`id <> ?`) → `UPDATE`, lalu menyegarkan `$_SESSION['nama']`.
- `aksi=password` → `password_verify` password lama → `validasiPassword` → cek konfirmasi → `password_hash` baru → `UPDATE`.

---

## 4. Fungsi JavaScript

Terdapat di halaman detail untuk menghitung estimasi total secara langsung (tanpa reload). Perhitungan akhir yang disimpan tetap dihitung ulang di server.

| Halaman | Fungsi | Cara kerja |
|---|---|---|
| `detailHomestay.php` | `hitung()` | Mengatur `min` tanggal check-out = check-in + 1 hari, menghitung selisih hari, menampilkan `N malam × Rp harga = Rp total` |
| `detailPaket.php` | `hitung()` | Menampilkan `N orang × Rp harga = Rp total` saat jumlah orang diubah |
| `detailRental.php` | `hitung()` | Mengatur `min` tanggal selesai = tanggal mulai, menghitung hari inklusif, menampilkan total |

Fungsi dipasang lewat `addEventListener('change' / 'input', hitung)`. Format rupiah memakai `toLocaleString('id-ID')`.

Selain itu, atribut `onsubmit="return confirm(...)"` pada tombol **Batalkan Pesanan** memunculkan dialog konfirmasi sebelum form dikirim.

---

## 5. Fungsi bawaan PHP yang dipakai

| Fungsi | Dipakai untuk |
|---|---|
| `session_start()`, `session_regenerate_id()`, `session_destroy()` | Mengelola session login |
| `password_hash()`, `password_verify()` | Menyimpan dan memeriksa password secara aman (bcrypt) |
| `mysqli_prepare()`, `mysqli_stmt_bind_param()`, `mysqli_stmt_execute()`, `mysqli_stmt_get_result()`, `mysqli_fetch_all()` | Prepared statement |
| `htmlspecialchars()` | Mencegah XSS |
| `filter_var(..., FILTER_VALIDATE_EMAIL)`, `preg_match()` | Validasi email dan password |
| `random_bytes()`, `bin2hex()`, `hash_equals()` | Token CSRF |
| `DateTime`, `DateTime::createFromFormat()`, `->diff()` | Validasi tanggal dan hitung selisih hari |
| `number_format()` | Format angka rupiah |
| `header("Location: ...")` + `exit` | Redirect |
| `http_build_query()`, `urlencode()` | Menyusun link filter/redirect |
| `usort()`, `array_filter()`, `array_column()`, `array_map()` | Mengolah array hasil query |
| `file_exists()`, `basename()` | Mengecek file foto dan mencegah path traversal |

---

© 2026 JogjaYuk! — dokumentasi fungsi program.
