<?php
require("../../config/config.default.php");
require("../../config/config.function.php");
require("../../config/functions.crud.php");
cek_session_guru();

header('Content-Type: application/json');

$setting = mysqli_fetch_array(mysqli_query($koneksi, "SELECT kamera FROM setting WHERE id_setting='1'"));
$curr = isset($setting['kamera']) ? intval($setting['kamera']) : 0;
$new_status = ($curr == 1) ? 0 : 1;

if (isset($_POST['status']) && $_POST['status'] !== '') {
    $new_status = intval($_POST['status']) == 1 ? 1 : 0;
}

$update = mysqli_query($koneksi, "UPDATE setting SET kamera='$new_status' WHERE id_setting='1'");
if ($update) {
    echo json_encode([
        'status' => 'ok',
        'kamera' => $new_status,
        'message' => ($new_status == 1) ? 'Kamera Pengawas Ujian AKTIF (ON)' : 'Kamera Pengawas Ujian NONAKTIF (OFF)'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengubah status kamera'
    ]);
}
