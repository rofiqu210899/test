<?php
require("../../config/config.default.php");
require("../../config/config.function.php");
require("../../config/functions.crud.php");
cek_session_admin();

header('Content-Type: application/json; charset=utf-8');

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

    // Bangun Prompt Instruksi Gemini
    $mapelName = $mapel['nama'] ?? $mapel['kode'];
    $systemPersona = !empty($ai_set['prompt'])
        ? $ai_set['prompt']
        : "Anda adalah Validator & Reviewer Soal Ujian dan Pakar Kurikulum Sekolah Profesional tingkat SMP/MTs/SMA.";

    $promptInstruction = "
{$systemPersona}
Mata Pelajaran: {$mapelName} (Level/Kelas: {$mapel['level']}).

TUGAS ANDA:
Analisis setiap butir soal pilihan ganda berikut. Tentukan apakah 'kunci_tercatat' sudah SESUAI atau TIDAK SESUAI (KUNCI SALAH) atau AMBIGU (opsi ganda/tidak ada opsi benar/soal rancu).

KRITERIA STATUS:
1. 'SESUAI' : Kunci yang tercatat sudah tepat dan benar secara akademis.
2. 'KUNCI_SALAH' : Kunci yang tercatat salah, Anda menemukan opsi lain yang terbukti benar menurut kaidah keilmuan.
3. 'AMBIGU' : Soal memiliki cacat logika, terdapat lebih dari satu jawaban yang benar, atau tidak ada satupun opsi yang benar.

OUTPUT HARUS FORMAT JSON ARRAY MURNI (tanpa teks pembuka/penutup):
[
  {
    \"nomor\": 1,
    \"id_soal\": 123,
    \"kunci_sekarang\": \"A\",
    \"kunci_ai\": \"A\",
    \"status\": \"SESUAI\",
    \"alasan\": \"Kunci A tepat karena...\"
  }
]

Berikut data butir soal yang harus dianalisis:
" . json_encode($soal_for_prompt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $apiKey = $ai_set['api_key'];
    $model  = $ai_set['model'];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $promptInstruction]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.1,
            'responseMimeType' => 'application/json'
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 45
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        echo json_encode(['status' => 'error', 'message' => 'Koneksi ke Gemini timeout/gagal: ' . $curlErr]);
        exit;
    }

    $resJson = json_decode($response, true);

    // Deteksi Rate Limit / Quota Exceeded (HTTP 429 atau RESOURCE_EXHAUSTED)
    if ($httpCode === 429 || stripos($response, 'RESOURCE_EXHAUSTED') !== false || stripos($response, 'Quota exceeded') !== false || stripos($response, 'rate limit') !== false) {
        $retryWait = 10;
        echo json_encode([
            'status' => 'error',
            'code' => 'RATE_LIMIT',
            'retry_after' => $retryWait,
            'message' => 'Batas Rate Limit (RPM/TPM 429) tercapai. Menunggu ' . $retryWait . ' detik sebelum mencoba ulang otomatis.'
        ]);
        exit;
    }

    if ($httpCode !== 200 || !isset($resJson['candidates'][0]['content']['parts'][0]['text'])) {
        $msg = $resJson['error']['message'] ?? ("HTTP {$httpCode}: respons Gemini tidak valid.");
        echo json_encode(['status' => 'error', 'message' => 'Error dari Gemini AI: ' . $msg]);
        exit;
    }

    $rawAiText = trim($resJson['candidates'][0]['content']['parts'][0]['text']);
    // Bersihkan code fences bila ada
    $rawAiText = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawAiText);
    $parsedAi = json_decode($rawAiText, true);

    if (!is_array($parsedAi)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'AI tidak mengembalikan format JSON yang valid.',
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
