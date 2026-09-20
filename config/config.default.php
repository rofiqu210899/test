<?php
session_start();
error_reporting(0);
(isset($_SESSION['id_user'])) ? $id_user = $_SESSION['id_user'] : $id_user = 0;

// ================= KONFIGURASI URL & ROUTING OTOMATIS =================
// 1. Deteksi Protokol (HTTP/HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// 2. Deteksi Base Subfolder secara Dinamis
$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base_path = '';
$panel_pos = stripos($script_name, '/panel/');
if ($panel_pos !== false) {
    $base_path = rtrim(substr($script_name, 0, $panel_pos), '/');
} elseif (substr($script_name, -6) === '/panel') {
    $base_path = rtrim(substr($script_name, 0, -6), '/');
} else {
    $dir = rtrim(dirname($script_name), '/');
    if ($dir !== '/' && $dir !== '.' && $dir !== '\\' && $dir !== '') {
        $base_path = $dir;
    }
}

// 3. Base Home URL
$homeurl = $protocol . $host . $base_path;

// 4. Routing Halaman
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
if ($base_path !== '' && stripos($uri, $base_path) === 0) {
    $uri = substr($uri, strlen($base_path));
}

$pageurl = explode('/', '/' . ltrim($uri, '/'));
$pg = isset($pageurl[1]) && $pageurl[1] !== '' ? $pageurl[1] : '';
$ac = isset($pageurl[2]) && $pageurl[2] !== '' ? $pageurl[2] : '';
$id = isset($pageurl[3]) && $pageurl[3] !== '' ? $pageurl[3] : 0;
// =====================================================================

require "config.database.php";

$no = $jam = $mnt = $dtk = 0;
$info = '';
$waktu = date('H:i:s');
$tanggal = date('Y-m-d');
$datetime = date('Y-m-d H:i:s');

define("KEY", "76310EEFF2B5D3C887F238976A421B638CFEB0942AB8249CD0A29B125C91B3E5");
