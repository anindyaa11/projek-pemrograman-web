
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
$userLogin = (int) $_SESSION["user_id"];

// PROSES UBAH ROLE
if (isset($_POST["ubah_role"])) {

    $id = (int) $_POST["id"];
    $role = $_POST["role"];

    $roleValid = ["user", "admin"];

    // Admin tidak boleh mengubah role akunnya sendiri
    if ($id === $userLogin) {
        $pesan = "Role akun yang sedang digunakan tidak dapat diubah.";
    } elseif ($id > 0 && in_array($role, $roleValid)) {

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE users SET role = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $role, $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: kelolaPengguna.php");
            exit;
        } else {
            $pesan = "Gagal mengubah role pengguna.";
        }

        mysqli_stmt_close($stmt);

    } else {
        $pesan = "Data role tidak valid.";
    }
}

// PROSES HAPUS PENGGUNA
if (isset($_POST["hapus"])) {

    $id = (int) $_POST["id"];

    if ($id === $userLogin) {
        $pesan = "Akun yang sedang digunakan tidak dapat dihapus.";
    } elseif ($id > 0) {

        // Cek apakah pengguna yang akan dihapus adalah admin
        $cek = mysqli_query(
            $koneksi,
            "SELECT role FROM users WHERE id = $id"
        );

        $userHapus = mysqli_fetch_assoc($cek);

        if ($userHapus && $userHapus["role"] === "admin") {

            // Pastikan masih ada admin lain
            $cekAdmin = mysqli_query(
                $koneksi,
                "SELECT COUNT(*) AS total
                 FROM users
                 WHERE role = 'admin' AND id != $id"
            );

            $jumlahAdmin = mysqli_fetch_assoc($cekAdmin)["total"];

            if ($jumlahAdmin < 1) {
                $pesan = "Admin terakhir tidak dapat dihapus.";
            } else {
                mysqli_query(
                    $koneksi,
                    "DELETE FROM users WHERE id = $id"
                );

                header("Location: kelolaPengguna.php");
                exit;
            }

        } elseif ($userHapus) {

            mysqli_query(
                $koneksi,
                "DELETE FROM users WHERE id = $id"
            );

            header("Location: kelolaPengguna.php");
            exit;
        }
    }
}

// MENGAMBIL DATA PENGGUNA
$result = mysqli_query(
    $koneksi,
    "SELECT id, nama, email, role, created_at
     FROM users
     ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - JogjaYuk!</title>
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
        <h1>Kelola Pengguna</h1>
        <p>Kelola akun pengguna JogjaYuk!</p>
    </div>

    <a href="index.php" class="tombol-login">
        Kembali ke Dashboard
    </a>

    <br><br>

    <h2>Daftar Pengguna</h2>

    <?php if ($pesan != "") { ?>
        <p><?php echo htmlspecialchars($pesan); ?></p>
    <?php } ?>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Tanggal Daftar</th>
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
                <?php echo htmlspecialchars($row["nama"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["email"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["role"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($row["created_at"]); ?>
            </td>

            <td>

                <?php if ((int) $row["id"] !== $userLogin) { ?>

                    <form method="POST" action="kelolaPengguna.php">

                        <input type="hidden" name="id"
                            value="<?php echo $row["id"]; ?>">

                        <select name="role" required>
                            <option value="user"
                                <?php if ($row["role"] == "user") echo "selected"; ?>>
                                User
                            </option>

                            <option value="admin"
                                <?php if ($row["role"] == "admin") echo "selected"; ?>>
                                Admin
                            </option>
                        </select>

                        <button type="submit" name="ubah_role">
                            Simpan Role
                        </button>

                    </form>

                    <br>

                    <form method="POST" action="kelolaPengguna.php"
                        onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">

                        <input type="hidden" name="id"
                            value="<?php echo $row["id"]; ?>">

                        <button type="submit" name="hapus">
                            Hapus
                        </button>

                    </form>

                <?php } else { ?>

                    <p>Akun Admin Aktif</p>

                <?php } ?>

            </td>
        </tr>

        <?php
            }
        } else {
        ?>

        <tr>
            <td colspan="6">Belum ada pengguna terdaftar.</td>
        </tr>

        <?php } ?>

    </table>

</main>

</body>
</html>
