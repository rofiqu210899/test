<?php
require("config/config.default.php");
require("config/config.function.php");
require("config/functions.crud.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$id_siswa = isset($_POST['id_siswa']) ? intval($_POST['id_siswa']) : 0;
$id_ujian = isset($_POST['id_ujian']) ? intval($_POST['id_ujian']) : 0;
$imgData  = isset($_POST['foto']) ? $_POST['foto'] : '';

if (!$id_siswa || !$id_ujian || empty($imgData)) {
    echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
    exit;
}

// Cek apakah kamera aktif pada setting
$setting = mysqli_fetch_array(mysqli_query($koneksi, "SELECT kamera FROM setting WHERE id_setting='1'"));
if (isset($setting['kamera']) && $setting['kamera'] == 0) {
    echo json_encode(['status' => 'disabled', 'message' => 'Fitur kamera nonaktif']);
    exit;
}

// Format base64 ke file binary
if (preg_match('/^data:image\/(\w+);base64,/', $imgData, $type)) {
    $imgData = substr($imgData, strpos($imgData, ',') + 1);
    $type = strtolower($type[1]);
    if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
        $type = 'jpg';
    }
} else {
    $type = 'jpg';
}

$imgDecoded = base64_decode($imgData);
if ($imgDecoded === false) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal decode gambar']);
    exit;
}

$dir = __DIR__ . '/files/kamera/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$filename = 'cam_' . $id_ujian . '_' . $id_siswa . '_' . time() . '_' . rand(100, 999) . '.' . $type;
$filepath = $dir . $filename;

if (file_put_contents($filepath, $imgDecoded)) {
    $data = [
        'id_siswa' => $id_siswa,
        'id_ujian' => $id_ujian,
        'foto'     => $filename,
        'waktu'    => date('Y-m-d H:i:s')
    ];
    insert($koneksi, 'log_kamera', $data);

    echo json_encode(['status' => 'ok', 'foto' => $filename]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file gambar']);
}
