<?php
$page_title = 'Peta Sebaran Mahasiswa';
require_once __DIR__ . '/header.php';

// Ambil data mahasiswa aktif beserta koordinat
$sql = "SELECT m.*, p.nama_prodi 
        FROM mahasiswa m 
        JOIN prodi p ON m.id_prodi = p.id 
        WHERE m.is_deleted = 0 
        ORDER BY m.nama ASC";

$stmt = $koneksi->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

$all_students = [];
$plotted = [];
$unplotted = [];

while ($row = $res->fetch_assoc()) {
    $row['avatar_url'] = get_mhs_avatar($row['nama'], $row['jenis_kelamin'], $row['nim'], $row['foto']);
    $all_students[] = $row;
    if (!empty($row['latitude']) && !empty($row['longitude'])) {
        $plotted[] = $row;
    } else {
        $unplotted[] = $row;
    }
}
$stmt->close();
?>

<div class="space-y-6">
  
  <!-- Header Title -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-onyx text-parchment">
          MILESTONE 4 · GIS MAP
        </span>
        <span class="text-xs font-mono text-[#706a5e]">
          Leaflet.js · OpenStreetMap · <?= count($plotted) ?> Lokasi Terpetakan
        </span>
      </div>
      <h1 class="text-2xl font-bold tracking-tight">Peta Spasial Sebaran Domisili Mahasiswa</h1>
      <p class="text-sm text-[#706a5e]">Visualisasi titik koordinat tempat tinggal mahasiswa FIKOM UDB berbasis Leaflet.js.</p>
    </div>

    <div class="flex items-center gap-2">
      <a href="index.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-cream border border-borderTone hover:bg-borderTone text-onyx text-xs font-semibold transition">
        <i data-lucide="table" class="w-4 h-4"></i>
        <span>Kembali ke Tabel</span>
      </a>
      <a href="tambah.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-onyx text-parchment hover:bg-onyxHover text-xs font-semibold transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Tambah Koordinat</span>
      </a>
    </div>
  </div>

  <!-- Map & Sidebar Grid Container -->
  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <!-- Map Canvas (3 Columns) -->
    <div class="lg:col-span-3 bg-parchment border border-borderTone rounded-xl overflow-hidden shadow-xs relative">
      <div id="leaflet-map" class="w-full h-[540px] z-10"></div>

      <!-- Map Legend Floating Card -->
      <div class="absolute bottom-4 left-4 z-20 bg-parchment/95 backdrop-blur-xs border border-borderTone p-3 rounded-lg shadow-md text-xs space-y-1.5 hidden sm:block">
        <p class="font-bold text-onyx">Legenda Peta</p>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-[#fe551b]"></span>
          <span>Titik Domisili Mahasiswa Aktif</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-[#181d26]"></span>
          <span>Kampus Utama FIKOM UDB</span>
        </div>
      </div>
    </div>

    <!-- Student List Sidebar (1 Column) -->
    <div class="bg-parchment border border-borderTone rounded-xl p-4 shadow-xs flex flex-col h-[540px]">
      <div class="border-b border-borderTone pb-3 mb-3">
        <h2 class="font-bold text-sm text-onyx">Daftar Titik Mahasiswa</h2>
        <p class="text-xs text-[#706a5e]"><?= count($plotted) ?> terpetakan, <?= count($unplotted) ?> belum ada koordinat</p>
      </div>

      <!-- Scrollable Student List -->
      <div class="flex-1 overflow-y-auto space-y-2 pr-1" id="student-map-list">
        <?php if (empty($plotted)): ?>
          <p class="text-xs text-[#706a5e] text-center py-8">Belum ada mahasiswa yang memiliki data koordinat latitude/longitude.</p>
        <?php else: ?>
          <?php foreach ($plotted as $m): ?>
            <div
              onclick="focusMarker(<?= (float)$m['latitude'] ?>, <?= (float)$m['longitude'] ?>, '<?= htmlspecialchars(addslashes($m['nama'])) ?>')"
              class="p-2.5 rounded-lg bg-cream/60 hover:bg-cream border border-borderTone transition cursor-pointer flex items-center gap-2.5 group"
            >
              <img src="<?= $m['avatar_url'] ?>" alt="<?= htmlspecialchars($m['nama']) ?>" class="w-8 h-8 rounded-md object-cover border border-borderTone shrink-0">
              <div class="min-w-0 flex-1">
                <div class="font-semibold text-xs text-onyx truncate group-hover:text-[#fe551b] transition-colors">
                  <?= htmlspecialchars($m['nama']) ?>
                </div>
                <div class="text-[11px] text-[#706a5e] font-mono">
                  <?= htmlspecialchars($m['nim']) ?> · <?= htmlspecialchars($m['nama_prodi']) ?>
                </div>
              </div>
              <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#fe551b] shrink-0"></i>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Quick Reset Center Button -->
      <div class="pt-3 border-t border-borderTone mt-auto">
        <button
          type="button"
          onclick="resetMapCenter()"
          class="w-full py-2 px-3 rounded-lg bg-cream hover:bg-borderTone text-xs font-semibold text-onyx transition flex items-center justify-center gap-1.5"
        >
          <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
          <span>Pusatkan ke Kampus UDB</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  let map;
  const markers = {};
  const kampusCoords = [-7.558000, 110.827000]; // Koordinat Kampus Surakarta

  document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi Peta Leaflet
    map = L.map('leaflet-map').setView(kampusCoords, 13);

    // Tile Layer OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Marker Kampus Utama UDB
    const kampusIcon = L.divIcon({
      className: 'custom-kampus-icon',
      html: `
        <div style="background-color: #181d26; color: #faf5e8; border: 2px solid #ffffff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
          UDB
        </div>
      `,
      iconSize: [32, 32],
      iconAnchor: [16, 16]
    });

    L.marker(kampusCoords, { icon: kampusIcon })
      .addTo(map)
      .bindPopup(`
        <div style="font-family: 'Plus Jakarta Sans', sans-serif; padding: 4px;">
          <strong style="color: #181d26; font-size: 13px;">Kampus FIKOM Universitas Duta Bangsa</strong>
          <p style="font-size: 11px; color: #706a5e; margin: 4px 0 0 0;">Pusat Kegiatan Akademik & Laboratorium</p>
        </div>
      `);

    // Inisialisasi Marker Mahasiswa
    const studentData = <?= json_encode($plotted, JSON_UNESCAPED_UNICODE) ?>;

    studentData.forEach(m => {
      const lat = parseFloat(m.latitude);
      const lng = parseFloat(m.longitude);

      if (!isNaN(lat) && !isNaN(lng)) {
        const studentIcon = L.divIcon({
          className: 'custom-mhs-icon',
          html: `
            <div style="background-color: #fe551b; color: #ffffff; border: 2px solid #ffffff; width: 28px; height: 28px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; box-shadow: 0 2px 5px rgba(0,0,0,0.25);">
              ${escapeHtml(m.nim.substring(0, 2))}
            </div>
          `,
          iconSize: [28, 28],
          iconAnchor: [14, 14]
        });

        const popupContent = `
          <div style="font-family: 'Plus Jakarta Sans', sans-serif; min-width: 180px; padding: 4px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
              <img src="${m.avatar_url}" style="width: 36px; height: 36px; border-radius: 6px; object-cover: cover; border: 1px solid #e6decb;" />
              <div>
                <strong style="font-size: 13px; color: #181d26; display: block; line-height: 1.2;">${escapeHtml(m.nama)}</strong>
                <span style="font-size: 11px; font-family: monospace; color: #706a5e;">NIM: ${escapeHtml(m.nim)}</span>
              </div>
            </div>
            <p style="font-size: 11px; color: #181d26; margin: 0 0 4px 0;"><strong>Prodi:</strong> ${escapeHtml(m.nama_prodi)}</p>
            <p style="font-size: 11px; color: #706a5e; margin: 0 0 8px 0; line-height: 1.3;">${escapeHtml(m.alamat)}</p>
            <div style="display: flex; gap: 4px; border-top: 1px solid #e6decb; padding-top: 6px;">
              <a href="ktm.php?id=${m.id}" target="_blank" style="font-size: 11px; font-weight: 600; background: #181d26; color: #faf5e8; padding: 3px 8px; border-radius: 4px; text-decoration: none;">Cetak KTM</a>
              <a href="edit.php?id=${m.id}" style="font-size: 11px; font-weight: 600; background: #f4ede0; color: #181d26; padding: 3px 8px; border-radius: 4px; text-decoration: none; border: 1px solid #e6decb;">Edit</a>
            </div>
          </div>
        `;

        const marker = L.marker([lat, lng], { icon: studentIcon })
          .addTo(map)
          .bindPopup(popupContent);

        markers[m.nama] = marker;
      }
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
  });

  function focusMarker(lat, lng, nama) {
    if (map) {
      map.flyTo([lat, lng], 15, { animate: true, duration: 1.2 });
      if (markers[nama]) {
        markers[nama].openPopup();
      }
    }
  }

  function resetMapCenter() {
    if (map) {
      map.flyTo(kampusCoords, 13, { animate: true, duration: 1 });
    }
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
      return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[m];
    });
  }
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

