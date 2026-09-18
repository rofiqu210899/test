<?php
require("../../config/config.default.php");
require("../../config/config.function.php");
require("../../config/functions.crud.php");
cek_session_guru();
(isset($_GET['pg'])) ? $pg = $_GET['pg'] : $pg = '';
if ($pg == 'ubah') {
    $id = $_POST['idu'];
    if (isset($_POST['status'])) {
        $status = $_POST['status'];
    } else {
        $status = 'tidak';
    }
    if (isset($_POST['idpk'])) {
        $pk = $_POST['idpk'];
    } else {
        $pk = 'semua';
    }
    if ($_POST['pass1'] <> '') {

        $data = [
            'id_kelas'     => $_POST['id_kelas'],
            'idpk'         => $pk,
            'nis'          => $_POST['nis'],
            'no_peserta'   => $_POST['no_peserta'],
            'nama'         => str_replace("'", "&#39;", $_POST['nama']),
            'sesi'         => $_POST['idsesi'],
            'ruang'        => $_POST['ruang'],
            'level'        => $_POST['level'],
            'username'     => $_POST['username'],
            'password'     => $_POST['pass1'],
            'server'       => $_POST['server'],
            'agama'        => $_POST['agama'],
            'status'       => $status,
            'hp'        => $_POST['hp']
        ];
    } else {
        $data = [
            'id_kelas'     => $_POST['id_kelas'],
            'idpk'         => $pk,
            'nis'          => $_POST['nis'],
            'no_peserta'   => $_POST['no_peserta'],
            'nama'         => str_replace("'", "&#39;", $_POST['nama']),
            'sesi'         => $_POST['idsesi'],
            'ruang'        => $_POST['ruang'],
            'level'        => $_POST['level'],
            'username'     => $_POST['username'],

            'server'       => $_POST['server'],
            'agama'        => $_POST['agama'],
            'status'       => $status,
            'hp'        => $_POST['hp']
        ];
    }

    if ($_POST['pass1'] <> $_POST['pass2']) {
        echo "password tidak sama";
    } else {
        $exec = update($koneksi, 'siswa', $data, ['id_siswa' => $id]);
        echo $exec;
    }
}
if ($pg == 'tambah') {
    if (isset($_POST['idpk'])) {
        $pk = $_POST['idpk'];
    } else {
        $pk = 'semua';
    }
    $data = [
        'id_kelas'     => $_POST['id_kelas'],
        'idpk'         => $pk,
        'nis'          => $_POST['nis'],
        'no_peserta'   => $_POST['no_peserta'],
        'nama'         => str_replace("'", "&#39;", $_POST['nama']),
        'sesi'         => $_POST['idsesi'],
        'ruang'        => $_POST['ruang'],
        'level'        => $_POST['level'],
        'username'     => $_POST['username'],
        'password'     => $_POST['pass1'],
        'server'       => $_POST['server'],
        'agama'        => $_POST['agama'],
        'hp'        => $_POST['hp']
    ];
    $cekuser = rowcount($koneksi, 'siswa', ['username' => $_POST['username']]);
    if ($cekuser > 0) {
        echo "username sudah ada";
    } else {
        if ($_POST['pass1'] <> $_POST['pass2']) {
            echo "password tidak sama";
        } else {
            $exec = insert($koneksi, 'siswa', $data);
            echo $exec;
        }
    }
}
if ($pg == 'hapus') {
    $id_siswa = $_POST['id_siswa'];
    delete($koneksi, 'siswa', ['id_siswa' => $id_siswa]);
}
if ($pg == 'ambil_siswa') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
        // include '../config/config.default.php';
        $query = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama ASC");
        $jsonResult = '{"data" : [ ';
        $i = 0;
        while ($data = mysqli_fetch_assoc($query)) {
            if ($i != 0) {
                $jsonResult .= ',';
            }
            $jsonResult .= json_encode($data);
            $i++;
        }
        $jsonResult .= ']}';
        echo $jsonResult;
    } else {
        echo '<script>window.location="404.html"</script>';
    }
}
if ($pg == 'uploadfoto') {
    if (isset($_POST["uplod"])) {
        $output = '';
        if (!empty($_FILES['zip_file']['name'])) {
            $file_name = $_FILES['zip_file']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($ext == 'zip') {
                $path = '../foto/fotosiswa/';
                $location = $path . time() . '_' . mt_rand(1000, 9999) . '.zip';
                if (move_uploaded_file($_FILES['zip_file']['tmp_name'], $location)) {
                    $zip = new ZipArchive;
                    if ($zip->open($location) === true) {
                        $allowed_ext = array('jpg', 'jpeg', 'png');
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $stat = $zip->statIndex($i);
                            $rawName = $stat['name'];
                            $entryName = basename($rawName);
                            $entryExt = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));

                            if (strpos($rawName, '..') !== false || substr($entryName, 0, 1) === '.') {
                                continue;
                            }

                            if (in_array($entryExt, $allowed_ext)) {
                                $targetPath = $path . $entryName;
                                $stream = $zip->getStream($rawName);
                                if ($stream) {
                                    file_put_contents($targetPath, stream_get_contents($stream));
                                    fclose($stream);
                                    $tmp = explode(".", $entryName);
                                    $nama = $tmp[0];
                                    mysqli_query($koneksi, "UPDATE siswa set foto='$entryName' where username='$nama'");
                                }
                            }
                        }
                        $zip->close();
                    }
                    @unlink($location);
                    $pesan = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button><h4><i class='icon fa fa-check'></i> Info</h4>Upload File zip berhasil</div>";
                }
            } else {
                $pesan = "<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button><h4><i class='icon fa fa-info'></i> Gagal Upload</h4>Mohon Upload file zip</div>";
            }
        }
    }
}
