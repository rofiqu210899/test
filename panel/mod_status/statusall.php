<?php
include "../../config/config.default.php";
include "../../config/config.function.php";
cek_session_guru();
if (empty($_GET['idu'])) {
    $queryidu = "";
} else {
    $queryidu = "and s.id_ujian='" . $_GET['idu'] . "'";
}
$pengawas = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM pengawas  WHERE id_pengawas='$_SESSION[id_pengawas]'"));
$tglsekarang = date('Y-m-d');
if ($pengawas['level'] == 'admin') {
    $nilaiq = mysqli_query($koneksi, "SELECT *  FROM nilai  s LEFT JOIN ujian c ON s.id_ujian=c.id_ujian  where c.status='1' and s.id_siswa<>'' " . $queryidu . " GROUP by s.id_nilai DESC");
} elseif ($pengawas['level'] == 'pengawas') {
    $nilaiq = mysqli_query($koneksi, "SELECT *  FROM nilai  s LEFT JOIN ujian c ON s.id_ujian=c.id_ujian  JOIN siswa b ON b.id_siswa=s.id_siswa where c.status='1' and s.id_siswa<>'' and b.ruang='$pengawas[ruang]' " . $queryidu . " GROUP by s.id_nilai DESC");
} else {
    $nilaiq = mysqli_query($koneksi, "SELECT *  FROM nilai  s LEFT JOIN ujian c ON s.id_ujian=c.id_ujian  where c.status='1' and s.id_siswa<>'' and c.id_guru='$_SESSION[id_pengawas]' " . $queryidu . " GROUP by s.id_nilai DESC");
} ?>
<div class='table-responsive'>
    <table id='example1' class='table table-bordered table-striped'>
        <thead>
            <tr>
                <th width='5px'>#</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Lama Ujian</th>
                <th>Jawaban</th>
                <th>Nilai</th>
                <th>IP Address</th>
                <th>Status</th>
                <th>Kamera</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id='divstatus'>
            <?php while ($nilai = mysqli_fetch_array($nilaiq)) {
                $tglx = strtotime($nilai['ujian_mulai']);
                $tgl = date('Y-m-d', $tglx);
                if ($tgl == $tglsekarang) {
                    $no++;
                    $ket = '';
                    $lama = $jawaban = $skor = '--';
                    $siswa = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa='$nilai[id_siswa]' "));
                    $kelas = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas='$siswa[id_kelas]'"));
                    $mapel = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM mapel WHERE id_mapel='$nilai[id_mapel]'"));
                    $nilaiQ = mysqli_query($koneksi, "SELECT * FROM nilai WHERE id_siswa='$siswa[id_siswa]'");
                    $nilaiC = mysqli_num_rows($nilaiQ);

                    if ($nilaiC <> 0) {
                        $lama = '';
                        if ($nilai['ujian_mulai'] <> '' and $nilai['ujian_selesai'] <> '') {
                            $selisih = strtotime($nilai['ujian_selesai']) - strtotime($nilai['ujian_mulai']);

                            $jawaban = "<small class='label bg-green'>$nilai[jml_benar] <i class='fa fa-check'></i></small>  <small class='label bg-red'>$nilai[jml_salah] <i class='fa fa-times'></i></small>";
                            $skor = "<small class='label bg-green'>" . number_format($nilai['skor'], 2, '.', '') . "</small>";
                            $ket = "<label class='label label-success'>Tes Selesai</label>";
                            $btn = "<button data-id='$nilai[id_nilai]' class='ulang btn btn-xs btn-warning'><i class='fa fa-history'></i></button>";
                        } elseif ($nilai['ujian_mulai'] <> '' and $nilai['ujian_selesai'] == '') {
                            $selisih = strtotime($nilai['ujian_berlangsung']) - strtotime($nilai['ujian_mulai']);

                            // Hitung total soal ujian
                            $total_soal = intval($mapel['tampil_pg']) + intval($mapel['tampil_esai']);
                            if ($total_soal == 0) {
                                $total_soal = intval($mapel['jml_soal']);
                            }

                            // Hitung jumlah soal terjawab di jawaban_temp
                            $q_terjawab = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM jawaban_temp WHERE id_siswa='$nilai[id_siswa]' AND id_ujian='$nilai[id_ujian]' AND ((jawaban IS NOT NULL AND jawaban <> '' AND jawaban <> 'X') OR (esai IS NOT NULL AND esai <> ''))");
                            $d_terjawab = mysqli_fetch_assoc($q_terjawab);
                            $jml_terjawab = intval($d_terjawab['jml']);
                            $no_aktif = !empty($nilai['no_soal_aktif']) ? intval($nilai['no_soal_aktif']) : 1;
                            $persen = ($total_soal > 0) ? min(100, round(($jml_terjawab / $total_soal) * 100)) : 0;

                            $jawaban = "<div style='min-width:130px;'>
                                <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;'>
                                    <span class='label label-primary' style='font-size:11px;' title='Soal Terjawab'><b>$jml_terjawab</b>/$total_soal Soal</span>
                                    <span class='label label-default' style='font-size:10px;border:1px solid #ccc;color:#333;' title='Sedang membuka nomor ini'><i class='fa fa-pencil'></i> No. <b>$no_aktif</b></span>
                                </div>
                                <div class='progress' style='height:6px;margin-bottom:0;background:#e2e8f0;border-radius:3px;overflow:hidden;' title='Progres: $persen%'>
                                    <div class='progress-bar progress-bar-success progress-bar-striped active' style='width: {$persen}%;'></div>
                                </div>
                            </div>";

                            $ket = "<label class='label label-danger'><i class='fa fa-spin fa-spinner' title='Sedang ujian'></i>&nbsp;Dikerjakan</label>";

                            $btn = "<button data-id='$nilai[id_nilai]' class='hapus btn btn-xs btn-danger'>selesai</button>";
                        }
                    }
            ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= $siswa['nis'] ?></td>
                        <td><?= $siswa['nama'] ?></td>
                        <td><?= $kelas['nama'] ?></td>
                        <td><small class='label bg-red'><?= $nilai['kode_ujian'] ?></small> <small class='label bg-purple'><?= $mapel['nama'] ?></small> <small class='label bg-blue'><?= $mapel['level'] ?></small></td>
                        <td><?= lamaujian($selisih) ?></td>
                        <td><?= $jawaban ?></td>
                        <td><?= $skor ?></td>
                        <td><?= $nilai['ipaddress'] ?></td>
                        <td><?= $ket ?></td>
                        <td>
                            <div style='display:flex;align-items:center;gap:6px;'>
                                <?php
                                $logkamera = mysqli_fetch_array(mysqli_query($koneksi, "SELECT foto, waktu FROM log_kamera WHERE id_siswa='$siswa[id_siswa]' AND id_ujian='$nilai[id_ujian]' ORDER BY id_log DESC LIMIT 1"));
                                if (!empty($logkamera['foto'])) {
                                    echo "<a href='javascript:void(0)' class='btn-view-kamera' data-foto='$homeurl/files/kamera/$logkamera[foto]' data-nama='" . htmlspecialchars($siswa['nama'], ENT_QUOTES) . "' data-waktu='$logkamera[waktu]'>
                                        <img src='$homeurl/files/kamera/$logkamera[foto]' style='width:38px;height:28px;object-fit:cover;border-radius:4px;border:2px solid #00a65a;cursor:pointer;' title='Snapshot: $logkamera[waktu] (Klik untuk perbesar)'/>
                                    </a>";
                                }
                                ?>
                                <button type="button" class="btn btn-xs btn-danger btn-pantau-live" data-idsiswa="<?= $siswa['id_siswa'] ?>" data-idujian="<?= $nilai['id_ujian'] ?>" data-nama="<?= htmlspecialchars($siswa['nama'], ENT_QUOTES) ?>" title="Pantau Live CCTV Video Siswa Ini">
                                    <i class="fa fa-video"></i> Live CCTV
                                </button>
                            </div>
                        </td>
                        <td><?= $btn ?></td>

                    </tr>

            <?php }
            }
            ?>
        </tbody>
    </table>
</div>
<script>
    $('#example1').dataTable({
        paging: false
    });
    $(document).on('click', '.hapus', function() {
        var id = $(this).data('id');
        console.log(id);
        swal({
            title: 'Apa anda yakin?',
            text: "aksi ini akan menyelesaikan secara paksa ujian yang sedang berlangsung!",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!',
            target: document.getElementById("statusfull"),
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'mod_status/ajax_status.php?pg=selesaikan',
                    method: "POST",
                    data: 'id=' + id,
                    success: function(data) {
                        //$('#htmlujianselesai').html('1');
                        toastr.options.target = '#statusfull';
                        toastr.success(data);
                    }
                });
            }
        })
    });

    $(document).on('click', '.ulang', function() {
        var id = $(this).data('id');
        console.log(id);
        swal({
            title: 'Apa anda yakin?',
            text: "Akan Mengulang Ujian Ini ??",

            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!',
            target: document.getElementById("statusfull"),
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'mod_status/ajax_status.php?pg=ulangujian',
                    method: "POST",
                    data: 'id=' + id,
                    success: function(data) {
                        toastr.options.target = '#statusfull';
                        toastr.success("berhasil diulang");
                    }
                });
            }
        })
    });
</script>