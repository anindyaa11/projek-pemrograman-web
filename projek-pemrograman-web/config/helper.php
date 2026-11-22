<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- Umum ---------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function tanggalValid($t) {
    $d = DateTime::createFromFormat('Y-m-d', (string)$t);
    return $d && $d->format('Y-m-d') === $t;
}
function sudahLogin() { return isset($_SESSION['user_id']); }
function halamanSekarang() {
    return basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
}
function urlAman($url, $default) {
    return (is_string($url) && preg_match('/^[A-Za-z]+\.php(\?[A-Za-z0-9_=&%.+\-]*)?$/', $url)) ? $url : $default;
}
function wajibLogin() {
    if (!sudahLogin()) {
        header('Location: login.php?redirect=' . urlencode(halamanSekarang()));
        exit;
    }
}

/* ---------- Notifikasi (flash) ---------- */
function setFlash($tipe, $pesan) { $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan]; }
function tampilFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="total-harga"><p>' . ($f['tipe'] === 'sukses' ? '✅ ' : '⚠️ ') . e($f['pesan']) . '</p></div><br>';
    }
}
function tampilPesan($pesan, $sukses = false) {
    if ($pesan !== '') {
        echo '<div class="total-harga"><p>' . ($sukses ? '✅ ' : '⚠️ ') . e($pesan) . '</p></div><br>';
    }
}

/* ---------- CSRF ---------- */
function csrfToken() {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrfField() { return '<input type="hidden" name="csrf" value="' . csrfToken() . '">'; }
function csrfValid() {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/* ---------- Database ---------- */
function qAll($k, $sql, $types = '', $params = []) {
    $s = mysqli_prepare($k, $sql);
    if ($types !== '') { mysqli_stmt_bind_param($s, $types, ...$params); }
    mysqli_stmt_execute($s);
    $r = mysqli_stmt_get_result($s);
    return $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
}
function qOne($k, $sql, $types = '', $params = []) {
    $a = qAll($k, $sql, $types, $params);
    return $a[0] ?? null;
}
function qExec($k, $sql, $types, $params) {
    $s = mysqli_prepare($k, $sql);
    mysqli_stmt_bind_param($s, $types, ...$params);
    mysqli_stmt_execute($s);
    return mysqli_stmt_affected_rows($s);
}

/* ---------- Navbar & footer ---------- */
function navAkun($prefix = '') {
    if (sudahLogin()) {
        echo '<div class="menu-akun"><span>Halo, <a href="' . $prefix . 'profil.php" title="Profil Saya" style="color:inherit;text-decoration:none">'
            . e($_SESSION['nama']) . '</a>!</span> <a href="' . $prefix . 'logout.php" class="tombol-login">Logout</a></div>';
    } else {
        echo '<a href="' . $prefix . 'login.php" class="tombol-login">Login</a>';
    }
}
function navbarHalaman($aktif = '', $logo = 'JogjaYuk!', $extraMenu = '') {
    $m = ['homestay' => ['homestay.php', 'Homestay'], 'paket' => ['paketWisata.php', 'Paket Wisata'], 'rental' => ['rental.php', 'Rental'],
          'riwayat' => ['riwayatBooking.php', 'Riwayat Booking']];
    echo '<header class="navbar"><a href="../index.php" class="logo">' . $logo . '</a><nav class="menu"><a href="../index.php">Beranda</a>';
    foreach ($m as $k => $v) {
        echo '<a href="' . $v[0] . '"' . ($aktif === $k ? ' class="aktif"' : '') . '>' . $v[1] . '</a>';
    }
    echo $extraMenu . '</nav>';
    navAkun();
    echo '</header>';
}
function footerHalaman() { ?>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-identitas">
                <h2>JogjaYuk!</h2>
                <p>Liburan ke Jogja, Tinggal Klik Aja.</p>
                <p>Temukan pengalaman terbaik untuk perjalananmu di Yogyakarta.</p>
            </div>
            <div class="footer-navigasi">
                <h3>Navigasi</h3>
                <a href="../index.php">Beranda</a>
                <a href="homestay.php">Homestay</a>
                <a href="paketWisata.php">Paket Wisata</a>
                <a href="rental.php">Rental Kendaraan</a>
            </div>
        </div>
        <div class="footer-bawah"><p>&copy; 2026 JogjaYuk! Semua hak dilindungi.</p></div>
    </footer>
<?php }

/* ---------- Wishlist ---------- */
function ambilWishlist($k, $tipe) {
    if (!sudahLogin()) { return []; }
    $r = qAll($k, "SELECT item_id FROM wishlist WHERE user_id = ? AND tipe = ?", 'is', [$_SESSION['user_id'], $tipe]);
    return array_map('intval', array_column($r, 'item_id'));
}
function tombolWishlist($tipe, $id, $aktif) {
    return '<form method="POST" action="wishlistToggle.php" style="display:inline">' . csrfField()
        . '<input type="hidden" name="tipe" value="' . e($tipe) . '">'
        . '<input type="hidden" name="item_id" value="' . (int)$id . '">'
        . '<input type="hidden" name="back" value="' . e(halamanSekarang()) . '">'
        . '<button type="submit" class="tombol-detail" style="border:none;cursor:pointer;margin-top:0">'
        . ($aktif ? '❤️ Favorit' : '🤍 Simpan') . '</button></form>';
}

/* ---------- Kartu produk ---------- */
function emojiPaket($nama) {
    $n = strtolower($nama);
    if (strpos($n, 'borobudur') !== false || strpos($n, 'candi') !== false) return '🏛️';
    if (strpos($n, 'pantai') !== false) return '🏖️';
    return '🏞️';
}
function emojiRental($jenis) {
    $j = strtolower($jenis);
    if (strpos($j, 'motor') !== false) return '🛵';
    if (strpos($j, 'jeep') !== false) return '🚙';
    return '🚗';
}
function metaItem($tipe, $r) {
    if ($tipe === 'homestay') {
        return ['judul' => $r['nama_homestay'], 'harga' => $r['harga_per_malam'], 'satuan' => 'malam',
                'detail' => 'detailHomestay.php', 'emoji' => '🏡', 'kelas' => 'gambar-produk gambar-homestay'];
    }
    if ($tipe === 'paket') {
        return ['judul' => $r['nama_paket'], 'harga' => $r['harga_per_orang'], 'satuan' => 'orang',
                'detail' => 'detailPaket.php', 'emoji' => emojiPaket($r['nama_paket']), 'kelas' => 'gambar-produk'];
    }
    return ['judul' => $r['nama_kendaraan'], 'harga' => $r['harga_per_hari'], 'satuan' => 'hari',
            'detail' => 'detailRental.php', 'emoji' => emojiRental($r['jenis_kendaraan']), 'kelas' => 'gambar-produk'];
}
function kartuProduk($tipe, $r, $favIds, $qs = '') {
    $m = metaItem($tipe, $r);
    $g = basename((string)($r['gambar'] ?? ''));
    echo '<article class="kartu-produk">';
    if ($g !== '' && file_exists(__DIR__ . '/../assets/images/' . $g)) {
        echo '<img src="../assets/images/' . e($g) . '" alt="' . e($m['judul']) . '" class="gambar-produk">';
    } else {
        echo '<div class="' . $m['kelas'] . '">' . $m['emoji'] . '</div>';
    }
    echo '<div class="isi-produk">';
    if (!empty($r['kategori'])) {
        echo '<span class="badge-kategori">' . emojiKategori($r['kategori']) . ' ' . e($r['kategori']) . '</span>';
    }
    echo '<h2>' . e($m['judul']) . '</h2><p>📍 ' . e($r['lokasi']) . '</p>';
    if ($tipe === 'rental') echo '<p>🚘 ' . e($r['jenis_kendaraan']) . '</p>';
    if ($tipe === 'paket' && !empty($r['durasi'])) echo '<p>🕒 ' . e($r['durasi']) . '</p>';
    echo '<p>' . e($r['deskripsi']) . '</p>';
    if ($r['status'] !== 'tersedia') echo '<p>⛔ Sedang tidak tersedia</p>';
    echo '<h3>' . rupiah($m['harga']) . '/' . $m['satuan'] . '</h3>';
    echo '<a href="' . $m['detail'] . '?id=' . (int)$r['id'] . $qs . '" class="tombol-detail">Lihat Detail</a> ';
    echo tombolWishlist($tipe, $r['id'], in_array((int)$r['id'], $favIds, true));
    echo '</div></article>';
}

/* ---------- Pencarian & filter ---------- */
function bangunFilter(array $kolomCari, $kolomHarga, $kolomJenis = '') {
    $w = []; $t = ''; $p = [];
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $or = [];
        foreach ($kolomCari as $k) { $or[] = "$k LIKE ?"; $t .= 's'; $p[] = "%$q%"; }
        $w[] = '(' . implode(' OR ', $or) . ')';
    }
    $lok = trim($_GET['lokasi'] ?? '');
    if ($lok !== '') { $w[] = 'lokasi = ?'; $t .= 's'; $p[] = $lok; }
    if ($kolomJenis !== '') {
        $j = trim($_GET['kategori'] ?? '');
        if ($j !== '') { $w[] = "$kolomJenis = ?"; $t .= 's'; $p[] = $j; }
    }
    $min = $_GET['harga_min'] ?? ''; $max = $_GET['harga_max'] ?? '';
    if ($min !== '' && is_numeric($min)) { $w[] = "$kolomHarga >= ?"; $t .= 'd'; $p[] = (float)$min; }
    if ($max !== '' && is_numeric($max)) { $w[] = "$kolomHarga <= ?"; $t .= 'd'; $p[] = (float)$max; }
    if (($_GET['status'] ?? 'tersedia') !== 'semua') { $w[] = "status = 'tersedia'"; }
    return [$w, $t, $p];
}
function urutan($kolomHarga) {
    $m = ['termurah' => "$kolomHarga ASC", 'termahal' => "$kolomHarga DESC", 'terbaru' => 'id DESC'];
    return $m[$_GET['sort'] ?? ''] ?? 'id ASC';
}
function formFilter(array $lokasiList, $tanggal = false, array $jenisList = []) {
    $v = function ($k) { return e($_GET[$k] ?? ''); };
    $sel = function ($k, $val) { return (($_GET[$k] ?? '') === $val) ? ' selected' : ''; };
    $reset = basename($_SERVER['PHP_SELF']);
    ?>
    <section>
        <article class="kartu-produk" style="margin-bottom:24px">
            <div class="isi-produk">
                <h2>🔍 Cari &amp; Filter</h2>
                <form method="GET">
                    <?php if (!empty($_GET['kategori'])): ?>
                        <input type="hidden" name="kategori" value="<?php echo $v('kategori'); ?>">
                    <?php endif; ?>
                    <label>Kata kunci</label>
                    <input type="text" name="q" value="<?php echo $v('q'); ?>" placeholder="Nama, lokasi, atau deskripsi">

                    <label>Lokasi</label>
                    <select name="lokasi">
                        <option value="">Semua lokasi</option>
                        <?php foreach ($lokasiList as $l): ?>
                            <option value="<?php echo e($l); ?>"<?php echo $sel('lokasi', $l); ?>><?php echo e($l); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($jenisList): ?>
                        <label>Jenis kendaraan</label>
                        <select name="jenis">
                            <option value="">Semua jenis</option>
                            <?php foreach ($jenisList as $j): ?>
                                <option value="<?php echo e($j); ?>"<?php echo $sel('jenis', $j); ?>><?php echo e($j); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <label>Harga minimum (Rp)</label>
                    <input type="number" name="harga_min" min="0" value="<?php echo $v('harga_min'); ?>">
                    <label>Harga maksimum (Rp)</label>
                    <input type="number" name="harga_max" min="0" value="<?php echo $v('harga_max'); ?>">

                    <?php if ($tanggal): ?>
                        <label>Check-in</label>
                        <input type="date" name="checkin" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $v('checkin'); ?>">
                        <label>Check-out</label>
                        <input type="date" name="checkout" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $v('checkout'); ?>">
                    <?php endif; ?>

                    <label>Ketersediaan</label>
                    <select name="status">
                        <option value="tersedia"<?php echo $sel('status', 'tersedia'); ?>>Hanya yang tersedia</option>
                        <option value="semua"<?php echo $sel('status', 'semua'); ?>>Tampilkan semua</option>
                    </select>

                    <label>Urutkan</label>
                    <select name="sort">
                        <option value="">Rekomendasi</option>
                        <option value="termurah"<?php echo $sel('sort', 'termurah'); ?>>Harga terendah</option>
                        <option value="termahal"<?php echo $sel('sort', 'termahal'); ?>>Harga tertinggi</option>
                        <option value="terbaru"<?php echo $sel('sort', 'terbaru'); ?>>Terbaru</option>
                    </select>

                    <div>
                        <button type="submit" class="tombol-detail" style="border:none">Cari</button>
                        <a href="<?php echo e($reset); ?>" class="tombol-detail">Reset</a>
                    </div>
                </form>
            </div>
        </article>
    </section>
<?php }

/* ---------- Kategori ---------- */
function emojiKategori($n) {
    $m = ['Pusat Kota' => '🏙️', 'Budaya & Heritage' => '🏛️', 'Keluarga' => '👨‍👩‍👧', 'Tenang & Santai' => '🌿',
          'Candi & Budaya' => '🏛️', 'Alam & Pemandangan' => '🏞️', 'Pantai' => '🏖️',
          'Motor' => '🛵', 'Mobil' => '🚗', 'Jeep' => '🚙'];
    return $m[$n] ?? '🏷️';
}
// $daftar: baris [nama => ..., n => jumlah]
function barKategori(array $daftar) {
    $aktif = $_GET['kategori'] ?? '';
    $base = basename($_SERVER['PHP_SELF']);
    $link = function ($v) use ($base) {
        $p = $_GET;
        unset($p['kategori']);
        if ($v !== '') { $p['kategori'] = $v; }
        $qs = http_build_query($p);
        return $base . ($qs ? '?' . $qs : '');
    };
    $total = array_sum(array_column($daftar, 'n'));
    echo '<div class="kategori-bar"><a href="' . e($link('')) . '" class="chip-kategori' . ($aktif === '' ? ' aktif' : '') . '">✨ Semua <span>' . (int)$total . '</span></a>';
    foreach ($daftar as $d) {
        echo '<a href="' . e($link($d['nama'])) . '" class="chip-kategori' . ($aktif === $d['nama'] ? ' aktif' : '') . '">'
            . emojiKategori($d['nama']) . ' ' . e($d['nama']) . ' <span>' . (int)$d['n'] . '</span></a>';
    }
    echo '</div>';
}

/* ---------- Validasi akun ---------- */
function validasiEmailGmail($email) {
    if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Format email tidak valid.';
    }
    if (!preg_match('/^[A-Za-z0-9._%+\-]+@gmail\.com$/i', $email)) {
        return 'Gunakan email @gmail.com (contoh: nama@gmail.com).';
    }
    return '';
}
function validasiPassword($p) {
    if (strlen($p) < 8) {
        return 'Password minimal 8 karakter.';
    }
    if (!preg_match('/[^A-Za-z0-9\s]/', $p)) {
        return 'Password harus mengandung minimal 1 simbol (contoh: ! @ # $ % & *).';
    }
    return '';
}
