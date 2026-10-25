<?php
require_once __DIR__ . '/koneksi.php';
$flash = get_flash();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>Sistem Data Mahasiswa Advance</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  
  <!-- Tailwind Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- SheetJS (Excel Export) -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <!-- Leaflet CSS & JS for Maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <!-- QRCode.js for KTM generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            parchment: '#faf5e8',
            cream: '#f4ede0',
            onyx: '#181d26',
            onyxHover: '#262d3a',
            borderTone: '#e6decb',
            accentFlame: '#fe551b',
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            mono: ['"JetBrains Mono"', 'monospace'],
          }
        }
      }
    }
  </script>
  <style>
    body {
      background-color: #faf5e8;
      color: #181d26;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-parchment text-onyx min-h-screen flex flex-col font-sans antialiased selection:bg-[#fe551b]/15">
  
  <!-- Navigation Header (Airtable Parchment Style) -->
  <nav class="bg-parchment border-b border-borderTone px-6 py-3.5 flex items-center justify-between sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-3">
      <span class="w-8 h-8 rounded-lg bg-onyx text-parchment flex items-center justify-center font-bold text-sm tracking-tighter">SDM</span>
      <a href="index.php" class="font-bold text-lg tracking-tight hover:opacity-80">Sistem Data Mahasiswa</a>
      <span class="hidden sm:inline-flex text-xs px-2.5 py-0.5 rounded-full bg-cream border border-borderTone font-mono text-[#706a5e]">
        FIKOM UDB · Advance
      </span>
    </div>
    
    <div class="flex items-center gap-2">
      <!-- Menu Tabel Data -->
      <a href="index.php" class="text-sm font-semibold px-3 py-1.5 rounded-md transition inline-flex items-center gap-1.5 <?= ($current_page === 'index.php') ? 'bg-cream text-onyx' : 'text-[#706a5e] hover:text-onyx hover:bg-cream/60' ?>">
        <i data-lucide="table" class="w-4 h-4"></i>
        <span>Tabel Data</span>
      </a>

      <!-- Menu Peta Sebaran (Milestone 4) -->
      <a href="peta.php" class="text-sm font-semibold px-3 py-1.5 rounded-md transition inline-flex items-center gap-1.5 <?= ($current_page === 'peta.php') ? 'bg-cream text-onyx' : 'text-[#706a5e] hover:text-onyx hover:bg-cream/60' ?>">
        <i data-lucide="map-pin" class="w-4 h-4 text-[#fe551b]"></i>
        <span>Peta Sebaran</span>
      </a>

      <!-- Menu Tambah Mahasiswa -->
      <a href="tambah.php" class="text-sm font-semibold bg-onyx hover:bg-onyxHover text-parchment px-4 py-1.5 rounded-md transition inline-flex items-center gap-1.5 shadow-xs ml-1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Tambah</span>
      </a>
    </div>
  </nav>

  <!-- Main Content Wrapper -->
  <main class="flex-1 max-w-7xl w-full mx-auto p-6 md:p-8">
    <?php if ($flash): ?>
      <div class="mb-6 p-4 rounded-xl border <?= $flash['type'] === 'success' ? 'bg-[#edf7ee] border-[#bce2be] text-[#1b5e20]' : 'bg-[#fdeeed] border-[#f8c5c3] text-[#b71c1c]' ?> flex items-center justify-between text-sm shadow-xs">
        <div class="flex items-center gap-2.5">
          <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider <?= $flash['type'] === 'success' ? 'bg-[#1b5e20] text-white' : 'bg-[#b71c1c] text-white' ?>">
            <?= strtoupper($flash['type']) ?>
          </span>
          <span class="font-medium"><?= htmlspecialchars($flash['message']) ?></span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="font-bold text-lg leading-none hover:opacity-70">&times;</button>
      </div>
    <?php endif; ?>
