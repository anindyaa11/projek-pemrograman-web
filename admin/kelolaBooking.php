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

$pesan = "";

// PROSES UBAH STATUS
if (isset($_POST["ubah_status"])) {

    $id = (int) $_POST["id"];
    $status = $_POST["status"];

    $statusValid = ["menunggu", "dikonfirmasi", "dibatalkan"];

    if ($id > 0 && in_array($status, $statusValid)) {

        $status = mysqli_real_escape_string($koneksi, $status);

        $query = "UPDATE booking
                  SET status = '$status'
                  WHERE id = $id";

        if (mysqli_query($koneksi, $query)) {
            header("Location: kelolaBooking.php");
            exit;
        } else {
            $pesan = "Gagal mengubah status booking.";
        }

    } else {
        $pesan = "Data status tidak valid.";
    }
}

// MENGAMBIL DATA BOOKING
$query = "SELECT 
            booking.*,
            homestay.nama_homestay
          FROM booking
          LEFT JOIN homestay
          ON booking.homestay_id = homestay.id
          ORDER BY booking.id DESC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <h1>Kelola Booking</h1>
        <p>Kelola seluruh pemesanan homestay JogjaYuk!</p>
    </div>

    <a href="index.php" class="tombol-login">
        Kembali ke Dashboard
    </a>

    <br><br>

    <h2>Daftar Booking</h2>

    <?php if ($pesan != "") { ?>
        <p><?php echo $pesan; ?></p>
    <?php } ?>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>No</th>
            <th>Nama Pemesan</th>
            <th>Homestay</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Jumlah Tamu</th>
            <th>Jumlah Malam</th>
            <th>Total Harga</th>
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

            <td>
                <?php echo htmlspecialchars($row["nama_pemesan"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["nama_homestay"] ?? "-"); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["tanggal_checkin"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["tanggal_checkout"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["jumlah_tamu"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["jumlah_malam"]); ?>
            </td>

            <td>
                Rp <?php echo number_format(
                    $row["total_harga"], 0, ',', '.'
                ); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["status"]); ?>
            </td>

            <td>
                <form method="POST" action="kelolaBooking.php">

                    <input type="hidden" name="id"
                        value="<?php echo $row["id"]; ?>">

                    <select name="status" required>
                        <option value="menunggu"
                            <?php if ($row["status"] == "menunggu") echo "selected"; ?>>
                            Menunggu
                        </option>

                        <option value="dikonfirmasi"
                            <?php if ($row["status"] == "dikonfirmasi") echo "selected"; ?>>
                            Dikonfirmasi
                        </option>

                        <option value="dibatalkan"
                            <?php if ($row["status"] == "dibatalkan") echo "selected"; ?>>
                            Dibatalkan
                        </option>
                    </select>

                    <button type="submit" name="ubah_status">
                        Simpan
                    </button>

                </form>
            </td>
        </tr>

        <?php
            }
        } else {
        ?>

        <tr>
            <td colspan="10">Belum ada data booking.</td>
        </tr>

        <?php } ?>

    </table>

</main>

</body>
</html>
