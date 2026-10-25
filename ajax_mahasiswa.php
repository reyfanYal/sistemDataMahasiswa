<?php
// ==============================================================================
// AJAX ENDPOINT MAHASISWA: LIVE SEARCH, CROSS-FILTER, & PAGINATION
// ==============================================================================

require_once __DIR__ . '/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Ambil Parameter Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$id_prodi = isset($_GET['id_prodi']) && $_GET['id_prodi'] !== '' ? (int)$_GET['id_prodi'] : 0;
$status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

// 2. Bangun Query Dinamis Menggunakan Prepared Statement
$conditions = ["m.is_deleted = 0"];
$params = [];
$types = "";

if ($search !== '') {
    $conditions[] = "(m.nama LIKE ? OR m.nim LIKE ? OR m.email LIKE ? OR m.alamat LIKE ? OR m.no_hp LIKE ?)";
    $like = "%" . $search . "%";
    for ($i = 0; $i < 5; $i++) {
        $params[] = $like;
        $types .= "s";
    }
}

if ($id_prodi > 0) {
    $conditions[] = "m.id_prodi = ?";
    $params[] = $id_prodi;
    $types .= "i";
}

if ($status !== '' && in_array($status, ['Aktif', 'Cuti', 'Lulus', 'DO'])) {
    $conditions[] = "m.status_akademik = ?";
    $params[] = $status;
    $types .= "s";
}

$where_clause = implode(" AND ", $conditions);

// 3. Hitung Total Data Sesuai Filter
$count_sql = "SELECT COUNT(*) as total FROM mahasiswa m WHERE {$where_clause}";
$stmt_count = $koneksi->prepare($count_sql);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_records = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_records / $limit);

// 4. Ambil Data Mahasiswa Halaman Ini
$data_sql = "SELECT m.*, p.nama_prodi, p.fakultas 
             FROM mahasiswa m 
             JOIN prodi p ON m.id_prodi = p.id 
             WHERE {$where_clause} 
             ORDER BY m.id DESC 
             LIMIT ? OFFSET ?";

$data_types = $types . "ii";
$data_params = array_merge($params, [$limit, $offset]);

$stmt_data = $koneksi->prepare($data_sql);
$stmt_data->bind_param($data_types, ...$data_params);
$stmt_data->execute();
$res = $stmt_data->get_result();

$mahasiswa = [];
while ($row = $res->fetch_assoc()) {
    $row['avatar_url'] = get_mhs_avatar($row['nama'], $row['jenis_kelamin'], $row['nim'], $row['foto']);
    $row['dokumen_url'] = (!empty($row['dokumen']) && file_exists(__DIR__ . '/uploads/dokumen/' . $row['dokumen'])) 
        ? 'uploads/dokumen/' . rawurlencode($row['dokumen']) 
        : null;
    $mahasiswa[] = $row;
}
$stmt_data->close();

// 5. Respons JSON Terstruktur
echo json_encode([
    'status' => 'success',
    'data' => $mahasiswa,
    'pagination' => [
        'total_records' => (int)$total_records,
        'total_pages' => (int)$total_pages,
        'current_page' => (int)$page,
        'limit' => (int)$limit,
        'offset' => (int)$offset,
        'showing_from' => $total_records > 0 ? $offset + 1 : 0,
        'showing_to' => min($offset + $limit, $total_records),
    ],
    'csrf_token' => generate_csrf_token()
], JSON_UNESCAPED_UNICODE);
exit;

