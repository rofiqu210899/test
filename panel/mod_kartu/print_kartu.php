<?php
require("../../config/config.default.php");
require("../../config/config.function.php");
require("../../config/functions.crud.php");
require("../../config/dis.php");

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

$logo_sekolah = !empty($setting['logo']) ? $homeurl . '/' . $setting['logo'] : $homeurl . '/dist/img/tutwuri.jpg';
$logo_almet = file_exists('../../foto/almet.png') ? $homeurl . '/foto/almet.png' : $homeurl . '/dist/img/tutwuri.jpg';
$ttd_file = file_exists('../../dist/img/ttd.png') ? $homeurl . '/dist/img/ttd.png' : '';
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
        /* UKURAN TETAP SETIAP KARTU (SERAGAM DAN RATA) */
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

<div class="no-print" style="position: fixed; top: 15px; right: 20px; z-index: 9999;">
    <button onclick="window.print()" style="background: #16a34a; color: #fff; border: none; padding: 8px 16px; font-size: 13px; font-weight: bold; border-radius: 6px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 6px;">
        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Cetak Kartu
    </button>
</div>

<div class="page-sheet">
    <table class="grid-kartu">
        <tr>
        <?php
        $siswaQ = mysqli_query($koneksi, "SELECT * FROM siswa $where_kelas ORDER BY id_kelas ASC, nama ASC");
        $no = 0;
        while ($siswa = mysqli_fetch_array($siswaQ)) :
            $no++;
            $kelas_nama = !empty($kelas_row['nama']) ? $kelas_row['nama'] : $siswa['id_kelas'];
        ?>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                <div class="kartu-item">
                    <!-- KOP KARTU -->
                    <table class="kop-table">
                        <tr>
                            <td style="width: 40px; text-align: left; vertical-align: middle;">
                                <img src="<?= $logo_almet ?>" class="kop-logo" onerror="this.style.visibility='hidden'">
                            </td>
                            <td class="kop-text">
                                <div class="kop-title"><?= strtoupper(!empty($setting['header_kartu']) ? $setting['header_kartu'] : 'KARTU PESERTA UJIAN') ?></div>
                                <div class="kop-school"><?= strtoupper($setting['sekolah']) ?></div>
                                <div class="kop-sub">TAHUN PELAJARAN <?= $ajaran ?></div>
                            </td>
                            <td style="width: 40px; text-align: right; vertical-align: middle;">
                                <img src="<?= $logo_sekolah ?>" class="kop-logo" onerror="this.style.visibility='hidden'">
                            </td>
                        </tr>
                    </table>

                    <!-- BADAN KARTU (FOTO & DATA) -->
                    <div class="content-wrap">
                        <!-- FOTO -->
                        <div class="foto-box">
                            <?php
                            $foto_path = "../../foto/fotosiswa/" . $siswa['foto'];
                            if (!empty($siswa['foto']) && file_exists($foto_path)) {
                                echo "<img src='{$homeurl}/foto/fotosiswa/{$siswa['foto']}' class='foto-img'>";
                            } else {
                                echo "<img src='{$homeurl}/dist/img/avatar_default.png' class='foto-img'>";
                            }
                            ?>
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
                                <?php if (!empty($ttd_file)) : ?>
                                    <img src="<?= $ttd_file ?>" class="ttd-img" onerror="this.style.display='none'">
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