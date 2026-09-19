<?php
require "config/config.default.php";
require "config/config.function.php";
require "config/functions.crud.php";
cek_session_siswa();

$id_mapel = (int)($_POST['id_mapel'] ?? 0);
$id_siswa = (int)($_POST['id_siswa'] ?? 0);
$id_ujian = (int)($_POST['id_ujian'] ?? 0);

$cekpg = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM soal WHERE id_mapel='$id_mapel' AND jenis='1'"));
$cekesai = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM soal WHERE id_mapel='$id_mapel' AND jenis='2'"));

$qujian = mysqli_fetch_array(mysqli_query($koneksi, "SELECT tampil_pg,tampil_esai FROM ujian WHERE id_ujian='$id_ujian'"));
$quero = mysqli_fetch_array(mysqli_query($koneksi, "SELECT tampil_pg,tampil_esai FROM mapel WHERE id_mapel='$id_mapel'"));

$target_pg = (!empty($qujian['tampil_pg']) && $qujian['tampil_pg'] > 0) ? (int)$qujian['tampil_pg'] : ((!empty($quero['tampil_pg']) && $quero['tampil_pg'] > 0) ? (int)$quero['tampil_pg'] : $cekpg);
$target_esai = (!empty($qujian['tampil_esai']) && $qujian['tampil_esai'] > 0) ? (int)$qujian['tampil_esai'] : ((!empty($quero['tampil_esai']) && $quero['tampil_esai'] > 0) ? (int)$quero['tampil_esai'] : $cekesai);

$soalpg = min($cekpg, $target_pg);
$soalesai = min($cekesai, $target_esai);

$jumjawab = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM jawaban_temp WHERE id_mapel='$id_mapel' AND id_siswa='$id_siswa' AND id_ujian='$id_ujian'"));
if ($jumjawab == 0) {
    // Check if answers were already stored in jawaban
    $jumjawab = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM jawaban WHERE id_mapel='$id_mapel' AND id_siswa='$id_siswa' AND id_ujian='$id_ujian'"));
}

$cekragu = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM jawaban_temp WHERE id_mapel='$id_mapel' AND id_siswa='$id_siswa' AND id_ujian='$id_ujian' AND ragu='1'"));
$jumsoal = $soalpg + $soalesai;

if ($jumjawab >= $jumsoal) {
    if ($cekragu == 0) {
        echo "ok";
    } else {
        echo "ragu";
    }
} else {
    echo "belum";
}
