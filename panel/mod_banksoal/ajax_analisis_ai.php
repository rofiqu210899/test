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

    $q_soal = mysqli_query($koneksi, "SELECT id_soal, nomor, soal, pilA, pilB, pilC, pilD, pilE, jawaban FROM soal WHERE id_mapel='$id_mapel' AND jenis='1' ORDER BY nomor ASC LIMIT $offset, $limit");

    $soal_list = [];
    $soal_for_prompt = [];

    while ($r = mysqli_fetch_assoc($q_soal)) {
        // Bersihkan HTML tag berlebih untuk efisiensi token prompt (Anti-TPM Peak)
        $clean_soal = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $r['soal'])));
        if (mb_strlen($clean_soal) > 1000) {
            $clean_soal = mb_substr($clean_soal, 0, 1000) . '... [dipersingkat]';
        }
        $cleanA = trim(strip_tags($r['pilA']));
        $cleanB = trim(strip_tags($r['pilB']));
        $cleanC = trim(strip_tags($r['pilC']));
        $cleanD = trim(strip_tags($r['pilD']));
        $cleanE = trim(strip_tags($r['pilE']));

        $soal_list[$r['nomor']] = [
            'id_soal' => (int)$r['id_soal'],
            'nomor' => (int)$r['nomor'],
            'soal_preview' => mb_substr($clean_soal, 0, 140) . (mb_strlen($clean_soal) > 140 ? '...' : ''),
            'kunci_sekarang' => strtoupper(trim($r['jawaban']))
        ];

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
        : "Anda adalah Validator & Reviewer Soal Ujian dan Pakar Kurikulum Sekolah Profesional tingkat SMP/MTs/SMA.";

    $sysPrompt = "{$systemPersona}
Mata Pelajaran: {$mapelName} (Level/Kelas: {$mapel['level']}).

TUGAS ANDA:
Analisis setiap butir soal pilihan ganda berikut. Tentukan apakah 'kunci_tercatat' sudah SESUAI atau TIDAK SESUAI (KUNCI SALAH) atau AMBIGU (opsi ganda/tidak ada opsi benar/soal rancu).

KRITERIA STATUS:
1. 'SESUAI' : Kunci yang tercatat sudah tepat dan benar secara akademis.
2. 'KUNCI_SALAH' : Kunci yang tercatat salah, Anda menemukan opsi lain yang terbukti benar menurut kaidah keilmuan.
3. 'AMBIGU' : Soal memiliki cacat logika, terdapat lebih dari satu jawaban yang benar, atau tidak ada satupun opsi yang benar.

OUTPUT WAJIB FORMAT JSON OBJECT BERIKUT (tanpa teks penjelasan pembuka/penutup):
{
  \"hasil\": [
    {
      \"nomor\": 1,
      \"id_soal\": 123,
      \"kunci_sekarang\": \"A\",
      \"kunci_ai\": \"A\",
      \"status\": \"SESUAI\",
      \"alasan\": \"Kunci A tepat karena...\"
    }
  ]
}";

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
        $local = $soal_list[$num] ?? null;

        $st = strtoupper(trim($item['status'] ?? 'SESUAI'));
        if (!in_array($st, ['SESUAI', 'KUNCI_SALAH', 'AMBIGU'])) {
            $st = 'SESUAI';
        }

        $kunciAi = strtoupper(trim($item['kunci_ai'] ?? ($item['kunci_rekomendasi'] ?? '')));
        $kunciSekarang = $local ? $local['kunci_sekarang'] : strtoupper(trim($item['kunci_sekarang'] ?? ''));

        // Koreksi status jika kunci_sekarang == kunci_ai tapi status diberi KUNCI_SALAH
        if ($kunciAi === $kunciSekarang && $st === 'KUNCI_SALAH') {
            $st = 'SESUAI';
        }

        $finalHasil[] = [
            'nomor' => $num,
            'id_soal' => $local ? $local['id_soal'] : intval($item['id_soal'] ?? 0),
            'soal_preview' => $local ? $local['soal_preview'] : '',
            'kunci_sekarang' => $kunciSekarang,
            'kunci_ai' => $kunciAi,
            'status' => $st,
            'alasan' => $item['alasan'] ?? ($item['penjelasan'] ?? '')
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

echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali.']);
