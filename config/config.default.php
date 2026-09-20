<?php
session_start();
error_reporting(0);
(isset($_SESSION['id_user'])) ? $id_user = $_SESSION['id_user'] : $id_user = 0;

// ================= KONFIGURASI URL & ROUTING OTOMATIS =================
// 1. Deteksi Protokol (HTTP/HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// 2. Deteksi Base Subfolder secara Dinamis (Case-Insensitive untuk Windows & SCRIPT_NAME Fallback)
$doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
$app_root = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$base_path = '';
if (!empty($doc_root) && stripos($app_root, $doc_root) === 0) {
    $base_path = substr($app_root, strlen($doc_root));
}
if (empty($base_path) && isset($_SERVER['SCRIPT_NAME'])) {
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if (substr($script_dir, -6) === '/panel') {
        $script_dir = substr($script_dir, 0, -6);
    }
    if (!empty($script_dir) && $script_dir !== '/' && $script_dir !== '.') {
        $base_path = $script_dir;
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
