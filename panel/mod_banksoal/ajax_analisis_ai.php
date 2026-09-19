<?php
require("../../config/config.default.php");
require("../../config/config.function.php");
require("../../config/functions.crud.php");
cek_session_admin();

header('Content-Type: application/json; charset=utf-8');
@set_time_limit(180);
@ini_set('max_execution_time', 180);

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ============================================================
// HELPER: DETEKSI SOAL GANDA / DUPLIKAT KOMPREHENSIF
// ============================================================
function detect_mapel_duplicates($koneksi, $id_mapel) {
    $q = mysqli_query($koneksi, "SELECT id_soal, nomor, soal, pilA, pilB FROM soal WHERE id_mapel='$id_mapel' AND jenis='1' ORDER BY id_soal ASC");
    $items = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $clean_text = preg_replace('/[^a-z0-9]/', '', strtolower(strip_tags(html_entity_decode($row['soal'], ENT_QUOTES, 'UTF-8'))));
        $clean_opsi = preg_replace('/[^a-z0-9]/', '', strtolower(strip_tags(html_entity_decode($row['pilA'] . $row['pilB'], ENT_QUOTES, 'UTF-8'))));
        $items[] = [
            'id_soal' => (int)$row['id_soal'],
            'nomor' => (int)$row['nomor'],
            'clean_text' => $clean_text,
            'clean_opsi' => $clean_opsi,
            'len' => strlen($clean_text)
        ];
    }

    $dup_map = [];
    $n = count($items);
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $t1 = $items[$i]['clean_text'];
            $t2 = $items[$j]['clean_text'];
            $o1 = $items[$i]['clean_opsi'];
            $o2 = $items[$j]['clean_opsi'];
            if (empty($t1) || empty($t2)) continue;

            $is_dup = false;
            $percent = 0;

            // 1. Teks soal identik 100%
            if ($t1 === $t2) {
                if ($o1 === $o2 || empty($o1) || empty($o2)) {
                    $is_dup = true;
                    $percent = 100;
                }
            } elseif ($items[$i]['len'] > 20 && $items[$j]['len'] > 20) {
                // 2. Teks soal mirip >= 88% DAN opsi pilihan ganda identik atau mirip >= 80%
                similar_text($t1, $t2, $sim_text);
                if ($sim_text >= 88.0) {
                    if ($o1 === $o2 && !empty($o1)) {
                        $is_dup = true;
                        $percent = round($sim_text, 1);
                    } else {
                        similar_text($o1, $o2, $sim_opsi);
                        if ($sim_opsi >= 80.0) {
                            $is_dup = true;
                            $percent = round(($sim_text + $sim_opsi) / 2, 1);
                        }
                    }
                }
            }

            if ($is_dup) {
                $second_id = $items[$j]['id_soal'];
                if (!isset($dup_map[$second_id])) {
                    $dup_map[$second_id] = [
                        'dup_id' => $items[$i]['id_soal'],
                        'dup_nomor' => $items[$i]['nomor'],
                        'percent' => $percent
                    ];
                }
            }
        }
    }
    return $dup_map;
}

// ============================================================
// 1. INFO MAPEL & STATUS AI
// ============================================================
if ($action == 'info') {
    $id_mapel = intval($_POST['id_mapel'] ?? 0);
    if ($id_mapel <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID Mapel tidak valid.']);
        exit;
    }

    $mapel = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM mapel WHERE id_mapel='$id_mapel'"));
    if (!$mapel) {
        echo json_encode(['status' => 'error', 'message' => 'Bank Soal tidak ditemukan.']);
        exit;
    }

    $ai_set = get_ai_setting($koneksi);
    $total_soal = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_soal FROM soal WHERE id_mapel='$id_mapel' AND jenis='1'"));

    echo json_encode([
        'status' => 'success',
        'id_mapel' => $id_mapel,
        'kode' => $mapel['kode'],
        'nama' => $mapel['nama'],
        'level' => $mapel['level'],
        'total_soal' => $total_soal,
        'ai_active' => ($ai_set['status'] == 1 && !empty($ai_set['api_key'])),
        'ai_provider' => $ai_set['provider'] ?? 'gemini',
        'ai_model' => $ai_set['model'],
        'ai_delay' => floatval($ai_set['delay'] ?? 4.5),
        'ai_batch_size' => intval($ai_set['batch_size'] ?? 15),
        'has_api_key' => !empty($ai_set['api_key'])
    ]);
    exit;
}

// ============================================================
// 2. ANALISIS SOAL DENGAN GEMINI AI (BATCH PROCESS)
// ============================================================
if ($action == 'analisis') {
    $id_mapel = intval($_POST['id_mapel'] ?? 0);
    $offset   = intval($_POST['offset'] ?? 0);

    if ($id_mapel <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID Mapel tidak valid.']);
        exit;
    }

    $ai_set = get_ai_setting($koneksi);
    $defaultLimit = intval($ai_set['batch_size'] ?? 15);
    $limit    = isset($_POST['limit']) ? intval($_POST['limit']) : $defaultLimit;
    if ($limit <= 0 || $limit > 25) $limit = ($defaultLimit > 0) ? $defaultLimit : 15;

    if (empty($ai_set['api_key'])) {
        echo json_encode([
            'status' => 'error',
            'code' => 'NO_API_KEY',
            'message' => 'Google Gemini API Key belum diisi. Silakan atur terlebih dahulu pada menu Pengaturan > Konfigurasi AI.'
        ]);
        exit;
    }

    if ($ai_set['status'] == 0) {
        echo json_encode([
            'status' => 'error',
            'code' => 'AI_INACTIVE',
            'message' => 'Fitur AI saat ini sedang nonaktif. Aktifkan di menu Pengaturan > Konfigurasi AI.'
        ]);
        exit;
    }

    $mapel = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM mapel WHERE id_mapel='$id_mapel'"));
    $total_all = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_soal FROM soal WHERE id_mapel='$id_mapel' AND jenis='1'"));

    // Peta seluruh soal ganda/duplikat dalam bank soal ini
    $duplicate_map = detect_mapel_duplicates($koneksi, $id_mapel);

    $q_soal = mysqli_query($koneksi, "SELECT id_soal, nomor, soal, pilA, pilB, pilC, pilD, pilE, jawaban FROM soal WHERE id_mapel='$id_mapel' AND jenis='1' ORDER BY id_soal ASC LIMIT $offset, $limit");

    $soal_list = [];
    $soal_for_prompt = [];

    while ($r = mysqli_fetch_assoc($q_soal)) {
        // Bersihkan HTML tag berlebih untuk efisiensi token prompt (Anti-TPM Peak)
        $clean_soal = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $r['soal'])));
        if (mb_strlen($clean_soal) > 2000) {
            $clean_soal = mb_substr($clean_soal, 0, 2000) . '... [dipersingkat]';
        }
        $cleanA = trim(strip_tags($r['pilA']));
        $cleanB = trim(strip_tags($r['pilB']));
        $cleanC = trim(strip_tags($r['pilC']));
        $cleanD = trim(strip_tags($r['pilD']));
        $cleanE = trim(strip_tags($r['pilE']));

        $item_info = [
            'id_soal' => (int)$r['id_soal'],
            'nomor' => (int)$r['nomor'],
            'soal_preview' => mb_substr($clean_soal, 0, 140) . (mb_strlen($clean_soal) > 140 ? '...' : ''),
            'kunci_sekarang' => strtoupper(trim($r['jawaban']))
        ];

        // Simpan dengan key id_soal dan key nomor untuk lookup yang aman
        $soal_list[(int)$r['id_soal']] = $item_info;
        $soal_list['no_' . (int)$r['nomor']] = $item_info;

        $prompt_item = [
            'nomor' => (int)$r['nomor'],
            'id_soal' => (int)$r['id_soal'],
            'pertanyaan' => $clean_soal,
            'pilihan' => [
                'A' => $cleanA,
                'B' => $cleanB,
                'C' => $cleanC,
                'D' => $cleanD,
            ],
            'kunci_tercatat' => strtoupper(trim($r['jawaban']))
        ];
        if (!empty($cleanE)) {
            $prompt_item['pilihan']['E'] = $cleanE;
        }

        $soal_for_prompt[] = $prompt_item;
    }

    if (empty($soal_for_prompt)) {
        echo json_encode([
            'status' => 'success',
            'is_finished' => true,
            'message' => 'Tidak ada soal lagi yang perlu dianalisis.',
            'total_all' => $total_all,
            'hasil' => []
        ]);
        exit;
    }

    // Bangun Prompt Instruksi AI Multi-Provider
    $mapelName = $mapel['nama'] ?? $mapel['kode'];
    $systemPersona = !empty($ai_set['prompt'])
        ? $ai_set['prompt']
        : "Anda adalah Validator & Quality Assurance Soal Ujian Profesional tingkat SMP/MTs/SMA berbasis CBT.";

    $sysPrompt = "{$systemPersona}
Mata Pelajaran: {$mapelName} (Level/Kelas: {$mapel['level']}).

TUGAS ANDA:
Lakukan audit, validasi kelayakan, dan verifikasi butir soal pilihan ganda berikut untuk persiapan ujian Computer-Based Testing (CBT).

PERINGATAN SISTEM CBT:
Dalam ujian CBT ini, SELURUH NOMOR SOAL AKAN DIACAK (SHUFFLE) SECARA OTOMATIS saat dikerjakan oleh siswa. Setiap butir soal WAJIB BERDIRI SENDIRI (MANDIRI) dan TIDAK BOLEH mengasumsikan urutan nomor soal tetap berurutan!

KRITERIA STATUS HASIL ANALISIS (Pilih salah satu status yang paling tepat):
1. 'DUPLIKAT' (Prioritas Deteksi Soal Ganda / Salah Input Dua Kali):
   - Butir soal ini memiliki teks pertanyaan atau isi stimulus yang sama persis / duplikat dengan butir soal lain dalam ujian ini (terindikasi salah input dua kali).
   - Jelaskan di 'alasan': 'Soal ini terindikasi ganda/duplikat dengan butir soal lain...'.

2. 'CACAT_ACAK' (Prioritas Deteksi Anomali Pengacakan CBT):
   - Soal merujuk pada nomor urut soal tertentu atau mengasumsikan urutan nomor soal tetap berurutan.
   - Contoh kasus CACAT_ACAK:
     * Teks memuat kalimat 'Bacalah teks berikut untuk menjawab soal nomor 1-5' atau 'Soal nomor 3 s.d. 5'.
     * Teks memuat rujukan 'Berdasarkan kutipan pada soal nomor 2...', 'Perhatikan jawaban pada soal sebelumnya...'.
     * Butir soal membutuhkan teks bacaan/gambar/stimulus yang hanya ada di nomor lain dan tidak disertakan di butir soal ini.
   - Dampak: Karena sistem CBT mengacak nomor butir soal, siswa akan kehilangan stimulus atau salah paham membaca nomor rujukan.
   - Jelaskan di 'alasan' mengapa cacat acak dan berikan saran perbaikan (contoh: 'Sertakan teks stimulus langsung di butir soal ini dan hilangkan kalimat rujukan nomor 1-5').

3. 'TIDAK_LOGIS' (Anomali Kelogisan / Pilihan Ganda Tertukar / Format Rusak):
   - Soal tidak masuk akal, kalimat rancu/rusak, atau susunan soal acak-acakan.
   - OPSI JAWABAN TERTUKAR DENGAN NOMOR LAIN: Pertanyaan dan pilihan ganda sama sekali tidak nyambung (contoh: pertanyaan menanyakan sinonim kata Bahasa Indonesia, tetapi opsi jawabannya adalah rumus atau opsi dari nomor soal lain yang keliru di-copy-paste).
   - Ambigu: Tidak ada satupun opsi yang benar, ATAU terdapat lebih dari satu opsi jawaban yang sama-sama benar.

4. 'KUNCI_SALAH' (Kunci Jawaban CBT Keliru):
   - Soal logis, mandiri (ramah pengacakan CBT), dan opsi jawaban nyambung, TETAPI kunci jawaban yang tercatat saat ini di CBT salah.
   - AI menemukan opsi jawaban yang terbukti benar menurut kaidah keilmuan. Wajib cantumkan huruf opsi yang benar pada 'kunci_ai'.

5. 'SESUAI' (Soal Valid, Mandiri & Kunci Benar):
   - Soal logis, mandiri (tidak merujuk nomor lain), opsi jawaban sinkron, dan kunci jawaban yang tercatat di CBT sudah tepat.

OUTPUT WAJIB FORMAT JSON OBJECT BERIKUT (tanpa teks penjelasan pembuka/penutup):
{
  \"hasil\": [
    {
      \"nomor\": 1,
      \"id_soal\": 123,
      \"kunci_sekarang\": \"A\",
      \"kunci_ai\": \"A\",
      \"status\": \"SESUAI\",
      \"alasan\": \"Penjelasan ringkas, spesifik, dan solutif.\"
    }
  ]
}
Catatan:
- Nilai 'status' HANYA boleh salah satu dari: 'SESUAI', 'KUNCI_SALAH', 'TIDAK_LOGIS', 'CACAT_ACAK', 'DUPLIKAT'.
- Jika berstatus 'CACAT_ACAK', 'TIDAK_LOGIS', atau 'DUPLIKAT', tetap rekomendasikan 'kunci_ai' jika ada opsi yang paling tepat, atau kosongkan (\"\") jika tidak ada.";

    $userPrompt = "Berikut data butir soal yang harus dianalisis:\n" . json_encode($soal_for_prompt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Panggil Unified AI Engine (Timeout disesuaikan: 120s untuk custom/reasoning model, 90s default)
    $timeoutSec = ($ai_set['provider'] === 'custom') ? 120 : 90;
    $call = call_ai_service($ai_set, $sysPrompt, $userPrompt, [
        'temperature' => 0.1,
        'json_mode' => true,
        'timeout' => $timeoutSec
    ]);

    if (!$call['success']) {
        if ($call['code'] === 'RATE_LIMIT') {
            echo json_encode([
                'status' => 'error',
                'code' => 'RATE_LIMIT',
                'retry_after' => $call['retry_after'] ?? 10,
                'message' => $call['message']
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => $call['message']
            ]);
        }
        exit;
    }

    $rawAiText = $call['content'];
    $parsedAi = json_decode($rawAiText, true);

    // Tangani jika respon AI terbungkus key (misal {"hasil": [...]}, {"data": [...]}, {"soal": [...]})
    if (is_array($parsedAi)) {
        if (isset($parsedAi['hasil']) && is_array($parsedAi['hasil'])) {
            $parsedAi = $parsedAi['hasil'];
        } elseif (isset($parsedAi['data']) && is_array($parsedAi['data'])) {
            $parsedAi = $parsedAi['data'];
        } elseif (isset($parsedAi['soal']) && is_array($parsedAi['soal'])) {
            $parsedAi = $parsedAi['soal'];
        } elseif (!isset($parsedAi[0])) {
            foreach ($parsedAi as $val) {
                if (is_array($val) && isset($val[0])) {
                    $parsedAi = $val;
                    break;
                }
            }
        }
    }

    if (!is_array($parsedAi)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'AI (' . strtoupper($call['provider']) . ') tidak mengembalikan format JSON yang valid.',
            'raw' => $rawAiText
        ]);
        exit;
    }

    // Gabungkan dengan info soal lokal
    $finalHasil = [];
    foreach ($parsedAi as $item) {
        $num = intval($item['nomor'] ?? 0);
        $idSoal = intval($item['id_soal'] ?? 0);

        // Cari data lokal berdasarkan id_soal terlebih dahulu, jika tidak ada baru gunakan nomor
        $local = null;
        if ($idSoal > 0 && isset($soal_list[$idSoal])) {
            $local = $soal_list[$idSoal];
        } elseif (isset($soal_list['no_' . $num])) {
            $local = $soal_list['no_' . $num];
        }

        $realId = $local ? $local['id_soal'] : $idSoal;
        $rawStatus = strtoupper(trim($item['status'] ?? 'SESUAI'));
        $st = 'SESUAI';
        $alasan = $item['alasan'] ?? ($item['penjelasan'] ?? '');

        // 1. Cek deteksi ganda / duplikat sistem (Prioritas Tertinggi)
        if ($realId > 0 && isset($duplicate_map[$realId])) {
            $dupInfo = $duplicate_map[$realId];
            $st = 'DUPLIKAT';
            $alasan = "Terdeteksi SOAL GANDA / DUPLIKAT (" . $dupInfo['percent'] . "% identik) dengan Butir Soal No. " . $dupInfo['dup_nomor'] . " (ID Soal: " . $dupInfo['dup_id'] . "). Terindikasi salah input dua kali. Harap periksa dan hapus salah satunya.";
        } elseif (strpos($rawStatus, 'DUPLIKAT') !== false || strpos($rawStatus, 'GANDA') !== false) {
            $st = 'DUPLIKAT';
        } elseif (strpos($rawStatus, 'ACAK') !== false) {
            $st = 'CACAT_ACAK';
        } elseif (strpos($rawStatus, 'LOGIS') !== false || strpos($rawStatus, 'AMBIGU') !== false || strpos($rawStatus, 'TUKAR') !== false || strpos($rawStatus, 'RANCU') !== false) {
            $st = 'TIDAK_LOGIS';
        } elseif (strpos($rawStatus, 'SALAH') !== false) {
            $st = 'KUNCI_SALAH';
        } elseif ($rawStatus === 'SESUAI') {
            $st = 'SESUAI';
        }

        $kunciAi = strtoupper(trim($item['kunci_ai'] ?? ($item['kunci_rekomendasi'] ?? '')));
        $kunciSekarang = $local ? $local['kunci_sekarang'] : strtoupper(trim($item['kunci_sekarang'] ?? ''));

        // Koreksi status jika kunci_sekarang == kunci_ai tapi status diberi KUNCI_SALAH
        if ($kunciAi === $kunciSekarang && $st === 'KUNCI_SALAH') {
            $st = 'SESUAI';
        }

        $finalHasil[] = [
            'nomor' => $local ? $local['nomor'] : $num,
            'id_soal' => $realId,
            'soal_preview' => $local ? $local['soal_preview'] : '',
            'kunci_sekarang' => $kunciSekarang,
            'kunci_ai' => $kunciAi,
            'status' => $st,
            'alasan' => $alasan
        ];
    }

    $processed_count = $offset + count($soal_for_prompt);
    $is_finished = ($processed_count >= $total_all);

    echo json_encode([
        'status' => 'success',
        'id_mapel' => $id_mapel,
        'offset' => $offset,
        'limit' => $limit,
        'processed' => count($finalHasil),
        'processed_total' => $processed_count,
        'total_all' => $total_all,
        'is_finished' => $is_finished,
        'hasil' => $finalHasil
    ]);
    exit;
}

// ============================================================
// 3. UPDATE KUNCI JAWABAN (1-CLICK FIX DARI MODAL)
// ============================================================
if ($action == 'update_kunci') {
    $id_soal    = intval($_POST['id_soal'] ?? 0);
    $kunci_baru = strtoupper(trim($_POST['kunci_baru'] ?? ''));

    if ($id_soal <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID Soal tidak valid.']);
        exit;
    }

    if (!in_array($kunci_baru, ['A', 'B', 'C', 'D', 'E'])) {
        echo json_encode(['status' => 'error', 'message' => 'Kunci jawaban baru harus berupa A, B, C, D, atau E.']);
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE soal SET jawaban = ? WHERE id_soal = ?");
    mysqli_stmt_bind_param($stmt, 'si', $kunci_baru, $id_soal);
    $exec = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($exec) {
        echo json_encode([
            'status' => 'success',
            'message' => "Kunci jawaban berhasil diperbarui menjadi {$kunci_baru}.",
            'id_soal' => $id_soal,
            'kunci_baru' => $kunci_baru
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui kunci di database: ' . mysqli_error($koneksi)
        ]);
    }
    exit;
}

// ============================================================
// 4. HAPUS BUTIR SOAL GANDA / DUPLIKAT (1-CLICK DELETE DARI MODAL)
// ============================================================
if ($action == 'hapus_soal') {
    $id_soal = intval($_POST['id_soal'] ?? 0);

    if ($id_soal <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID Soal tidak valid.']);
        exit;
    }

    $exec = mysqli_query($koneksi, "DELETE FROM soal WHERE id_soal = '$id_soal'");
    if ($exec) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Butir soal ganda berhasil dihapus dari bank soal.',
            'id_soal' => $id_soal
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus butir soal: ' . mysqli_error($koneksi)
        ]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali.']);
