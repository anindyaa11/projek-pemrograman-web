# JogjaYuk! 🏡🗺️🚗

> **Liburan ke Jogja, Tinggal Klik Aja.**

JogjaYuk! adalah website pemesanan wisata Yogyakarta (terinspirasi fungsionalitas Traveloka) yang menyediakan tiga layanan: **Homestay**, **Paket Wisata**, dan **Rental Kendaraan**. Seluruh website hanya bisa diakses setelah login.

---

## ✨ Fitur

| Fitur | Keterangan |
|---|---|
| 🔐 Autentikasi | Register, login, logout dengan session PHP. Seluruh halaman wajib login |
| 🏷️ Kategori | Chip kategori ala Traveloka di setiap katalog, lengkap dengan jumlah item |
| 🔍 Pencarian & filter | Kata kunci, lokasi, rentang harga, ketersediaan, tanggal check-in/out (homestay), dan urutan harga |
| 📄 Detail layanan | Deskripsi, fasilitas, harga, dan tanggal yang sudah terbooking |
| 🛎️ Pemesanan | Booking homestay (check-in/out), paket wisata (tanggal kunjungan), dan rental (tanggal sewa) |
| 💰 Total otomatis | Total biaya dihitung langsung saat tanggal/jumlah dipilih |
| 🚫 Cek bentrok | Homestay dan kendaraan tidak bisa dibooking pada tanggal yang sama |
| 📋 Riwayat booking | Semua pesanan milik user, dengan filter jenis dan status |
| ❌ Pembatalan | Pesanan berstatus "menunggu" bisa dibatalkan |
| ❤️ Favorit (wishlist) | Simpan homestay, paket, dan kendaraan favorit |
| 👤 Profil | Ubah nama, email, dan password |
| 🔔 Notifikasi | Pesan berhasil/gagal untuk setiap transaksi |
| 🛡️ Keamanan | Prepared statement, CSRF token, `htmlspecialchars`, hash password, redirect aman |

### Aturan akun
- Email wajib **@gmail.com**
- Password minimal **8 karakter** dan mengandung minimal **1 simbol** (contoh: `Jogja@2026`)
- Belum punya akun? Daftar dulu di halaman Register

---

## 🧰 Teknologi

- PHP 8.x (mysqli + prepared statement)
- MySQL / MariaDB
- HTML, CSS, dan JavaScript (tanpa framework)
- Bootstrap Icons (via CDN, untuk ikon di beranda)

---

## 📁 Struktur Folder

```
JogjaYuk/
├── index.php                 # Beranda
├── assets/
│   ├── css/style.css         # Stylesheet utama
│   └── images/               # Foto homestay/paket/rental
├── config/
│   ├── koneksi.php           # Koneksi database ($koneksi)
│   └── helper.php            # Fungsi bersama (auth, CSRF, query, kartu, filter, navbar)
├── database/
│   └── jogjayuk.sql          # Database lengkap (struktur + data)
└── pages/
    ├── login.php, register.php, logout.php
    ├── homestay.php, detailHomestay.php
    ├── paketWisata.php, detailPaket.php
    ├── rental.php, detailRental.php
    ├── riwayatBooking.php, batalBooking.php
    ├── wishlist.php, wishlistToggle.php
    └── profil.php
```

---

## 🚀 Cara Instalasi (XAMPP)

1. **Salin project** ke `C:\xampp\htdocs\JogjaYuk` (atau nama folder lain).
2. **Jalankan** Apache dan MySQL dari XAMPP Control Panel.
3. **Import database:** buka `http://localhost/phpmyadmin` → klik database project (sesuai `config/koneksi.php`) → **Import** → pilih `database/jogjayuk.sql` → **Go**.
   > ⚠️ File ini membuat ulang semua tabel, sehingga data lama di tabel dengan nama yang sama akan diganti.
4. **Cek `config/koneksi.php`**. Contoh isinya:
   ```php
   <?php
   $koneksi = mysqli_connect("localhost", "root", "", "jogjayuk");

   if (!$koneksi) {
       die("Koneksi database gagal: " . mysqli_connect_error());
   }
   mysqli_set_charset($koneksi, "utf8mb4");
   ```
5. **Buka** `http://localhost/JogjaYuk/` → daftar akun → login.

### Foto layanan
Taruh foto di `assets/images/` lalu isi nama filenya di kolom `gambar` pada tabel terkait (mis. `malioboro.jpg`). Jika foto tidak ada, otomatis tampil emoji pengganti.

---

## 🗄️ Database

| Tabel | Fungsi |
|---|---|
| `users` | Akun pengguna (`role`: user/admin) |
| `homestay` | Data homestay + `kategori` |
| `paket_wisata` | Data paket wisata + `kategori` |
| `rental` | Data kendaraan (kategori = `jenis_kendaraan`) |
| `booking` | Pesanan homestay |
| `booking_paket` | Pesanan paket wisata |
| `booking_rental` | Pesanan rental |
| `wishlist` | Item favorit per user |

**Status pesanan:** `menunggu` → `dikonfirmasi` / `dibatalkan`

**Kategori bawaan**
- Homestay: Pusat Kota, Budaya & Heritage, Keluarga, Tenang & Santai
- Paket Wisata: Candi & Budaya, Alam & Pemandangan, Pantai
- Rental: Motor, Mobil, Jeep

Menambah item atau kategori baru cukup dengan `INSERT`/`UPDATE` di tabel terkait. Chip kategori akan muncul otomatis.

---

## 📝 Catatan

- Satu homestay/kendaraan dianggap satu unit, sehingga tanggal yang bentrok akan ditolak.
- Hari sewa rental dihitung inklusif (ambil tgl 10, kembali tgl 12 = 3 hari).
- Paket wisata belum memiliki batas kapasitas.
- Belum ada halaman admin, jadi status "dikonfirmasi" diubah manual lewat database.
- Pesan error login menyebutkan apakah email belum terdaftar atau password salah.

## 🧯 Troubleshooting

| Masalah | Solusi |
|---|---|
| Tampilan polos (tanpa CSS) | Pastikan `assets/css/style.css` ada, buka lewat `http://localhost/...` (bukan file langsung), lalu tekan Ctrl+F5 |
| Error `Unknown column 'kategori'` | Import ulang `database/jogjayuk.sql` |
| Error `Table ... doesn't exist` | Import `database/jogjayuk.sql` |
| Selalu diarahkan ke login | Wajib login; cek cookie/session browser aktif |
| Koneksi database gagal | Cek `config/koneksi.php` (host, user, password, nama DB `jogjayuk`) |

---

© 2026 JogjaYuk! — Liburan ke Jogja, Tinggal Klik Aja.
