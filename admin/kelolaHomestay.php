<?php
include "../config/koneksi.php";
include "../config/upload.php";

$pesan = "";

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

$edit = false;

// HAPUS DATA
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    mysqli_query($koneksi, "DELETE FROM homestay WHERE id = $id");

    header("Location: kelolaHomestay.php");
    exit;
}

// AMBIL DATA UNTUK DIEDIT
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $hasil = mysqli_query($koneksi, "SELECT * FROM homestay WHERE id = $id");
    $data = mysqli_fetch_assoc($hasil);

    if ($data) {
        $edit = true;
    }
}

// SIMPAN DATA
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

    $gambar = mysqli_real_escape_string($koneksi, $hasilUpload["nama"]);

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
            // TAMBAH DATA
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

// AMBIL SEMUA DATA HOMESTAY
$queryData = mysqli_query($koneksi, "SELECT * FROM homestay ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Homestay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="halaman-admin">

    <h1>Kelola Homestay</h1>

    <a href="index.php">Kembali ke Dashboard</a>

    <hr>

    <h2><?php echo $edit ? "Edit Homestay" : "Tambah Homestay"; ?></h2>

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
        <textarea name="deskripsi" required><?php echo htmlspecialchars($data["deskripsi"]); ?></textarea><br><br>

        <label>Harga per Malam</label><br>
        <input type="number" name="harga_per_malam"
            value="<?php echo htmlspecialchars($data["harga_per_malam"]); ?>"
            required><br><br>

        <label>Fasilitas</label><br>
        <textarea name="fasilitas" required><?php echo htmlspecialchars($data["fasilitas"]); ?></textarea><br><br>

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
        <select name="status">
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

    <hr>

    <h2>Daftar Homestay</h2>

    <table border="1" cellpadding="10" cellspacing="0">

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

        while ($row = mysqli_fetch_assoc($queryData)) {
        ?>

        <tr>
            <td><?php echo $no++; ?></td>

            <td><?php echo htmlspecialchars($row["nama_homestay"]); ?></td>

            <td><?php echo htmlspecialchars($row["lokasi"]); ?></td>

            <td><?php echo htmlspecialchars($row["kategori"]); ?></td>

            <td>Rp <?php echo number_format($row["harga_per_malam"], 0, ",", "."); ?></td>

            <td>
                <?php
                if (strpos($row["gambar"], "assets/images/homestay/img_") === 0) {
                ?>
                    <img src="../<?php echo htmlspecialchars($row["gambar"]); ?>"
                        width="100" alt="Gambar Homestay">
                <?php } else { ?>
                    Belum tersedia
                <?php } ?>
            </td>

            <td><?php echo htmlspecialchars($row["status"]); ?></td>

            <td>
                <a href="kelolaHomestay.php?edit=<?php echo $row["id"]; ?>">
                    Edit
                </a>

                |

                <a href="kelolaHomestay.php?hapus=<?php echo $row["id"]; ?>"
                    onclick="return confirm('Yakin ingin menghapus homestay ini?')">
                    Hapus
                </a>
            </td>
        </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>
