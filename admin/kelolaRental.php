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
    "nama_kendaraan" => "",
    "jenis_kendaraan" => "",
    "lokasi" => "",
    "deskripsi" => "",
    "harga_per_hari" => "",
    "gambar" => "",
    "status" => "tersedia"
];

// PROSES HAPUS
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    if ($id > 0) {
        mysqli_query($koneksi, "DELETE FROM rental WHERE id = $id");
    }

    header("Location: kelolaRental.php");
    exit;
}

// PROSES EDIT
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $resultEdit = mysqli_query(
        $koneksi,
        "SELECT * FROM rental WHERE id = $id"
    );

    if ($resultEdit && mysqli_num_rows($resultEdit) > 0) {
        $data = mysqli_fetch_assoc($resultEdit);
        $edit = true;
    }
}

// PROSES SIMPAN
if (isset($_POST["simpan"])) {

    $id = (int) ($_POST["id"] ?? 0);
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama_kendaraan"]);
    $jenis = mysqli_real_escape_string($koneksi, $_POST["jenis_kendaraan"]);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST["lokasi"]);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST["deskripsi"]);
    $harga = (int) $_POST["harga_per_hari"];
    $gambarLama = $_POST["gambar_lama"] ?? "";
    $hasilUpload = uploadGambar("gambar", $gambarLama, "rental");
    $gambar = mysqli_real_escape_string($koneksi, $hasilUpload["nama"]);
    $status = mysqli_real_escape_string($koneksi, $_POST["status"]);


    if ($hasilUpload["error"] !== "") {
    $pesan = $hasilUpload["error"];
    $data = array_merge($data, $_POST);
    $data["gambar"] = $gambarLama;
    $edit = $id > 0;
    } else {
    if ($id > 0) {
        // UPDATE
        $query = "UPDATE rental SET
            nama_kendaraan = '$nama',
            jenis_kendaraan = '$jenis',
            lokasi = '$lokasi',
            deskripsi = '$deskripsi',
            harga_per_hari = '$harga',
            gambar = '$gambar',
            status = '$status'
            WHERE id = $id";
    } else {
        // INSERT
        $query = "INSERT INTO rental
            (nama_kendaraan, jenis_kendaraan, lokasi,
            deskripsi, harga_per_hari, gambar, status)
            VALUES
            ('$nama', '$jenis', '$lokasi',
            '$deskripsi', '$harga', '$gambar', '$status')";
    }
    
    if (mysqli_query($koneksi, $query)) {
    header("Location: kelolaRental.php");
    exit;
    } else {
    $pesan = "Gagal menyimpan data rental.";
    }

}
}

// MENGAMBIL DATA
$result = mysqli_query(
    $koneksi,
    "SELECT * FROM rental ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rental - JogjaYuk!</title>
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
        <h1>Kelola Rental</h1>
        <p>Kelola seluruh kendaraan rental JogjaYuk!</p>
    </div>

    <a href="index.php" class="tombol-login">
        Kembali ke Dashboard
    </a>

    <br><br>

    <h2><?php echo $edit ? "Edit Rental" : "Tambah Rental"; ?></h2>

    <?php if ($pesan != "") { ?>
        <p><?php echo $pesan; ?></p>
    <?php } ?>

    <form method="POST" action="kelolaRental.php" enctype="multipart/form-data">

        <input type="hidden" name="id"
            value="<?php echo htmlspecialchars($data["id"]); ?>">

        <label>Nama Kendaraan</label><br>
        <input type="text" name="nama_kendaraan"
            value="<?php echo htmlspecialchars($data["nama_kendaraan"]); ?>"
            required><br><br>

        <label>Jenis Kendaraan</label><br>
        <input type="text" name="jenis_kendaraan"
            value="<?php echo htmlspecialchars($data["jenis_kendaraan"]); ?>"
            required><br><br>

        <label>Lokasi</label><br>
        <input type="text" name="lokasi"
            value="<?php echo htmlspecialchars($data["lokasi"]); ?>"
            required><br><br>

        <label>Deskripsi</label><br>
        <textarea name="deskripsi" required><?php
            echo htmlspecialchars($data["deskripsi"]);
        ?></textarea><br><br>

        <label>Harga per Hari</label><br>
        <input type="number" name="harga_per_hari"
            value="<?php echo htmlspecialchars($data["harga_per_hari"]); ?>"
            min="0" required><br><br>

        <label>Upload Gambar (JPG, PNG, WEBP, maksimal 2 MB)</label><br>
        <?php if ($data["gambar"] != "") { ?>
        <p>Gambar saat ini:</p>
        <img src="../<?php echo htmlspecialchars($data["gambar"]); ?>"
         width="120" alt="Gambar Rental"><br>
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
            <?php echo $edit ? "Simpan Perubahan" : "Tambah Rental"; ?>
        </button>

        <?php if ($edit) { ?>
            <a href="kelolaRental.php">Batal Edit</a>
        <?php } ?>

    </form>

    <br>

    <h2>Daftar Rental</h2>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>No</th>
            <th>Nama Kendaraan</th>
            <th>Jenis Kendaraan</th>
            <th>Lokasi</th>
            <th>Harga per Hari</th>
            <th>Gambar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>

        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row["nama_kendaraan"]); ?></td>
            <td><?php echo htmlspecialchars($row["jenis_kendaraan"]); ?></td>
            <td><?php echo htmlspecialchars($row["lokasi"]); ?></td>
            <td>
                Rp <?php echo number_format($row["harga_per_hari"], 0, ',', '.'); ?>
            </td>
            <td>
                <?php if (strpos($row["gambar"], "assets/images/rental/img_") === 0) {?>
                <img src="../<?php echo htmlspecialchars($row["gambar"]); ?>"
                width="100" alt="Gambar Rental">
                <?php } else { ?>
                Belum ada gambar
                <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($row["status"]); ?></td>
            <<td>
                <a href="...?edit=<?= $data['id']; ?>"
                   class="btn btn-primary btn-sm">
                   Edit
                </a>

                <a href="...?hapus=<?= $data['id']; ?>"
                   class="btn btn-danger btn-sm"
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
            <td colspan="8">Belum ada data rental.</td>
        </tr>

        <?php } ?>

    </table>

</main>

</body>
</html>
