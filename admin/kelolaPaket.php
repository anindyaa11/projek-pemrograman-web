<?php
include "../config/koneksi.php";
include "../config/upload.php";

$pesan = "";

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

$edit = false;

// HAPUS DATA
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    mysqli_query($koneksi, "DELETE FROM paket_wisata WHERE id = $id");

    header("Location: kelolaPaket.php");
    exit;
}

// AMBIL DATA UNTUK DIEDIT
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $hasil = mysqli_query($koneksi, "SELECT * FROM paket_wisata WHERE id = $id");
    $data = mysqli_fetch_assoc($hasil);

    if ($data) {
        $edit = true;
    }
}

// SIMPAN DATA
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

    $gambar = mysqli_real_escape_string($koneksi, $hasilUpload["nama"]);

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

// AMBIL SEMUA DATA PAKET
$queryData = mysqli_query($koneksi, "SELECT * FROM paket_wisata ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Paket Wisata</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="halaman-admin">

    <h1>Kelola Paket Wisata</h1>

    <a href="index.php">Kembali ke Dashboard</a>

    <hr>

    <h2><?php echo $edit ? "Edit Paket Wisata" : "Tambah Paket Wisata"; ?></h2>

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
        <textarea name="deskripsi" required><?php echo htmlspecialchars($data["deskripsi"]); ?></textarea><br><br>

        <label>Harga per Orang</label><br>
        <input type="number" name="harga_per_orang"
            value="<?php echo htmlspecialchars($data["harga_per_orang"]); ?>"
            required><br><br>

        <label>Durasi</label><br>
        <input type="text" name="durasi"
            value="<?php echo htmlspecialchars($data["durasi"]); ?>"
            required><br><br>

        <label>Fasilitas</label><br>
        <textarea name="fasilitas" required><?php echo htmlspecialchars($data["fasilitas"]); ?></textarea><br><br>

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
            <?php echo $edit ? "Simpan Perubahan" : "Tambah Paket Wisata"; ?>
        </button>

        <?php if ($edit) { ?>
            <a href="kelolaPaket.php">Batal Edit</a>
        <?php } ?>

    </form>

    <hr>

    <h2>Daftar Paket Wisata</h2>

    <table border="1" cellpadding="10" cellspacing="0">

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

        while ($row = mysqli_fetch_assoc($queryData)) {
        ?>

        <tr>
            <td><?php echo $no++; ?></td>

            <td><?php echo htmlspecialchars($row["nama_paket"]); ?></td>

            <td><?php echo htmlspecialchars($row["lokasi"]); ?></td>

            <td><?php echo htmlspecialchars($row["kategori"]); ?></td>

            <td>Rp <?php echo number_format($row["harga_per_orang"], 0, ",", "."); ?></td>

            <td><?php echo htmlspecialchars($row["durasi"]); ?></td>

            <td>
                <?php
                if (strpos($row["gambar"], "assets/images/paket-wisata/img_") === 0) {
                ?>
                    <img src="../<?php echo htmlspecialchars($row["gambar"]); ?>"
                        width="100" alt="Gambar Paket Wisata">
                <?php } else { ?>
                    Belum tersedia
                <?php } ?>
            </td>

            <td><?php echo htmlspecialchars($row["status"]); ?></td>

            <td>
                <a href="kelolaPaket.php?edit=<?php echo $row["id"]; ?>">
                    Edit
                </a>

                |

                <a href="kelolaPaket.php?hapus=<?php echo $row["id"]; ?>"
                    onclick="return confirm('Yakin ingin menghapus paket ini?')">
                    Hapus
                </a>
            </td>
        </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>
