<?php
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// 1. Verifikasi Keamanan CSRF Token
verify_csrf_token($_POST['csrf_token'] ?? '');

// 2. Sanitasi & Pengambilan Input
$nim = trim($_POST['nim'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$id_prodi = (int)($_POST['id_prodi'] ?? 0);
$jenis_kelamin = $_POST['jenis_kelamin'] ?? 'Laki-laki';
$angkatan = (int)($_POST['angkatan'] ?? date('Y'));
$status_akademik = $_POST['status_akademik'] ?? 'Aktif';
$dpa = trim($_POST['dpa'] ?? 'Dr. Muhtarom, M.Kom');
$email = trim($_POST['email'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

$lat_raw = trim($_POST['latitude'] ?? '');
$lng_raw = trim($_POST['longitude'] ?? '');
$latitude = ($lat_raw !== '') ? (float)$lat_raw : null;
$longitude = ($lng_raw !== '') ? (float)$lng_raw : null;

// 3. Validasi Data
if (empty($nim) || empty($nama) || empty($id_prodi) || empty($email) || empty($no_hp) || empty($alamat)) {
    set_flash('error', 'Semua kolom yang bertanda bintang (*) wajib diisi.');
    header("Location: tambah.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Format alamat email tidak valid.');
    header("Location: tambah.php");
    exit;
}

// Cek keunikan NIM di database menggunakan Prepared Statement
$stmt_check = $koneksi->prepare("SELECT id, nama FROM mahasiswa WHERE LOWER(nim) = LOWER(?) LIMIT 1");
$stmt_check->bind_param("s", $nim);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($dup = $res_check->fetch_assoc()) {
    set_flash('error', "NIM {$nim} sudah terdaftar atas nama " . htmlspecialchars($dup['nama']) . ".");
    $stmt_check->close();
    header("Location: tambah.php");
    exit;
}
$stmt_check->close();

// 4. Penanganan Upload File 1: Pasfoto (JPG/PNG maks 2MB)
$foto_name = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png'];
    $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowed_mimes) || !in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
        set_flash('error', 'Format foto harus berupa JPG atau PNG yang valid.');
        header("Location: tambah.php");
        exit;
    }

    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        set_flash('error', 'Ukuran file foto melebihi batas maksimal 2 MB.');
        header("Location: tambah.php");
        exit;
    }

    $upload_foto_dir = __DIR__ . '/uploads/foto/';
    if (!is_dir($upload_foto_dir)) {
        mkdir($upload_foto_dir, 0755, true);
    }

    $safe_foto = 'foto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_foto_dir . $safe_foto)) {
        $foto_name = $safe_foto;
    }
}

// 5. Penanganan Upload File 2: Dokumen PDF (Maks 5MB)
$dokumen_name = null;
if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['dokumen']['tmp_name']);
    finfo_close($finfo);

    $file_ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));

    if ($mime !== 'application/pdf' || $file_ext !== 'pdf') {
        set_flash('error', 'Format dokumen harus berupa berkas PDF.');
        header("Location: tambah.php");
        exit;
    }

    if ($_FILES['dokumen']['size'] > 5 * 1024 * 1024) {
        set_flash('error', 'Ukuran file dokumen PDF melebihi batas maksimal 5 MB.');
        header("Location: tambah.php");
        exit;
    }

    $upload_doc_dir = __DIR__ . '/uploads/dokumen/';
    if (!is_dir($upload_doc_dir)) {
        mkdir($upload_doc_dir, 0755, true);
    }

    $safe_doc = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
    if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $upload_doc_dir . $safe_doc)) {
        $dokumen_name = $safe_doc;
    }
}

// 6. Eksekusi INSERT Menggunakan MySQLi Prepared Statement
$insert_sql = "INSERT INTO mahasiswa (
    nim, nama, id_prodi, jenis_kelamin, angkatan, status_akademik, dpa, email, no_hp, alamat, latitude, longitude, foto, dokumen
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $koneksi->prepare($insert_sql);
$stmt->bind_param(
    "ssisisssssddss",
    $nim,
    $nama,
    $id_prodi,
    $jenis_kelamin,
    $angkatan,
    $status_akademik,
    $dpa,
    $email,
    $no_hp,
    $alamat,
    $latitude,
    $longitude,
    $foto_name,
    $dokumen_name
);

if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    // 7. Catat Riwayat Aktivitas ke audit_logs
    write_audit_log(
        $koneksi,
        1, // user_id admin
        'INSERT',
        'mahasiswa',
        $new_id,
        "Menambahkan data mahasiswa baru {$nama} (NIM: {$nim}) dengan Program Studi ID {$id_prodi}"
    );

    set_flash('success', "Mahasiswa baru {$nama} (NIM {$nim}) berhasil didaftarkan ke sistem.");
    $stmt->close();
    header("Location: index.php");
    exit;
} else {
    set_flash('error', "Gagal menyimpan ke database: " . $stmt->error);
    $stmt->close();
    header("Location: tambah.php");
    exit;
}

