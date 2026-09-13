# AGENTS.md - Candy CBT Architecture & Agent Guidance

> **IMPORTANT FOR AI AGENTS**: Read this file before scanning the codebase. Do NOT perform full recursive searches across this repository to conserve token usage. All essential architectural details, routing logic, database configurations, and directory maps are documented below.

---

## 1. Environment & Stack Specifications

- **Application**: X-Candy CBT (v2.9.2 r1 Raya) - Computer-Based Testing System.
- **Backend**: Native Procedural PHP (Version strictly **PHP 7.4.x**, x64 Thread Safe).
- **Web Server**: Apache 2.4 (Laragon on Windows / Apache on Linux cPanel).
- **Database**: MySQL 8.0 / MariaDB (MySQLi extension, procedural driver).
- **Frontend**: AdminLTE 2.4, Bootstrap 3.3.7, jQuery 2.2.3 / 3.2.1, iziToast, SweetAlert2, DataTables.

---

## 2. Token Saver: Directories to IGNORE / DO NOT SCAN

Unless explicitly asked to debug frontend styles or media assets, **DO NOT scan or grep recursively** through:
- `/dist/` - Compiled CSS, fonts, admin theme assets, images.
- `/plugins/` - Vendor libraries (Bootstrap, DataTables, MathJax, TinyMCE, jQuery, SweetAlert2, Toastr, etc.).
- `/vendor/` - Composer vendor dependencies.
- `/files/`, `/berkas/`, `/foto/`, `/temp/`, `/tugas/` - Uploaded exam files, audio attachments, teacher photos, temp files.
- `*.zip`, `*.apk` - Backup archives and Android browser packages.

---

## 3. Directory Map & Key Files

```
C:\laragon\www\test\
├── config/                  # Core system configuration & database drivers
│   ├── config.default.php   # Dynamic URL ($homeurl) & URI Segment Router ($pg, $ac, $id)
│   ├── config.database.php  # Auto-detection environment for MySQL (Localhost vs Hosting)
│   ├── config.candy.php     # Version, app name, constants (KEY, BASEPATH)
│   ├── config.function.php  # Common helper functions (jump(), enkripsi(), date helpers)
│   └── functions.crud.php   # Database CRUD helper wrappers (fetch(), insert(), update(), delete())
│
├── [Root Files]             # STUDENT (Peserta Ujian) Portal
│   ├── index.php            # Main student dashboard & route controller ($pg)
│   ├── login.php            # Student login page
│   ├── ceklogin.php         # Student authentication handler (queries table `siswa`)
│   ├── jadwal.php           # Exam schedule listing
│   ├── aturan.php           # Exam rules / guidelines
│   ├── konfirmasi.php       # Token confirmation before starting exam
│   ├── soal.php             # Core exam engine (questions, choices, timers, audio)
│   ├── cekkonfirmasi.php    # Exam token validator
│   ├── simpantugas.php      # Answer submission handler
│   └── selesai.php          # Final exam submission
│
└── panel/                   # ADMIN & TEACHER (Pengawas) Portal
    ├── index.php            # Admin dashboard & view controller ($pg, $ac)
    ├── login.php            # Admin login interface (AJAX-based, expects "ok", "nopass", "td")
    ├── ceklogin.php         # Admin authentication handler (table `pengawas`, bcrypt password_verify)
    ├── content.php          # Module router & page loader for admin
    ├── home.php             # Admin home dashboard widgets & summary
    └── mod_.../             # Feature Modules:
        ├── mod_banksoal/    # Question bank management (soal, opsi, import Excel/Word)
        ├── mod_jadwal/      # Exam scheduling & token generation
        ├── mod_siswa/       # Student management & import
        ├── mod_guru/        # Teacher management
        ├── mod_user/        # Admin & proctor user management
        ├── mod_nilai/       # Exam scoring, analysis & export Excel
        ├── mod_setting/     # School info, logos, server sync settings
        ├── mod_absen/       # Student attendance tracking
        ├── mod_kartu/       # Exam participant card printing
        └── mod_status/      # Live student status during active exam
```

---

## 4. Critical Architecture & Coding Rules

### 1. File Encoding: STRICTLY UTF-8 WITHOUT BOM
- **CRITICAL**: Never save any `.php` file with a UTF-8 BOM (`\xEF\xBB\xBF`).
- If a BOM is present in `config.default.php` or `config.database.php`, it outputs 3 invisible bytes before HTTP headers, which breaks:
  - `header("Location: ...")` redirects
  - AJAX responses (`data == "ok"` in `login.php` evaluates to `false`).

### 2. Environment Auto-Detection Logic
- `config/config.default.php` dynamically computes:
  - `$homeurl`: Detects protocol (`http://` vs `https://`), host (`localhost`, `127.0.0.1`, `test.test`, or domain), and subfolder (`/test`).
  - `$pg`, `$ac`, `$id`: Strips subfolder prefix and splits remaining path by `/`.
- `config/config.database.php` detects environment via `$_SERVER['HTTP_HOST']`:
  - **Local (Laragon)**: Host: `localhost`, User: `root`, Password: `""`, DB: `test`.
  - **Hosting (Production)**: In the `else` block (preserves cPanel credentials).
  - *Do not hardcode single credentials; always maintain the auto-detection block.*

### 3. Authentication & Passwords
- **Admins & Teachers**: Stored in table `pengawas`.
  - Passwords MUST be hashed with `password_hash($password, PASSWORD_DEFAULT)` (Bcrypt).
  - Verified using `password_verify($input_password, $user['password'])`.
- **Students**: Stored in table `siswa`.
  - Authentication checks `username` (or NIS) and `password` directly.

### 4. Database Queries
- Procedural `mysqli` connection instance variable is `$koneksi`.
- CRUD helper functions from `functions.crud.php` can be used:
  - `fetch($koneksi, $table, $where_array)`
  - `insert($koneksi, $table, $data_array)`
  - `update($koneksi, $table, $data_array, $where_array)`
  - `delete($koneksi, $table, $where_array)`