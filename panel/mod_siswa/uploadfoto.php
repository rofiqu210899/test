<?php
defined('APLIKASI') or exit('Akses tidak diizinkan');
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
?>
<?php
if (isset($_POST['hapussemuasfoto'])) {
    $files = glob('../foto/fotosiswa/*'); // Ambil semua file yang ada dalam folder
    foreach ($files as $file) { // Lakukan perulangan dari file yang kita ambil
        if (is_file($file)) // Cek apakah file tersebut benar-benar ada
            unlink($file); // Jika ada, hapus file tersebut
    }
}
?>
<div class='box box-danger'>
    <div class='box-header with-border'>
        <h3 class='box-title'>Upload Foto Peserta Ujian</h3>
        <div class='box-tools pull-right '>
            <a href='?pg=siswa' class='btn btn-sm bg-maroon' title='Batal'><i class='fa fa-times'></i></a>
        </div>
    </div><!-- /.box-header -->
    <div class='box-body'>
        <div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
            <h4><i class='icon fa fa-info'></i> Info</h4>
            Upload gambar dalam berkas zip. Penamaan gambar sesuai dengan no peserta siswa ujian
        </div>
        <form action='' method='post' enctype='multipart/form-data'>
            <div class='col-md-6'>
                <input class='form-control' type='file' name='zip_file' accept='.zip' />
            </div>
            <div class='col-md-6'>
                <button class='btn bg-maroon' name='uplod' type='submit'>Upload Foto</button>
            </div>
        </form>
    </div><!-- /.box-body -->
</div><!-- /.box -->
<div class='box box-solid'>
    <div class='box-header with-border'>
        <h3 class='box-title'>Daftar Foto Peserta</h3>
        <div class='box-tools pull-right '>
            <form action='' method='post'>
                <button class='btn btn-sm bg-maroon' name='hapussemuafoto'>hapus semua foto</button>
            </form>
        </div>
    </div><!-- /.box-header -->
    <div class='box-body'>
        <?php
        $ektensi = ['jpg', 'png', 'JPG', 'PNG'];
        $folder = "../foto/fotosiswa/"; //Sesuaikan Folder nya
        if (!($buka_folder = opendir($folder))) die("eRorr... Tidak bisa membuka Folder");
        $file_array = array();
        while ($baca_folder = readdir($buka_folder)) :
            $file_array[] = $baca_folder;
        endwhile;
        $jumlah_array = count($file_array);
        for ($i = 2; $i < $jumlah_array; $i++) :
            $nama_file = $file_array;
            $nomor = $i - 1;
            $ext = explode('.', $nama_file[$i]);
            $ext = end($ext);
            if (in_array($ext, $ektensi)) {
                echo "<div class='col-md-1'><img class='img-logo' src='$folder$nama_file[$i]' style='width:65px'/><br><br></div>";
            }
        endfor;
        closedir($buka_folder);
        ?>
    </div><!-- /.box-body -->
</div><!-- /.box -->