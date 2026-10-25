<?php
// ==============================================================================
// CETAK KARTU TANDA MAHASISWA (KTM) DENGAN QR CODE (MILESTONE 4)
// ==============================================================================

require_once __DIR__ . '/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID Mahasiswa tidak valid.");
}

$stmt = $koneksi->prepare("SELECT m.*, p.nama_prodi, p.fakultas 
                           FROM mahasiswa m 
                           JOIN prodi p ON m.id_prodi = p.id 
                           WHERE m.id = ? AND m.is_deleted = 0 
                           LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$mhs = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mhs) {
    die("Data mahasiswa tidak ditemukan atau telah dihapus.");
}

$avatar_src = get_mhs_avatar($mhs['nama'], $mhs['jenis_kelamin'], $mhs['nim'], $mhs['foto']);
$valid_until = ((int)$mhs['angkatan'] + 4);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KTM — <?= htmlspecialchars($mhs['nama']) ?> (<?= htmlspecialchars($mhs['nim']) ?>)</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
  
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- QRCode.js CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f4ede0;
      color: #181d26;
    }
    .font-mono-tech {
      font-family: 'JetBrains Mono', monospace;
    }
    @media print {
      body {
        background-color: #ffffff;
        padding: 0;
      }
      .no-print {
        display: none !important;
      }
      .ktm-container {
        box-shadow: none !important;
        border: 1px solid #000000 !important;
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body class="min-h-screen p-4 sm:p-8 flex flex-col items-center justify-center">

  <!-- Action Bar (Hidden when printed) -->
  <div class="no-print w-full max-w-xl mb-6 flex items-center justify-between">
    <a href="index.php" class="px-3 py-1.5 rounded-md bg-[#faf5e8] border border-[#e6decb] text-xs font-semibold hover:bg-[#e6decb] transition inline-flex items-center gap-1.5">
      &larr; Kembali ke Tabel
    </a>
    
    <div class="flex items-center gap-2">
      <button onclick="window.print()" class="px-4 py-1.5 rounded-md bg-[#181d26] text-[#faf5e8] text-xs font-semibold hover:bg-[#262d3a] transition inline-flex items-center gap-1.5 shadow-sm">
        <i data-lucide="printer" class="w-4 h-4"></i>
        <span>Cetak Kartu (Print)</span>
      </button>
    </div>
  </div>

  <div class="space-y-6 w-full max-w-xl">
    
    <!-- 1. KARTU SISI DEPAN (FRONT SIDE) -->
    <div class="ktm-container relative w-full h-[320px] rounded-2xl bg-[#faf5e8] border-2 border-[#181d26] p-5 shadow-xl overflow-hidden flex flex-col justify-between">
      
      <!-- Watermark Background Logo Pattern -->
      <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none select-none text-[#181d26] font-extrabold text-[180px]">
        UDB
      </div>

      <!-- Top Header -->
      <div class="flex items-center justify-between border-b-2 border-[#181d26] pb-3">
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-lg bg-[#181d26] text-[#faf5e8] flex items-center justify-center font-extrabold text-base tracking-tighter">
            UDB
          </div>
          <div>
            <h1 class="font-extrabold text-sm uppercase tracking-tight text-[#181d26] leading-none">
              Universitas Duta Bangsa
            </h1>
            <p class="text-[11px] font-semibold text-[#fe551b] tracking-wider uppercase mt-0.5">
              Fakultas Ilmu Komputer (FIKOM)
            </p>
          </div>
        </div>

        <div class="text-right">
          <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#181d26] text-[#faf5e8] font-mono-tech text-[10px] font-bold uppercase tracking-wider">
            KTM DIGITAL
          </span>
        </div>
      </div>

      <!-- Body: Foto + Identitas Mahasiswa -->
      <div class="flex items-start gap-4 my-auto pt-2">
        <!-- Foto 3x4 Proporsional -->
        <div class="w-24 h-32 rounded-lg border-2 border-[#181d26] overflow-hidden bg-[#f4ede0] shadow-xs shrink-0 flex items-center justify-center">
          <img src="<?= $avatar_src ?>" alt="<?= htmlspecialchars($mhs['nama']) ?>" class="w-full h-full object-cover">
        </div>

        <!-- Detail Biodata -->
        <div class="flex-1 min-w-0 space-y-1">
          <div>
            <span class="text-[10px] font-bold uppercase text-[#706a5e] tracking-wider block">Nama Mahasiswa</span>
            <span class="font-extrabold text-base text-[#181d26] truncate block leading-tight">
              <?= htmlspecialchars($mhs['nama']) ?>
            </span>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
            <div>
              <span class="text-[10px] font-bold uppercase text-[#706a5e] block">NIM</span>
              <span class="font-mono-tech font-bold text-[#181d26]">
                <?= htmlspecialchars($mhs['nim']) ?>
              </span>
            </div>

            <div>
              <span class="text-[10px] font-bold uppercase text-[#706a5e] block">Angkatan</span>
              <span class="font-mono-tech font-bold text-[#181d26]">
                <?= htmlspecialchars($mhs['angkatan']) ?>
              </span>
            </div>
          </div>

          <div class="pt-1 text-xs">
            <span class="text-[10px] font-bold uppercase text-[#706a5e] block">Program Studi</span>
            <span class="font-bold text-[#181d26] block">
              <?= htmlspecialchars($mhs['nama_prodi']) ?>
            </span>
          </div>
        </div>

        <!-- QR Code Mahasiswa (Milestone 4) -->
        <div class="flex flex-col items-center justify-center shrink-0">
          <div id="qrcode" class="p-1.5 bg-white border border-[#181d26] rounded-lg shadow-xs"></div>
          <span class="text-[9px] font-mono-tech text-[#706a5e] mt-1 font-bold">VERIFIKASI ID</span>
        </div>
      </div>

      <!-- Bottom Strip -->
      <div class="border-t border-[#e6decb] pt-2 flex items-center justify-between text-[10px] font-semibold text-[#706a5e]">
        <span>Status: <strong class="text-[#181d26]"><?= htmlspecialchars($mhs['status_akademik']) ?></strong></span>
        <span>Berlaku s/d: <strong class="font-mono-tech text-[#181d26]">31 Agu <?= $valid_until ?></strong></span>
      </div>
    </div>

    <!-- 2. KARTU SISI BELAKANG (BACK SIDE) -->
    <div class="ktm-container relative w-full h-[320px] rounded-2xl bg-[#faf5e8] border-2 border-[#181d26] p-5 shadow-xl flex flex-col justify-between">
      <div class="border-b border-[#e6decb] pb-2">
        <h2 class="font-bold text-xs uppercase tracking-wider text-[#181d26]">Ketentuan Penggunaan KTM</h2>
      </div>

      <div class="text-[11px] text-[#524d43] space-y-1.5 leading-relaxed">
        <p>1. Kartu Tanda Mahasiswa ini merupakan tanda pengenal resmi mahasiswa aktif Universitas Duta Bangsa.</p>
        <p>2. Digunakan untuk keperluan administrasi akademik, akses perpustakaan, praktikum laboratorium, dan presensi kuliah.</p>
        <p>3. Dilarang meminjamkan atau menyalahgunakan kartu ini kepada pihak lain.</p>
        <p>4. Jika kartu ini hilang atau ditemukan, silakan mengembalikan ke Bagian Administrasi Akademik & Kemahasiswaan (BAAK) FIKOM UDB.</p>
      </div>

      <div class="border-t-2 border-[#181d26] pt-3 flex items-end justify-between">
        <div class="text-[10px] text-[#706a5e]">
          <p class="font-bold text-[#181d26]">Kampus Pusat UDB Surakarta</p>
          <p>Jl. Bhayangkara No. 55, Serengan, Surakarta</p>
          <p class="font-mono-tech">www.udb.ac.id · fikom@udb.ac.id</p>
        </div>

        <div class="text-center text-[10px]">
          <p class="text-[#706a5e] mb-6">Dekan FIKOM UDB,</p>
          <p class="font-bold text-[#181d26] underline">Dr. Muhtarom, S.E, S.Kom, M.Kom</p>
          <p class="font-mono-tech text-[#706a5e]">NIDN. 0628047701</p>
        </div>
      </div>
    </div>

  </div>

  <script>
    // Milestone 4: Inisialisasi QR Code Resmi Mahasiswa
    document.addEventListener('DOMContentLoaded', () => {
      const qrData = JSON.stringify({
        instansi: "FIKOM UDB",
        nim: "<?= addslashes($mhs['nim']) ?>",
        nama: "<?= addslashes($mhs['nama']) ?>",
        prodi: "<?= addslashes($mhs['nama_prodi']) ?>",
        status: "<?= addslashes($mhs['status_akademik']) ?>"
      });

      new QRCode(document.getElementById("qrcode"), {
        text: qrData,
        width: 68,
        height: 68,
        colorDark: "#181d26",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
      });

      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
  </script>
</body>
</html>

