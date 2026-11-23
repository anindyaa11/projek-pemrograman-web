<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

include "../config/koneksi.php";
include "../config/upload.php";

$pesan = "";
$edit = false;

$data = [
    "id" => "",
    "nama_paket" => "",
    "lokasi" => "",
    "kategori" => "",
    "deskripsi" => "",
    "harga_per_orang" => "",
    "durasi" => "",
    "fasilitas" => "",
    "gambar" => "",
    "status" => "tersedia"
];

// PROSES HAPUS
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    if ($id > 0) {
        mysqli_query($koneksi, "DELETE FROM paket_wisata WHERE id = $id");
    }

    header("Location: kelolaPaket.php");
    exit;
}

// PROSES EDIT
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $hasil = mysqli_query(
        $koneksi,
        "SELECT * FROM paket_wisata WHERE id = $id"
    );

    if ($hasil && mysqli_num_rows($hasil) > 0) {
        $data = mysqli_fetch_assoc($hasil);
        $edit = true;
    }
}

// PROSES SIMPAN
if (isset($_POST["simpan"])) {

    $id = (int) ($_POST["id"] ?? 0);

    $nama = mysqli_real_escape_string($koneksi, $_POST["nama_paket"]);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST["lokasi"]);
    $kategori = mysqli_real_escape_string($koneksi, $_POST["kategori"]);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST["deskripsi"]);
    $harga = (int) $_POST["harga_per_orang"];
    $durasi = mysqli_real_escape_string($koneksi, $_POST["durasi"]);
    $fasilitas = mysqli_real_escape_string($koneksi, $_POST["fasilitas"]);
    $status = mysqli_real_escape_string($koneksi, $_POST["status"]);

    $gambarLama = $_POST["gambar_lama"] ?? "";

    $hasilUpload = uploadGambar("gambar", $gambarLama, "paket-wisata");

    $gambar = mysqli_real_escape_string(
        $koneksi,
        $hasilUpload["nama"]
    );

    if ($hasilUpload["error"] !== "") {

        $pesan = $hasilUpload["error"];

        $data = array_merge($data, $_POST);
        $data["gambar"] = $gambarLama;
        $edit = $id > 0;

    } else {

        if ($id > 0) {

            // UPDATE DATA
            $query = "UPDATE paket_wisata SET
                nama_paket = '$nama',
                lokasi = '$lokasi',
                kategori = '$kategori',
                deskripsi = '$deskripsi',
                harga_per_orang = '$harga',
                durasi = '$durasi',
                fasilitas = '$fasilitas',
                gambar = '$gambar',
                status = '$status'
                WHERE id = $id";

        } else {

            // TAMBAH DATA
            $query = "INSERT INTO paket_wisata
                (nama_paket, lokasi, kategori, deskripsi,
                harga_per_orang, durasi, fasilitas, gambar, status)
                VALUES
                ('$nama', '$lokasi', '$kategori', '$deskripsi',
                '$harga', '$durasi', '$fasilitas', '$gambar', '$status')";
        }

        if (mysqli_query($koneksi, $query)) {
            header("Location: kelolaPaket.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan data paket wisata.";
        }
    }
}

// MENGAMBIL DATA PAKET WISATA
$queryData = mysqli_query(
    $koneksi,
    "SELECT * FROM paket_wisata ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket Wisata - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar">
    <a href="index.php" class="logo">JogjaYuk! Admin</a>

    <div class="menu-akun">
        Halo, <?php echo htmlspecialchars($_SESSION["nama"]); ?>
        <a href="../pages/logout.php" class="tombol-login">Logout</a>
    </div>
</nav>

<main class="halaman-katalog">

    <div class="judul-katalog">
        <h1>Kelola Paket Wisata</h1>
        <p>Kelola seluruh paket wisata JogjaYuk!</p>
    </div>

    <a href="index.php" class="tombol-login">
        Kembali ke Dashboard
    </a>

    <br><br>

    <h2>
        <?php echo $edit ? "Edit Paket Wisata" : "Tambah Paket Wisata"; ?>
    </h2>

    <?php if ($pesan != "") { ?>
        <p><?php echo htmlspecialchars($pesan); ?></p>
    <?php } ?>

    <form method="POST" action="kelolaPaket.php" enctype="multipart/form-data">

        <input type="hidden" name="id"
            value="<?php echo htmlspecialchars($data["id"]); ?>">

        <label>Nama Paket</label><br>
        <input type="text" name="nama_paket"
            value="<?php echo htmlspecialchars($data["nama_paket"]); ?>"
            required><br><br>

        <label>Lokasi</label><br>
        <input type="text" name="lokasi"
            value="<?php echo htmlspecialchars($data["lokasi"]); ?>"
            required><br><br>

        <label>Kategori</label><br>
        <input type="text" name="kategori"
            value="<?php echo htmlspecialchars($data["kategori"]); ?>"
            required><br><br>

        <label>Deskripsi</label><br>
        <textarea name="deskripsi" required><?php
            echo htmlspecialchars($data["deskripsi"]);
        ?></textarea><br><br>

        <label>Harga per Orang</label><br>
        <input type="number" name="harga_per_orang"
            value="<?php echo htmlspecialchars($data["harga_per_orang"]); ?>"
            min="0" required><br><br>

        <label>Durasi</label><br>
        <input type="text" name="durasi"
            value="<?php echo htmlspecialchars($data["durasi"]); ?>"
            required><br><br>

        <label>Fasilitas</label><br>
        <textarea name="fasilitas" required><?php
            echo htmlspecialchars($data["fasilitas"]);
        ?></textarea><br><br>

        <label>Upload Gambar (JPG, PNG, WEBP, maksimal 2 MB)</label><br>

        <?php if ($data["gambar"] != "") { ?>
            <p>Gambar saat ini:</p>
            <img src="../<?php echo htmlspecialchars($data["gambar"]); ?>"
                width="120" alt="Gambar Paket Wisata"><br>
        <?php } ?>

        <input type="file" name="gambar"
            accept=".jpg,.jpeg,.png,.webp"><br>

        <input type="hidden" name="gambar_lama"
            value="<?php echo htmlspecialchars($data["gambar"]); ?>">

        <br><br>

        <label>Status</label><br>
        <select name="status" required>

            <option value="tersedia"
                <?php if ($data["status"] == "tersedia") echo "selected"; ?>>
                Tersedia
            </option>

            <option value="tidak tersedia"
                <?php if ($data["status"] == "tidak tersedia") echo "selected"; ?>>
                Tidak Tersedia
            </option>

        </select><br><br>

        <button type="submit" name="simpan">
            <?php echo $edit ? "Simpan Perubahan" : "Tambah Paket Wisata"; ?>
        </button>

        <?php if ($edit) { ?>
            <a href="kelolaPaket.php">Batal Edit</a>
        <?php } ?>

    </form>

    <br>

    <h2>Daftar Paket Wisata</h2>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>No</th>
            <th>Nama Paket</th>
            <th>Lokasi</th>
            <th>Kategori</th>
            <th>Harga per Orang</th>
            <th>Durasi</th>
            <th>Gambar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;

        if ($queryData && mysqli_num_rows($queryData) > 0) {
            while ($row = mysqli_fetch_assoc($queryData)) {
        ?>

        <tr>
            <td><?php echo $no++; ?></td>

            <td><?php echo htmlspecialchars($row["nama_paket"]); ?></td>

            <td><?php echo htmlspecialchars($row["lokasi"]); ?></td>

            <td><?php echo htmlspecialchars($row["kategori"]); ?></td>

            <td>
                Rp <?php echo number_format($row["harga_per_orang"], 0, ",", "."); ?>
            </td>

            <td><?php echo htmlspecialchars($row["durasi"]); ?></td>

            <td>
                <?php if (strpos($row["gambar"], "assets/images/paket-wisata/img_") === 0) { ?>

                    <img src="../<?php echo htmlspecialchars($row["gambar"]); ?>"
                        width="100" alt="Gambar Paket Wisata">

                <?php } else { ?>

                    Belum tersedia

                <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($row["status"]); ?></td>

            <td>
                <a href="kelolaPaket.php?edit=<?= $row['id']; ?>"
                class="btn btn-primary btn-sm">
                    Edit
                </a>

                <a href="kelolaPaket.php?hapus=<?= $row['id']; ?>"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Yakin ingin menghapus paket wisata ini?')">
                    Hapus
                </a>
            </td>

        </td>
        </tr>

        <?php
            }
        } else {
        ?>

        <tr>
            <td colspan="9">Belum ada data paket wisata.</td>
        </tr>

        <?php } ?>

    </table>

</main>

</body>
</html>