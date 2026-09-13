<?php
// =====================================================
// API KEY / TOKEN RAHASIA - JANGAN DIUBAH TANPA PERLU
// Simpan file ini di luar direktori publik bila memungkinkan.
// =====================================================

return [
    // GANTI nilai di bawah ini dengan token rahasia pribadimu.
    // Cara pakai: header "X-API-KEY: <nilai di bawah ini>"
    'api_key' => 'zEa-2026-CBT-LOCAL-v2-secret-9f3a1c',

    // Pembatas (delimiter) soal & opsi di file Word
    // Default kompatibel dengan Candy CBT (regex baku)
    'question_split' => '/^\*\s*1\./mi',   //  *1. sebagai pembatas nomor soal
    'option_split'   => '/^\*\s*[A-E]\./mi', // *A. dst. sebagai pembatas opsi
    'correct_split'  => '/\*\*\s*([A-E])\.?\s*/mi', // **X untuk jawaban benar
    'description_split' => '/^\*\s*4\./mi', //  *4. (opsional, untuk deskripsi)

    // Lokasi penyimpanan file pendukung (gambar) hasil ekstrak
    'upload_dir' => __DIR__ . '/../files/',
    'temp_dir'   => __DIR__ . '/../temp/word/',
];