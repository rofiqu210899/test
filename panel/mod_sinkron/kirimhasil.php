<?php
require "../../config/config.default.php";
require "../../config/config.function.php";
cek_session_admin();
if ($koneksi) {
    $idujian = (int)($_POST['id'] ?? 0);
    $query = mysqli_query($koneksi, "SELECT * FROM nilai WHERE id_ujian='$idujian' AND status IS NULL");
    $cek = mysqli_num_rows($query);
    if ($cek == 0) {
        // Jika semua sudah berstatus 1 tapi admin ingin sinkronkan ulang / kirim paksa
        $query = mysqli_query($koneksi, "SELECT * FROM nilai WHERE id_ujian='$idujian' AND ujian_selesai IS NOT NULL");
        $cek = mysqli_num_rows($query);
    }

    if ($cek <> 0) {
        $array_nilai = array();
        while ($nilai = mysqli_fetch_assoc($query)) {
            $array_nilai[] = $nilai;
        }
        $payload = json_encode($array_nilai);

        $url_host = rtrim($setting['url_host'] ?? '', '/');
        $url = $url_host . '/syncnilai.php?token=' . urlencode($setting['token_api'] ?? '');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if (trim($result) === 'berhasil' || ($http_code == 200 && strpos($result, 'berhasil') !== false)) {
            mysqli_query($koneksi, "UPDATE nilai SET status='1' WHERE id_ujian='$idujian'");
            echo '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
                Sebanyak ' . $cek . ' data hasil ujian berhasil dikirimkan ke server utama.
            </div>';
        } else {
            $err = !empty($curl_err) ? $curl_err : htmlspecialchars($result);
            echo '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Gagal Mengirim Nilai!</h4>
                Koneksi gagal atau server utama menolak pengiriman.<br>
                <b>Detail:</b> ' . $err . ' (HTTP ' . $http_code . ')
            </div>';
        }
    } else {
        echo '<div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info-circle"></i> Tidak Ada Data</h4>
            Tidak ada data nilai siswa yang selesai pada ujian ini untuk dikirimkan.
        </div>';
    }
} else {
    echo '<div class="alert alert-danger">Koneksi database lokal terputus.</div>';
}
