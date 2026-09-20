<?php
require("config/config.default.php");
require("config/config.function.php");
require("config/functions.crud.php");
cek_session_siswa();

$idm = (int)($_POST['id_mapel'] ?? 0);
$ids = (int)($_POST['id_siswa'] ?? 0);
$idu = (int)($_POST['id_ujian'] ?? 0);

if (!$idm || !$ids || !$idu) {
    echo "invalid";
    exit;
}

$where = array(
    'id_mapel' => $idm,
    'id_siswa' => $ids,
    'id_ujian' => $idu
);

// Idempotency check: if exam has already been submitted and scored, do not overwrite or reset!
$cek_nilai = fetch($koneksi, 'nilai', $where);
if ($cek_nilai && !empty($cek_nilai['ujian_selesai'])) {
    echo "ok";
    exit;
}

$benar = 0;
$salah = 0;

// Fetch config from ujian first, fallback to mapel
$ujian = fetch($koneksi, 'ujian', array('id_ujian' => $idu));
$mapel = fetch($koneksi, 'mapel', array('id_mapel' => $idm));

$ceksoal = select($koneksi, 'soal', array('id_mapel' => $idm, 'jenis' => 1));
$ceksoalesai = select($koneksi, 'soal', array('id_mapel' => $idm, 'jenis' => 2));

$total_pg_bank = is_array($ceksoal) ? count($ceksoal) : 0;
$tampil_pg = 0;
if (!empty($ujian['tampil_pg']) && $ujian['tampil_pg'] > 0) {
    $tampil_pg = (int)$ujian['tampil_pg'];
} elseif (!empty($mapel['tampil_pg']) && $mapel['tampil_pg'] > 0) {
    $tampil_pg = (int)$mapel['tampil_pg'];
} else {
    $tampil_pg = $total_pg_bank;
}
if ($tampil_pg <= 0) {
    $tampil_pg = max(1, $total_pg_bank);
}

$bobot_pg = 100;
if (isset($ujian['bobot_pg']) && is_numeric($ujian['bobot_pg']) && $ujian['bobot_pg'] > 0) {
    $bobot_pg = (float)$ujian['bobot_pg'];
} elseif (isset($mapel['bobot_pg']) && is_numeric($mapel['bobot_pg']) && $mapel['bobot_pg'] > 0) {
    $bobot_pg = (float)$mapel['bobot_pg'];
}

// 1. Process Essay Questions
$arrayjawabesai = array();
if (is_array($ceksoalesai)) {
    foreach ($ceksoalesai as $getsoalesai) {
        $w2 = array(
            'id_siswa' => $ids,
            'id_mapel' => $idm,
            'id_soal' => $getsoalesai['id_soal'],
            'id_ujian' => $idu,
            'jenis' => 2
        );
        $getjwb2 = fetch($koneksi, 'jawaban_temp', $w2);
        if (!$getjwb2) {
            $getjwb2 = fetch($koneksi, 'jawaban', $w2);
        }
        if ($getjwb2 && !empty($getjwb2['esai'])) {
            $jawabxx = str_replace("'", "`", $getjwb2['esai']);
            $jawabxx = str_replace("#", ">>", $jawabxx);
            $jawabxx = preg_replace('/[^A-Za-z0-9\@\<\>\$\_\&\-\+\(\)\/\?\!\;\:\`\"\[\]\*\{\}\=\%\~\`\÷\× ]/', '', $jawabxx);
            $arrayjawabesai[$getsoalesai['id_soal']] = $jawabxx;
        } else {
            $arrayjawabesai[$getsoalesai['id_soal']] = 'Tidak Diisi';
        }
    }
}

// 2. Process Multiple Choice Questions (PG)
$arrayjawab = array();
if (is_array($ceksoal)) {
    foreach ($ceksoal as $getsoal) {
        $w = array(
            'id_siswa' => $ids,
            'id_mapel' => $idm,
            'id_soal' => $getsoal['id_soal'],
            'id_ujian' => $idu,
            'jenis' => 1
        );
        $getjwb = fetch($koneksi, 'jawaban_temp', $w);
        if (!$getjwb) {
            $getjwb = fetch($koneksi, 'jawaban', $w);
        }

        if ($getjwb && !empty($getjwb['jawaban']) && strtoupper($getjwb['jawaban']) != 'X') {
            $arrayjawab[$getsoal['id_soal']] = $getjwb['jawaban'];
            if (strtoupper($getjwb['jawaban']) == strtoupper($getsoal['jawaban'])) {
                $benar++;
            } else {
                $salah++;
            }
        } else {
            $arrayjawab[$getsoal['id_soal']] = 'X';
            $salah++;
        }
    }
}

// Calculate score safely without division by zero
$skor = round(($benar / $tampil_pg) * $bobot_pg, 2);
$jml_salah = max(0, $tampil_pg - $benar);

$data = array(
    'ujian_selesai' => $datetime,
    'jml_benar' => $benar,
    'jml_salah' => $jml_salah,
    'skor' => $skor,
    'total' => $skor,
    'online' => 0,
    'jawaban' => serialize($arrayjawab),
    'jawaban_esai' => serialize($arrayjawabesai)
);

$simpan = update($koneksi, 'nilai', $data, $where);
if ($simpan) {
    // Migrate temporary answers to permanent jawaban table safely
    mysqli_query($koneksi, "INSERT IGNORE INTO jawaban (id_jawaban,id_siswa,id_mapel,id_soal,id_ujian,jawaban,jawabx,jenis,esai,nilai_esai,ragu) 
        SELECT id_jawaban, id_siswa, id_mapel, id_soal, id_ujian, jawaban, jawabx, jenis, esai, nilai_esai, ragu 
        FROM jawaban_temp 
        WHERE id_ujian='$idu' AND id_mapel='$idm' AND id_siswa='$ids'");

    // Clean up temporary answers for this student & exam
    mysqli_query($koneksi, "DELETE FROM jawaban_temp WHERE id_ujian='$idu' AND id_mapel='$idm' AND id_siswa='$ids'");

    // AUTO-SYNC NILAI KE SERVER UTAMA JIKA SERVER MODE LOKAL
    if (($setting['server'] ?? '') === 'lokal' && !empty($setting['url_host'])) {
        $row_nilai = fetch($koneksi, 'nilai', $where);
        if ($row_nilai) {
            $payload = json_encode([$row_nilai]);
            $url_host = rtrim($setting['url_host'], '/');
            $url = $url_host . '/syncnilai.php?token=' . urlencode($setting['token_api'] ?? '');

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

            $res = curl_exec($ch);
            $hcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (trim($res) === 'berhasil' || ($hcode == 200 && strpos($res, 'berhasil') !== false)) {
                mysqli_query($koneksi, "UPDATE nilai SET status='1' WHERE id_ujian='$idu' AND id_mapel='$idm' AND id_siswa='$ids'");
            }
        }
    }
}

mysqli_query($koneksi, "INSERT INTO log (id_siswa,type,text,date) VALUES ('$ids','login','Selesai Ujian','$tanggal $waktu')");
echo "ok";
