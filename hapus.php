<?php
require_once __DIR__ . '/koneksi.php';

// 1. Verifikasi Keamanan CSRF Token
$token = $_GET['csrf_token'] ?? '';
verify_csrf_token($token);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 2. Periksa apakah data mahasiswa ada dan belum di-soft-delete
    $stmt_find = $koneksi->prepare("SELECT id, nama, nim FROM mahasiswa WHERE id = ? AND is_deleted = 0 LIMIT 1");
    $stmt_find->bind_param("i", $id);
    $stmt_find->execute();
    $res = $stmt_find->get_result();
    $mhs = $res->fetch_assoc();
    $stmt_find->close();

    if ($mhs) {
        $nama = $mhs['nama'];
        $nim = $mhs['nim'];

        // 3. Eksekusi SOFT DELETE: Update is_deleted = 1 dan catat deleted_at
        $stmt_del = $koneksi->prepare("UPDATE mahasiswa SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
        $stmt_del->bind_param("i", $id);

        if ($stmt_del->execute()) {
            // 4. Catat ke tabel audit_logs
            write_audit_log(
                $koneksi,
                1, // user_id admin
                'SOFT_DELETE',
                'mahasiswa',
                $id,
                "Melakukan penghapusan lunak (soft delete) pada mahasiswa {$nama} (NIM: {$nim})"
            );

            set_flash('success', "Mahasiswa {$nama} (NIM {$nim}) berhasil dinonaktifkan (Soft Delete) dari tabel aktif.");
        } else {
            set_flash('error', "Gagal melakukan penghapusan data: " . $stmt_del->error);
        }
        $stmt_del->close();
    } else {
        set_flash('error', "Data mahasiswa dengan ID #{$id} tidak ditemukan atau sudah dihapus.");
    }
} else {
    set_flash('error', "ID Mahasiswa tidak valid.");
}

header("Location: index.php");
exit;

