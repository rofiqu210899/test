<?php
require '../../config/config.default.php';
require '../../config/config.function.php';
cek_session_admin();
$url_host = rtrim($setting['url_host'] ?? '', '/');
$url = $url_host . "/syncsiswa.php?token=" . urlencode($setting['token_api'] ?? '') . "&server=" . urlencode($setting['id_server'] ?? '');
$datax = http_request($url);
$r = json_decode($datax, TRUE);

if ($r !== null && (isset($r['siswa']) || ($r['status'] ?? '') === 'success')) {
    $total = isset($r['siswa']) ? count($r['siswa']) : 0;
    echo "<h3 class='text-green'><i class='fa fa-check-circle'></i> Terhubung</h3>Kode Server: <b>" . htmlspecialchars($setting['id_server']) . "</b> (Tersedia $total Siswa)";
} elseif ($r !== null && isset($r['message'])) {
    echo "<h3 class='text-yellow'><i class='fa fa-exclamation-triangle'></i> Gagal Autentikasi</h3>" . htmlspecialchars($r['message']);
} else {
    echo "<h3 class='text-red'><i class='fa fa-times-circle'></i> Tidak Ada Koneksi</h3>Periksa kembali URL Server Pusat (" . htmlspecialchars($url_host) . ") dan Token";
}
