<?php
// Deteksi apakah sedang berjalan di komputer lokal (Localhost / Laragon)
$host_server = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = (
    strpos($host_server, 'localhost') !== false ||
    strpos($host_server, '127.0.0.1') !== false ||
    substr($host_server, -5) === '.test'
);

if ($is_local) {
    // ================= PENGATURAN LOKAL (LARAGON) =================
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $debe = 'test';
} else {
    // ================= PENGATURAN HOSTING (PRODUCTION) =============
    $host = 'localhost';
    $user = 'u332410731_uasbk';
    $pass = '@Chikining1999';
    $debe = 'u332410731_uasbk';
}

$koneksi = mysqli_connect($host, $user, $pass, "");
if ($koneksi) {
	$pilihdb = mysqli_select_db($koneksi, $debe);
	if ($pilihdb) {
		$query = mysqli_query($koneksi, "SELECT * FROM setting WHERE id_setting='1'");
		if ($query) {
			$setting = mysqli_fetch_array($query);
			mysqli_set_charset($koneksi, 'utf8');
			$sess = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM session WHERE id='1'"));
			date_default_timezone_set($setting['waktu']);
		}
	}
}
