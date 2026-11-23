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
    "nama_homestay" => "",
    "lokasi" => "",
    "kategori" => "",
    "deskripsi" => "",
    "harga_per_malam" => "",
    "fasilitas" => "",
    "gambar" => "",
    "status" => "tersedia"
];

// PROSES HAPUS
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    if ($id > 0) {
        mysqli_query($koneksi, "DELETE FROM homestay WHERE id = $id");
    }

    header("Location: kelolaHomestay.php");
    exit;
}

// PROSES EDIT
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $hasil = mysqli_query(
        $koneksi,
        "SELECT * FROM homestay WHERE id = $id"
    );

    if ($hasil && mysqli_num_rows($hasil) > 0) {
        $data = mysqli_fetch_assoc($hasil);
        $edit = true;
    }
}

// PROSES SIMPAN
if (isset($_POST["simpan"])) {

    $id = (int) ($_POST["id"] ?? 0);

    $nama = mysqli_real_escape_string($koneksi, $_POST["nama_homestay"]);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST["lokasi"]);
    $kategori = mysqli_real_escape_string($koneksi, $_POST["kategori"]);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST["deskripsi"]);
    $harga = (int) $_POST["harga_per_malam"];
    $fasilitas = mysqli_real_escape_string($koneksi, $_POST["fasilitas"]);
    $status = mysqli_real_escape_string($koneksi, $_POST["status"]);

    $gambarLama = $_POST["gambar_lama"] ?? "";

    $hasilUpload = uploadGambar("gambar", $gambarLama, "homestay");

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
            $query = "UPDATE homestay SET
                nama_homestay = '$nama',
                lokasi = '$lokasi',
                kategori = '$kategori',
                deskripsi = '$deskripsi',
                harga_per_malam = '$harga',
                fasilitas = '$fasilitas',
                gambar = '$gambar',
                status = '$status'
                WHERE id = $id";

        } else {

            // INSERT DATA
            $query = "INSERT INTO homestay
                (nama_homestay, lokasi, kategori, deskripsi,
                harga_per_malam, fasilitas, gambar, status)
                VALUES
                ('$nama', '$lokasi', '$kategori', '$deskripsi',
                '$harga', '$fasilitas', '$gambar', '$status')";
        }

        if (mysqli_query($koneksi, $query)) {
            header("Location: kelolaHomestay.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan data homestay.";
        }
    }
}

// MENGAMBIL DATA HOMESTAY
$queryData = mysqli_query(
    $koneksi,
    "SELECT * FROM homestay ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Homestay - JogjaYuk!</title>
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
        <h1>Kelola Homestay</h1>
        <p>Kelola seluruh homestay JogjaYuk!</p>
    </div>

    <a href="index.php" class="tombol-login">
        Kembali ke Dashboard
    </a>

    <br><br>

    <h2>
        <?php echo $edit ? "Edit Homestay" : "Tambah Homestay"; ?>
    </h2>

    <?php if ($pesan != "") { ?>
        <p><?php echo htmlspecialchars($pesan); ?></p>
    <?php } ?>

    <form method="POST" action="kelolaHomestay.php" enctype="multipart/form-data">

        <input type="hidden" name="id"
            value="<?php echo htmlspecialchars($data["id"]); ?>">

        <label>Nama Homestay</label><br>
        <input type="text" name="nama_homestay"
            value="<?php echo htmlspecialchars($data["nama_homestay"]); ?>"
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

        <label>Harga per Malam</label><br>
        <input type="number" name="harga_per_malam"
            value="<?php echo htmlspecialchars($data["harga_per_malam"]); ?>"
            min="0" required><br><br>

        <label>Fasilitas</label><br>
        <textarea name="fasilitas" required><?php
            echo htmlspecialchars($data["fasilitas"]);
        ?></textarea><br><br>

        <label>Upload Gambar (JPG, PNG, WEBP, maksimal 2 MB)</label><br>

        <?php if ($data["gambar"] != "") { ?>
            <p>Gambar saat ini:</p>
            <img src="../<?php echo htmlspecialchars($data["gambar"]); ?>"
                width="120" alt="Gambar Homestay"><br>
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
            <?php echo $edit ? "Simpan Perubahan" : "Tambah Homestay"; ?>
        </button>

        <?php if ($edit) { ?>
            <a href="kelolaHomestay.php">Batal Edit</a>
        <?php } ?>

    </form>

    <br>

    <h2>Daftar Homestay</h2>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>No</th>
            <th>Nama Homestay</th>
            <th>Lokasi</th>
            <th>Kategori</th>
            <th>Harga per Malam</th>
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

            <td><?php echo htmlspecialchars($row["nama_homestay"]); ?></td>

            <td><?php echo htmlspecialchars($row["lokasi"]); ?></td>

            <td><?php echo htmlspecialchars($row["kategori"]); ?></td>

            <td>
                Rp <?php echo number_format($row["harga_per_malam"], 0, ",", "."); ?>
            </td>

            <td>
                <?php if (strpos($row["gambar"], "assets/images/homestay/img_") === 0) { ?>

                    <img src="../<?php echo htmlspecialchars($row["gambar"]); ?>"
                        width="100" alt="Gambar Homestay">

                <?php } else { ?>

                    Belum ada gambar

                <?php } ?>
            </td>

            <td><?php echo htmlspecialchars($row["status"]); ?></td>

            <td>
                <a href="...?edit=<?= $data['id']; ?>"class="btn btn-primary btn-sm">
                Edit
                </a>
            
            <a href="...?hapus=<?= $data['id']; ?>"class="btn btn-danger btn-sm"
            onclick="return confirm('Yakin ingin menghapus data ini?')">
            Hapus
            </a>
        </td>
        </tr>

        <?php
            }
        } else {
        ?>

        <tr>
            <td colspan="8">Belum ada data homestay.</td>
        </tr>

        <?php } ?>

    </table>

</main>

</body>
</html>
