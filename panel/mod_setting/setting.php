<?php
cek_session_admin();
$info1 = $info2 = $info3 = $info4 = '';
$admin = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM pengawas WHERE level='admin' AND id_pengawas='1'"));
$setting = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM setting WHERE id_setting='1'"));
$setting['alamat'] = str_replace('<br />', '', $setting['alamat']);
$setting['header'] = str_replace('<br />', '', $setting['header']);
$ai_set = get_ai_setting($koneksi);
?>
<div class='row'>
    <div class='col-md-12'>
        <div class="box box-solid">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fas fa-tools fa-2x fa-fw"></i> Pengaturan</h3>
            </div>
            <div class="box-body no-padding ">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Pengaturan Umum</a></li>
                        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Hapus Data</a></li>
                        <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Backup & Restore</a></li>
                        <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">Backup Master Soal</a></li>
                        <li class=""><a href="#tab_ai" data-toggle="tab" aria-expanded="false"><i class="fa fa-robot text-purple"></i> Konfigurasi AI (Multi-Provider)</a></li>

                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <form id="formpengaturan" action='' method='post' enctype='multipart/form-data'>

                                <div class='box-body'>
                                    <button type='submit' name='submit1' class='btn btn-flat pull-right btn-success' style='margin-bottom:5px'><i class='fa fa-check'></i> Simpan</button>
                                    <?= $info1 ?>
                                    <div class='form-group'>
                                        <label>Nama Aplikasi</label>
                                        <input type='text' name='aplikasi' value="<?= $setting['aplikasi'] ?>" class='form-control' required='true' />
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Nama Sekolah</label>
                                                <input type='text' name='sekolah' value="<?= $setting['sekolah'] ?>" class='form-control' required='true' />
                                            </div>
                                            <div class='col-md-6'>
                                                <label>Kode Sekolah</label>
                                                <input type='text' name='kode' value="<?= $setting['kode_sekolah'] ?>" class='form-control' required='true' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Alamat Server / Ip Server</label>
                                                <input type='text' name='ipserver' value="<?= $setting['ip_server'] ?>" class='form-control' />
                                            </div>
                                            <div class='col-md-6'>
                                                <label>Waktu Server</label>
                                                <select name='waktu' class='form-control' required='true'>
                                                    <option value="<?= $setting['waktu'] ?>"><?= $setting['waktu'] ?></option>
                                                    <option value='Asia/Jakarta'>Asia/Jakarta</option>
                                                    <option value='Asia/Makassar'>Asia/Makassar</option>
                                                    <option value='Asia/Jayapura'>Asia/Jayapura</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class='form-group'>
                                                <label>Token Sinkronisasi</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name='token_api' class="form-control" id='tokenapi' value="<?= $setting['token_api'] ?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-info btn-flat" id='buattoken'><i class="fas fa-spinner    "></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class='form-group'>
                                                <label>Jenjang</label>
                                                <select name='jenjang' class='form-control' required='true'>
                                                    <option value="<?= $setting['jenjang'] ?>"><?= $setting['jenjang'] ?></option>
                                                    <option value='SD'>SD/MI</option>
                                                    <option value='SMP'>SMP/MTS</option>
                                                    <option value='SMK'>SMK/SMA/MA</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-12'>
                                                <label><i class='fa fa-camera'></i> Kamera Pengawas Ujian (Webcam Siswa)</label>
                                                <div style='padding: 10px; background: #f9f9f9; border-radius: 4px; border: 1px solid #e1e1e1;'>
                                                    <label class='radio-inline' style='font-weight: normal; margin-right: 25px;'>
                                                        <input type='radio' name='kamera' value='1' <?= (isset($setting['kamera']) && $setting['kamera'] == 1) ? 'checked' : '' ?>>
                                                        <span class='label label-success' style='font-size: 11px; padding: 4px 8px;'><i class='fa fa-check'></i> AKTIF (ON)</span> &nbsp; Kamera siswa aktif merekam snapshot saat ujian
                                                    </label>
                                                    <label class='radio-inline' style='font-weight: normal;'>
                                                        <input type='radio' name='kamera' value='0' <?= (empty($setting['kamera']) || $setting['kamera'] == 0) ? 'checked' : '' ?>>
                                                        <span class='label label-danger' style='font-size: 11px; padding: 4px 8px;'><i class='fa fa-times'></i> NONAKTIF (OFF)</span> &nbsp; Siswa mengerjakan ujian tanpa kamera
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label>Kepala Sekolah</label>
                                        <input type='text' name='kepsek' value="<?= $setting['kepsek'] ?>" class='form-control' />
                                    </div>
                                    <div class='form-group'>
                                        <label>NIP Kepala Sekolah</label>
                                        <input type='text' name='nip' value="<?= $setting['nip'] ?>" class='form-control' />
                                    </div>
                                    <div class='form-group'>
                                        <label>Alamat</label>
                                        <textarea name='alamat' class='form-control' rows='3'><?= $setting['alamat'] ?></textarea>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Kecamatan</label>
                                                <input type='text' name='kecamatan' value="<?= $setting['kecamatan'] ?> " class='form-control' />
                                            </div>
                                            <div class='col-md-6'>
                                                <label>Kota/Kabupaten</label>
                                                <input type='text' name='kota' value="<?= $setting['kota'] ?>" class='form-control' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Telepon</label>
                                                <input type='text' name='telp' value="<?= $setting['telp'] ?>" class='form-control' />
                                            </div>
                                            <div class='col-md-6'>
                                                <label>Fax</label>
                                                <input type='text' name='fax' value="<?= $setting['fax'] ?>" class='form-control' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Website</label>
                                                <input type='text' name='web' value="<?= $setting['web'] ?>" class='form-control' />
                                            </div>
                                            <div class='col-md-6'>
                                                <label>E-mail</label>
                                                <input type='text' name='email' value="<?= $setting['email'] ?>" class='form-control' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Logo</label>
                                                <input type='file' name='logo' class='form-control' />
                                            </div>
                                            <div class='col-md-2'>
                                                &nbsp;<br />
                                                <img class='img img-responsive' src="<?= $homeurl ?>/<?= $setting['logo'] ?>" height='50' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Background</label>
                                                <input type='file' name='bc' class='form-control' />
                                            </div>
                                            <div class='col-md-2'>
                                                &nbsp;<br />
                                                <img class='img img-responsive' src="<?= $homeurl ?>/<?= $setting['bc'] ?>" height='50' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <div class='row'>
                                            <div class='col-md-6'>
                                                <label>Tanda Tangan</label>
                                                <input type='file' name='ttd' class='form-control' />
                                            </div>
                                            <div class='col-md-2'>
                                                &nbsp;<br />
                                                <img class='img img-responsive' src="
												<?php echo $homeurl . '/dist/img/ttd.png' . '?date=' . time(); ?> ?>" height='50' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label>Header Laporan</label>
                                        <textarea name='header' class='form-control' rows='3'><?= $setting['header'] ?></textarea>
                                    </div>
                                </div><!-- /.box-body -->

                            </form>
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_2">
                            <form id='formhapusdata' action='' method='post'>
                                <div class='box-body'>
                                    <?= $info4 ?>

                                    <div class='form-group'>
                                        <label>Pilih Data</label>
                                        <div class='row'>
                                            <div class='col-md-5'>
                                                <div class='checkbox'>
                                                    <small class='label bg-purple'>Pilih Data Hasil Nilai</small><br />
                                                    <label><input type='checkbox' name='data[]' value='nilai' /> Data Nilai</label><br />

                                                    <label><input type='checkbox' name='data[]' value='jawaban_temp' /> Data Jawaban Temporary</label><br />
                                                    <label><input type='checkbox' name='data[]' value='jawaban' /> Data Jawaban</label><br />
                                                    <small class='label bg-green'>Pilih Data Ujian</small><br />
                                                    <label><input type='checkbox' name='data[]' value='soal' /> Data Soal</label><br />
                                                    <label><input type='checkbox' name='data[]' value='mapel' /> Data Bank Soal</label><br />
                                                    <label><input type='checkbox' name='data[]' value='file_pendukung' /> Data File Pendukung</label><br />
                                                    <label><input type='checkbox' name='data[]' value='ujian' /> Data Jadwal Ujian</label><br />
                                                    <label><input type='checkbox' name='data[]' value='berita' /> Data Berita Acara</label><br />
                                                    <label><input type='checkbox' name='data[]' value='tugas' /> Data Tugas</label><br />
                                                    <label><input type='checkbox' name='data[]' value='jawaban_tugas' /> Data Jawaban Tugas</label><br />

                                                    <small class='label label-danger'>Pilih Data Master</small><br />
                                                    <label><input type='checkbox' name='data[]' value='siswa' /> Data Siswa</label><br />
                                                    <label><input type='checkbox' name='data[]' value='kelas' /> Data Kelas</label><br />
                                                    <label><input type='checkbox' name='data[]' value='mata_pelajaran' /> Data Mata Pelajaran</label><br />
                                                    <label><input type='checkbox' name='data[]' value='pk' /> Data Jurusan</label><br />
                                                    <label><input type='checkbox' name='data[]' value='level' /> Data Level</label><br />
                                                    <label><input type='checkbox' name='data[]' value='ruang' /> Data Ruangan</label><br />
                                                    <label><input type='checkbox' name='data[]' value='sesi' /> Data Sesi</label><br />

                                                </div>
                                            </div>
                                            <div class='col-md-7'>
                                                <button type='submit' name='submit3' class='btn btn-sm bg-maroon'><i class='fa fa-trash-o'></i> Kosongkan</button>
                                                <div class='form-group'>
                                                    <label>Password Admin</label>
                                                    <input type='password' name='password' class='form-control' required='true' />
                                                </div>

                                                <p class='text-danger'><i class='fa fa-warning'></i> <strong>Mohon di ingat!</strong> Data yang telah dikosongkan tidak dapat dikembalikan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- /.box-body -->
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_3">
                            <div class='col-md-12 notif'></div>
                            <div class='col-md-6'>
                                <div class='box box-solid'>
                                    <div class='box-header '>
                                        <h3 class='box-title'>Backup Data</h3>
                                    </div><!-- /.box-header -->
                                    <div class='box-body'>
                                        <p>Klik Tombol dibawah ini untuk membackup database </p>
                                        <button id='btnbackup' class='btn btn-flat btn-success'><i class='fa fa-database'></i> Backup Data</button>
                                    </div><!-- /.box-body -->
                                </div><!-- /.box -->
                            </div>
                            <div class='col-md-6'>
                                <div class='box box-solid'>
                                    <div class='box-header '>
                                        <h3 class='box-title'>Restore Data</h3>
                                    </div><!-- /.box-header -->
                                    <div class='box-body'>
                                        <form id='formrestore'>
                                            <p>Klik Tombol dibawah ini untuk merestore database </p>
                                            <div class='col-md-8'>
                                                <input class='form-control' name='datafile' type='file' required />
                                            </div>
                                            <button name='restore' class='btn btn-flat btn-success'><i class='fa fa-database'></i> Restore Data</button>
                                        </form>
                                    </div><!-- /.box-body -->
                                </div><!-- /.box -->
                            </div>
                        </div>
                        <div class="tab-pane" id="tab_4">
                            <div class="row">
                                <div class='col-md-12 notif_mapel'></div>
                                <div class='col-md-12'>
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <label for="mapel" class="col-sm-2">Mapel yang Tersedia</label>
                                            <div class="col-sm-10">
                                                <select name="mapel_id" id="mapel_id" class="form-control select2" style="width: 100%;" required>
                                                    <?php $mapelbackup = mysqli_query($koneksi, "SELECT id_mapel,kode FROM mapel  GROUP BY id_mapel ASC"); ?>
                                                    <?php while ($mapelb = mysqli_fetch_array($mapelbackup)) : ?>
                                                        <option value="<?= $mapelb['id_mapel']  ?>"><?= $mapelb['kode'] ?></option>
                                                    <?php endwhile ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="panel-footer clearfix">
                                            <div class="pull-right">
                                                <button id='mastersoal' class='btn btn-flat btn-success'><i class='fa fa-database'></i> Proses</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab_ai">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="box box-solid" style="border: 1px solid #d2d6de; border-radius: 4px;">
                                        <div class="box-header with-border bg-purple" style="border-radius: 4px 4px 0 0;">
                                            <h3 class="box-title"><i class="fa fa-robot"></i> Konfigurasi AI Multi-Provider</h3>
                                        </div>
                                        <form id="formpengaturan_ai" method="post">
                                            <div class="box-body">
                                                <div class="form-group">
                                                    <label><i class="fa fa-toggle-on"></i> Status Fitur AI</label>
                                                    <div style="padding: 10px 15px; background: #f9f9f9; border-radius: 4px; border: 1px solid #e1e1e1;">
                                                        <label class="radio-inline" style="font-weight: 600; margin-right: 25px;">
                                                            <input type="radio" name="gemini_status" value="1" <?= ($ai_set['status'] == 1) ? 'checked' : '' ?>>
                                                            <span class="label label-success" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-check"></i> AKTIF (ON)</span> &nbsp; Fitur analisis soal AI aktif
                                                        </label>
                                                        <label class="radio-inline" style="font-weight: 600;">
                                                            <input type="radio" name="gemini_status" value="0" <?= ($ai_set['status'] == 0) ? 'checked' : '' ?>>
                                                            <span class="label label-danger" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-times"></i> NONAKTIF (OFF)</span> &nbsp; Matikan analisis AI
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label><i class="fa fa-server text-purple"></i> Provider AI <span class="text-danger">*</span></label>
                                                    <select name="ai_provider" id="ai_provider" class="form-control" style="font-weight: 600; font-size: 14px;">
                                                        <option value="gemini" <?= ($ai_set['provider'] == 'gemini') ? 'selected' : '' ?>>🌟 Google Gemini (Gemini 3.5 Flash Lite, 2.5 Flash, 1.5 Pro)</option>
                                                        <option value="groq" <?= ($ai_set['provider'] == 'groq') ? 'selected' : '' ?>>⚡ Groq (Super Cepat - Llama 3.3 70B, Llama 3.1 8B, Mixtral)</option>
                                                        <option value="openrouter" <?= ($ai_set['provider'] == 'openrouter') ? 'selected' : '' ?>>🌐 OpenRouter (1 API Key untuk Ratusan Model AI)</option>
                                                        <option value="deepseek" <?= ($ai_set['provider'] == 'deepseek') ? 'selected' : '' ?>>🧠 DeepSeek AI (DeepSeek V3 / Reasoner R1)</option>
                                                        <option value="openai" <?= ($ai_set['provider'] == 'openai') ? 'selected' : '' ?>>🤖 OpenAI (ChatGPT - gpt-4o, gpt-4o-mini)</option>
                                                        <option value="custom" <?= ($ai_set['provider'] == 'custom') ? 'selected' : '' ?>>⚙️ Custom / Local AI (Ollama, vLLM, Reverse Proxy)</option>
                                                    </select>
                                                </div>

                                                <div class="form-group" id="grp-base-url" style="<?= ($ai_set['provider'] == 'custom') ? '' : 'display: none;' ?>">
                                                    <label><i class="fa fa-link text-blue"></i> Custom Base URL (OpenAI Compatible Endpoint)</label>
                                                    <input type="text" name="ai_base_url" id="ai_base_url" value="<?= htmlspecialchars($ai_set['base_url'] ?? '', ENT_QUOTES) ?>" class="form-control" placeholder="Contoh: http://localhost:11434/v1 atau https://api.yourproxy.com/v1">
                                                    <small class="text-muted">Untuk model lokal Ollama gunakan <code>http://localhost:11434/v1</code>. Untuk vLLM/Proxy sesuaikan dengan endpoint Anda.</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><i class="fa fa-key"></i> <span id="lbl-api-key">API Key</span> <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" name="gemini_api_key" id="gemini_api_key" value="<?= htmlspecialchars($ai_set['api_key'], ENT_QUOTES) ?>" class="form-control" placeholder="Masukkan API Key..." required>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-default btn-flat" id="btn-toggle-key" title="Tampilkan/Sembunyikan Key"><i class="fa fa-eye" id="icon-eye"></i></button>
                                                        </span>
                                                    </div>
                                                    <small class="text-muted" id="help-api-key">
                                                        Dapatkan API Key di portal resmi provider.
                                                    </small>
                                                </div>

                                                <div class="form-group">
                                                    <label><i class="fa fa-microchip"></i> Model AI <span class="text-danger">*</span></label>
                                                    <input type="text" name="gemini_model" id="gemini_model" value="<?= htmlspecialchars(!empty($ai_set['model']) ? $ai_set['model'] : 'gemini-3.5-flash-lite', ENT_QUOTES) ?>" class="form-control" placeholder="Tulis nama model AI..." list="model_list" required>
                                                    <datalist id="model_list"></datalist>
                                                    <small class="text-muted" style="display: block; margin-top: 5px;">
                                                        Tulis nama model atau klik rekomendasi cepat berikut:
                                                        <div id="quick-model-badges" style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 5px;"></div>
                                                    </small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><i class="fa fa-hourglass-half text-purple"></i> Jeda Antar Batch (Detik) <small class="text-success">[Anti-RPM Limit]</small></label>
                                                            <input type="number" step="0.5" min="0.5" max="30" name="gemini_delay" id="gemini_delay" value="<?= htmlspecialchars($ai_set['delay'] ?? 4.5, ENT_QUOTES) ?>" class="form-control" required>
                                                            <small class="text-muted">Jeda tunggu sebelum request batch berikutnya. Gemini: <b>4.5s</b>, Groq/OpenAI: <b>1.0 - 2.0s</b>.</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><i class="fa fa-cubes text-blue"></i> Soal per Batch <small class="text-success">[Hemat Kuota]</small></label>
                                                            <input type="number" min="5" max="25" name="gemini_batch_size" id="gemini_batch_size" value="<?= htmlspecialchars($ai_set['batch_size'] ?? 15, ENT_QUOTES) ?>" class="form-control" required>
                                                            <small class="text-muted">Jumlah soal per batch request. Disarankan <b>12 - 15 butir</b>.</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label><i class="fa fa-commenting-o"></i> Instruksi Tambahan / Custom Prompt (Opsional)</label>
                                                    <textarea name="gemini_prompt" id="gemini_prompt" class="form-control" rows="3" placeholder="Instruksi kustom untuk validator AI (kosongkan jika ingin memakai default sistem)..."><?= htmlspecialchars($ai_set['prompt'], ENT_QUOTES) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="box-footer">
                                                <button type="submit" class="btn btn-flat btn-success" id="btn-save-ai"><i class="fa fa-save"></i> Simpan Konfigurasi AI</button>
                                                <button type="button" id="btn-test-ai" class="btn btn-flat btn-primary pull-right"><i class="fa fa-bolt"></i> Test Koneksi AI</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div id="hasil-test-ai" style="display: none; margin-top: 15px;"></div>
                                </div>

                                <div class="col-md-4">
                                    <div class="box box-solid" style="border: 1px solid #d2d6de; border-radius: 4px;">
                                        <div class="box-header with-border">
                                            <h3 class="box-title"><i class="fa fa-info-circle text-info"></i> Info & Panduan Provider</h3>
                                        </div>
                                        <div class="box-body" id="petunjuk-provider-container" style="font-size: 13px; line-height: 1.6;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#mastersoal').click(function() {
        var mapel_id = $('#mapel_id').val();
        $('.notif_mapel').load('mod_setting/backup_excel.php?mapel_id=' + mapel_id);
        console.log('sukses');
    });
    $('#btnbackup').click(function() {
        $('.notif').load('mod_setting/backup.php');
        console.log('sukses');
    });
    $("#buattoken").click(function() {
        // set the length of the string
        var stringLength = 15;

        // list containing characters for the random string
        var stringArray = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];


        var rndString = "";

        // build a string with random characters
        for (var i = 1; i < stringLength; i++) {
            var rndNum = Math.ceil(Math.random() * stringArray.length) - 1;
            rndString = rndString + stringArray[rndNum];
        };

        $("#tokenapi").val(rndString);

    });
    $('#formrestore').submit(function(e) {
        e.preventDefault();
        var data = new FormData(this);
        //console.log(data);
        $.ajax({
            type: 'POST',
            url: 'mod_setting/crud_setting.php?pg=setting_restore',
            enctype: 'multipart/form-data',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.loader').show();
            },
            success: function(data) {
                $('.loader').hide();
                iziToast.success({
                    title: 'Mantap!',
                    message: 'data berhasil direstore',
                    position: 'topRight'
                });
            }
        });
        return false;
    });
    $('#formpengaturan').submit(function(e) {
        e.preventDefault();
        var data = new FormData(this);
        //console.log(data);
        $.ajax({
            type: 'POST',
            url: 'mod_setting/crud_setting.php?pg=setting_app',
            enctype: 'multipart/form-data',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data) {
                iziToast.success({
                    title: 'Mantap!',
                    message: 'data berhasil diperbarui',
                    position: 'topRight'
                });
                setTimeout(function() {
                    window.location.reload();
                }, 2000);

            }
        });
        return false;
    });
    $('#formhapusdata').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'mod_setting/crud_setting.php?pg=setting_clear',
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                if (data == "ok") {
                    iziToast.success({
                        title: 'Mantap!',
                        message: 'data berhasil dikosongkan',
                        position: 'topRight'
                    });
                } else {
                    iziToast.error({
                        title: 'Maaf!',
                        message: data,
                        position: 'topRight'
                    });
                }

            }
        });
        return false;
    });

    // Toggle show/hide API Key
    $('#btn-toggle-key').click(function() {
        var input = $('#gemini_api_key');
        var icon = $('#icon-eye');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Simpan Konfigurasi AI
    $('#formpengaturan_ai').submit(function(e) {
        e.preventDefault();
        var btn = $('#btn-save-ai');
        var origHtml = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'mod_setting/crud_setting.php?pg=setting_ai',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                btn.html(origHtml).prop('disabled', false);
                if (res.status === 'success') {
                    iziToast.success({
                        title: 'Berhasil!',
                        message: res.message,
                        position: 'topRight'
                    });
                } else {
                    iziToast.error({
                        title: 'Gagal!',
                        message: res.message,
                        position: 'topRight'
                    });
                }
            },
            error: function(xhr) {
                btn.html(origHtml).prop('disabled', false);
                iziToast.error({
                    title: 'Error!',
                    message: 'Terjadi kesalahan komunikasi dengan server: ' + xhr.statusText,
                    position: 'topRight'
                });
            }
        });
        return false;
    });

    // ============================================================
    // DYNAMIC MULTI-PROVIDER AI CONTROLLER
    // ============================================================
    var providerMeta = {
        gemini: {
            name: 'Google Gemini',
            keyLabel: 'Google Gemini API Key',
            keyPlaceholder: 'AIzaSy...',
            helpText: 'Dapatkan API Key gratis di <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-primary font-weight-bold"><i class="fa fa-external-link"></i> Google AI Studio</a>.',
            defaultModel: 'gemini-3.5-flash-lite',
            models: [
                { id: 'gemini-3.5-flash-lite', label: '⭐ gemini-3.5-flash-lite (15 RPM / 500 RPD)', badge: 'bg-purple' },
                { id: 'gemini-2.5-flash', label: 'gemini-2.5-flash', badge: 'bg-blue' },
                { id: 'gemini-2.0-flash', label: 'gemini-2.0-flash', badge: 'bg-green' },
                { id: 'gemini-1.5-pro', label: 'gemini-1.5-pro', badge: 'bg-yellow' }
            ],
            defaultDelay: 4.5,
            defaultBatch: 15,
            guide: '<ol style="padding-left:18px;"><li>Buka <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</li><li>Klik <b>Create API Key</b>.</li><li>Salin API Key dan tempel di form.</li><li>Model disarankan: <b>gemini-3.5-flash-lite</b> (kuota gratis luas: 15 RPM & 500 request/hari).</li></ol>'
        },
        groq: {
            name: 'Groq Cloud',
            keyLabel: 'Groq API Key',
            keyPlaceholder: 'gsk_...',
            helpText: 'Dapatkan API Key gratis di <a href="https://console.groq.com/keys" target="_blank" class="text-primary font-weight-bold"><i class="fa fa-external-link"></i> Groq Console</a>.',
            defaultModel: 'llama-3.3-70b-versatile',
            models: [
                { id: 'llama-3.3-70b-versatile', label: '⭐ llama-3.3-70b-versatile (Tercepat)', badge: 'bg-purple' },
                { id: 'llama-3.1-8b-instant', label: 'llama-3.1-8b-instant', badge: 'bg-blue' },
                { id: 'mixtral-8x7b-32768', label: 'mixtral-8x7b-32768', badge: 'bg-green' },
                { id: 'gemma2-9b-it', label: 'gemma2-9b-it', badge: 'bg-yellow' }
            ],
            defaultDelay: 1.5,
            defaultBatch: 15,
            guide: '<ol style="padding-left:18px;"><li>Buka <a href="https://console.groq.com/keys" target="_blank">Groq Console</a>.</li><li>Klik <b>Create API Key</b>.</li><li>Salin API Key (awalan <code>gsk_...</code>).</li><li><b>Keunggulan Groq:</b> Kecepatan inferensi super cepat (>300 tps). Jeda antar batch cukup 1.5 detik.</li></ol>'
        },
        openrouter: {
            name: 'OpenRouter',
            keyLabel: 'OpenRouter API Key',
            keyPlaceholder: 'sk-or-v1-...',
            helpText: 'Dapatkan API Key di <a href="https://openrouter.ai/keys" target="_blank" class="text-primary font-weight-bold"><i class="fa fa-external-link"></i> OpenRouter Keys</a>.',
            defaultModel: 'meta-llama/llama-3.3-70b-instruct',
            models: [
                { id: 'meta-llama/llama-3.3-70b-instruct', label: '⭐ llama-3.3-70b-instruct', badge: 'bg-purple' },
                { id: 'deepseek/deepseek-chat', label: 'deepseek/deepseek-chat', badge: 'bg-blue' },
                { id: 'google/gemini-2.5-flash', label: 'google/gemini-2.5-flash', badge: 'bg-green' },
                { id: 'mistralai/mistral-large', label: 'mistralai/mistral-large', badge: 'bg-yellow' }
            ],
            defaultDelay: 2.0,
            defaultBatch: 15,
            guide: '<ol style="padding-left:18px;"><li>Daftar di <a href="https://openrouter.ai/keys" target="_blank">OpenRouter</a>.</li><li>Buat API Key baru.</li><li><b>Keunggulan:</b> 1 API Key untuk mengakses ratusan model AI (Claude, DeepSeek, Llama, Gemini, Qwen).</li></ol>'
        },
        deepseek: {
            name: 'DeepSeek AI',
            keyLabel: 'DeepSeek API Key',
            keyPlaceholder: 'sk-...',
            helpText: 'Dapatkan API Key di <a href="https://platform.deepseek.com/api_keys" target="_blank" class="text-primary font-weight-bold"><i class="fa fa-external-link"></i> DeepSeek Platform</a>.',
            defaultModel: 'deepseek-chat',
            models: [
                { id: 'deepseek-chat', label: '⭐ deepseek-chat (V3)', badge: 'bg-purple' },
                { id: 'deepseek-reasoner', label: 'deepseek-reasoner (R1 Reasoning)', badge: 'bg-blue' }
            ],
            defaultDelay: 2.0,
            defaultBatch: 15,
            guide: '<ol style="padding-left:18px;"><li>Buka <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek Platform</a>.</li><li>Buat API Key baru.</li><li><b>Keunggulan:</b> Penalaran cerdas dan harga per token sangat murah.</li></ol>'
        },
        openai: {
            name: 'OpenAI (ChatGPT)',
            keyLabel: 'OpenAI API Key',
            keyPlaceholder: 'sk-...',
            helpText: 'Dapatkan API Key di <a href="https://platform.openai.com/api-keys" target="_blank" class="text-primary font-weight-bold"><i class="fa fa-external-link"></i> OpenAI Platform</a>.',
            defaultModel: 'gpt-4o-mini',
            models: [
                { id: 'gpt-4o-mini', label: '⭐ gpt-4o-mini (Cepat & Akurat)', badge: 'bg-purple' },
                { id: 'gpt-4o', label: 'gpt-4o (Standar Flagship)', badge: 'bg-blue' }
            ],
            defaultDelay: 2.0,
            defaultBatch: 15,
            guide: '<ol style="padding-left:18px;"><li>Buka <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI API Keys</a>.</li><li>Buat API Secret Key.</li><li>Model <b>gpt-4o-mini</b> sangat efisien dan akurat untuk validasi soal.</li></ol>'
        },
        custom: {
            name: 'Custom / Local AI',
            keyLabel: 'API Key (Opsional bila tanpa password)',
            keyPlaceholder: 'Bearer token / sk-... (opsional)',
            helpText: 'Untuk Ollama lokal biasanya tanpa API Key (bisa diisi apa saja).',
            defaultModel: 'llama3:8b',
            models: [
                { id: 'llama3:8b', label: 'llama3:8b', badge: 'bg-purple' },
                { id: 'qwen2.5:7b', label: 'qwen2.5:7b', badge: 'bg-blue' },
                { id: 'mistral:latest', label: 'mistral:latest', badge: 'bg-green' }
            ],
            defaultDelay: 1.0,
            defaultBatch: 10,
            guide: '<ol style="padding-left:18px;"><li>Jalankan server lokal, contoh Ollama: <code>ollama run llama3:8b</code>.</li><li>Isi Base URL: <code>http://localhost:11434/v1</code>.</li><li>Tulis nama model yang sedang aktif di lokal Anda.</li><li>Privasi 100% lokal tanpa biaya token!</li></ol>'
        }
    };

    function applyProviderUI(providerKey, isInitial) {
        var meta = providerMeta[providerKey] || providerMeta['gemini'];

        $('#lbl-api-key').text(meta.keyLabel);
        $('#gemini_api_key').attr('placeholder', meta.keyPlaceholder);
        $('#help-api-key').html(meta.helpText);

        if (providerKey === 'custom') {
            $('#grp-base-url').slideDown();
        } else {
            $('#grp-base-url').slideUp();
        }

        // Render Quick Model Badges & Datalist
        var badgesHtml = '';
        var datalistHtml = '';
        meta.models.forEach(function(m) {
            badgesHtml += '<span style="cursor: pointer;" class="badge ' + m.badge + ' btn-quick-model" data-model="' + m.id + '">' + m.label + '</span> ';
            datalistHtml += '<option value="' + m.id + '">';
        });
        $('#quick-model-badges').html(badgesHtml);
        $('#model_list').html(datalistHtml);

        // Update Petunjuk
        var guideCard = meta.guide +
            '<div class="callout callout-info" style="margin-bottom: 0; padding: 10px 12px; margin-top: 10px;">' +
            '<i class="fa fa-magic"></i> <b>Analisis Bank Soal:</b><br>' +
            'Buka menu <b>Bank Soal</b> lalu klik tombol <b><i class="fa fa-robot"></i> Analisis AI</b> untuk memeriksa kesesuaian soal dan kunci jawaban.' +
            '</div>';
        $('#petunjuk-provider-container').html(guideCard);

        if (!isInitial) {
            $('#gemini_model').val(meta.defaultModel);
            $('#gemini_delay').val(meta.defaultDelay);
            $('#gemini_batch_size').val(meta.defaultBatch);
        }
    }

    $('#ai_provider').change(function() {
        applyProviderUI($(this).val(), false);
    });

    // Inisialisasi awal UI provider
    applyProviderUI($('#ai_provider').val(), true);

    // Test Koneksi AI
    $('#btn-test-ai').click(function() {
        var btn = $(this);
        var origHtml = btn.html();
        var provider = $('#ai_provider').val();
        var apiKey = $('#gemini_api_key').val().trim();
        var model = $('#gemini_model').val().trim();
        var baseUrl = $('#ai_base_url').val().trim();
        var container = $('#hasil-test-ai');

        if (!apiKey && provider !== 'custom') {
            iziToast.warning({
                title: 'Perhatian',
                message: 'Masukkan API Key untuk provider ' + provider.toUpperCase() + ' terlebih dahulu.',
                position: 'topRight'
            });
            $('#gemini_api_key').focus();
            return;
        }

        btn.html('<i class="fa fa-spinner fa-spin"></i> Menguji ' + provider.toUpperCase() + '...').prop('disabled', true);
        container.hide().html('');

        $.ajax({
            type: 'POST',
            url: 'mod_setting/crud_setting.php?pg=test_gemini',
            data: {
                ai_provider: provider,
                ai_base_url: baseUrl,
                gemini_api_key: apiKey,
                gemini_model: model
            },
            dataType: 'json',
            success: function(res) {
                btn.html(origHtml).prop('disabled', false);
                if (res.status === 'success') {
                    iziToast.success({
                        title: 'Sukses!',
                        message: res.message,
                        position: 'topRight'
                    });
                    container.html(
                        '<div class="alert alert-success" style="border-radius: 4px;">' +
                        '<h4><i class="icon fa fa-check"></i> ' + res.message + '</h4>' +
                        '<p><b>Provider:</b> ' + (res.provider || provider.toUpperCase()) + ' &bull; <b>Model:</b> ' + res.model + '</p>' +
                        '<p><b>Respons AI:</b> <i>"' + res.reply + '"</i></p>' +
                        '</div>'
                    ).slideDown();
                } else {
                    iziToast.error({
                        title: 'Koneksi Gagal',
                        message: res.message,
                        position: 'topRight'
                    });
                    container.html(
                        '<div class="alert alert-danger" style="border-radius: 4px;">' +
                        '<h4><i class="icon fa fa-ban"></i> Uji Koneksi Gagal (' + provider.toUpperCase() + ')</h4>' +
                        '<p>' + res.message + '</p>' +
                        '</div>'
                    ).slideDown();
                }
            },
            error: function(xhr) {
                btn.html(origHtml).prop('disabled', false);
                iziToast.error({
                    title: 'Error',
                    message: 'Gagal menghubungi endpoint: ' + xhr.statusText,
                    position: 'topRight'
                });
                container.html(
                    '<div class="alert alert-danger" style="border-radius: 4px;">' +
                    '<h4><i class="icon fa fa-ban"></i> Error Sistem</h4>' +
                    '<p>HTTP Error ' + xhr.status + ': ' + xhr.statusText + '</p>' +
                    '</div>'
                ).slideDown();
            }
        });
    });

    $(document).on('click', '.btn-quick-model', function() {
        var m = $(this).data('model');
        $('#gemini_model').val(m).focus();
    });
</script>