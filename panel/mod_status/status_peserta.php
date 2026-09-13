<?php if ($ac == '') : ?>
    <div class='row'>
        <?php if (isset($_GET['id'])) { ?>
            <?php $qujian = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM ujian where id_ujian='$_GET[id]'")) ?>
            <?php $ikut = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM nilai where id_ujian='$_GET[id]'")) ?>
            <?php
            $kelas = implode("','", unserialize($qujian['kelas']));
            if ($kelas == "semua") {
                if ($qujian['level'] == 'semua') {
                    $peserta = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM siswa "));
                } else {
                    $peserta = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM siswa where level='$qujian[level]'"));
                }
            } else {
                $peserta = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM siswa where id_kelas IN ('" . $kelas . "')"));
            }
            ?>
            <div class="col-lg-8">
                <div class="small-box bg-primary ">
                    <div class="inner">
                        Nama Ujian<h3><?= $qujian['nama'] ?></h3>
                        <?= $qujian['tgl_ujian'] ?> S/d <?= $qujian['tgl_selesai'] ?>
                    </div>
                    <div class="icon">
                        <!-- <i class="fa fa-file"></i> -->
                    </div>
                    <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                </div>
            </div>
            <?php if ($qujian['tgl_ujian'] > date('Y-m-d H:i:s') and $qujian['tgl_selesai'] > date('Y-m-d H:i:s')) {
                $color = "bg-gray";
                $status = "BELUM MULAI";
            } elseif ($qujian['tgl_ujian'] < date('Y-m-d H:i:s') and $qujian['tgl_selesai'] > date('Y-m-d H:i:s')) {
                $color = "bg-blue";
                $status = "<i class='fa fa-spinner fa-spin'></i> MULAI UJIAN";
            } else {
                $color = "bg-red";
                $status = "WAKTU HABIS";
            } ?>
            <div class="col-lg-4">
                <div class="small-box bg-yellow ">
                    <div class="inner">
                        Status Ujian<h3><?= $status ?></h3>
                        <?= $qujian['tampil_pg'] ?> Soal / <?= $qujian['lama_ujian'] ?> menit / <?= $qujian['opsi'] ?> opsi</small>
                    </div>
                    <div class="icon">
                        <!-- <i class="fa fa-file"></i> -->
                    </div>
                    <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                </div>
            </div>


            <div class="col-lg-2">
                <div class="small-box bg-green ">
                    <div class="inner">
                        Sedang Ujian<h3><i class="fas fa-user    "></i> <?= $ikut ?></h3>
                    </div>
                    <div class="icon">
                        <!-- <i class="fa fa-file"></i> -->
                    </div>
                    <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                </div>
            </div>
            <div class="col-lg-2">
                <div class="small-box bg-red ">
                    <div class="inner">
                        Belum Ujian<h3><i class="fas fa-user    "></i> <?= $peserta - $ikut ?></h3>
                    </div>
                    <div class="icon">
                        <!-- <i class="fa fa-file"></i> -->
                    </div>
                    <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                </div>
            </div>
            <div class="col-lg-2">

                <iframe name='printabsen' src='mod_status/print_absen.php?id=<?= enkripsi($_GET['id']) ?>' style='display:none'></iframe>
                <a role="button" onclick="frames['printabsen'].print()">
                    <div class="small-box bg-primary ">
                        <div class="inner">
                            Cetak Absensi
                            <center>
                                <h3><i class="fas fa-print"></i> </h3>
                            </center>
                        </div>
                        <div class="icon">
                            <!-- <i class="fa fa-file"></i> -->
                        </div>
                        <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="?pg=beritaujian&id=<?= enkripsi($_GET['id']) ?>">
                    <div class="small-box bg-purple ">
                        <div class="inner">
                            Cetak Berita Acara
                            <center>
                                <h3><i class="fas fa-file"></i> </h3>
                            </center>
                        </div>
                        <div class="icon">
                            <!-- <i class="fa fa-file"></i> -->
                        </div>
                        <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="mod_status/report_excel.php?m=<?= $_GET['id'] ?>">
                    <div class="small-box bg-green ">
                        <div class="inner">
                            Cetak Excel Nilai
                            <center>
                                <h3><i class="fas fa-file"></i> </h3>
                            </center>
                        </div>
                        <div class="icon">
                            <!-- <i class="fa fa-file"></i> -->
                        </div>
                        <!-- <a href="?pg=banksoal" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
                    </div>
                </a>
            </div>
        <?php } ?>
        <div class='col-md-12'>

            <div class='box box-solid' id="statusfull">
                <div class='box-header with-border'>
                    <h3 class='box-title'><i class="fas fa-user-friends    "></i> Status Peserta</h3>
                    <div class='box-tools pull-right '>
                        <?php
                        $qsetting = mysqli_fetch_array(mysqli_query($koneksi, "SELECT kamera FROM setting WHERE id_setting='1'"));
                        $isKamera = (isset($qsetting['kamera']) && $qsetting['kamera'] == 1);
                        ?>
                        <button type="button" class="btn <?= $isKamera ? 'btn-success' : 'btn-default' ?>" id="btn-toggle-kamera" data-status="<?= $isKamera ? '1' : '0' ?>" title="Kontrol Kamera Pengawas Siswa">
                            <i class="fa <?= $isKamera ? 'fa-video' : 'fa-video-slash' ?>"></i> Kamera: <b id="lbl-kamera-status"><?= $isKamera ? 'ON' : 'OFF' ?></b>
                        </button>
                        <button type="button" class="btn btn-warning" id="btnselesai"><i class="fas fa-upload    "></i> Selesai Semua</button>
                        <button type="button" class="btn btn-primary" id="btnfull"><i class="fa fa-arrows-alt"></i> FullScreen</button>
                        <button type="button" class="btn btn-primary" id="closefull"><i class="fa fa-times"></i> Close</button>
                    </div>
                </div><!-- /.box-header -->
                <div class='box-body'>
                    <div class='alert alert-info'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                        <i class='icon fa fa-info'></i>
                        Status peserta akan muncul saat ujian berlangsung dan refresh setiap 10 detik..
                    </div>

                    <div id='divstatus'>
                        <?php
                        if (empty($_GET['id'])) {
                            $queryidu = "";
                        } else {
                            $queryidu = "and s.id_ujian='" . $_GET['id'] . "'";
                        }
                        $pengawas = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM pengawas WHERE id_pengawas='$_SESSION[id_pengawas]'"));
                        $tglsekarang = date('Y-m-d');
                        if ($pengawas['level'] == 'admin') {
                            $nilaiq = mysqli_query($koneksi, "SELECT * FROM nilai s LEFT JOIN ujian c ON s.id_ujian=c.id_ujian where c.status='1' and s.id_siswa<>'' " . $queryidu . " GROUP by s.id_nilai DESC");
                        } elseif ($pengawas['level'] == 'pengawas') {
                            $nilaiq = mysqli_query($koneksi, "SELECT * FROM nilai s LEFT JOIN ujian c ON s.id_ujian=c.id_ujian JOIN siswa b ON b.id_siswa=s.id_siswa where c.status='1' and s.id_siswa<>'' and b.ruang='$pengawas[ruang]' " . $queryidu . " GROUP by s.id_nilai DESC");
                        } else {
                            $nilaiq = mysqli_query($koneksi, "SELECT * FROM nilai s LEFT JOIN ujian c ON s.id_ujian=c.id_ujian where c.status='1' and s.id_siswa<>'' and c.id_guru='$_SESSION[id_pengawas]' " . $queryidu . " GROUP by s.id_nilai DESC");
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
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
<?php endif ?>
<script>
    var autoRefresh = setInterval(
        function() {
            <?php if (isset($_GET['id'])) { ?>
                $('#divstatus').load("mod_status/statusall_ujian.php?idu=<?= $_GET['id'] ?>");
            <?php } else { ?>
                $('#divstatus').load("mod_status/statusall.php");
            <?php } ?>
        }, 10000
    );

    function fullScreen(element) {
        if (element.requestFullScreen) {
            element.requestFullScreen();
        } else if (element.webkitRequestFullScreen) {
            element.webkitRequestFullScreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        }
    }
    var elem = document.documentElement;
    /* Close fullscreen */
    function closeFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            /* Safari */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            /* IE11 */
            document.msExitFullscreen();
        }
    }

    $("#closefull").hide();
    $("#btnfull").click(function() {
        var element = document.getElementById('statusfull');
        fullScreen(element)
        $("#closefull").show();
        $(this).hide();
    });
    $("#closefull").click(function() {
        closeFullscreen();
        $("#btnfull").show();
        $(this).hide();
    });
    $("#btnselesai").click(function() {
        swal({
            title: 'Apa anda yakin?',
            text: "aksi ini akan menyelesaikan secara paksa semua ujian yang sedang berlangsung!",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!',
            target: document.getElementById("statusfull"),
        }).then((result) => {
            if (result.value) {
                <?php if (isset($_GET['id'])) { ?>
                    var urlx = 'mod_status/ajax_status.php?pg=selesaisemua&id=<?= $_GET['id'] ?>';
                <?php } else { ?>
                    var urlx = 'mod_status/ajax_status.php?pg=selesaisemua';
                <?php } ?>
                $.ajax({
                    url: urlx,
                    method: "POST",
                    success: function(data) {
                        //$('#htmlujianselesai').html('1');
                        toastr.options.target = '#statusfull';
                        toastr.success(data);
                    }
                });
            }
        })
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

    $(document).on('click', '.btn-view-kamera', function(e) {
        e.preventDefault();
        var foto = $(this).data('foto');
        var nama = $(this).data('nama');
        var waktu = $(this).data('waktu');
        $('#cam-nama-siswa').text(nama);
        $('#cam-waktu').text(waktu);
        $('#cam-img-preview').attr('src', foto);
        $('#cam-download-btn').attr('href', foto);
        $('#modal-preview-kamera').modal('show');
    });

    $(document).on('click', '#btn-toggle-kamera', function() {
        var btn = $(this);
        var current = btn.data('status');
        var newStatus = (current == '1') ? '0' : '1';

        $.ajax({
            url: 'mod_status/toggle_kamera.php',
            type: 'POST',
            data: { status: newStatus },
            dataType: 'json',
            success: function(res) {
                if (res.status == 'ok') {
                    btn.data('status', res.kamera);
                    if (res.kamera == 1) {
                        btn.removeClass('btn-default').addClass('btn-success');
                        btn.html('<i class="fa fa-video"></i> Kamera: <b id="lbl-kamera-status">ON</b>');
                        toastr.success('Kamera pengawas siswa telah diaktifkan (ON)');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-default');
                        btn.html('<i class="fa fa-video-slash"></i> Kamera: <b id="lbl-kamera-status">OFF</b>');
                        toastr.info('Kamera pengawas siswa telah dinonaktifkan (OFF)');
                    }
                } else {
                    swal('Error', res.message, 'error');
                }
            },
            error: function() {
                swal('Error', 'Gagal mengubah status kamera', 'error');
            }
        });
    });

    // Kontrol Pemantauan Live CCTV Video Real-Time
    var activeLiveSiswa = null;
    var activeLiveUjian = null;
    var cctvFrameInterval = null;
    var cctvPeer = null;

    $(document).on('click', '.btn-pantau-live', function(e) {
        e.preventDefault();
        var idSiswa = $(this).data('idsiswa');
        var idUjian = $(this).data('idujian');
        var namaSiswa = $(this).data('nama');

        activeLiveSiswa = idSiswa;
        activeLiveUjian = idUjian;

        $('#cctv-nama-siswa').text(namaSiswa);
        $('#cctv-loading-box').show();
        $('#cctv-status-msg').text('Menghubungkan ke kamera siswa...');
        $('#cctv-video-stream').hide();
        $('#cctv-img-stream').hide();
        $('#cctv-live-badge').hide();
        $('#cctv-mode-badge').hide();

        $('#modal-cctv-live').modal('show');

        $.ajax({
            url: '<?= $homeurl ?>/api_stream.php?action=start_watch',
            type: 'POST',
            data: { id_siswa: idSiswa, id_ujian: idUjian },
            dataType: 'json',
            success: function() {
                startAdminWebRtc(idSiswa);
                startFastFramePuller(idSiswa);
            }
        });
    });

    function startAdminWebRtc(idSiswa) {
        try {
            var rtcConfig = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };
            cctvPeer = new RTCPeerConnection(rtcConfig);

            cctvPeer.addTransceiver('video', { direction: 'recvonly' });

            cctvPeer.ontrack = function(event) {
                if (event.streams && event.streams[0]) {
                    var vid = document.getElementById('cctv-video-stream');
                    vid.srcObject = event.streams[0];
                    vid.play().catch(function(){});
                    // Hanya alihkan ke video jika video benar-benar merender frame (bukan hitam)
                    vid.onplaying = function() {
                        setTimeout(function() {
                            if (vid.videoWidth > 0 && !vid.paused) {
                                $('#cctv-loading-box').hide();
                                $('#cctv-img-stream').hide();
                                $('#cctv-video-stream').show();
                                $('#cctv-live-badge').show();
                                $('#cctv-mode-badge').text('WebRTC HD Live').show();
                            }
                        }, 500);
                    };
                }
            };

            cctvPeer.createOffer().then(function(offer) {
                return cctvPeer.setLocalDescription(offer);
            }).then(function() {
                $.ajax({
                    type: 'POST',
                    url: '<?= $homeurl ?>/api_stream.php?action=send_offer',
                    data: { id_siswa: idSiswa, offer: JSON.stringify(cctvPeer.localDescription) }
                });
            });

            var pollCount = 0;
            var ansTimer = setInterval(function() {
                if (!activeLiveSiswa || activeLiveSiswa != idSiswa) {
                    clearInterval(ansTimer);
                    return;
                }
                pollCount++;
                if (pollCount > 15) {
                    clearInterval(ansTimer);
                    return;
                }
                $.ajax({
                    url: '<?= $homeurl ?>/api_stream.php?action=get_answer&id_siswa=' + idSiswa,
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.answer && cctvPeer && cctvPeer.signalingState === 'have-local-offer') {
                            clearInterval(ansTimer);
                            try {
                                var ans = JSON.parse(res.answer);
                                cctvPeer.setRemoteDescription(new RTCSessionDescription(ans));
                            } catch(e) {}
                        }
                    }
                });
            }, 1500);
        } catch(err) {
            console.warn('WebRTC admin init error:', err);
        }
    }

    function startFastFramePuller(idSiswa) {
        if (cctvFrameInterval) clearInterval(cctvFrameInterval);

        function fetchFrame() {
            if (!activeLiveSiswa || activeLiveSiswa != idSiswa) {
                clearInterval(cctvFrameInterval);
                return;
            }
            $.ajax({
                url: '<?= $homeurl ?>/api_stream.php?action=get_frame&id_siswa=' + idSiswa,
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'ok') {
                        var img = document.getElementById('cctv-img-stream');
                        var preloader = new Image();
                        preloader.onload = function() {
                            img.src = preloader.src;
                            if ($('#cctv-video-stream').is(':hidden')) {
                                $('#cctv-loading-box').hide();
                                $('#cctv-img-stream').show();
                                $('#cctv-live-badge').show();
                                $('#cctv-mode-badge').text('Live CCTV Streaming').show();
                            }
                        };
                        preloader.src = res.frame;
                        var timePart = res.waktu ? (res.waktu.split(' ')[1] || res.waktu) : '--:--:--';
                        $('#cctv-time-display').text(timePart);
                    }
                }
            });
        }

        setTimeout(fetchFrame, 300);
        cctvFrameInterval = setInterval(fetchFrame, 1000);
    }

    function stopLiveCctv() {
        if (activeLiveSiswa) {
            $.ajax({
                url: '<?= $homeurl ?>/api_stream.php?action=stop_watch&id_siswa=' + activeLiveSiswa,
                type: 'POST'
            });
        }
        activeLiveSiswa = null;
        activeLiveUjian = null;
        if (cctvFrameInterval) {
            clearInterval(cctvFrameInterval);
            cctvFrameInterval = null;
        }
        if (cctvPeer) {
            cctvPeer.close();
            cctvPeer = null;
        }
        var vid = document.getElementById('cctv-video-stream');
        if (vid) {
            vid.pause();
            vid.srcObject = null;
        }
        $('#cctv-img-stream').attr('src', '').hide();
        $('#cctv-video-stream').hide();
        $('#cctv-loading-box').show();
    }

    $(document).on('click', '.btn-tutup-cctv', function() {
        stopLiveCctv();
    });

    $('#modal-cctv-live').on('hidden.bs.modal', function() {
        stopLiveCctv();
    });

    $('#btn-fullscreen-cctv').on('click', function() {
        var elem = document.querySelector('#modal-cctv-live .modal-body');
        if (elem.requestFullscreen) { elem.requestFullscreen(); }
        else if (elem.webkitRequestFullscreen) { elem.webkitRequestFullscreen(); }
    });
</script>

<!-- Modal Preview Foto Kamera -->
<div class="modal fade" id="modal-preview-kamera" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-camera"></i> Snapshot Pengawasan Siswa: <span id="cam-nama-siswa"></span></h4>
            </div>
            <div class="modal-body text-center" style="background:#1e1e1e; padding: 20px;">
                <img id="cam-img-preview" src="" style="max-width:100%;max-height:450px;border-radius:6px;box-shadow:0 4px 15px rgba(0,0,0,0.5);border:2px solid #555;"/>
                <div style="margin-top:12px;color:#eee;font-size:13px;">
                    <i class="fa fa-clock-o"></i> Waktu Pengambilan: <b id="cam-waktu"></b>
                </div>
            </div>
            <div class="modal-footer">
                <a id="cam-download-btn" href="" target="_blank" class="btn btn-default"><i class="fa fa-external-link"></i> Buka Ukuran Penuh</a>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Live CCTV Video Streaming -->
<div class="modal fade" id="modal-cctv-live" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 800px;">
        <div class="modal-content" style="background:#181818; color:#fff; border-radius:8px; overflow:hidden; border: 1px solid #333;">
            <div class="modal-header" style="background:#222; border-bottom:1px solid #333; display:flex; justify-content:space-between; align-items:center;">
                <h4 class="modal-title" style="color:#fff; font-size:16px;">
                    <span class="label label-danger" style="margin-right:8px;"><i class="fa fa-circle"></i> LIVE CCTV</span>
                    <span id="cctv-nama-siswa" style="font-weight:bold;">Nama Siswa</span>
                </h4>
                <div>
                    <button type="button" class="btn btn-sm btn-default" id="btn-fullscreen-cctv" title="Fullscreen"><i class="fa fa-arrows-alt"></i></button>
                    <button type="button" class="close btn-tutup-cctv" data-dismiss="modal" style="color:#fff; opacity:0.8; font-size:24px; margin-left:15px;">&times;</button>
                </div>
            </div>
            <div class="modal-body" style="padding:0; position:relative; background:#000; min-height:380px; display:flex; justify-content:center; align-items:center;">
                <!-- Video Element for WebRTC -->
                <video id="cctv-video-stream" autoplay playsinline style="width:100%; max-height:480px; object-fit:contain; display:none;"></video>
                <!-- Image Element for Live Frame Stream Fallback -->
                <img id="cctv-img-stream" src="" style="width:100%; max-height:480px; object-fit:contain; display:none;" />
                
                <!-- Loading & Connecting Spinner -->
                <div id="cctv-loading-box" style="text-align:center; padding:40px;">
                    <i class="fa fa-spinner fa-spin fa-3x" style="color:#00c0ef;"></i>
                    <p style="margin-top:15px; color:#aaa; font-size:14px;" id="cctv-status-msg">Menghubungkan ke kamera siswa...</p>
                </div>

                <!-- Live Stream Overlays -->
                <div style="position:absolute; top:12px; left:15px; pointer-events:none;">
                    <span class="badge bg-red" id="cctv-live-badge" style="font-size:12px; padding:4px 8px; display:none;">
                        <i class="fa fa-video"></i> LIVE STREAM
                    </span>
                    <span class="badge bg-green" id="cctv-mode-badge" style="font-size:11px; margin-left:5px; display:none;">Live CCTV</span>
                </div>

                <div style="position:absolute; bottom:12px; left:15px; color:#00ff00; font-family:monospace; font-size:12px; background:rgba(0,0,0,0.6); padding:2px 8px; border-radius:3px; pointer-events:none;">
                    <i class="fa fa-clock-o"></i> <span id="cctv-time-display">--:--:--</span> | <span id="cctv-fps-display">CCTV Active</span>
                </div>
            </div>
            <div class="modal-footer" style="background:#222; border-top:1px solid #333; display:flex; justify-content:space-between; align-items:center;">
                <span class="text-muted" style="font-size:12px;"><i class="fa fa-info-circle"></i> Streaming real-time dari kamera siswa. Otomatis berhenti saat modal ditutup.</span>
                <button type="button" class="btn btn-default btn-tutup-cctv" data-dismiss="modal">Tutup Pemantauan</button>
            </div>
        </div>
    </div>
</div>