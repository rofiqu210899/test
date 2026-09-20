<?php
require("config/config.default.php");
require("config/config.function.php");

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$querys = mysqli_query($koneksi, "select token_api from setting where token_api='$token'");
$cektoken = mysqli_num_rows($querys);

if ($cektoken <> 0) {
    if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
        http_response_code(405);
        echo "Method Not Allowed";
        exit;
    }

    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);

    if (!is_array($decoded)) {
        http_response_code(400);
        echo "Invalid JSON";
        exit;
    }

    $sukses = 0;
    foreach ($decoded as $nilai) {
        $id_siswa = (int)($nilai['id_siswa'] ?? 0);
        $id_ujian = (int)($nilai['id_ujian'] ?? 0);
        $id_mapel = (int)($nilai['id_mapel'] ?? 0);

        if (!$id_siswa || !$id_ujian || !$id_mapel) {
            continue;
        }

        $kode_ujian = mysqli_real_escape_string($koneksi, $nilai['kode_ujian'] ?? '');
        $ujian_mulai = mysqli_real_escape_string($koneksi, $nilai['ujian_mulai'] ?? '');
        $ujian_berlangsung = mysqli_real_escape_string($koneksi, $nilai['ujian_berlangsung'] ?? '');
        $ujian_selesai = mysqli_real_escape_string($koneksi, $nilai['ujian_selesai'] ?? '');
        $jml_benar = (int)($nilai['jml_benar'] ?? 0);
        $jml_salah = (int)($nilai['jml_salah'] ?? 0);
        $skor = (float)($nilai['skor'] ?? 0);
        $total = (float)($nilai['total'] ?? 0);
        $ipaddress = mysqli_real_escape_string($koneksi, $nilai['ipaddress'] ?? '');
        $hasil = mysqli_real_escape_string($koneksi, $nilai['hasil'] ?? '');
        $jawaban = mysqli_real_escape_string($koneksi, $nilai['jawaban'] ?? '');
        $jawaban_esai = mysqli_real_escape_string($koneksi, $nilai['jawaban_esai'] ?? '');
        $no_soal_aktif = (int)($nilai['no_soal_aktif'] ?? 0);

        $cek = mysqli_num_rows(mysqli_query($koneksi, "select id_nilai from nilai where id_siswa='$id_siswa' and id_ujian='$id_ujian' and id_mapel='$id_mapel'"));
        if ($cek == 0) {
            mysqli_query($koneksi, "insert into nilai 
                (id_ujian,id_mapel,id_siswa,kode_ujian,ujian_mulai,ujian_berlangsung,ujian_selesai,jml_benar,jml_salah,skor,total,ipaddress,hasil,jawaban,jawaban_esai,no_soal_aktif,status)
            values 
                ('$id_ujian','$id_mapel','$id_siswa','$kode_ujian','$ujian_mulai','$ujian_berlangsung','$ujian_selesai','$jml_benar','$jml_salah','$skor','$total','$ipaddress','$hasil','$jawaban','$jawaban_esai','$no_soal_aktif','1')");
        } else {
            mysqli_query($koneksi, "update nilai set 
                kode_ujian='$kode_ujian',
                ujian_mulai='$ujian_mulai',
                ujian_berlangsung='$ujian_berlangsung',
                ujian_selesai='$ujian_selesai',
                jml_benar='$jml_benar',
                jml_salah='$jml_salah',
                skor='$skor',
                total='$total',
                ipaddress='$ipaddress',
                hasil='$hasil',
                jawaban='$jawaban',
                jawaban_esai='$jawaban_esai',
                no_soal_aktif='$no_soal_aktif',
                status='1'
            where id_siswa='$id_siswa' and id_ujian='$id_ujian' and id_mapel='$id_mapel'");
        }
        $sukses++;
    }

    echo "berhasil";
} else {
    http_response_code(403);
    echo "Token API tidak valid";
}
