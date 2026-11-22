<?php

function uploadGambar($input, $gambarLama, $folder)
{
    // Jika tidak memilih gambar baru, gunakan gambar lama
    if (!isset($_FILES[$input]) || $_FILES[$input]["error"] === UPLOAD_ERR_NO_FILE) {
        return ["nama" => $gambarLama, "error" => ""];
    }

    $file = $_FILES[$input];

    // Periksa apakah upload berhasil
    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["nama" => $gambarLama, "error" => "Upload gambar gagal."];
    }

    // Maksimal ukuran gambar 2 MB
    if ($file["size"] > 2 * 1024 * 1024) {
        return ["nama" => $gambarLama, "error" => "Ukuran gambar maksimal 2 MB."];
    }

    // Periksa ekstensi file
    $ekstensi = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $ekstensiBoleh = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($ekstensi, $ekstensiBoleh)) {
        return ["nama" => $gambarLama, "error" => "Format gambar harus JPG, JPEG, PNG, atau WEBP."];
    }

    // Periksa isi file benar-benar gambar
    if (getimagesize($file["tmp_name"]) === false) {
        return ["nama" => $gambarLama, "error" => "File yang diunggah bukan gambar."];
    }

    // Buat nama file baru
    $namaBaru = uniqid("img_") . "." . $ekstensi;

    // Tentukan lokasi penyimpanan
    $tujuan = __DIR__ . "/../assets/images/" . $folder . "/" . $namaBaru;

    if (!move_uploaded_file($file["tmp_name"], $tujuan)) {
        return ["nama" => $gambarLama, "error" => "Gambar gagal disimpan."];
    }

    return [
        "nama" => "assets/images/" . $folder . "/" . $namaBaru,
        "error" => ""
    ];
}

