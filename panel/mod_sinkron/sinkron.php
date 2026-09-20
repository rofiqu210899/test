<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
require "../../config/config.default.php";
require "../../config/config.function.php";
cek_session_admin();
$datetime = date('Y-m-d H:i:s');
if ($koneksi) {
    //if ($settingpusat['status'] == 'aktif') {
    $data = isset($_POST['datasinkron']) ? $_POST['datasinkron'] : '';
    $token = $_POST['tokenapi'];
    if ($token <> '' and $data <> '') {
        mysqli_query($koneksi, "UPDATE setting set token_api='$token' where id_setting='1'");
        foreach ($data as $data) {
            $gagal = $gagal2 = $gagal3 = $gagal4 = $gagal5 = 0;
            $masuk1 = $masuk2 = $masuk3 = $masuk4 = $masuk5 = 0;
            $url_host = rtrim($setting['url_host'] ?? '', '/');
            if ($data == 'siswa') {
                $url_siswa = $url_host . "/syncsiswa.php?token=" . urlencode($token) . "&server=" . urlencode($setting['id_server']);
                $datax = http_request($url_siswa);
                $r = json_decode($datax, TRUE);
                if ($r !== null && isset($r['siswa']) && is_array($r['siswa'])) {
                    if (count($r['siswa']) === 0) {
                        echo '<div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-warning"></i> Data Siswa Kosong di Pusat!</h4>
                            Server pusat terhubung, tetapi data peserta didik tidak ditemukan untuk server "' . htmlspecialchars($setting['id_server']) . '". Data lokal tidak diubah.
                        </div>';
                    } else {
                        mysqli_query($koneksi, "TRUNCATE TABLE siswa");
                        $i = 1;
                        foreach ($r['siswa'] as $sw) {
                            $id_siswa = (int)($sw['id_siswa'] ?? 0);
                            $id_kelas = mysqli_real_escape_string($koneksi, $sw['id_kelas'] ?? '');
                            $idpk = mysqli_real_escape_string($koneksi, $sw['idpk'] ?? '');
                            $nis = mysqli_real_escape_string($koneksi, $sw['nis'] ?? '');
                            $no_peserta = mysqli_real_escape_string($koneksi, $sw['no_peserta'] ?? '');
                            $nama = mysqli_real_escape_string($koneksi, $sw['nama'] ?? '');
                            $level = mysqli_real_escape_string($koneksi, $sw['level'] ?? '');
                            $ruang = mysqli_real_escape_string($koneksi, $sw['ruang'] ?? '');
                            $sesi = mysqli_real_escape_string($koneksi, $sw['sesi'] ?? '');
                            $username = mysqli_real_escape_string($koneksi, $sw['username'] ?? '');
                            $password = mysqli_real_escape_string($koneksi, $sw['password'] ?? '');
                            $foto = mysqli_real_escape_string($koneksi, $sw['foto'] ?? '');
                            $server_sw = mysqli_real_escape_string($koneksi, $sw['server'] ?? '');
                            $agama = mysqli_real_escape_string($koneksi, $sw['agama'] ?? '');

                            $sql = mysqli_query($koneksi, "INSERT INTO siswa
                                (id_siswa,id_kelas,idpk,nis,no_peserta,nama,level,ruang,sesi,username,password,foto,server,agama) VALUES 			
                                ('$id_siswa','$id_kelas','$idpk','$nis','$no_peserta','$nama','$level','$ruang','$sesi','$username','$password','$foto','$server_sw','$agama')");

                            if (!empty($id_kelas)) {
                                $qkelas = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_kelas='$id_kelas'");
                                if (mysqli_num_rows($qkelas) == 0) {
                                    mysqli_query($koneksi, "INSERT INTO kelas (id_kelas,level,nama) VALUES ('$id_kelas','$level','$id_kelas')");
                                }
                            }
                            if (($setting['jenjang'] ?? '') == 'SMK' && !empty($idpk)) {
                                $qpk = mysqli_query($koneksi, "SELECT id_pk FROM pk WHERE id_pk='$idpk'");
                                if (mysqli_num_rows($qpk) == 0) {
                                    mysqli_query($koneksi, "INSERT INTO pk (id_pk,program_keahlian) VALUES ('$idpk','$idpk')");
                                }
                            }
                            if (!empty($level)) {
                                $qlevel = mysqli_query($koneksi, "SELECT kode_level FROM level WHERE kode_level='$level'");
                                if (mysqli_num_rows($qlevel) == 0) {
                                    mysqli_query($koneksi, "INSERT INTO level (kode_level,keterangan) VALUES ('$level','$level')");
                                }
                            }
                            if (!empty($ruang)) {
                                $qruang = mysqli_query($koneksi, "SELECT kode_ruang FROM ruang WHERE kode_ruang='$ruang'");
                                if (mysqli_num_rows($qruang) == 0) {
                                    mysqli_query($koneksi, "INSERT INTO ruang (kode_ruang,keterangan) VALUES ('$ruang','$ruang')");
                                }
                            }
                            if (!empty($sesi)) {
                                $qsesi = mysqli_query($koneksi, "SELECT kode_sesi FROM sesi WHERE kode_sesi='$sesi'");
                                if (mysqli_num_rows($qsesi) == 0) {
                                    mysqli_query($koneksi, "INSERT INTO sesi (kode_sesi,nama_sesi) VALUES ('$sesi','$sesi')");
                                }
                            }

                            if (!$sql) {
                                $gagal++;
                            } else {
                                $masuk1++;
                            }
                        }

                        $exec = mysqli_query($koneksi, "UPDATE sinkron SET jumlah='$masuk1', status_sinkron='1', tanggal='$datetime' WHERE nama_data='DATA1'");

                        echo "
                        <div class='row'>
                            <div class='col-md-12'>
                                <div class='box box-solid'>
                                    <div class='box-header with-border bg-blue'>
                                    <h5 class='box-title'>DATA YANG MASUK KE LOKAL</h5>
                                    </div>
                                    <div class='box-body'>
                                    <table class='table table-striped'>
                                            <th>Nama Data</th><th>Data Berhasil Masuk</th><th>Data Gagal</th>
                                            <tr><td>Peserta Ujian</td><td><i class='fa fa-check text-green'></i> $masuk1</td><td><i class='fa fa-times text-red'></i> $gagal</td></tr>
                                            
                                        </table>
                                    
                                    </div><!-- /.box-body -->
                                </div><!-- /.box -->
                            </div>
                        </div>";
                    }
                } else {
                    $err_msg = isset($r['message']) ? htmlspecialchars($r['message']) : 'Silahkan periksa URL server pusat, koneksi internet, dan token API.';
                    echo '<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Sinkron Data Siswa Gagal!</h4>
                        ' . $err_msg . '
                    </div>';
                }
            }
            if ($data == 'soal') {


                $syncdata = http_request($url_host . "/syncsoal.php?token=" . urlencode($token));

                $sync = json_decode($syncdata, TRUE);

                //$jurarray = unserialize($setting['jurusan']);
                //$jur = implode(',', $jurarray);
                if ($sync <> null) {
                    $sql = mysqli_query($koneksi, "truncate table mapel");
                    $sql = mysqli_query($koneksi, "truncate table soal");

                    foreach ($sync['bank'] as $banksoal) {
                        // if ($jadwal['jenjang'] == $setting['jenjang'] or $jadwal['jenjang'] == 'semua') {
                        //    if (in_array($jadwal['id_pk'], $jurarray) or $jadwal['id_pk'] == 'semua') {
                        if ($banksoal['status'] == 1) {
                            $sql = mysqli_query($koneksi, "insert into mapel
                            (id_mapel,idpk,idguru,nama,jml_soal,jml_esai,bobot_pg,bobot_esai,level,status,kelas,tampil_pg,tampil_esai,opsi,date,kkm,soal_agama,kode) values 			
                            ('$banksoal[id_mapel]','$banksoal[idpk]','$banksoal[idguru]','$banksoal[nama]','$banksoal[jml_soal]','$banksoal[jml_esai]','$banksoal[bobot_pg]','$banksoal[bobot_esai]','$banksoal[level]','$banksoal[status]','$banksoal[kelas]','$banksoal[tampil_pg]','$banksoal[tampil_esai]','$banksoal[opsi]','$banksoal[date]','$banksoal[kkm]','$banksoal[soal_agama]','$banksoal[kode]')");
                            if (!$sql) {
                                $gagal2++;
                            } else {
                                $masuk2++;
                            }

                            foreach ($sync['soal'] as $soal) {
                                if ($soal['id_mapel'] == $banksoal['id_mapel']) {
                                    $soalx = addslashes($soal['soal']);
                                    $pilA = addslashes($soal['pilA']);
                                    $pilB = addslashes($soal['pilB']);
                                    $pilC = addslashes($soal['pilC']);
                                    $pilD = addslashes($soal['pilD']);
                                    $pilE = addslashes($soal['pilE']);
                                    $sqlsoal = mysqli_query($koneksi, "insert into soal
                    (id_soal,id_mapel,nomor,soal,jenis,pilA,pilB,pilC,pilD,pilE,jawaban,file,file1,fileA,fileB,fileC,fileD,fileE) values 			
                    ('$soal[id_soal]','$soal[id_mapel]','$soal[nomor]','$soalx','$soal[jenis]','$pilA','$pilB','$pilC','$pilD','$pilE','$soal[jawaban]','$soal[file]','$soal[file1]','$soal[fileA]','$soal[fileB]','$soal[fileC]','$soal[fileD]','$soal[fileE]')");
                                    if (!$sqlsoal) {
                                        $gagal3++;
                                    } else {
                                        $masuk3++;
                                    }
                                }
                            }


                            //sinkron data file
                            $urls = [];
                            $i = 0;
                            foreach ($sync['file'] as $file) {

                                if ($file['id_mapel'] == $banksoal['id_mapel']) {
                                    $urls[$i] = $setting['web'] . "/files/" . $file['nama_file'];
                                    $i++;
                                }
                            }
                            multiple_download($urls);
                            // }
                        }
                    }
                    $exec = mysqli_query($koneksi, "update sinkron set jumlah='$masuk2', status_sinkron='1',tanggal='$datetime'  where nama_data='DATA2'");
                    $exec = mysqli_query($koneksi, "update sinkron set jumlah='$masuk3', status_sinkron='1',tanggal='$datetime' where nama_data='DATA3'");

                    echo "
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <div class='box box-solid'>
                                                <div class='box-header with-border bg-blue'>
                                                <h5 class='box-title'>DATA SOAL YANG MASUK KE LOKAL</h5>
                                                </div>
                                                <div class='box-body'>
                                                <table class='table table-striped'>
                                                        <th>Nama Data</th><th>Data Berhasil Masuk</th><th>Data Gagal</th>
                                                        <tr><td>Bank Soal</td><td><i class='fa fa-check text-green'></i> $masuk2</td><td><i class='fa fa-times text-red'></i> $gagal2</td></tr>
                                                        <tr><td>Data Soal</td><td><i class='fa fa-check text-green'></i> $masuk3</td><td><i class='fa fa-times text-red'></i> $gagal3</td></tr>
                                                       
                                                    </table>
                                                
                                                </div><!-- /.box-body -->
                                            </div><!-- /.box -->
                                        </div>
                                    </div>";
                } else {
                    echo '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Sinkron Data Soal Gagal!</h4>
        Silahkan periksa koneksi internet dan token
      </div>';
                }
            }
            if ($data == 'jadwal') {
                $syncdata = http_request($url_host . "/syncsoal.php?token=" . urlencode($token));

                $sync = json_decode($syncdata, TRUE);

                //$jurarray = unserialize($setting['jurusan']);
                //$jur = implode(',', $jurarray);
                if ($sync <> null) {

                    $sql = mysqli_query($koneksi, "truncate table ujian");
                    foreach ($sync['jadwal'] as $jadwal) {
                        // if ($jadwal['jenjang'] == $setting['jenjang'] or $jadwal['jenjang'] == 'semua') {
                        //    if (in_array($jadwal['id_pk'], $jurarray) or $jadwal['id_pk'] == 'semua') {
                        $sqljadwal = mysqli_query($koneksi, "insert into ujian
                                (id_ujian,id_pk,id_guru,id_mapel,nama,jml_soal,jml_esai,bobot_pg,bobot_esai,lama_ujian,tgl_ujian,tgl_selesai,waktu_ujian,level,acak,token,hasil,kelas,tampil_pg,tampil_esai,opsi,kode_ujian,ulang,kkm,soal_agama,kode_nama,sesi,status,ulang_kkm,btn_selesai,pelanggaran) values 			
                                ('$jadwal[id_ujian]','$jadwal[id_pk]','$jadwal[id_guru]','$jadwal[id_mapel]','$jadwal[nama]','$jadwal[jml_soal]','$jadwal[jml_esai]','$jadwal[bobot_pg]','$jadwal[bobot_esai]','$jadwal[lama_ujian]','$jadwal[tgl_ujian]','$jadwal[tgl_selesai]',
                                '$jadwal[waktu_ujian]','$jadwal[level]','$jadwal[acak]','$jadwal[token]','$jadwal[hasil]','$jadwal[kelas]','$jadwal[tampil_pg]','$jadwal[tampil_esai]','$jadwal[opsi]','$jadwal[kode_ujian]','$jadwal[ulang]','$jadwal[kkm]','$jadwal[soal_agama]','$jadwal[kode_nama]','$jadwal[sesi]','$jadwal[status]','$jadwal[ulang_kkm]','$jadwal[btn_selesai]','$jadwal[pelanggaran]')");
                        if (!$sqljadwal) {
                            $gagal4++;
                        } else {
                            $masuk4++;
                        }
                    }
                    $exec = mysqli_query($koneksi, "update sinkron set jumlah='$masuk4', status_sinkron='1',tanggal='$datetime' where nama_data='DATA4'");
                    echo "
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <div class='box box-solid'>
                                                <div class='box-header with-border bg-blue'>
                                                <h5 class='box-title'>DATA JADWAL YANG MASUK KE LOKAL</h5>
                                                </div>
                                                <div class='box-body'>
                                                <table class='table table-striped'>
                                                <th>Nama Data</th><th>Data Berhasil Masuk</th><th>Data Gagal</th>
                                                        <tr><td>Jadwal Ujian</td><td><i class='fa fa-check text-green'></i> $masuk4</td><td><i class='fa fa-times text-red'></i> $gagal4</td></tr>
                                                    </table>
                                                
                                                </div><!-- /.box-body -->
                                            </div><!-- /.box -->
                                        </div>
                                    </div>";
                } else {
                    echo '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Sinkron Data Soal Gagal!</h4>
        Silahkan periksa koneksi internet dan token
      </div>';
                }
            }
        }
    } else {
        echo '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Sinkron Gagal!</h4>
        Silahkan memilih data sinkron dan mengisi token dengan benar
      </div>';
    }
    //Tarik Data Peserta Ujian



} else {
    echo "
								<div class='row'>
									<div class='col-md-12'>
										<div class='box box-solid'>
											<div class='box-header with-border bg-red'>
											<h5 class='box-title'>SINKRONISASI GAGAL</h5>
											</div>
											<div class='box-body'>
											<ul>
											<li>Periksa Koneksi Internet</li>
											<li>Periksa Pengaturan Jaringan</li>
											<li>Pastikan Server dalam kondisi AKTIF</li>
											</ul>
											</div><!-- /.box-body -->
										</div><!-- /.box -->
									</div>
								</div>";
}
