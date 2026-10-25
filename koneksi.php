<?php
// ==============================================================================
// KONEKSI DATABASE & UTILITIES SISTEM DATA MAHASISWA ADVANCE (FIKOM UDB)
// ==============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "db_mahasiswa_advance";

$koneksi = null;
try {
    $koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    // Upaya inisialisasi database otomatis jika belum ada
    try {
        $test_conn = mysqli_connect($db_host, $db_user, $db_pass);
        $create_db_query = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (mysqli_query($test_conn, $create_db_query)) {
            $koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
            $init_sql = file_exists(__DIR__ . '/database.sql') ? file_get_contents(__DIR__ . '/database.sql') : '';
            if (!empty($init_sql)) {
                mysqli_multi_query($koneksi, $init_sql);
                while (mysqli_next_result($koneksi)) {;}
            }
        }
    } catch (Exception $e2) {
        $koneksi = null;
    }
}

if (!$koneksi) {
    die("
    <div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 24px; border: 1px solid #e6decb; border-radius: 12px; background: #faf5e8; color: #181d26;'>
        <h3 style='color: #b71c1c; margin-top:0;'>Gagal Terhubung ke Database MySQL!</h3>
        <p>Pastikan layanan <strong>MySQL</strong> pada <strong>XAMPP</strong> sudah berjalan.</p>
        <p style='font-family: monospace; font-size: 13px; background: #ffffff; padding: 12px; border-radius: 8px; border: 1px solid #e6decb;'>
            " . htmlspecialchars(mysqli_connect_error()) . "
        </p>
        <p style='color: #706a5e; font-size: 13px;'>Silakan impor file <code>database.sql</code> ke phpMyAdmin.</p>
    </div>
    ");
}

mysqli_set_charset($koneksi, "utf8mb4");

// -------------------------------------------------------------
// HELPER CSRF PROTECTION
// -------------------------------------------------------------
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die("Validasi Keamanan Gagal: Token CSRF Tidak Valid atau Telah Kedaluwarsa.");
    }
}

// -------------------------------------------------------------
// HELPER FLASH SESSION
// -------------------------------------------------------------
function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success' | 'error' | 'info'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// -------------------------------------------------------------
// HELPER AUDIT LOG (Pencatatan Riwayat Aktivitas Otomatis)
// -------------------------------------------------------------
function write_audit_log($koneksi, $user_id, $action, $table_name, $record_id, $description) {
    $stmt = $koneksi->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, description) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $action, $table_name, $record_id, $description);
        $stmt->execute();
        $stmt->close();
    }
}

// -------------------------------------------------------------
// HELPER EDITORIAL AVATAR SVG (Zero Broken Image)
// -------------------------------------------------------------
function generate_avatar_svg($name, $gender = 'Laki-laki', $nim = '') {
    $clean_name = trim($name ?: 'M');
    $parts = preg_split('/\s+/', $clean_name);
    $initials = '';
    for ($i = 0; $i < min(2, count($parts)); $i++) {
        $initials .= strtoupper(mb_substr($parts[$i], 0, 1));
    }

    $is_female = ($gender === 'Perempuan' || $gender === 'P');
    $bg_tone = $is_female ? '#f4ede0' : '#e6decb';
    $jacket_tone = '#181d26';
    $collar_tone = '#ffffff';
    $skin_tone = $is_female ? '#e8cfc1' : '#dfc3b4';
    $hair_tone = '#181d26';
    $tag = $nim ?: $initials;

    $female_hair_back = $is_female ? '<path d="M35 52 C35 30, 85 30, 85 52 L88 82 L32 82 Z" fill="' . $hair_tone . '" />' : '';
    $hair_top = $is_female 
        ? '<path d="M38 48 C38 28, 82 28, 82 48 C74 39, 52 38, 38 48 Z" fill="' . $hair_tone . '" />'
        : '<path d="M38 47 C38 27, 82 27, 82 47 C76 38, 45 38, 38 47 Z" fill="' . $hair_tone . '" />';

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="120" height="120">
        <rect width="120" height="120" fill="' . $bg_tone . '" />
        <path d="M20 120 C20 88, 40 82, 60 82 C80 82, 100 88, 100 120 Z" fill="' . $jacket_tone . '" />
        <polygon points="48,82 60,102 72,82" fill="' . $collar_tone . '" />
        <rect x="52" y="66" width="16" height="20" rx="6" fill="' . $skin_tone . '" />
        ' . $female_hair_back . '
        <rect x="39" y="32" width="42" height="46" rx="21" fill="' . $skin_tone . '" />
        ' . $hair_top . '
        <circle cx="51" cy="53" r="2.2" fill="#181d26" />
        <circle cx="69" cy="53" r="2.2" fill="#181d26" />
        <path d="M55 64 Q60 67 65 64" stroke="#181d26" stroke-width="1.8" fill="none" stroke-linecap="round" />
        <circle cx="16" cy="16" r="4" fill="#fe551b" />
        <text x="108" y="18" text-anchor="end" font-family="monospace" font-weight="700" font-size="10" fill="#706a5e">#' . htmlspecialchars($tag) . '</text>
    </svg>';

    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}

function get_mhs_avatar($nama, $gender, $nim, $foto = null) {
    if (!empty($foto)) {
        $file_path = __DIR__ . '/uploads/foto/' . $foto;
        if (file_exists($file_path)) {
            return 'uploads/foto/' . rawurlencode($foto);
        }
    }
    return generate_avatar_svg($nama, $gender, $nim);
}

