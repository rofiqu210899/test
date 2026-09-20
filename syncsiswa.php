<?php
require("config/config.default.php");
require("config/config.function.php");

header('Content-Type: application/json; charset=utf-8');

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$server = isset($_GET['server']) ? trim($_GET['server']) : '';

$querys = mysqli_query($koneksi, "select token_api from setting where token_api='$token'");
$cektoken = mysqli_num_rows($querys);

if ($cektoken <> 0) {
    $sql_server = '';
    if (!empty($server) && strtolower($server) !== 'all' && strtolower($server) !== 'online') {
        $server_escaped = mysqli_real_escape_string($koneksi, $server);
        $cek_server = mysqli_query($koneksi, "select id_siswa from siswa where server='$server_escaped' limit 1");
        if (mysqli_num_rows($cek_server) > 0) {
            $sql_server = " where server='$server_escaped'";
        }
    }
    $query = mysqli_query($koneksi, "select * from siswa" . $sql_server);
    $array_data = array();
    while ($baris = mysqli_fetch_assoc($query)) {
        $array_data[] = $baris;
    }

    echo json_encode([
        'status' => 'success',
        'total' => count($array_data),
        'siswa' => $array_data
    ]);
} else {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Token API tidak valid atau tidak cocok dengan server pusat!'
    ]);
}
