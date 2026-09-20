<?php
// Deteksi apakah sedang berjalan di komputer lokal / server LAN (Laragon / XAMPP / Private IP)
$host_server = $_SERVER['HTTP_HOST'] ?? '';
$is_win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// Deteksi apakah host adalah IP privat jaringan lokal (misal: 192.168.x.x, 10.x.x.x, 172.16-31.x.x)
$host_only = strtok($host_server, ':');
$is_private_ip = false;
if (filter_var($host_only, FILTER_VALIDATE_IP)) {
    $is_private_ip = !filter_var(
        $host_only,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
}

$is_local = (
    $is_win ||
    $is_private_ip ||
    strpos($host_server, 'localhost') !== false ||
    strpos($host_server, '127.0.0.1') !== false ||
    substr($host_server, -5) === '.test' ||
    substr($host_server, -6) === '.local' ||
    substr($host_server, -4) === '.lan' ||
    php_sapi_name() === 'cli'
);

// Pengecualian: Hanya jika diakses via domain hosting resmi dan server bukan Windows
if (strpos($host_server, 'smpblokagung.com') !== false && !$is_win) {
    $is_local = false;
}

if ($is_local) {
    // ================= PENGATURAN LOKAL (XAMPP / LARAGON) =================
    $host = 'localhost';
    $user = 'root';
    $pass = '';

    // Coba koneksi ke MySQL root lokal
    $koneksi = @mysqli_connect($host, $user, $pass, "");
    if (!$koneksi) {
        $fallback_passes = ['root', 'toor', 'admin', '123456'];
        foreach ($fallback_passes as $fp) {
            $koneksi = @mysqli_connect($host, $user, $fp, "");
            if ($koneksi) {
                $pass = $fp;
                break;
            }
        }
    }

    // Deteksi otomatis nama database yang tersedia di lokal
    $app_dir = basename(dirname(__DIR__)); // misal 'cbt' atau 'test'
    $db_candidates = [];
    if (!empty($app_dir)) {
        $db_candidates[] = $app_dir;
    }
    $db_candidates[] = 'test';
    $db_candidates[] = 'cbt';
    $db_candidates[] = 'candycbt';
    $db_candidates[] = 'cbtcandy';
    $db_candidates[] = 'u332410731_uasbk';

    $debe = 'test';
    if ($koneksi) {
        foreach ($db_candidates as $candidate) {
            if (@mysqli_select_db($koneksi, $candidate)) {
                $cek_tbl = @mysqli_query($koneksi, "SHOW TABLES LIKE 'setting'");
                if ($cek_tbl && mysqli_num_rows($cek_tbl) > 0) {
                    $debe = $candidate;
                    break;
                }
            }
        }
    }
} else {
    // ================= PENGATURAN HOSTING (PRODUCTION) =============
    $host = 'localhost';
    $user = 'u332410731_uasbk';
    $pass = '@Chikining1999';
    $debe = 'u332410731_uasbk';
    $koneksi = @mysqli_connect($host, $user, $pass, "");
}

// Inisialisasi Database & Pengaturan Aplikasi
if ($koneksi) {
    $pilihdb = @mysqli_select_db($koneksi, $debe);
    if ($pilihdb) {
        mysqli_set_charset($koneksi, 'utf8');
        $query = @mysqli_query($koneksi, "SELECT * FROM setting WHERE id_setting='1'");
        if ($query && mysqli_num_rows($query) > 0) {
            $setting = mysqli_fetch_array($query);
            $sess = @mysqli_fetch_array(@mysqli_query($koneksi, "SELECT * FROM session WHERE id='1'"));
            if (!empty($setting['waktu'])) {
                date_default_timezone_set($setting['waktu']);
            }
        }
    }
}
