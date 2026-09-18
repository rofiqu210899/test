<?php
// =====================================================
// API ENDPOINT - Candy CBT (X-Candy CBT)
// Akses HTTP+JSON, autentikasi via header X-API-KEY
// =====================================================
// GANTI konfigurasi regex di api/api_key.php bila perlu.
// =====================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$cfg = require __DIR__ . '/api_key.php';

// ---------- Autentikasi ----------
$provided = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!hash_equals($cfg['api_key'], $provided)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Header X-API-KEY tidak valid.']);
    exit;
}

require __DIR__ . '/../config/config.database.php'; // $koneksi

// ---------- Helper ----------
function api_json($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function api_fail($msg, $code = 400)
{
    api_json(['status' => 'error', 'message' => $msg], $code);
}

// ---------- Ekstrak & simpan gambar dari word ----------
function extract_and_save_images($zip, $target_dir, $cfg)
{
    $rels_file = $target_dir . 'word/_rels/document.xml.rels';
    $relXml = @simplexml_load_file($rels_file);
    if ($relXml === false) {
        return [];
    }

    $relations = [];
    foreach ($relXml as $rel) {
        $ext = strtolower(pathinfo((string) $rel['Target'], PATHINFO_EXTENSION));
        if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'bmp'])) {
            $relations[(string) $rel['Id']] = (string) $rel['Target'];
        }
    }

    $saved = [];
    $inc = 1;
    foreach ($relations as $id => $target) {
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $newName = time() . $inc . '.' . $ext;
        $oldPath = $target_dir . 'word/' . $target;
        $newPath = $cfg['upload_dir'] . $newName;
        if (file_exists($oldPath)) {
            copy($oldPath, $newPath);
            $saved[$id] = $newName; // rel: nama-file-baru
            $inc++;
        }
    }
    return $saved;
}

// ---------- Parse konten word menjadi array soal ----------
// Menangani DUA format:
//   A) Eksplisit: *1. pembatas soal + *A. pembatas opsi (kartu soal eksplisit)
//   B) Kartu soal urutan: tiap soal = 1 paragraf soal + 4 paragraf opsi (A-D)
//      + instruksi "Soal berikut untuk nomor X dan Y" (2 soal berbagi 1 gambar
//      berdiri-sendiri) + deretan kunci di akhir (dipetakan ke urutan).
function parse_questions($content, $cfg)
{
    $lines = preg_split('/\R/', $content);
    $clean = array_values(array_filter($lines, function ($l) {
        return trim($l) !== '';
    }));

    // ============ STRATEGI A: pembatas eksplisit ============
    if (preg_match($cfg['question_split'], $content)) {
        $blocks = array_filter(preg_split($cfg['question_split'], $content), function ($b) {
            return trim($b) !== '';
        });
        $result = [];
        foreach ($blocks as $block) {
            $parts = array_filter(preg_split($cfg['option_split'], $block), function ($b) {
                return trim($b) !== '';
            });
            $question = count($parts) > 0 ? trim($parts[0]) : '';
            $options = array_map('trim', $parts);
            $correct = '';
            if (count($parts) > 0 && preg_match($cfg['correct_split'], $parts[count($parts) - 1], $m)) {
                $correct = strtoupper($m[1]);
            }
            $cleanOpts = array_map(function ($o) use ($cfg) {
                return trim(preg_replace($cfg['correct_split'], '', $o));
            }, $options);
            $result[] = [
                'question' => $question,
                'option' => $cleanOpts,
                'correct' => $correct,
                'option_count' => count($cleanOpts),
            ];
        }
        return $result;
    }

    // ============ STRATEGI B: kartu soal urutan / bernomor ============
    // Langkah 1: Cari posisi KUNCI → potong di situ
    $kunciIdx = null;
    $kunciMap = [];
    foreach ($clean as $k => $l) {
        if (preg_match('/^KUNCI/i', trim($l))) {
            $kunciIdx = $k;
            break;
        }
    }
    // Ambil kunci jawaban (format berurutan A-E ATAU pasangan angka+huruf: 1 B 2 A ...)
    if ($kunciIdx !== null) {
        $kunciTail = array_slice($clean, $kunciIdx + 1);
        $kunciText = implode(' ', $kunciTail);
        if (preg_match_all('/(?:No\.?\s*)?(\d+)[\.\s:\-\)]+([A-Ea-e])\b/i', $kunciText, $km)) {
            foreach ($km[1] as $ki => $num) {
                $kunciMap[(int)$num] = strtoupper($km[2][$ki]);
            }
        } else {
            $kSeq = 1;
            foreach ($kunciTail as $kt) {
                $kt = trim($kt);
                if (preg_match('/^[A-Ea-e]$/', $kt)) {
                    $kunciMap[$kSeq] = strtoupper($kt);
                    $kSeq++;
                }
            }
        }
        $soalLines = array_slice($clean, 0, $kunciIdx);
    } else {
        $soalLines = $clean;
    }

    // Langkah 2: Cek apakah format soal bernomor eksplisit (misal: "1. Soal...", "2. Soal...")
    $numberedCount = 0;
    foreach ($soalLines as $sl) {
        if (preg_match('/^\d+[\.\)]\s+/', trim($sl))) {
            $numberedCount++;
        }
    }

    if ($numberedCount >= 3) {
        $result = [];
        $currentQ = null;
        foreach ($soalLines as $sl) {
            $t = trim($sl);
            $is_new_q = false;
            $qNum = 0;
            $qTextRem = '';
            if (preg_match('/^(\d+)[\.\)]\s*(.*)$/', $t, $mn)) {
                $candNum = (int)$mn[1];
                $expectedNum = $currentQ ? ($currentQ['num'] + 1) : 1;
                if ($candNum === $expectedNum || ($currentQ && $candNum > $currentQ['num'] && $candNum <= $currentQ['num'] + 2)) {
                    $is_new_q = true;
                    $qNum = $candNum;
                    $qTextRem = $mn[2];
                }
            }

            if ($is_new_q) {
                if ($currentQ) {
                    $result[] = $currentQ;
                }
                $currentQ = [
                    'num' => $qNum,
                    'question' => $qTextRem,
                    'option' => [],
                    'correct' => $kunciMap[$qNum] ?? '',
                    'option_count' => 0,
                ];
            } elseif (preg_match('/^([A-Ea-e])[\.\)]\s*(.*)$/', $t, $mo) && $currentQ) {
                $letter = strtoupper($mo[1]);
                $mapIdx = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];
                $idx = $mapIdx[$letter] ?? count($currentQ['option']);
                $currentQ['option'][$idx] = $mo[2];
                $currentQ['option_count'] = count($currentQ['option']);
                $currentQ['_last_idx'] = $idx;
            } elseif ($currentQ) {
                if (empty($currentQ['option'])) {
                    $currentQ['question'] .= '<br>' . $t;
                } else {
                    $lastIdx = $currentQ['_last_idx'] ?? (count($currentQ['option']) - 1);
                    $currentQ['option'][$lastIdx] .= ' ' . $t;
                }
            }
        }
        if ($currentQ) {
            $result[] = $currentQ;
        }
        return $result;
    }

    // Langkah 3: Format kartu soal urutan klasik (5 baris = 1 soal)
    $filtered = [];
    $skipNext = false;
    $sharedImg = null;
    $sharedNums = []; // nomor soal yg berbagi gambar (mis: [8,9])
    foreach ($soalLines as $sl) {
        $t = trim($sl);
        if ($skipNext) {
            if (strpos($t, '[IMG:') !== false) {
                $sharedImg = $t;
            }
            $skipNext = false;
            continue;
        }
        if (preg_match('/^Soal\s+berikut\s+untuk\s+nomor\s+(\d+)\s+dan\s+(\d+)$/i', $t, $m)) {
            $sharedNums = [(int)$m[1], (int)$m[2]];
            $skipNext = true;
            continue;
        }
        if (strpos($t, '[IMG:') !== false) {
            if (count($filtered) > 0) {
                $filtered[count($filtered) - 1] .= ' ' . $t;
            }
            continue;
        }
        $filtered[] = $t;
    }

    $totalLines = count($filtered);
    $remainder = $totalLines % 5;
    $offset = 0;
    if ($remainder !== 0 && $remainder <= 1) {
        $offset = $remainder;
    }
    $result = [];
    $soalNum = 1;
    for ($i = $offset; $i + 4 < count($filtered); $i += 5) {
        $qText = $filtered[$i];
        if ($sharedImg !== null && in_array($soalNum, $sharedNums)) {
            $qText .= ' ' . $sharedImg;
        }
        $result[] = [
            'question' => $qText,
            'option' => [$filtered[$i+1], $filtered[$i+2], $filtered[$i+3], $filtered[$i+4]],
            'correct' => $kunciMap[$soalNum] ?? '',
            'option_count' => 4,
        ];
        $soalNum++;
    }

    return $result;
}

// ---------- Sanitasi teks (tanda kutip & baris baru) ----------
function clean_text($t)
{
    $t = str_replace(['"', '“', '”', '\'', '‘', '’'], ['&quot;', '&quot;', '&quot;', '&#39;', '&#39;', '&#39;'], $t);
    $t = str_replace(["\r\n", "\r", "\n"], '<br>', $t);
    return $t;
}

// ---------- Insert soal ke tabel `soal` ----------
function insert_questions($koneksi, $id_mapel, $questions)
{
    $inserted = 0;
    $counter = 1;
    foreach ($questions as $q) {
        $imgMap = $GLOBALS['img_map'] ?? [];
        $replaceImg = function ($t) use ($imgMap) {
            foreach ($imgMap as $rel => $fname) {
                $t = str_replace('[IMG:' . $rel . ']', "<img src='../files/" . $fname . "'>", $t);
            }
            return $t;
        };
        $soal = $replaceImg(clean_text($q['question']));
        $jenis = ($q['option_count'] > 1) ? 1 : 2; // 1=PG, 2=esai

        $pilA = isset($q['option'][0]) ? clean_text($q['option'][0]) : '';
        $pilB = isset($q['option'][1]) ? clean_text($q['option'][1]) : '';
        $pilC = isset($q['option'][2]) ? clean_text($q['option'][2]) : '';
        $pilD = isset($q['option'][3]) ? clean_text($q['option'][3]) : '';
        $pilE = isset($q['option'][4]) ? clean_text($q['option'][4]) : '';

        // Jawaban benar (hanya utk PG) - pastikan cocok dengan abjad
        $jawaban = '';
        if ($jenis == 1) {
            $mapAbjad = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];
            $jawaban = ($q['correct'] !== '' && isset($mapAbjad[$q['correct']]))
                ? $q['correct'] : '';
        }

        // Actual insert (10 kolom, parameter: 1 int + 9 string)
        $sql = "INSERT INTO soal (id_mapel, nomor, soal, jenis, pilA, pilB, pilC, pilD, pilE, jawaban)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'isssssssss',
            $id_mapel, $counter, $soal, $jenis,
            $pilA, $pilB, $pilC, $pilD, $pilE, $jawaban
        );
        if (mysqli_stmt_execute($stmt)) {
            $inserted++;
            $counter++;
        }
        mysqli_stmt_close($stmt);
    }
    return $inserted;
}

// ---------- Buat mapel baru bila belum ada ----------
function ensure_mapel($koneksi, $nama, $guru = '', $kelas = '', $level = '', $customKode = '')
{
    $nama = trim($nama);
    if ($nama === '') {
        api_fail('Nama mapel kosong. Kirim field "mapel".', 400);
    }
    $customKode = trim($customKode);
    $q = mysqli_query($koneksi, "SELECT id_mapel FROM mapel WHERE nama = '" . mysqli_real_escape_string($koneksi, $nama) . "'");
    if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_array($q);
        $id_mapel = (int) $row['id_mapel'];
        if ($customKode !== '') {
            mysqli_query($koneksi, "UPDATE mapel SET kode = '" . mysqli_real_escape_string($koneksi, $customKode) . "' WHERE id_mapel = $id_mapel");
        }
        return $id_mapel;
    }
    // Kolom NOT NULL wajib diisi: kode,idpk,idguru,nama,jml_soal,jml_esai,
    // tampil_pg,tampil_esai,bobot_pg,bobot_esai,level,opsi,kelas,status
    $lvl = $level !== '' ? $level : '7';
    if ($customKode !== '') {
        $kode = $customKode;
    } else {
        $cleanKode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $nama), 0, 5));
        $kode = $cleanKode . ($lvl !== '' ? $lvl : '');
    }
    $idpk  = 'a:1:{i:0;s:5:"semua";}'; // paket "semua" (compat Candy CBT)
    $idguru = $guru !== '' ? $guru : '0';
    $jml_soal = 0;
    $jml_esai = 0;
    $tampil_pg = 0;
    $tampil_esai = 0;
    $bobot_pg = 0;
    $bobot_esai = 0;
    $opsi = 4; // default 4 opsi; akan disetel dari file bila ada
    $kelas_arr = $kelas !== '' ? $kelas : 'a:1:{i:0;s:5:"semua";}';
    $status = '1';

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO mapel (kode,idpk,idguru,nama,jml_soal,jml_esai,tampil_pg,tampil_esai,bobot_pg,bobot_esai,level,opsi,kelas,status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $bv1 = $kode; $bv2 = $idpk; $bv3 = $idguru; $bv4 = $nama; $bv5 = $jml_soal;
    $bv6 = $jml_esai; $bv7 = $tampil_pg; $bv8 = $tampil_esai; $bv9 = $bobot_pg;
    $bv10 = $bobot_esai; $bv11 = $lvl; $bv12 = $opsi; $bv13 = $kelas_arr; $bv14 = $status;
    mysqli_stmt_bind_param($stmt, 'ssssiiiiii' . 'siss',
        $bv1, $bv2, $bv3, $bv4, $bv5, $bv6, $bv7, $bv8, $bv9, $bv10, $bv11, $bv12, $bv13, $bv14
    );
    mysqli_stmt_execute($stmt);
    $newId = (int) mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);
    return $newId;
}

// =====================================================
// ROUTER: ?action=...
// =====================================================
$action = $_GET['action'] ?? '';

switch ($action) {

    // ---- UPLOAD FILE WORD -> parsing -> insert otomatis ----
    case 'import_word':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            api_fail('Gunakan method POST.', 405);
        }
        if (!isset($_FILES['file'])) {
            api_fail('Tidak ada file terkirim. Field name: "file".', 400);
        }
        $mapelName = $_POST['mapel'] ?? '';
        $levelParam = $_POST['level'] ?? '';
        $kodeParam = $_POST['kode'] ?? '';
        $id_mapel = ensure_mapel($koneksi, $mapelName, '', '', $levelParam, $kodeParam);
        $file = $_FILES['file'];

        // Cek ekstensi .docx (word)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'docx') {
            api_fail('Hanya file .docx yang didukung.', 400);
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            api_fail('Upload gagal: error ' . $file['error'], 400);
        }

        // Salin ke temp, rename jadi .Zip (kompatibel parser Candy)
        $target_dir = $cfg['temp_dir'];
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $zipPath = $target_dir . time() . '_' . mt_rand(1000, 9999) . '.Zip';
        if (!move_uploaded_file($file['tmp_name'], $zipPath)) {
            api_fail('Gagal menyimpan file sementara.', 500);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            api_fail('File bukan arsip Word yang valid (zip).', 400);
        }
        $zip->extractTo($target_dir);
        $zip->close();

        // Baca document.xml
        $docXml = $target_dir . 'word/document.xml';
        if (!file_exists($docXml)) {
            api_fail('Struktur word/document.xml tidak ditemukan.', 400);
        }
        $content = file_get_contents($docXml);
        $content = str_replace(['<w:br/>', '<w:br />', '<w:br>', '<w:tab/>', '<w:tab />'], "\n", $content);
        $content = str_replace('</w:p>', "\n", $content); // pertahankan pemisah paragraf
        $content = htmlentities(strip_tags($content, '<a:blip>'));

        // Ganti blip dengan placeholder [IMG:relId] utk gambar (rel -> nama file)
        $images = extract_and_save_images(new ZipArchive, $target_dir, $cfg);
        foreach ($images as $relId => $newName) {
            $r1 = '&lt;a:blip r:embed=&quot;' . $relId . '&quot; cstate=&quot;print&quot;/&gt;';
            $r2 = '&lt;a:blip r:embed=&quot;' . $relId . '&quot;&gt;&lt;/a:blip&gt;';
            $r3 = '&lt;a:blip r:embed=&quot;' . $relId . '&quot;/&gt;';
            $imgPlace = '[IMG:' . $relId . ']';
            $content = str_replace([$r1, $r2, $r3], $imgPlace, $content);
            $GLOBALS['img_map'][$relId] = $newName; // utk ganti di insert_questions
        }

        $questions = parse_questions($content, $cfg);
        if (count($questions) === 0) {
            api_fail('Tidak ada soal terdeteksi. Cek regex di api_key.php.', 400);
        }

        $inserted = insert_questions($koneksi, $id_mapel, $questions);
        $totalQ = (int) $inserted;
        mysqli_query($koneksi, "UPDATE mapel SET jml_soal = $totalQ, tampil_pg = $totalQ, bobot_pg = 100 WHERE id_mapel = $id_mapel");

        api_json([
            'status' => 'success',
            'message' => "Berhasil import $inserted soal.",
            'mapel' => $mapelName,
            'id_mapel' => $id_mapel,
            'total_deteksi' => count($questions),
            'total_insert' => $inserted,
        ]);

    // ---- INPUT LANGSUNG DATA SOAL VIA JSON ----
    case 'import_json':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            api_fail('Gunakan method POST.', 405);
        }
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!$body || empty($body['questions'])) {
            api_fail('Payload JSON tidak valid atau field "questions" kosong.', 400);
        }
        $mapelName = trim($body['mapel'] ?? '');
        if ($mapelName === '') {
            api_fail('Field "mapel" wajib diisi.', 400);
        }
        $levelParam = trim($body['level'] ?? '');
        $kodeParam = trim($body['kode'] ?? '');
        $id_mapel = ensure_mapel($koneksi, $mapelName, '', '', $levelParam, $kodeParam);

        $questions = [];
        foreach ($body['questions'] as $q) {
            $opts = $q['option'] ?? ($q['options'] ?? []);
            $questions[] = [
                'question' => $q['question'] ?? '',
                'option' => $opts,
                'correct' => strtoupper(trim($q['correct'] ?? ($q['jawaban'] ?? ''))),
                'option_count' => count($opts),
            ];
        }

        $inserted = insert_questions($koneksi, $id_mapel, $questions);
        $totalQ = (int) $inserted;
        mysqli_query($koneksi, "UPDATE mapel SET jml_soal = $totalQ, tampil_pg = $totalQ, bobot_pg = 100 WHERE id_mapel = $id_mapel");

        api_json([
            'status' => 'success',
            'message' => "Berhasil import $inserted soal via JSON.",
            'mapel' => $mapelName,
            'id_mapel' => $id_mapel,
            'total_deteksi' => count($questions),
            'total_insert' => $inserted,
        ]);

    // ---- AKSES SEMUA DATA (read-only) ----
    case 'list_tables':
        global $debe;
        $q = mysqli_query($koneksi, 'SHOW TABLES FROM `' . $debe . '`');
        $tables = [];
        while ($r = mysqli_fetch_array($q)) {
            $tables[] = $r[0];
        }
        api_json(['status' => 'success', 'tables' => $tables]);

    case 'get_soal':
        $id_mapel = isset($_GET['id_mapel']) ? (int) $_GET['id_mapel'] : 0;
        $where = $id_mapel > 0 ? 'WHERE id_mapel = ' . $id_mapel : '';
        $q = mysqli_query($koneksi, "SELECT * FROM soal $where ORDER BY nomor ASC");
        $rows = [];
        while ($r = mysqli_fetch_assoc($q)) {
            $rows[] = $r;
        }
        api_json(['status' => 'success', 'data' => $rows, 'jumlah' => count($rows)]);

    case 'delete_soal':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            api_fail('Gunakan method POST.', 405);
        }
        $id_mapel = isset($_POST['id_mapel']) ? (int) $_POST['id_mapel'] : 0;
        if ($id_mapel <= 0) {
            api_fail('Parameter id_mapel wajib diisi.', 400);
        }
        mysqli_query($koneksi, 'DELETE FROM soal WHERE id_mapel = ' . $id_mapel);
        api_json(['status' => 'success', 'message' => "Soal utk id_mapel $id_mapel telah dihapus."]);

    default:
        api_json([
            'status' => 'success',
            'message' => 'Candy CBT API v2. Aktif.',
            'action' => '?action=import_word | list_tables | get_soal | delete_soal',
            'auth' => 'Header X-API-KEY wajib di semua request.',
            'version' => '2.9.2',
        ]);
}