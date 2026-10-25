<?php
$page_title = 'Tambah Mahasiswa';
require_once __DIR__ . '/header.php';

// Ambil daftar Program Studi dari database
$q_prodi = $koneksi->query("SELECT * FROM prodi ORDER BY nama_prodi ASC");
?>

<div class="max-w-3xl mx-auto space-y-6">
  <!-- Breadcrumb & Title -->
  <div class="flex items-center justify-between">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-onyx text-parchment">
          INSERT RECORD
        </span>
        <span class="text-xs font-mono text-[#706a5e]">Milestone 2 · CRUD Form</span>
      </div>
      <h1 class="text-2xl font-bold tracking-tight">Formulir Pendaftaran Mahasiswa</h1>
      <p class="text-sm text-[#706a5e]">Lengkapi seluruh atribut data diri, kontak, berkas dokumen, dan koordinat domisili.</p>
    </div>
    
    <a href="index.php" class="text-xs font-semibold px-3 py-1.5 rounded-md border border-borderTone hover:bg-cream transition">
      &larr; Kembali ke Tabel
    </a>
  </div>

  <!-- Form Container (Airtable Style) -->
  <form action="proses_tambah.php" method="POST" enctype="multipart/form-data" novalidate class="bg-parchment border border-borderTone rounded-xl p-6 sm:p-8 space-y-6 shadow-xs">
    
    <!-- CSRF Token Protection -->
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <!-- Section 1: Data Identitas Akademik -->
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
            required
            placeholder="Contoh: 26"
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
            required
            placeholder="Masukkan nama lengkap"
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
            <option value="">-- Pilih Program Studi --</option>
            <?php while ($p = $q_prodi->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_prodi']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div>
          <span class="block font-semibold mb-1 text-xs text-onyx">
            Jenis Kelamin <span class="text-[#fe551b]">*</span>
          </span>
          <div class="flex items-center gap-4 mt-2">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="jenis_kelamin" value="Laki-laki" checked class="accent-onyx">
              <span class="text-sm">Laki-laki</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="jenis_kelamin" value="Perempuan" class="accent-onyx">
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
            value="2026"
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
            <option value="Aktif" selected>Aktif</option>
            <option value="Cuti">Cuti</option>
            <option value="Lulus">Lulus</option>
            <option value="DO">DO (Drop Out)</option>
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
            value="Dr. Muhtarom, M.Kom"
            placeholder="Nama DPA"
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          />
        </div>
      </div>
    </div>

    <!-- Section 2: Kontak & Lokasi Domisili -->
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
            required
            placeholder="nama@email.com"
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
            required
            placeholder="081234567890"
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
            placeholder="Jl. Nama Jalan No. XX, Kota/Kabupaten"
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx text-sm"
          ></textarea>
        </div>

        <div>
          <label for="input-lat" class="block font-semibold mb-1 text-xs text-onyx">
            Latitude Spasial (Opsional)
          </label>
          <input
            type="number"
            step="any"
            id="input-lat"
            name="latitude"
            placeholder="-7.558000"
            class="w-full px-3.5 py-2 rounded-lg bg-cream border border-borderTone focus:outline-none focus:border-onyx font-mono text-sm"
          />
        </div>

        <div>
          <label for="input-lng" class="block font-semibold mb-1 text-xs text-onyx">
            Longitude Spasial (Opsional)
          </label>
          <input
            type="number"
            step="any"
            id="input-lng"
            name="longitude"
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
            <span class="text-[11px] font-mono text-[#706a5e]" id="coords-status">Klik peta untuk set koordinat</span>
          </div>
          <div id="picker-map" class="w-full h-44 rounded-lg border border-borderTone z-10 shadow-inner"></div>
        </div>
      </div>
    </div>

    <!-- Section 3: Dual File Upload -->
    <div class="space-y-4">
      <h2 class="text-xs font-bold uppercase tracking-wider text-[#706a5e] border-b border-borderTone pb-1.5">
        3. Berkas & Dokumen Terunggah
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div class="p-4 rounded-lg bg-cream border border-borderTone space-y-2">
          <div class="flex items-center gap-2">
            <i data-lucide="image" class="w-4 h-4 text-onyx"></i>
            <span class="font-semibold text-xs text-onyx">Pasfoto Mahasiswa</span>
          </div>
          <p class="text-[11px] text-[#706a5e]">Format: JPG, PNG (Maksimal 2 MB)</p>
          <input
            type="file"
            name="foto"
            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
            class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-onyx file:text-parchment hover:file:bg-onyxHover cursor-pointer"
          />
        </div>

        <div class="p-4 rounded-lg bg-cream border border-borderTone space-y-2">
          <div class="flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4 text-[#fe551b]"></i>
            <span class="font-semibold text-xs text-onyx">Dokumen KRS / KTP (PDF)</span>
          </div>
          <p class="text-[11px] text-[#706a5e]">Format: Berkas PDF (Maksimal 5 MB)</p>
          <input
            type="file"
            name="dokumen"
            accept=".pdf,application/pdf"
            class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-onyx file:text-parchment hover:file:bg-onyxHover cursor-pointer"
          />
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
        <i data-lucide="save" class="w-3.5 h-3.5"></i>
        <span>Simpan Data Mahasiswa &rarr;</span>
      </button>
    </div>
  </form>
</div>

<script>
  let pickerMap, pickerMarker;
  document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('input-lat');
    const lngInput = document.getElementById('input-lng');
    const defaultLat = latInput.value ? parseFloat(latInput.value) : -7.558000;
    const defaultLng = lngInput.value ? parseFloat(lngInput.value) : 110.827000;

    pickerMap = L.map('picker-map').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(pickerMap);

    if (latInput.value && lngInput.value) {
      pickerMarker = L.marker([defaultLat, defaultLng]).addTo(pickerMap);
      document.getElementById('coords-status').textContent = `Terpilih: ${defaultLat}, ${defaultLng}`;
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

