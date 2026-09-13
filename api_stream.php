<?php
require("config/config.default.php");
require("config/config.function.php");
require("config/functions.crud.php");

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id_siswa = isset($_POST['id_siswa']) ? intval($_POST['id_siswa']) : (isset($_GET['id_siswa']) ? intval($_GET['id_siswa']) : 0);
$id_ujian = isset($_POST['id_ujian']) ? intval($_POST['id_ujian']) : (isset($_GET['id_ujian']) ? intval($_GET['id_ujian']) : 0);

if ($action == 'start_watch') {
    if (!$id_siswa) {
        echo json_encode(['status' => 'error', 'message' => 'id_siswa required']);
        exit;
    }
    $now = date('Y-m-d H:i:s');
    $cek = mysqli_query($koneksi, "SELECT id_siswa FROM stream_signal WHERE id_siswa='$id_siswa'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE stream_signal SET id_ujian='$id_ujian', status='requested', offer=NULL, answer=NULL, updated_at='$now' WHERE id_siswa='$id_siswa'");
    } else {
        mysqli_query($koneksi, "INSERT INTO stream_signal (id_siswa, id_ujian, status, updated_at) VALUES ('$id_siswa', '$id_ujian', 'requested', '$now')");
    }
    echo json_encode(['status' => 'ok', 'message' => 'Live watch started']);
    exit;
}

if ($action == 'stop_watch') {
    if (!$id_siswa) {
        echo json_encode(['status' => 'error']);
        exit;
    }
    $now = date('Y-m-d H:i:s');
    mysqli_query($koneksi, "UPDATE stream_signal SET status='idle', offer=NULL, answer=NULL, updated_at='$now' WHERE id_siswa='$id_siswa'");
    echo json_encode(['status' => 'ok', 'message' => 'Live watch stopped']);
    exit;
}

if ($action == 'check_signal') {
    if (!$id_siswa) {
        echo json_encode(['status' => 'idle']);
        exit;
    }
    $q = mysqli_query($koneksi, "SELECT status, offer, updated_at FROM stream_signal WHERE id_siswa='$id_siswa'");
    if ($row = mysqli_fetch_assoc($q)) {
        // Jika status requested lebih dari 15 detik tanpa update dari admin, anggap timeout
        $diff = time() - strtotime($row['updated_at']);
        if ($diff > 30) {
            mysqli_query($koneksi, "UPDATE stream_signal SET status='idle' WHERE id_siswa='$id_siswa'");
            echo json_encode(['status' => 'idle']);
            exit;
        }
        echo json_encode([
            'status' => $row['status'],
            'has_offer' => !empty($row['offer'])
        ]);
    } else {
        echo json_encode(['status' => 'idle']);
    }
    exit;
}

if ($action == 'send_offer') {
    $offer = isset($_POST['offer']) ? mysqli_real_escape_string($koneksi, $_POST['offer']) : '';
    $now = date('Y-m-d H:i:s');
    mysqli_query($koneksi, "UPDATE stream_signal SET offer='$offer', updated_at='$now' WHERE id_siswa='$id_siswa'");
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($action == 'get_offer') {
    $q = mysqli_query($koneksi, "SELECT offer FROM stream_signal WHERE id_siswa='$id_siswa'");
    $row = mysqli_fetch_assoc($q);
    echo json_encode(['status' => 'ok', 'offer' => ($row ? $row['offer'] : null)]);
    exit;
}

if ($action == 'send_answer') {
    $answer = isset($_POST['answer']) ? mysqli_real_escape_string($koneksi, $_POST['answer']) : '';
    $now = date('Y-m-d H:i:s');
    mysqli_query($koneksi, "UPDATE stream_signal SET answer='$answer', status='streaming', updated_at='$now' WHERE id_siswa='$id_siswa'");
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($action == 'get_answer') {
    $q = mysqli_query($koneksi, "SELECT answer, status FROM stream_signal WHERE id_siswa='$id_siswa'");
    $row = mysqli_fetch_assoc($q);
    echo json_encode([
        'status' => 'ok',
        'answer' => ($row ? $row['answer'] : null),
        'stream_status' => ($row ? $row['status'] : 'idle')
    ]);
    exit;
}

if ($action == 'push_frame') {
    $imgData = isset($_POST['foto']) ? $_POST['foto'] : '';
    if (!$id_siswa || empty($imgData)) {
        echo json_encode(['status' => 'error', 'message' => 'Empty payload']);
        exit;
    }

    if (preg_match('/^data:image\/(\w+);base64,/', $imgData)) {
        $imgData = substr($imgData, strpos($imgData, ',') + 1);
    }
    $imgDecoded = base64_decode($imgData);
    if ($imgDecoded === false) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $dir = __DIR__ . '/files/kamera_live/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $filename = 'live_' . $id_siswa . '.jpg';
    file_put_contents($dir . $filename, $imgDecoded);

    $now = date('Y-m-d H:i:s');
    mysqli_query($koneksi, "UPDATE stream_signal SET live_frame='$filename', updated_at='$now' WHERE id_siswa='$id_siswa'");

    echo json_encode(['status' => 'ok', 'time' => $now]);
    exit;
}

if ($action == 'get_frame') {
    $q = mysqli_query($koneksi, "SELECT live_frame, updated_at FROM stream_signal WHERE id_siswa='$id_siswa'");
    $row = mysqli_fetch_assoc($q);
    if ($row && !empty($row['live_frame'])) {
        $diff = time() - strtotime($row['updated_at']);
        $frameUrl = $homeurl . '/files/kamera_live/' . $row['live_frame'] . '?t=' . round(microtime(true) * 1000);
        echo json_encode([
            'status'    => 'ok',
            'frame'     => $frameUrl,
            'waktu'     => $row['updated_at'],
            'is_online' => ($diff <= 10)
        ]);
    } else {
        echo json_encode(['status' => 'empty', 'is_online' => false]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
