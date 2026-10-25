<?php
$page_title = 'Edit Mahasiswa';
require_once __DIR__ . '/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'ID Mahasiswa tidak valid.');
    header("Location: index.php");
    exit;
}

// Ambil data mahasiswa dengan Prepared Statement
$stmt = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id = ? AND is_deleted = 0 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$mhs = $res->fetch_assoc();
$stmt->close();

if (!$mhs) {
    set_flash('error', 'Data mahasiswa tidak ditemukan atau telah dihapus.');
    header("Location: index.php");
    exit;
}

// Ambil opsi Program Studi
$q_prodi = $koneksi->query("SELECT * FROM prodi ORDER BY nama_prodi ASC");
?>

<div class="max-w-3xl mx-auto space-y-6">
  <!-- Breadcrumb & Title -->
  <div class="flex items-center justify-between">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-onyx text-parchment">
          UPDATE RECORD
        </span>
        <span class="text-xs font-mono text-[#706a5e]">Milestone 2 · ID #<?= $id ?></span>
      </div>
      <h1 class="text-2xl font-bold tracking-tight">Formulir Perbaruan Biodata</h1>
      <p class="text-sm text-[#706a5e]">Memperbarui data akademik untuk <strong class="text-onyx"><?= htmlspecialchars($mhs['nama']) ?></strong> (NIM: <?= htmlspecialchars($mhs['nim']) ?>).</p>
    </div>
    
    <a href="index.php" class="text-xs font-semibold px-3 py-1.5 rounded-md border border-borderTone hover:bg-cream transition">
      &larr; Kembali ke Tabel
    </a>
  </div>

  <!-- Form Container -->
  <form action="proses_edit.php" method="POST" enctype="multipart/form-data" novalidate class="bg-parchment border border-borderTone rounded-xl p-6 sm:p-8 space-y-6 shadow-xs">
    
    <!-- CSRF Token & Record ID -->
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $mhs['id'] ?>">

    <!-- Section 1: Data Identitas & Akademik -->
    <div class="space-y-4">
      <h2 class="text-xs font-bold uppercase tracking-wider text-[#706a5e] border-b border-borderTone pb-1.5">
        1. Identitas & Akademik
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
          <label for="input-nim" class="block font-semibold mb-1 text-xs text-onyx">
            NIM (Nomor Induk Mahasiswa) <span class="text-[#fe551b]">*</span>
          </label>
          <input
            type="text"
            id="input-nim"
            name="nim"
            value="<?= htmlspecialchars($mhs['nim']) ?>"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx font-mono text-sm"
          />
        </div>

        <div>
          <label for="input-nama" class="block font-semibold mb-1 text-xs text-onyx">
            Nama Lengkap <span class="text-[#fe551b]">*</span>
          </label>
          <input
            type="text"
            id="input-nama"
            name="nama"
            value="<?= htmlspecialchars($mhs['nama']) ?>"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          />
        </div>

        <div>
          <label for="input-prodi" class="block font-semibold mb-1 text-xs text-onyx">
            Program Studi <span class="text-[#fe551b]">*</span>
          </label>
          <select
            id="input-prodi"
            name="id_prodi"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          >
            <?php while ($p = $q_prodi->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>" <?= ($p['id'] == $mhs['id_prodi']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nama_prodi']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div>
          <span class="block font-semibold mb-1 text-xs text-onyx">
            Jenis Kelamin <span class="text-[#fe551b]">*</span>
          </span>
          <div class="flex items-center gap-4 mt-2">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="jenis_kelamin" value="Laki-laki" <?= ($mhs['jenis_kelamin'] === 'Laki-laki') ? 'checked' : '' ?> class="accent-onyx">
              <span class="text-sm">Laki-laki</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="jenis_kelamin" value="Perempuan" <?= ($mhs['jenis_kelamin'] === 'Perempuan') ? 'checked' : '' ?> class="accent-onyx">
              <span class="text-sm">Perempuan</span>
            </label>
          </div>
        </div>

        <div>
          <label for="input-angkatan" class="block font-semibold mb-1 text-xs text-onyx">
            Tahun Angkatan <span class="text-[#fe551b]">*</span>
          </label>
          <input
            type="number"
            id="input-angkatan"
            name="angkatan"
            value="<?= htmlspecialchars($mhs['angkatan']) ?>"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx font-mono text-sm"
          />
        </div>

        <div>
          <label for="input-status" class="block font-semibold mb-1 text-xs text-onyx">
            Status Akademik <span class="text-[#fe551b]">*</span>
          </label>
          <select
            id="input-status"
            name="status_akademik"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          >
            <?php foreach (['Aktif', 'Cuti', 'Lulus', 'DO'] as $st): ?>
              <option value="<?= $st ?>" <?= ($mhs['status_akademik'] === $st) ? 'selected' : '' ?>>
                <?= $st ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sm:col-span-2">
          <label for="input-dpa" class="block font-semibold mb-1 text-xs text-onyx">
            Dosen Pembimbing Akademik (DPA)
          </label>
          <input
            type="text"
            id="input-dpa"
            name="dpa"
            value="<?= htmlspecialchars($mhs['dpa']) ?>"
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          />
        </div>
      </div>
    </div>

    <!-- Section 2: Kontak & Geolocation -->
    <div class="space-y-4">
      <h2 class="text-xs font-bold uppercase tracking-wider text-[#706a5e] border-b border-borderTone pb-1.5">
        2. Kontak & Geolocation Domisili
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
          <label for="input-email" class="block font-semibold mb-1 text-xs text-onyx">
            Alamat Email <span class="text-[#fe551b]">*</span>
          </label>
          <input
            type="email"
            id="input-email"
            name="email"
            value="<?= htmlspecialchars($mhs['email']) ?>"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          />
        </div>

        <div>
          <label for="input-nohp" class="block font-semibold mb-1 text-xs text-onyx">
            Nomor WhatsApp/HP <span class="text-[#fe551b]">*</span>
          </label>
          <input
            type="tel"
            id="input-nohp"
            name="no_hp"
            value="<?= htmlspecialchars($mhs['no_hp']) ?>"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx font-mono text-sm"
          />
        </div>

        <div class="sm:col-span-2">
          <label for="input-alamat" class="block font-semibold mb-1 text-xs text-onyx">
            Alamat Domisili Lengkap <span class="text-[#fe551b]">*</span>
          </label>
          <textarea
            id="input-alamat"
            name="alamat"
            rows="2"
            required
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          ><?= htmlspecialchars($mhs['alamat']) ?></textarea>
        </div>

        <div>
          <label for="input-lat" class="block font-semibold mb-1 text-xs text-onyx">
            Latitude Spasial
          </label>
          <input
            type="number"
            step="any"
            id="input-lat"
            name="latitude"
            value="<?= htmlspecialchars($mhs['latitude'] ?? '') ?>"
            placeholder="-7.558000"
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx font-mono text-sm"
          />
        </div>

        <div>
          <label for="input-lng" class="block font-semibold mb-1 text-xs text-onyx">
            Longitude Spasial
          </label>
          <input
            type="number"
            step="any"
            id="input-lng"
            name="longitude"
            value="<?= htmlspecialchars($mhs['longitude'] ?? '') ?>"
            placeholder="110.827000"
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx font-mono text-sm"
          />
        </div>

        <!-- Interactive Map Picker Helper -->
        <div class="sm:col-span-2 space-y-1.5 pt-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-onyx inline-flex items-center gap-1.5">
              <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#fe551b]"></i>
              <span>Klik pada Peta untuk Memilih Titik Koordinat:</span>
            </span>
            <span class="text-[11px] font-mono text-[#706a5e]" id="coords-status">
              <?= (!empty($mhs['latitude']) && !empty($mhs['longitude'])) ? 'Terpilih: ' . htmlspecialchars($mhs['latitude']) . ', ' . htmlspecialchars($mhs['longitude']) : 'Klik peta untuk set koordinat' ?>
            </span>
          </div>
          <div id="picker-map" class="w-full h-44 rounded-lg border border-borderTone z-10 shadow-inner"></div>
        </div>
      </div>
    </div>

    <!-- Section 3: Dual File Upload & Existing Files -->
    <div class="space-y-4">
      <h2 class="text-xs font-bold uppercase tracking-wider text-[#706a5e] border-b border-borderTone pb-1.5">
        3. Pasfoto & Dokumen Terlampir
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <!-- Pasfoto -->
        <div class="p-4 rounded-lg bg-cream border border-borderTone space-y-3">
          <div class="flex items-center justify-between">
            <span class="font-semibold text-xs text-onyx">Pasfoto Saat Ini</span>
            <?php if (!empty($mhs['foto'])): ?>
              <span class="text-[11px] text-[#1b5e20] font-mono">Tersimpan</span>
            <?php else: ?>
              <span class="text-[11px] text-[#706a5e] italic">Avatar SVG</span>
            <?php endif; ?>
          </div>

          <div class="flex items-center gap-3">
            <img
              src="<?= get_mhs_avatar($mhs['nama'], $mhs['jenis_kelamin'], $mhs['nim'], $mhs['foto']) ?>"
              alt="Foto Mahasiswa"
              class="w-14 h-14 rounded-md object-cover border border-borderTone shrink-0"
            />
            <div class="min-w-0 flex-1">
              <label class="block text-xs font-semibold text-onyx mb-1">Ganti Foto (JPG/PNG maks 2MB)</label>
              <input
                type="file"
                name="foto"
                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                class="w-full text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:bg-onyx file:text-parchment cursor-pointer"
              />
            </div>
          </div>
        </div>

        <!-- Dokumen PDF -->
        <div class="p-4 rounded-lg bg-cream border border-borderTone space-y-3">
          <div class="flex items-center justify-between">
            <span class="font-semibold text-xs text-onyx">Dokumen KRS/KTP Saat Ini</span>
            <?php if (!empty($mhs['dokumen']) && file_exists(__DIR__ . '/uploads/dokumen/' . $mhs['dokumen'])): ?>
              <a href="uploads/dokumen/<?= rawurlencode($mhs['dokumen']) ?>" target="_blank" class="text-[11px] font-semibold text-onyx underline inline-flex items-center gap-1">
                <i data-lucide="external-link" class="w-3 h-3"></i>
                <span>Buka PDF</span>
              </a>
            <?php else: ?>
              <span class="text-[11px] text-[#706a5e] italic">Belum Ada</span>
            <?php endif; ?>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-onyx mb-1">Unggah / Ganti Dokumen PDF (Maks 5MB)</label>
            <input
              type="file"
              name="dokumen"
              accept=".pdf,application/pdf"
              class="w-full text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:bg-onyx file:text-parchment cursor-pointer"
            />
            <p class="text-[11px] text-[#706a5e]">Biarkan kosong jika tidak ingin mengubah dokumen.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Footer Actions -->
    <div class="pt-4 border-t border-borderTone flex items-center justify-end gap-3">
      <a href="index.php" class="px-4 py-2 text-xs font-semibold rounded-lg border border-borderTone hover:bg-cream transition">
        Batal
      </a>
      <button
        type="submit"
        class="px-5 py-2.5 text-xs font-semibold rounded-lg bg-onyx text-parchment hover:bg-onyxHover transition shadow-xs inline-flex items-center gap-2"
      >
        <i data-lucide="check" class="w-3.5 h-3.5"></i>
        <span>Simpan Perubahan &rarr;</span>
      </button>
    </div>
  </form>
</div>

<script>
  let pickerMap, pickerMarker;
  document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('input-lat');
    const lngInput = document.getElementById('input-lng');
    const hasExisting = latInput.value && lngInput.value;
    const defaultLat = hasExisting ? parseFloat(latInput.value) : -7.558000;
    const defaultLng = hasExisting ? parseFloat(lngInput.value) : 110.827000;

    pickerMap = L.map('picker-map').setView([defaultLat, defaultLng], hasExisting ? 15 : 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(pickerMap);

    if (hasExisting) {
      pickerMarker = L.marker([defaultLat, defaultLng]).addTo(pickerMap);
    }

    pickerMap.on('click', function(e) {
      const lat = e.latlng.lat.toFixed(6);
      const lng = e.latlng.lng.toFixed(6);
      latInput.value = lat;
      lngInput.value = lng;
      document.getElementById('coords-status').textContent = `Terpilih: ${lat}, ${lng}`;

      if (pickerMarker) {
        pickerMarker.setLatLng(e.latlng);
      } else {
        pickerMarker = L.marker(e.latlng).addTo(pickerMap);
      }
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

