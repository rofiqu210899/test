<?php
require(__DIR__ . "/../../config/config.default.php");
require(__DIR__ . "/../../config/config.function.php");
require(__DIR__ . "/../../config/functions.crud.php");
require(__DIR__ . "/../../config/dis.php");

(isset($_SESSION['id_pengawas'])) ? $id_pengawas = $_SESSION['id_pengawas'] : $id_pengawas = 0;
($id_pengawas == 0) ? header('location:index.php') : null;

$id_kelas = isset($_GET['id_kelas']) ? trim($_GET['id_kelas']) : '';
if (date('m') >= 7 and date('m') <= 12) {
    $ajaran = date('Y') . "/" . (date('Y') + 1);
} elseif (date('m') >= 1 and date('m') <= 6) {
    $ajaran = (date('Y') - 1) . "/" . date('Y');
}

$kelas_row = null;
if (!empty($id_kelas) && strtolower($id_kelas) !== 'semua') {
    $id_kelas_esc = mysqli_real_escape_string($koneksi, $id_kelas);
    $kelas_row = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas='$id_kelas_esc'"));
    $where_kelas = "WHERE id_kelas='$id_kelas_esc'";
} else {
    $where_kelas = "";
}

// Helper untuk menghasilkan URL gambar yang valid dan dapat di-cache oleh browser
function card_asset_src($rel_path, $homeurl, $fallback_rel = '') {
    $clean_path = ltrim($rel_path, '/');
    $abs_path = __DIR__ . '/../../' . $clean_path;
    if (file_exists($abs_path) && !is_dir($abs_path) && filesize($abs_path) > 0) {
        return rtrim($homeurl, '/') . '/' . $clean_path;
    }
    if (!empty($fallback_rel)) {
        $fallback_clean = ltrim($fallback_rel, '/');
        return rtrim($homeurl, '/') . '/' . $fallback_clean;
    }
    return rtrim($homeurl, '/') . '/' . $clean_path;
}

// Aset Kop & TTD
$logo_sekolah_src = !empty($setting['logo']) ? card_asset_src($setting['logo'], $homeurl, 'dist/img/tutwuri.jpg') : card_asset_src('dist/img/tutwuri.jpg', $homeurl);
$logo_almet_src = file_exists(__DIR__ . '/../../foto/almet.png') ? card_asset_src('foto/almet.png', $homeurl) : card_asset_src('dist/img/tutwuri.jpg', $homeurl);
$ttd_src = file_exists(__DIR__ . '/../../dist/img/ttd.png') ? card_asset_src('dist/img/ttd.png', $homeurl) : '';
$default_avatar_src = card_asset_src('dist/img/avatar_default.png', $homeurl);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Peserta - <?= htmlspecialchars($setting['aplikasi']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 5mm 6mm 5mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 8px 0;
            background: #eef2f5;
        }
        .page-sheet {
            width: 200mm;
            margin: 0 auto;
            background: #fff;
            padding: 2mm 3mm;
            box-shadow: 0 0 8px rgba(0,0,0,0.15);
        }
        .grid-kartu {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3mm 2.5mm;
            margin: 0 auto;
        }
        /* UKURAN TETAP SETIAP KARTU (SERAGAM DAN RATA 100%) */
        .kartu-item {
            width: 96mm;
            height: 66mm;
            min-height: 66mm;
            max-height: 66mm;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 4px 6px 3px 6px;
            position: relative;
            background: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Header Kop */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .kop-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            display: block;
        }
        .kop-text {
            text-align: center;
            line-height: 1.15;
            padding: 0 4px;
        }
        .kop-title {
            font-size: 9pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .kop-school {
            font-size: 9pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .kop-sub {
            font-size: 7.5pt;
            font-weight: normal;
            color: #333;
        }

        /* Badan Kartu */
        .content-wrap {
            display: flex;
            flex: 1;
            align-items: flex-start;
            overflow: hidden;
        }
        .foto-box {
            width: 50px;
            vertical-align: top;
            text-align: center;
            padding-right: 5px;
            flex-shrink: 0;
        }
        .foto-img {
            width: 48px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #bbb;
            border-radius: 2px;
            display: block;
            margin: 0 auto;
            background: #fafafa;
        }
        .bio-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            line-height: 1.25;
        }
        .bio-label {
            width: 26%;
            color: #333;
            vertical-align: top;
            white-space: nowrap;
        }
        .bio-sep {
            width: 3%;
            vertical-align: top;
            text-align: center;
        }
        .bio-val {
            vertical-align: top;
            color: #000;
            font-weight: 500;
        }
        /* Nama panjang terkontrol dalam 2 baris tanpa mengubah tinggi kartu */
        .bio-nama {
            font-weight: bold;
            max-height: 24px;
            line-height: 12px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            word-break: break-word;
        }
        .bio-code {
            font-family: 'Consolas', monospace, sans-serif;
            font-weight: bold;
            font-size: 9pt;
            letter-spacing: 0.5px;
        }

        /* Footer Tanda Tangan */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1px;
        }
        .ttd-box {
            width: 55%;
            text-align: center;
            font-size: 7.5pt;
            line-height: 1.15;
            position: relative;
        }
        .ttd-img {
            height: 30px;
            position: absolute;
            left: 50%;
            top: 10px;
            transform: translateX(-50%);
            z-index: 1;
            opacity: 0.85;
            pointer-events: none;
        }
        .kepsek-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .page-sheet {
                width: 100%;
                padding: 0;
                box-shadow: none;
                margin: 0;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
        }
    </style>
</head>
<body>

<div class="page-sheet">
    <table class="grid-kartu">
        <tr>
        <?php
        $siswaQ = mysqli_query($koneksi, "SELECT * FROM siswa $where_kelas ORDER BY id_kelas ASC, nama ASC");
        $no = 0;
        while ($siswa = mysqli_fetch_array($siswaQ)) :
            $no++;
            $kelas_nama = !empty($kelas_row['nama']) ? $kelas_row['nama'] : $siswa['id_kelas'];
            
            // Foto Siswa
            $foto_src = $default_avatar_src;
            if (!empty($siswa['foto'])) {
                $check_foto = "foto/fotosiswa/" . $siswa['foto'];
                if (file_exists(__DIR__ . '/../../' . $check_foto)) {
                    $foto_src = card_asset_src($check_foto, $homeurl);
                }
            }
        ?>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                <div class="kartu-item">
                    <!-- KOP KARTU -->
                    <table class="kop-table">
                        <tr>
                            <td style="width: 40px; text-align: left; vertical-align: middle;">
                                <img src="<?= $logo_almet_src ?>" class="kop-logo">
                            </td>
                            <td class="kop-text">
                                <div class="kop-title"><?= strtoupper(!empty($setting['header_kartu']) ? $setting['header_kartu'] : 'KARTU PESERTA UJIAN') ?></div>
                                <div class="kop-school"><?= strtoupper($setting['sekolah']) ?></div>
                                <div class="kop-sub">TAHUN PELAJARAN <?= $ajaran ?></div>
                            </td>
                            <td style="width: 40px; text-align: right; vertical-align: middle;">
                                <img src="<?= $logo_sekolah_src ?>" class="kop-logo">
                            </td>
                        </tr>
                    </table>

                    <!-- BADAN KARTU (FOTO & DATA) -->
                    <div class="content-wrap">
                        <!-- FOTO -->
                        <div class="foto-box">
                            <img src="<?= $foto_src ?>" class="foto-img" onerror="this.onerror=null;this.src='<?= $default_avatar_src ?>';">
                        </div>

                        <!-- DATA SISWA -->
                        <div style="flex: 1;">
                            <table class="bio-table">
                                <tr>
                                    <td class="bio-label">No Peserta</td>
                                    <td class="bio-sep">:</td>
                                    <td class="bio-val"><?= htmlspecialchars($siswa['no_peserta']) ?></td>
                                </tr>
                                <tr>
                                    <td class="bio-label">Nama</td>
                                    <td class="bio-sep">:</td>
                                    <td class="bio-val"><div class="bio-nama"><?= htmlspecialchars($siswa['nama']) ?></div></td>
                                </tr>
                                <tr>
                                    <td class="bio-label">Kelas / Sesi</td>
                                    <td class="bio-sep">:</td>
                                    <td class="bio-val"><?= htmlspecialchars($kelas_nama) ?> / Sesi <?= htmlspecialchars($siswa['sesi']) ?></td>
                                </tr>
                                <tr>
                                    <td class="bio-label">Username</td>
                                    <td class="bio-sep">:</td>
                                    <td class="bio-val bio-code"><?= htmlspecialchars($siswa['username']) ?></td>
                                </tr>
                                <tr>
                                    <td class="bio-label">Password</td>
                                    <td class="bio-sep">:</td>
                                    <td class="bio-val bio-code"><?= htmlspecialchars($siswa['password']) ?></td>
                                </tr>
                                <tr>
                                    <td class="bio-label">Ruang</td>
                                    <td class="bio-sep">:</td>
                                    <td class="bio-val"><?= htmlspecialchars($siswa['ruang']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- FOOTER TANDA TANGAN -->
                    <table class="footer-table">
                        <tr>
                            <td style="width: 45%;"></td>
                            <td class="ttd-box">
                                Kepala Sekolah,<br>
                                <?php if (!empty($ttd_src)) : ?>
                                    <img src="<?= $ttd_src ?>" class="ttd-img">
                                <?php endif; ?>
                                <div style="height: 20px;"></div>
                                <div class="kepsek-nama"><?= htmlspecialchars(!empty($setting['kepsek']) ? $setting['kepsek'] : 'Kepala Sekolah') ?></div>
                                <div>NIP. <?= htmlspecialchars(!empty($setting['nip']) ? $setting['nip'] : '-') ?></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <?php if (($no % 2) == 0 && ($no % 8) != 0) : ?>
                </tr><tr>
            <?php elseif (($no % 8) == 0) : ?>
                </tr>
                </table>
                <div class="page-break"></div>
                <table class="grid-kartu">
                <tr>
            <?php endif; ?>
        <?php endwhile; ?>
        </tr>
    </table>
</div>

</body>
</html>