<?php
$page_title = 'Daftar Biodata Mahasiswa';
require_once __DIR__ . '/header.php';

// Ambil daftar Program Studi untuk filter dropdown
$q_prodi = $koneksi->query("SELECT * FROM prodi ORDER BY nama_prodi ASC");
$prodi_list = [];
while ($p = $q_prodi->fetch_assoc()) {
    $prodi_list[] = $p;
}

// Hitung total statistik untuk summary banner
$count_aktif = $koneksi->query("SELECT COUNT(*) as c FROM mahasiswa WHERE is_deleted = 0")->fetch_assoc()['c'];
$count_cuti = $koneksi->query("SELECT COUNT(*) as c FROM mahasiswa WHERE is_deleted = 0 AND status_akademik = 'Cuti'")->fetch_assoc()['c'];
$count_prodi = count($prodi_list);
?>

<div class="space-y-6">
  
  <!-- Header Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-onyx text-parchment">
          MILESTONE 3 & 4
        </span>
        <span class="text-xs font-mono text-[#706a5e]">
          AJAX Live Search · Cross Filter · Pagination · Excel Export · KTM
        </span>
      </div>
      <h1 class="text-2xl font-bold tracking-tight">Data Induk Mahasiswa</h1>
      <p class="text-sm text-[#706a5e]">Kelola data akademik mahasiswa secara real-time dengan Fetch API dan integrasi ekspor.</p>
    </div>

    <!-- Action Toolbar (Milestone 4: Ekspor Excel & Peta) -->
    <div class="flex flex-wrap items-center gap-2">
      <!-- Tombol Ekspor Excel via SheetJS -->
      <button
        type="button"
        onclick="exportToExcel()"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-cream border border-borderTone hover:bg-borderTone text-onyx text-xs font-semibold transition shadow-xs"
        title="Unduh seluruh data tabel ke file Microsoft Excel .xlsx"
      >
        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#1b5e20]"></i>
        <span>Ekspor Excel (.xlsx)</span>
      </button>

      <!-- Tombol Peta Sebaran -->
      <a
        href="peta.php"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-cream border border-borderTone hover:bg-borderTone text-onyx text-xs font-semibold transition shadow-xs"
        title="Buka Peta GIS Geolocation Mahasiswa"
      >
        <i data-lucide="map" class="w-4 h-4 text-[#fe551b]"></i>
        <span>Peta GIS</span>
      </a>

      <!-- Tombol Tambah Mahasiswa -->
      <a
        href="tambah.php"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-onyx text-parchment hover:bg-onyxHover text-xs font-semibold transition shadow-xs"
      >
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        <span>Tambah Data</span>
      </a>
    </div>
  </div>

  <!-- Mini Statistic Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
    <div class="p-4 rounded-xl bg-parchment border border-borderTone flex items-center justify-between shadow-xs">
      <div>
        <p class="text-xs font-semibold text-[#706a5e] uppercase">Total Mahasiswa Aktif</p>
        <p class="text-2xl font-bold font-mono mt-0.5" id="stat-total"><?= $count_aktif ?></p>
      </div>
      <div class="w-10 h-10 rounded-lg bg-cream border border-borderTone flex items-center justify-center">
        <i data-lucide="users" class="w-5 h-5 text-onyx"></i>
      </div>
    </div>

    <div class="p-4 rounded-xl bg-parchment border border-borderTone flex items-center justify-between shadow-xs">
      <div>
        <p class="text-xs font-semibold text-[#706a5e] uppercase">Mahasiswa Berstatus Cuti</p>
        <p class="text-2xl font-bold font-mono mt-0.5"><?= $count_cuti ?></p>
      </div>
      <div class="w-10 h-10 rounded-lg bg-cream border border-borderTone flex items-center justify-center">
        <i data-lucide="clock" class="w-5 h-5 text-[#b78103]"></i>
      </div>
    </div>

    <div class="p-4 rounded-xl bg-parchment border border-borderTone flex items-center justify-between shadow-xs">
      <div>
        <p class="text-xs font-semibold text-[#706a5e] uppercase">Program Studi Terdata</p>
        <p class="text-2xl font-bold font-mono mt-0.5"><?= $count_prodi ?> Prodi</p>
      </div>
      <div class="w-10 h-10 rounded-lg bg-cream border border-borderTone flex items-center justify-center">
        <i data-lucide="graduation-cap" class="w-5 h-5 text-onyx"></i>
      </div>
    </div>
  </div>

  <!-- Milestone 3: Filter & Live Search Toolbar -->
  <div class="p-4 rounded-xl bg-parchment border border-borderTone space-y-3 shadow-xs">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
      
      <!-- Live Search Box -->
      <div class="relative w-full md:w-80">
        <i data-lucide="search" class="w-4 h-4 text-[#706a5e] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
        <input
          type="text"
          id="search-input"
          placeholder="Cari nama, NIM, email, alamat..."
          class="w-full h-10 pl-9 pr-8 rounded-lg bg-cream border border-borderTone text-sm focus:outline-none focus:border-onyx transition"
        />
        <button
          type="button"
          id="clear-search-btn"
          onclick="clearSearch()"
          class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-[#706a5e] hover:text-onyx"
        >
          <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
      </div>

      <!-- Cross Filter: Prodi & Status & Per Page -->
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Filter Prodi -->
        <select
          id="filter-prodi"
          onchange="fetchData(1)"
          class="h-10 px-3 rounded-lg bg-cream border border-borderTone text-xs font-semibold text-onyx focus:outline-none focus:border-onyx"
        >
          <option value="">Semua Program Studi</option>
          <?php foreach ($prodi_list as $pr): ?>
            <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['nama_prodi']) ?></option>
          <?php endforeach; ?>
        </select>

        <!-- Filter Status Akademik -->
        <select
          id="filter-status"
          onchange="fetchData(1)"
          class="h-10 px-3 rounded-lg bg-cream border border-borderTone text-xs font-semibold text-onyx focus:outline-none focus:border-onyx"
        >
          <option value="">Semua Status</option>
          <option value="Aktif">Status: Aktif</option>
          <option value="Cuti">Status: Cuti</option>
          <option value="Lulus">Status: Lulus</option>
          <option value="DO">Status: DO</option>
        </select>

        <!-- Limit Per Page -->
        <select
          id="filter-limit"
          onchange="fetchData(1)"
          class="h-10 px-2.5 rounded-lg bg-cream border border-borderTone text-xs font-semibold text-onyx focus:outline-none focus:border-onyx"
        >
          <option value="5">5 baris</option>
          <option value="10" selected>10 baris</option>
          <option value="25">25 baris</option>
          <option value="50">50 baris</option>
        </select>

        <!-- Reset Button -->
        <button
          type="button"
          onclick="resetFilter()"
          class="h-10 px-3 rounded-lg bg-cream border border-borderTone text-xs font-semibold hover:bg-borderTone transition inline-flex items-center gap-1"
        >
          <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-[#706a5e]"></i>
          <span>Reset</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Table Container (Airtable Style) -->
  <div class="bg-parchment border border-borderTone rounded-xl overflow-hidden shadow-xs relative">
    
    <!-- Loading Overlay Indicator -->
    <div id="loading-overlay" class="hidden absolute inset-0 bg-parchment/60 backdrop-blur-xs z-20 flex items-center justify-center">
      <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-onyx text-parchment text-xs font-semibold shadow-lg">
        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
        <span>Memuat data...</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm" id="mahasiswa-table">
        <thead>
          <tr class="bg-cream border-b border-borderTone text-xs uppercase text-[#524d43] font-semibold tracking-wider">
            <th class="py-3 px-4 w-12 text-center">No</th>
            <th class="py-3 px-3 w-16">Foto</th>
            <th class="py-3 px-4">Nama Lengkap</th>
            <th class="py-3 px-4">NIM</th>
            <th class="py-3 px-4">Program Studi</th>
            <th class="py-3 px-4">Gender</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Kontak</th>
            <th class="py-3 px-4">Dokumen</th>
            <th class="py-3 px-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-borderTone bg-parchment" id="table-body">
          <!-- Rendered dynamically via Vanilla JS Fetch API -->
        </tbody>
      </table>
    </div>

    <!-- Milestone 3: Pagination & Footer Bar -->
    <div class="px-5 py-3.5 bg-cream border-t border-borderTone flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#706a5e]">
      <div id="pagination-info">
        Menampilkan data...
      </div>

      <!-- Pagination Buttons -->
      <div class="flex items-center gap-1.5" id="pagination-controls">
        <!-- Rendered via JS -->
      </div>
    </div>
  </div>
</div>

<script>
  let currentPage = 1;
  let searchTimeout = null;
  let cachedData = []; // Untuk kebutuhan ekspor SheetJS

  // 1. Fetch Data dari AJAX Endpoint
  async function fetchData(page = 1) {
    currentPage = page;
    const search = document.getElementById('search-input').value.trim();
    const idProdi = document.getElementById('filter-prodi').value;
    const status = document.getElementById('filter-status').value;
    const limit = document.getElementById('filter-limit').value;

    const overlay = document.getElementById('loading-overlay');
    overlay.classList.remove('hidden');

    try {
      const url = `ajax_mahasiswa.php?page=${page}&limit=${limit}&search=${encodeURIComponent(search)}&id_prodi=${encodeURIComponent(idProdi)}&status=${encodeURIComponent(status)}`;
      const res = await fetch(url);
      const json = await res.json();

      if (json.status === 'success') {
        cachedData = json.data;
        renderTable(json.data, json.pagination, json.csrf_token);
        renderPagination(json.pagination);
      }
    } catch (err) {
      console.error("Gagal memuat data:", err);
      document.getElementById('table-body').innerHTML = `
        <tr><td colspan="10" class="py-8 text-center text-[#b71c1c]">Gagal memuat data dari server. Silakan muat ulang halaman.</td></tr>
      `;
    } finally {
      overlay.classList.add('hidden');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
  }

  // 2. Render Baris Tabel
  function renderTable(data, pagination, csrfToken) {
    const tbody = document.getElementById('table-body');
    if (!data || data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="10" class="py-12 text-center text-[#706a5e]">
            <i data-lucide="inbox" class="w-8 h-8 mx-auto text-[#a19a8d] mb-2"></i>
            <p class="font-semibold text-onyx">Tidak ada data mahasiswa yang sesuai filter</p>
            <p class="text-xs mt-1">Coba ubah kata kunci pencarian atau reset filter program studi & status.</p>
          </td>
        </tr>
      `;
      return;
    }

    let html = '';
    data.forEach((mhs, idx) => {
      const rowNo = pagination.offset + idx + 1;
      
      let badgeColor = 'bg-[#edf7ee] text-[#1b5e20] border-[#bce2be]';
      if (mhs.status_akademik === 'Cuti') badgeColor = 'bg-[#fff8e1] text-[#b78103] border-[#ffe082]';
      else if (mhs.status_akademik === 'DO') badgeColor = 'bg-[#fdeeed] text-[#b71c1c] border-[#f8c5c3]';

      const dokumenLink = mhs.dokumen_url 
        ? `<a href="${mhs.dokumen_url}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-cream border border-borderTone text-xs font-semibold text-onyx hover:bg-borderTone">
             <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#fe551b]"></i>
             <span>PDF</span>
           </a>`
        : `<span class="text-[#a19a8d] italic text-xs">-</span>`;

      html += `
        <tr class="hover:bg-cream/50 transition">
          <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold text-[#706a5e]">${rowNo}</td>
          <td class="py-3.5 px-3">
            <div class="w-10 h-10 rounded-md overflow-hidden border border-borderTone bg-cream flex items-center justify-center shrink-0">
              <img src="${mhs.avatar_url}" alt="${escapeHtml(mhs.nama)}" class="w-10 h-10 object-cover">
            </div>
          </td>
          <td class="py-3.5 px-4 font-semibold">
            <div class="text-onyx">${escapeHtml(mhs.nama)}</div>
            <div class="text-xs text-[#706a5e] font-normal line-clamp-1 max-w-[200px]" title="${escapeHtml(mhs.alamat)}">${escapeHtml(mhs.alamat)}</div>
          </td>
          <td class="py-3.5 px-4 whitespace-nowrap">
            <span class="inline-flex px-2 py-0.5 rounded bg-cream border border-borderTone font-mono text-xs font-semibold">
              ${escapeHtml(mhs.nim)}
            </span>
          </td>
          <td class="py-3.5 px-4 text-xs whitespace-nowrap">
            <span class="font-medium">${escapeHtml(mhs.nama_prodi)}</span>
            <div class="text-[11px] text-[#706a5e] font-mono">Angkatan ${escapeHtml(mhs.angkatan)}</div>
          </td>
          <td class="py-3.5 px-4 text-xs whitespace-nowrap">${escapeHtml(mhs.jenis_kelamin)}</td>
          <td class="py-3.5 px-4 text-xs whitespace-nowrap">
            <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold border ${badgeColor}">
              ${escapeHtml(mhs.status_akademik)}
            </span>
          </td>
          <td class="py-3.5 px-4 text-xs whitespace-nowrap">
            <div class="text-onyx">${escapeHtml(mhs.email)}</div>
            <div class="font-mono text-[11px] text-[#706a5e]">${escapeHtml(mhs.no_hp)}</div>
          </td>
          <td class="py-3.5 px-4 text-xs whitespace-nowrap">${dokumenLink}</td>
          <td class="py-3.5 px-4 text-right whitespace-nowrap">
            <div class="inline-flex items-center justify-end gap-1.5">
              <!-- Milestone 4: Tombol Cetak KTM -->
              <a href="ktm.php?id=${mhs.id}" target="_blank" title="Cetak KTM dengan QR Code" class="px-2 py-1 text-xs font-semibold rounded bg-cream border border-borderTone hover:bg-borderTone text-onyx transition inline-flex items-center gap-1">
                <i data-lucide="qr-code" class="w-3.5 h-3.5 text-onyx"></i>
                <span class="hidden md:inline">KTM</span>
              </a>

              <a href="edit.php?id=${mhs.id}" class="px-2.5 py-1 text-xs font-semibold rounded bg-cream border border-borderTone hover:bg-borderTone text-onyx transition">
                Edit
              </a>

              <a
                href="hapus.php?id=${mhs.id}&csrf_token=${csrfToken}"
                onclick="return confirm('Peringatan Soft Delete:\n\nApakah Anda yakin ingin menonaktifkan mahasiswa ${escapeQuotes(mhs.nama)} (NIM ${escapeQuotes(mhs.nim)})?')"
                class="px-2.5 py-1 text-xs font-semibold rounded border border-[#ef4444] text-[#ef4444] hover:bg-[#ef4444] hover:text-white transition"
              >
                Hapus
              </a>
            </div>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  // 3. Render Pagination
  function renderPagination(p) {
    const info = document.getElementById('pagination-info');
    info.innerHTML = `Menampilkan <strong>${p.showing_from}</strong> - <strong>${p.showing_to}</strong> dari <strong>${p.total_records}</strong> mahasiswa`;

    const ctrl = document.getElementById('pagination-controls');
    if (p.total_pages <= 1) {
      ctrl.innerHTML = '';
      return;
    }

    let btns = '';

    // Tombol Prev
    btns += `
      <button
        type="button"
        onclick="fetchData(${p.current_page - 1})"
        ${p.current_page === 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-borderTone"'}
        class="px-2.5 py-1 rounded bg-cream border border-borderTone text-xs font-semibold transition inline-flex items-center gap-1"
      >
        &larr; Prev
      </button>
    `;

    // Tombol Halaman
    for (let i = 1; i <= p.total_pages; i++) {
      if (i === 1 || i === p.total_pages || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
        btns += `
          <button
            type="button"
            onclick="fetchData(${i})"
            class="px-2.5 py-1 rounded text-xs font-mono font-semibold transition ${
              i === p.current_page
                ? 'bg-onyx text-parchment'
                : 'bg-cream border border-borderTone hover:bg-borderTone text-onyx'
            }"
          >
            ${i}
          </button>
        `;
      } else if (i === p.current_page - 2 || i === p.current_page + 2) {
        btns += `<span class="px-1 text-xs text-[#706a5e]">...</span>`;
      }
    }

    // Tombol Next
    btns += `
      <button
        type="button"
        onclick="fetchData(${p.current_page + 1})"
        ${p.current_page === p.total_pages ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-borderTone"'}
        class="px-2.5 py-1 rounded bg-cream border border-borderTone text-xs font-semibold transition inline-flex items-center gap-1"
      >
        Next &rarr;
      </button>
    `;

    ctrl.innerHTML = btns;
  }

  // 4. Milestone 3: Live Search dengan Debounce (300ms)
  document.getElementById('search-input').addEventListener('input', function(e) {
    const clearBtn = document.getElementById('clear-search-btn');
    if (this.value.length > 0) {
      clearBtn.classList.remove('hidden');
    } else {
      clearBtn.classList.add('hidden');
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      fetchData(1);
    }, 300);
  });

  function clearSearch() {
    document.getElementById('search-input').value = '';
    document.getElementById('clear-search-btn').classList.add('hidden');
    fetchData(1);
  }

  function resetFilter() {
    document.getElementById('search-input').value = '';
    document.getElementById('filter-prodi').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-limit').value = '10';
    document.getElementById('clear-search-btn').classList.add('hidden');
    fetchData(1);
  }

  // 5. Milestone 4: Ekspor Data ke Excel (.xlsx) Menggunakan SheetJS
  async function exportToExcel() {
    try {
      // Ambil seluruh data mahasiswa aktif untuk diekspor (tanpa batas limit pagination)
      const res = await fetch('ajax_mahasiswa.php?page=1&limit=1000');
      const json = await res.json();
      
      if (!json.data || json.data.length === 0) {
        alert('Tidak ada data mahasiswa untuk diekspor.');
        return;
      }

      // Format baris data Excel secara rapi
      const exportRows = json.data.map((item, idx) => ({
        "No": idx + 1,
        "NIM": item.nim,
        "Nama Lengkap": item.nama,
        "Program Studi": item.nama_prodi,
        "Fakultas": item.fakultas,
        "Jenis Kelamin": item.jenis_kelamin,
        "Angkatan": item.angkatan,
        "Status Akademik": item.status_akademik,
        "DPA": item.dpa,
        "Email": item.email,
        "Nomor HP": item.no_hp,
        "Alamat": item.alamat,
        "Latitude": item.latitude || "-",
        "Longitude": item.longitude || "-",
        "Tanggal Didaftarkan": item.created_at
      }));

      // Buat Worksheet & Workbook SheetJS
      const ws = XLSX.utils.json_to_sheet(exportRows);
      
      // Atur lebar kolom otomatis
      const wscols = [
        { wch: 5 },  // No
        { wch: 12 }, // NIM
        { wch: 25 }, // Nama
        { wch: 25 }, // Prodi
        { wch: 25 }, // Fakultas
        { wch: 14 }, // Gender
        { wch: 10 }, // Angkatan
        { wch: 15 }, // Status
        { wch: 22 }, // DPA
        { wch: 25 }, // Email
        { wch: 16 }, // HP
        { wch: 35 }, // Alamat
        { wch: 14 }, // Lat
        { wch: 14 }, // Lng
        { wch: 20 }, // Created
      ];
      ws['!cols'] = wscols;

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Data Mahasiswa");

      const filename = `Data_Mahasiswa_FIKOM_UDB_${new Date().toISOString().slice(0, 10)}.xlsx`;
      XLSX.writeFile(wb, filename);

    } catch (e) {
      console.error(e);
      alert('Gagal mengekspor data ke Excel.');
    }
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
      return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[m];
    });
  }

  function escapeQuotes(text) {
    if (!text) return '';
    return String(text).replace(/'/g, "\\'");
  }

  // Load awal saat halaman dibuka
  document.addEventListener('DOMContentLoaded', () => {
    fetchData(1);
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
