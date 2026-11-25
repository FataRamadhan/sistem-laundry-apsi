<?php
// index.php - Sistem Pengelolaan Laundry Mobile-First Web App (DFD Revisi Kelompok 2)
require_once __DIR__ . '/config.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Sistem Pengelolaan Laundry - Mobile App</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        primaryDark: '#1d4ed8',
                        accent: '#0284c7'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- QR Code Generator Library Offline -->
    <script src="assets/js/qrcode.min.js"></script>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
            margin: 0;
            padding: 0;
        }

        /* Desktop View: Realistic Smartphone Mockup Frame */
        @media (min-width: 640px) {
            body {
                background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px 0;
            }
            .phone-bezel {
                width: 420px;
                height: 870px;
                max-height: 94vh;
                background: #ffffff;
                border-radius: 46px;
                border: 11px solid #0f172a;
                outline: 2px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.75), 0 0 0 1px rgba(255, 255, 255, 0.08);
                overflow: hidden;
                display: flex;
                flex-direction: column;
                position: relative;
            }
            .phone-status-bar {
                display: flex;
            }
            .phone-home-indicator {
                display: block;
            }
        }

        /* Mobile & APK View: 100% Fullscreen Responsive */
        @media (max-width: 639px) {
            body {
                background-color: #ffffff;
                min-height: 100vh;
            }
            .phone-bezel {
                width: 100%;
                min-height: 100vh;
                border: none;
                border-radius: 0;
                box-shadow: none;
                display: flex;
                flex-direction: column;
            }
            .phone-status-bar {
                display: none !important;
            }
            .phone-home-indicator {
                display: none !important;
            }
        }

        /* Hide scrollbars for native feel */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Print Nota Stylesheet */
        @media print {
            body * {
                visibility: hidden;
            }
            #printableNota, #printableNota * {
                visibility: visible;
            }
            #printableNota {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 10px;
                box-shadow: none;
                border: none;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="antialiased text-slate-800">

<!-- FLOATING TOAST CONTAINER -->
<div id="toastContainer" class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-[90%] max-w-[380px] pointer-events-none flex flex-col space-y-2"></div>

<!-- SMARTPHONE FRAME MOCKUP (DESKTOP) / FULLSCREEN CONTAINER (MOBILE/APK) -->
<div class="phone-bezel">

    <!-- SIMULATED PHONE STATUS BAR (DESKTOP PREVIEW) -->
    <div class="phone-status-bar bg-blue-600 text-white text-[11px] font-semibold px-6 py-2 items-center justify-between select-none tracking-tight flex-shrink-0 z-40">
        <span id="statusBarClock">09:41</span>
        <!-- Notch / Dynamic Island -->
        <div class="w-20 h-4 bg-slate-900/60 rounded-full flex items-center justify-center space-x-1">
            <div class="w-1.5 h-1.5 rounded-full bg-slate-800"></div>
            <div class="w-2.5 h-1 rounded-full bg-blue-400/40"></div>
        </div>
        <!-- Status Icons -->
        <div class="flex items-center space-x-1.5 text-[10px]">
            <i class="fa-solid fa-signal"></i>
            <i class="fa-solid fa-wifi"></i>
            <i class="fa-solid fa-battery-full text-xs text-emerald-300"></i>
        </div>
    </div>

    <!-- TOP APP BAR -->
    <header class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white p-4 sticky top-0 z-30 shadow-md flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm text-white shadow-inner">
                    <i class="fa-solid fa-soap text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight">LaundryCare</h1>
                    <p class="text-[11px] text-blue-100 font-medium">Pengelolaan Laundry Mobile-First</p>
                </div>
            </div>

            <!-- User Auth Status / Action -->
            <div id="topUserBar" class="flex items-center space-x-2">
                <?php if ($currentUser): ?>
                    <div class="text-right">
                        <span class="block text-xs font-semibold leading-tight"><?= htmlspecialchars($currentUser['username']) ?></span>
                        <span class="inline-block text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-medium text-blue-100">
                            <?= htmlspecialchars($currentUser['peran']) ?>
                        </span>
                    </div>
                    <button onclick="handleLogout()" title="Logout" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition active:scale-95">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                    </button>
                <?php else: ?>
                    <a href="cek_status.php" class="text-xs bg-white/15 hover:bg-white/25 px-3 py-1.5 rounded-full transition font-semibold flex items-center space-x-1.5 active:scale-95">
                        <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                        <span>Cek Status</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- CONTENT SCROLLABLE AREA -->
    <main class="flex-1 overflow-y-auto p-4 hide-scrollbar relative bg-slate-50/50 pb-24" id="mainAppContent">
        <?php if (!$currentUser): ?>
        <!-- ========================================================== -->
        <!-- VIEW: LOGIN PENGGUNA (D6 Akun Pengguna) -->
        <!-- ========================================================== -->
        <div id="viewLogin" class="py-6 space-y-6">
            <div class="text-center space-y-2">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl mx-auto flex items-center justify-center text-2xl shadow-sm border border-blue-100">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">Masuk ke Sistem</h2>
                <p class="text-xs text-slate-500 max-w-xs mx-auto">
                    Khusus Pemilik (A-01) & Petugas Operasional (A-02). Pelanggan tidak memerlukan login.
                </p>
            </div>

            <!-- Form Login -->
            <form id="formLogin" onsubmit="event.preventDefault(); submitLogin();" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div id="loginErrorAlert" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                    <span id="loginErrorMsg">Username atau kata sandi tidak cocok.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username Akun</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-user text-sm"></i>
                        </span>
                        <input type="text" id="loginUsername" required placeholder="Masukkan username" 
                               class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" id="loginPassword" required placeholder="Masukkan kata sandi" 
                               class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <button type="submit" id="btnLoginSubmit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md shadow-blue-500/20 active:scale-[0.98] transition flex items-center justify-center space-x-2 text-sm">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk</span>
                </button>
            </form>

            <!-- Link Pelanggan Publik -->
            <div class="text-center pt-2">
                <a href="cek_status.php" class="inline-flex items-center space-x-1.5 text-xs text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 hover:bg-blue-100 px-4 py-2.5 rounded-full transition active:scale-95 shadow-xs">
                    <i class="fa-solid fa-eye"></i>
                    <span>Pelanggan? Klik Cek Status Tanpa Login (Proses 4.0)</span>
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- ========================================================== -->
        <!-- DASHBOARD APLIKASI (LOGIN BERHASIL) -->
        <!-- ========================================================== -->

        <!-- TAB 1: PROSES 1.0 CATAT PESANAN (KHUSUS PEMILIK) -->
        <section id="tabProses1" class="space-y-4 <?= ($currentUser['peran'] !== 'Pemilik') ? 'hidden' : '' ?>">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-file-circle-plus text-blue-600 mr-2"></i> Proses 1.0: Catat Pesanan
                    </h2>
                    <p class="text-xs text-slate-500">Input data pesanan cucian pelanggan baru/lama</p>
                </div>
            </div>

            <form id="formCatatPesanan" onsubmit="event.preventDefault(); openKonfirmasiPesananModal();" class="space-y-4">
                <!-- Data Pelanggan (D1) -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                        <span><i class="fa-solid fa-address-card text-blue-500 mr-1.5"></i> Data Pelanggan (D1)</span>
                        <span class="text-[10px] font-normal text-blue-600">Auto-fill Otomatis</span>
                    </h3>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor HP Pelanggan <span class="text-rose-500">*</span></label>
                        <div class="flex space-x-2">
                            <input type="tel" id="inputNoHp" required placeholder="08xxxxxxxxxx" onblur="checkPelangganLama(this.value)"
                                   class="flex-1 px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="button" onclick="checkPelangganLama(document.getElementById('inputNoHp').value)" 
                                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-xl text-xs font-semibold transition flex items-center space-x-1 active:scale-95">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Cek</span>
                            </button>
                        </div>
                        <p id="pelangganLamaNotice" class="hidden text-[11px] text-emerald-600 font-semibold mt-1 flex items-center">
                            <i class="fa-solid fa-check-circle mr-1"></i> Pelanggan lama ditemukan! Data terisi otomatis.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap Pelanggan <span class="text-rose-500">*</span></label>
                        <input type="text" id="inputNamaPelanggan" required placeholder="Nama Pelanggan" 
                               class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <textarea id="inputAlamatPelanggan" required rows="2" placeholder="Alamat rumah / domisili" 
                                  class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                    </div>
                </div>

                <!-- Data Pesanan & Hitung Harga Otomatis (D2) -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        <i class="fa-solid fa-scale-balanced text-indigo-500 mr-1.5"></i> Spesifikasi Pesanan (D2)
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Titip <span class="text-rose-500">*</span></label>
                            <input type="date" id="inputTanggalTitip" required value="<?= date('Y-m-d') ?>" 
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Berat Cucian (Kg) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.1" min="0.1" id="inputBerat" required placeholder="0.0" oninput="calculatePriceClient()" 
                                   class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Layanan <span class="text-rose-500">*</span></label>
                        <select id="selectJenisLayanan" onchange="calculatePriceClient()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                            <option value="Cuci Komplit">Cuci Komplit (Rp 7.000 / kg)</option>
                            <option value="Cuci Kering">Cuci Kering (Rp 5.000 / kg)</option>
                            <option value="Setrika Saja">Setrika Saja (Rp 4.500 / kg)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Lama Pengerjaan <span class="text-rose-500">*</span></label>
                        <select id="selectLamaPengerjaan" onchange="calculatePriceClient()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                            <option value="Reguler (2 Hari)">Reguler (2 Hari) - Tarif Standar</option>
                            <option value="Kilat (1 Hari)">Kilat (1 Hari) - Tambahan Rp 3.000 / kg</option>
                            <option value="Express (6 Jam)">Express (6 Jam) - Tambahan Rp 6.000 / kg</option>
                        </select>
                    </div>

                    <!-- Kartu Total Harga Otomatis -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-3 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-blue-700 font-semibold block">Total Harga Otomatis:</span>
                            <span class="text-xs text-slate-500" id="priceFormulaPreview">0 kg × tarif</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-extrabold text-blue-700" id="totalHargaText">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Rincian Item Cucian Opsional (D3) -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fa-solid fa-tags text-amber-500 mr-1.5"></i> Rincian Item Cucian (D3 - Opsional)
                        </h3>
                        <button type="button" onclick="tambahBarisItem()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center space-x-1 active:scale-95">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>Tambah Item</span>
                        </button>
                    </div>
                    <div id="containerRincianItem" class="space-y-2.5">
                        <!-- Baris item ditambahkan dinamis -->
                    </div>
                    <p class="text-[11px] text-slate-400 italic">Tambahkan detail pakaian berharga/khusus seperti warna, merek, atau kondisi.</p>
                </div>

                <!-- Layanan Antar-Jemput Opsional (D5) -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" id="checkAntarJemput" onchange="toggleAntarJemput(this.checked)" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-xs font-bold text-slate-700">Gunakan Layanan Antar-Jemput (D5)</span>
                    </label>

                    <div id="panelAntarJemput" class="hidden space-y-2.5 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Layanan Antar-Jemput</label>
                            <select id="selectAJJenis" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="Antar">Antar ke Alamat (Setelah Selesai)</option>
                                <option value="Jemput">Jemput dari Alamat</option>
                                <option value="Antar & Jemput">Antar & Jemput</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat Antar-Jemput</label>
                            <textarea id="inputAJAlamat" rows="2" placeholder="Gunakan alamat pelanggan atau masukkan alamat tujuan lain" 
                                      class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit Form Catat Pesanan -->
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-2xl shadow-lg shadow-blue-500/25 active:scale-[0.98] transition flex items-center justify-center space-x-2 text-sm">
                    <i class="fa-solid fa-check"></i>
                    <span>Tinjau & Konfirmasi Pesanan</span>
                </button>
            </form>
        </section>

        <!-- TAB 2: PROSES 2.0 CARI PESANAN (KHUSUS PEMILIK) -->
        <section id="tabProses2" class="space-y-4 hidden">
            <div class="pb-2 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-800 flex items-center">
                    <i class="fa-solid fa-magnifying-glass text-blue-600 mr-2"></i> Proses 2.0: Cari Pesanan
                </h2>
                <p class="text-xs text-slate-500">Cari berdasarkan nama pelanggan, tanggal titip, ciri barang, atau nomor pesanan</p>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <input type="text" id="inputPencarian" placeholder="Ketik nama, tgl (YYYY-MM-DD), ciri, no pesanan..." 
                       oninput="debounceSearch()" 
                       class="w-full pl-10 pr-10 py-2.5 text-xs rounded-2xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-xs">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-search text-xs"></i>
                </span>
                <button onclick="clearSearch()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 active:scale-95">
                    <i class="fa-solid fa-times text-xs"></i>
                </button>
            </div>

            <!-- Pesan "Pesanan tidak ditemukan" sesuai DFD -->
            <div id="searchNotFoundAlert" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-center space-y-1">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto text-sm">
                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                </div>
                <h4 class="text-xs font-bold text-amber-800">Pesanan tidak ditemukan</h4>
                <p class="text-[11px] text-amber-600">Tidak ada pesanan yang cocok dengan kata kunci pencarian Anda.</p>
            </div>

            <!-- Search Results Container -->
            <div id="searchResultsList" class="space-y-3">
                <!-- Order cards injected via JS -->
            </div>
        </section>

        <!-- TAB 3: PROSES 3.0 KELOLA STATUS & ANTAR-JEMPUT (PEMILIK & PETUGAS) -->
        <section id="tabProses3" class="space-y-4 <?= ($currentUser['peran'] === 'Petugas Operasional') ? '' : 'hidden' ?>">
            <div class="pb-2 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-arrows-rotate text-blue-600 mr-2"></i> Proses 3.0: Kelola Status
                    </h2>
                    <p class="text-xs text-slate-500">Pembaruan status pengerjaan (D2) & antar-jemput (D5)</p>
                </div>
                <button onclick="loadStatusOrders()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold p-1.5 rounded-lg bg-blue-50 active:scale-95">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            <!-- Filter Status Baku Stepper -->
            <div class="overflow-x-auto pb-1 -mx-1 px-1 flex space-x-1.5 text-[11px] hide-scrollbar">
                <button onclick="filterStatusTab('all')" class="status-filter-btn active px-3 py-1.5 rounded-xl font-semibold bg-blue-600 text-white shadow-xs whitespace-nowrap active:scale-95" data-filter="all">Semua</button>
                <button onclick="filterStatusTab('Diterima')" class="status-filter-btn px-3 py-1.5 rounded-xl font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap active:scale-95" data-filter="Diterima">Diterima</button>
                <button onclick="filterStatusTab('Dicuci')" class="status-filter-btn px-3 py-1.5 rounded-xl font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap active:scale-95" data-filter="Dicuci">Dicuci</button>
                <button onclick="filterStatusTab('Disetrika')" class="status-filter-btn px-3 py-1.5 rounded-xl font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap active:scale-95" data-filter="Disetrika">Disetrika</button>
                <button onclick="filterStatusTab('Siap Diambil/Diantar')" class="status-filter-btn px-3 py-1.5 rounded-xl font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap active:scale-95" data-filter="Siap Diambil/Diantar">Siap</button>
                <button onclick="filterStatusTab('Selesai')" class="status-filter-btn px-3 py-1.5 rounded-xl font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap active:scale-95" data-filter="Selesai">Selesai</button>
            </div>

            <!-- List Orders for Process 3.0 -->
            <div id="statusOrdersList" class="space-y-3">
                <!-- Cards injected here -->
            </div>
        </section>

        <!-- TAB 4: PROSES 5.0 KELOLA CUCIAN TANPA PEMILIK (KHUSUS PEMILIK) -->
        <section id="tabProses5" class="space-y-4 hidden">
            <div class="pb-2 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-800 flex items-center">
                    <i class="fa-solid fa-basket-shopping text-blue-600 mr-2"></i> Proses 5.0: Cucian Tanpa Pemilik
                </h2>
                <p class="text-xs text-slate-500">Pencatatan barang tertinggal & pengelolaan status D4</p>
            </div>

            <!-- Form Tambah Cucian Tanpa Pemilik -->
            <form id="formCucianHilang" onsubmit="event.preventDefault(); submitCucianHilang();" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    <i class="fa-solid fa-plus text-emerald-500 mr-1.5"></i> Catat Barang Tertinggal Baru
                </h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Barang <span class="text-rose-500">*</span></label>
                    <textarea id="inputDeskripsiCucian" required rows="2" placeholder="Contoh: Sajadah hijau tua, kemeja putih tanpa merek..." 
                              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Foto Barang (Opsional)</label>
                    <input type="file" id="inputFotoCucian" accept="image/*" 
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <button type="submit" id="btnSubmitCucianHilang" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition active:scale-[0.98]">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan ke Data Store D4</span>
                </button>
            </form>

            <!-- List Data Cucian Tanpa Pemilik -->
            <div class="space-y-2.5">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                    <span>Daftar Cucian Tanpa Pemilik (D4)</span>
                    <button onclick="loadCucianHilangList()" class="text-blue-600 hover:text-blue-800 text-[11px] font-semibold active:scale-95">
                        <i class="fa-solid fa-rotate-right"></i> Refresh
                    </button>
                </h3>
                <div id="cucianHilangList" class="space-y-3">
                    <!-- Lost laundry cards injected via JS -->
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- BOTTOM NAVIGATION BAR (Khusus Pengguna Login) -->
    <?php if ($currentUser): ?>
    <nav class="absolute bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 flex-shrink-0">
        <div class="flex items-center justify-around py-1.5">
            <?php if ($currentUser['peran'] === 'Pemilik'): ?>
                <button onclick="switchTab('tabProses1')" id="navProses1" class="nav-item active flex flex-col items-center text-blue-600 py-1 px-2.5 transition active:scale-90">
                    <i class="fa-solid fa-file-circle-plus text-base mb-0.5"></i>
                    <span class="text-[10px] font-semibold">Catat (1.0)</span>
                </button>
                <button onclick="switchTab('tabProses2')" id="navProses2" class="nav-item flex flex-col items-center text-slate-400 hover:text-blue-600 py-1 px-2.5 transition active:scale-90">
                    <i class="fa-solid fa-magnifying-glass text-base mb-0.5"></i>
                    <span class="text-[10px] font-medium">Cari (2.0)</span>
                </button>
                <button onclick="switchTab('tabProses3')" id="navProses3" class="nav-item flex flex-col items-center text-slate-400 hover:text-blue-600 py-1 px-2.5 transition active:scale-90">
                    <i class="fa-solid fa-arrows-rotate text-base mb-0.5"></i>
                    <span class="text-[10px] font-medium">Status (3.0)</span>
                </button>
                <button onclick="switchTab('tabProses5')" id="navProses5" class="nav-item flex flex-col items-center text-slate-400 hover:text-blue-600 py-1 px-2.5 transition active:scale-90">
                    <i class="fa-solid fa-basket-shopping text-base mb-0.5"></i>
                    <span class="text-[10px] font-medium">Cucian (5.0)</span>
                </button>
            <?php else: ?>
                <!-- Petugas Operasional: Hanya Akses Proses 3.0 -->
                <button onclick="switchTab('tabProses3')" id="navProses3" class="nav-item active flex flex-col items-center text-blue-600 py-1.5 px-6 transition active:scale-95">
                    <i class="fa-solid fa-arrows-rotate text-lg mb-1"></i>
                    <span class="text-xs font-bold">Kelola Status & Antar-Jemput (Proses 3.0)</span>
                </button>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

    <!-- SIMULATED PHONE BOTTOM HOME INDICATOR (DESKTOP PREVIEW) -->
    <div class="phone-home-indicator py-1.5 bg-white select-none flex-shrink-0 z-40">
        <div class="w-28 h-1 bg-slate-300 rounded-full mx-auto"></div>
    </div>

</div>

<!-- ================================================================== -->
<!-- REUSABLE GENERIC CONFIRMATION MODAL                                -->
<!-- ================================================================== -->
<div id="modalGenericConfirm" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden backdrop-blur-xs">
    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center space-x-3">
            <div id="genConfirmIconWrap" class="w-11 h-11 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-lg flex-shrink-0">
                <i id="genConfirmIcon" class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 id="genConfirmTitle" class="text-sm font-bold text-slate-800">Konfirmasi Tindakan</h3>
                <p id="genConfirmMessage" class="text-xs text-slate-500 leading-relaxed mt-0.5">Apakah Anda yakin?</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-1">
            <button type="button" onclick="closeGenericConfirm(false)" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition active:scale-95">
                Batal
            </button>
            <button type="button" id="btnGenConfirmAction" class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition active:scale-95">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<!-- ================================================================== -->
<!-- MODAL DIALOG KONFIRMASI SIMPAN PESANAN (Proses 1.0)                 -->
<!-- ================================================================== -->
<div id="modalKonfirmasiPesanan" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden backdrop-blur-xs">
    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center space-x-3 text-blue-600">
            <div class="w-10 h-10 bg-blue-50 rounded-2xl flex items-center justify-center text-lg">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Konfirmasi Simpan Pesanan</h3>
                <p class="text-[11px] text-slate-500">Periksa kembali data sebelum menyimpan</p>
            </div>
        </div>

        <div class="bg-slate-50 rounded-2xl p-3.5 text-xs space-y-2 border border-slate-200">
            <div class="flex justify-between">
                <span class="text-slate-500">Pelanggan:</span>
                <span class="font-bold text-slate-800" id="konfNama">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">No. HP:</span>
                <span class="font-mono text-slate-800" id="konfHp">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Layanan:</span>
                <span class="font-semibold text-slate-800" id="konfLayanan">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Berat Cucian:</span>
                <span class="font-bold text-slate-800" id="konfBerat">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Status Awal:</span>
                <span class="font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded">Diterima</span>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-bold">
                <span class="text-slate-700">Total Harga:</span>
                <span class="text-blue-700 text-base" id="konfTotalHarga">Rp 0</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-1">
            <button type="button" onclick="closeKonfirmasiPesananModal()" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition active:scale-95">
                Batal
            </button>
            <button type="button" onclick="submitSimpanPesanan()" id="btnSubmitPesananFinal" class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition active:scale-95">
                Ya, Simpan Pesanan
            </button>
        </div>
    </div>
</div>

<!-- ================================================================== -->
<!-- MODAL NOTA DIGITAL & QR CODE (Proses 1.0)                          -->
<!-- ================================================================== -->
<div id="modalNotaDigital" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden backdrop-blur-xs">
    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4 border border-slate-100 hide-scrollbar">
        <!-- Printable Nota Area -->
        <div id="printableNota" class="space-y-3.5 border-b border-dashed border-slate-300 pb-4">
            <div class="text-center">
                <h2 class="text-base font-extrabold text-slate-800 tracking-tight">LAUNDRYCARE</h2>
                <p class="text-[10px] text-slate-500 leading-tight">Jasa Pencucian & Perawatan Pakaian Berkualitas</p>
                <p class="text-[9px] text-slate-400">Jl. Margonda Raya No. 45, Depok • Telp. 0812-3456-7890</p>
            </div>

            <div class="border-t border-b border-dashed border-slate-300 py-2 text-[11px] space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Pesanan:</span>
                    <span class="font-mono font-bold text-slate-800" id="notaNoPesanan">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tanggal Titip:</span>
                    <span class="font-medium text-slate-700" id="notaTanggal">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pelanggan:</span>
                    <span class="font-bold text-slate-800" id="notaPelanggan">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">No. HP:</span>
                    <span class="font-mono text-slate-700" id="notaHp">-</span>
                </div>
            </div>

            <!-- Detail Layanan & Total -->
            <div class="text-xs space-y-1">
                <div class="flex justify-between">
                    <span id="notaLayananText">Cuci Komplit</span>
                    <span class="font-bold" id="notaBeratText">0 kg</span>
                </div>
                <div class="flex justify-between text-slate-500 text-[11px]">
                    <span id="notaDurasiText">Reguler (2 Hari)</span>
                    <span class="text-blue-600 font-semibold" id="notaStatusAwal">Status: Diterima</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-bold">
                    <span>TOTAL HARGA:</span>
                    <span class="text-base text-blue-700 font-extrabold" id="notaTotalHarga">Rp 0</span>
                </div>
            </div>

            <!-- QR Code Section untuk Cek Status Publik -->
            <div class="text-center pt-2">
                <p class="text-[10px] font-semibold text-slate-600 mb-1.5">Scan QR untuk Cek Status Real-time (Proses 4.0):</p>
                <div id="notaQrCodeContainer" class="flex justify-center p-2 bg-white rounded-xl border border-slate-200 inline-block mx-auto"></div>
                <p class="text-[9px] text-slate-400 mt-1 font-mono break-all" id="notaLinkUrl">-</p>
            </div>
        </div>

        <!-- Tombol Aksi Nota -->
        <div class="space-y-2 no-print">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="window.print()" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs flex items-center justify-center space-x-1.5 shadow active:scale-95 transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Nota</span>
                </button>
                <a id="notaBtnBukaCek" href="#" target="_blank" class="py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center justify-center space-x-1.5 shadow active:scale-95 transition">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Buka Cek Status</span>
                </a>
            </div>
            <button type="button" onclick="closeNotaModal()" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition active:scale-95">
                Selesai / Transaksi Baru
            </button>
        </div>
    </div>
</div>

<!-- ================================================================== -->
<!-- MODAL DETAIL PESANAN LENGKAP (Proses 2.0 & 3.0)                    -->
<!-- ================================================================== -->
<div id="modalDetailPesanan" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden backdrop-blur-xs">
    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4 border border-slate-100 hide-scrollbar">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <div>
                <span class="text-[10px] font-mono font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded" id="detNoPesanan">-</span>
                <h3 class="text-sm font-bold text-slate-800 mt-1" id="detNamaPelanggan">-</h3>
            </div>
            <button onclick="closeDetailModal()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition active:scale-90">
                <i class="fa-solid fa-times text-xs"></i>
            </button>
        </div>

        <div class="text-xs space-y-3">
            <!-- Info D1 Pelanggan -->
            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Data Pelanggan (D1)</h4>
                <p><strong>No. HP:</strong> <span id="detNoHp">-</span></p>
                <p><strong>Alamat:</strong> <span id="detAlamat">-</span></p>
            </div>

            <!-- Info D2 Pesanan -->
            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Data Pesanan (D2)</h4>
                <p><strong>Tanggal Titip:</strong> <span id="detTanggalTitip">-</span></p>
                <p><strong>Layanan:</strong> <span id="detJenisLayanan">-</span> (<span id="detBerat">-</span> kg)</p>
                <p><strong>Lama Pengerjaan:</strong> <span id="detLamaPengerjaan">-</span></p>
                <p><strong>Total Harga:</strong> <span class="font-bold text-blue-700" id="detTotalHarga">-</span></p>
                <p><strong>Status Pengerjaan:</strong> <span class="font-bold text-emerald-600" id="detStatusPengerjaan">-</span></p>
                <p><strong>Waktu Pembaruan:</strong> <span id="detWaktuPembaruan">-</span></p>
            </div>

            <!-- Info D3 Rincian Item -->
            <div id="detSectionD3" class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1.5 hidden">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rincian Item Cucian (D3)</h4>
                <ul id="detListD3" class="space-y-1 divide-y divide-slate-200 text-[11px]"></ul>
            </div>

            <!-- Info D5 Antar-Jemput -->
            <div id="detSectionD5" class="bg-emerald-50 p-3 rounded-2xl border border-emerald-200 space-y-1 hidden">
                <h4 class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Layanan Antar-Jemput (D5)</h4>
                <p><strong>Layanan:</strong> <span id="detAJJenis">-</span></p>
                <p><strong>Alamat:</strong> <span id="detAJAlamat">-</span></p>
                <p><strong>Status Antar-Jemput:</strong> <span class="font-bold text-emerald-700" id="detAJStatus">-</span></p>
            </div>

            <!-- Info D7 Riwayat Status -->
            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-1.5">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Riwayat Status (D7)</h4>
                <div id="detListD7" class="space-y-2 text-[11px] max-h-40 overflow-y-auto hide-scrollbar"></div>
            </div>
        </div>

        <button type="button" onclick="closeDetailModal()" class="w-full py-2.5 rounded-xl bg-slate-800 text-white font-semibold text-xs active:scale-95 transition">
            Tutup
        </button>
    </div>
</div>

<!-- ================================================================== -->
<!-- MODAL UBAH STATUS PENGERJAAN & ANTAR-JEMPUT (Proses 3.0)           -->
<!-- ================================================================== -->
<div id="modalUbahStatus" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden backdrop-blur-xs">
    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center space-x-3 text-blue-600">
            <div class="w-10 h-10 bg-blue-50 rounded-2xl flex items-center justify-center text-lg">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800" id="ubahStatusModalTitle">Ubah Status Pengerjaan</h3>
                <p class="text-[11px] text-slate-500" id="ubahStatusModalSubtitle">Verifikasi dan konfirmasi pembaruan</p>
            </div>
        </div>

        <input type="hidden" id="ubahStatusPesananId">
        <input type="hidden" id="ubahStatusTipe" value="pengerjaan">

        <div class="space-y-3">
            <div id="boxPilihanPengerjaan">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Status Baku Baru:</label>
                <div class="space-y-2 text-xs" id="radioStatusOptions">
                    <!-- Options inserted via JS -->
                </div>
            </div>

            <div id="boxPilihanAntarJemput" class="hidden">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Status Antar-Jemput Baru:</label>
                <div class="space-y-2 text-xs" id="radioAJOptions">
                    <!-- AJ options inserted via JS -->
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2">
            <button type="button" onclick="closeUbahStatusModal()" class="py-2.5 px-3 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition active:scale-95">
                Batal
            </button>
            <button type="button" onclick="submitPerubahanStatus()" id="btnSubmitStatusFinal" class="py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition active:scale-95">
                Konfirmasi Ubah
            </button>
        </div>
    </div>
</div>

<script>
    // Data State Global
    const CURRENT_USER = <?= json_encode($currentUser) ?>;
    let allOrdersCache = [];
    let currentFilterTab = 'all';
    let searchTimeout = null;

    // Status Baku Definisi DFD
    const STATUS_PENGERJAAN_BAKU = ['Diterima', 'Dicuci', 'Disetrika', 'Siap Diambil/Diantar', 'Selesai'];
    const STATUS_AJ_BAKU = ['Dijadwalkan', 'Dijemput', 'Diantar', 'Selesai'];

    // ----------------------------------------------------------------
    // MODERN FLOATING TOAST NOTIFICATION SYSTEM
    // ----------------------------------------------------------------
    function showToast(message, type = 'info', title = '') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto transform transition-all duration-300 ease-out translate-y-[-20px] opacity-0 flex items-start space-x-3 p-3.5 rounded-2xl shadow-xl backdrop-blur-md text-xs border';

        let bgStyle = 'bg-slate-900/95 text-white border-slate-700/80 shadow-slate-950/40';
        let icon = '<i class="fa-solid fa-circle-info text-blue-400 text-sm mt-0.5"></i>';

        if (type === 'success') {
            bgStyle = 'bg-emerald-950/95 text-emerald-100 border-emerald-700/80 shadow-emerald-950/40';
            icon = '<i class="fa-solid fa-circle-check text-emerald-400 text-sm mt-0.5"></i>';
        } else if (type === 'error') {
            bgStyle = 'bg-rose-950/95 text-rose-100 border-rose-700/80 shadow-rose-950/40';
            icon = '<i class="fa-solid fa-circle-xmark text-rose-400 text-sm mt-0.5"></i>';
        } else if (type === 'warning') {
            bgStyle = 'bg-amber-950/95 text-amber-100 border-amber-700/80 shadow-amber-950/40';
            icon = '<i class="fa-solid fa-triangle-exclamation text-amber-400 text-sm mt-0.5"></i>';
        }

        toast.className += ` ${bgStyle}`;
        toast.innerHTML = `
            <div class="flex-shrink-0">${icon}</div>
            <div class="flex-1 pr-1">
                ${title ? `<p class="font-bold text-[11px] uppercase tracking-wider mb-0.5">${title}</p>` : ''}
                <p class="leading-relaxed font-medium">${escapeHtml(message)}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-white/40 hover:text-white flex-shrink-0 text-xs transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        container.appendChild(toast);

        // Animate entrance
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-20px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Auto dismiss after 3.5 seconds
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ----------------------------------------------------------------
    // REUSABLE NATIVE-FEEL CONFIRMATION MODAL HELPER
    // ----------------------------------------------------------------
    let genericConfirmCallback = null;
    function showConfirmDialog({ title, message, iconClass = 'fa-triangle-exclamation', iconColor = 'text-rose-600', iconBg = 'bg-rose-50', confirmBtnClass = 'bg-rose-600 hover:bg-rose-700', confirmText = 'Ya, Lanjutkan', onConfirm }) {
        document.getElementById('genConfirmTitle').innerText = title;
        document.getElementById('genConfirmMessage').innerText = message;
        document.getElementById('genConfirmIcon').className = `fa-solid ${iconClass}`;
        document.getElementById('genConfirmIconWrap').className = `w-11 h-11 ${iconBg} ${iconColor} rounded-2xl flex items-center justify-center text-lg flex-shrink-0`;

        const btn = document.getElementById('btnGenConfirmAction');
        btn.className = `w-full py-2.5 px-3 rounded-xl ${confirmBtnClass} text-white font-bold text-xs shadow-md transition active:scale-95`;
        btn.innerText = confirmText;

        genericConfirmCallback = onConfirm;
        document.getElementById('modalGenericConfirm').classList.remove('hidden');
    }

    function closeGenericConfirm(confirmed = false) {
        document.getElementById('modalGenericConfirm').classList.add('hidden');
        if (confirmed && typeof genericConfirmCallback === 'function') {
            genericConfirmCallback();
        }
        genericConfirmCallback = null;
    }
    document.getElementById('btnGenConfirmAction').onclick = () => closeGenericConfirm(true);

    // Live Phone Status Bar Clock
    function updatePhoneClock() {
        const el = document.getElementById('statusBarClock');
        if (!el) return;
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        el.innerText = `${hrs}:${mins}`;
    }
    setInterval(updatePhoneClock, 1000);
    updatePhoneClock();

    // Inisialisasi Aplikasi Saat Dimuat
    window.addEventListener('DOMContentLoaded', () => {
        if (CURRENT_USER) {
            if (CURRENT_USER.peran === 'Pemilik') {
                loadStatusOrders();
            } else if (CURRENT_USER.peran === 'Petugas Operasional') {
                switchTab('tabProses3');
                loadStatusOrders();
            }
        }
    });

    // ----------------------------------------------------------------
    // NAVIGASI TAB SESUAI HAK AKSES AKTOR
    // ----------------------------------------------------------------
    function switchTab(tabId) {
        if (!CURRENT_USER) return;

        // Validasi Hak Akses Frontend: Petugas Operasional hanya boleh ke Proses 3.0
        if (CURRENT_USER.peran === 'Petugas Operasional' && tabId !== 'tabProses3') {
            showToast('Petugas Operasional hanya berhak mengakses Proses 3.0.', 'error', 'Akses Ditolak');
            return;
        }

        // Sembunyikan semua tab
        ['tabProses1', 'tabProses2', 'tabProses3', 'tabProses5'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });

        // Hapus kelas aktif di tombol navigasi
        document.querySelectorAll('.nav-item').forEach(btn => {
            btn.classList.remove('active', 'text-blue-600');
            btn.classList.add('text-slate-400');
        });

        // Tampilkan tab yang dipilih
        const activeTabEl = document.getElementById(tabId);
        if (activeTabEl) activeTabEl.classList.remove('hidden');

        // Beri style aktif pada tombol nav terkait
        const activeNavBtn = document.getElementById(tabId.replace('tab', 'nav'));
        if (activeNavBtn) {
            activeNavBtn.classList.add('active', 'text-blue-600');
            activeNavBtn.classList.remove('text-slate-400');
        }

        // Muat data spesifik tab jika perlu
        if (tabId === 'tabProses2') {
            searchPesanan('');
        } else if (tabId === 'tabProses3') {
            loadStatusOrders();
        } else if (tabId === 'tabProses5') {
            loadCucianHilangList();
        }
    }

    // ----------------------------------------------------------------
    // AUTENTIKASI PENGGUNA (D6 Akun Pengguna)
    // ----------------------------------------------------------------
    async function submitLogin() {
        const username = document.getElementById('loginUsername').value.trim();
        const password = document.getElementById('loginPassword').value.trim();
        const btn = document.getElementById('btnLoginSubmit');
        const alertEl = document.getElementById('loginErrorAlert');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Memproses...</span>';
        alertEl.classList.add('hidden');

        try {
            const response = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            const result = await response.json();

            if (result.success) {
                showToast(`Selamat datang, ${result.data.username}!`, 'success', 'Login Berhasil');
                setTimeout(() => window.location.reload(), 600);
            } else {
                document.getElementById('loginErrorMsg').innerText = result.message || 'Login gagal';
                alertEl.classList.remove('hidden');
                showToast(result.message || 'Username atau kata sandi tidak cocok.', 'error', 'Login Gagal');
            }
        } catch (err) {
            document.getElementById('loginErrorMsg').innerText = 'Koneksi ke server terputus.';
            alertEl.classList.remove('hidden');
            showToast('Koneksi ke server lokal terputus.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> <span>Masuk</span>';
        }
    }

    function handleLogout() {
        showConfirmDialog({
            title: 'Konfirmasi Logout',
            message: 'Apakah Anda yakin ingin keluar dari sesi aplikasi?',
            iconClass: 'fa-arrow-right-from-bracket',
            iconColor: 'text-amber-600',
            iconBg: 'bg-amber-50',
            confirmBtnClass: 'bg-amber-600 hover:bg-amber-700',
            confirmText: 'Ya, Logout',
            onConfirm: async () => {
                try {
                    await fetch('api/auth.php?action=logout', { method: 'POST' });
                    window.location.reload();
                } catch (e) {
                    window.location.reload();
                }
            }
        });
    }

    // ----------------------------------------------------------------
    // PROSES 1.0: CATAT PESANAN (KHUSUS PEMILIK)
    // ----------------------------------------------------------------

    // Cek & Pengisian Otomatis Data Pelanggan Lama dari D1
    async function checkPelangganLama(noHp) {
        const cleanVal = noHp.trim();
        const notice = document.getElementById('pelangganLamaNotice');
        if (!cleanVal || cleanVal.length < 4) {
            notice.classList.add('hidden');
            return;
        }

        try {
            const res = await fetch(`api/pelanggan.php?no_hp=${encodeURIComponent(cleanVal)}`);
            const data = await res.json();
            if (data.success && data.data && data.data.length > 0) {
                const customer = data.data[0];
                document.getElementById('inputNamaPelanggan').value = customer.nama;
                document.getElementById('inputAlamatPelanggan').value = customer.alamat;
                notice.classList.remove('hidden');
                showToast(`Pelanggan lama "${customer.nama}" terdeteksi dari D1.`, 'info', 'Auto-fill Aktif');
            } else {
                notice.classList.add('hidden');
            }
        } catch (e) {
            console.error('Gagal cek pelanggan:', e);
        }
    }

    // Kalkulasi Total Harga Otomatis Berdasarkan Berat, Layanan, dan Durasi
    function calculatePriceClient() {
        const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
        const jenis = document.getElementById('selectJenisLayanan').value;
        const durasi = document.getElementById('selectLamaPengerjaan').value;

        let basePrice = 7000;
        if (jenis === 'Cuci Kering') basePrice = 5000;
        if (jenis === 'Setrika Saja') basePrice = 4500;

        let extraPerKg = 0;
        if (durasi.includes('Kilat')) extraPerKg = 3000;
        if (durasi.includes('Express')) extraPerKg = 6000;

        const ratePerKg = basePrice + extraPerKg;
        const total = Math.round(berat * ratePerKg);

        document.getElementById('totalHargaText').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('priceFormulaPreview').innerText = `${berat} kg × Rp ${ratePerKg.toLocaleString('id-ID')}/kg`;
        return total;
    }

    // Tambah Baris Rincian Item Cucian (D3)
    function tambahBarisItem() {
        const container = document.getElementById('containerRincianItem');
        const rowId = 'item_' + Date.now();
        const row = document.createElement('div');
        row.id = rowId;
        row.className = 'grid grid-cols-12 gap-1.5 items-center bg-slate-50 p-2 rounded-xl border border-slate-200';
        row.innerHTML = `
            <div class="col-span-4">
                <input type="text" placeholder="Jenis (e.g. Kemeja)" class="item-jenis w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300">
            </div>
            <div class="col-span-2">
                <input type="number" min="1" value="1" placeholder="Jml" class="item-jumlah w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 text-center">
            </div>
            <div class="col-span-5">
                <input type="text" placeholder="Ciri (warna/merek)" class="item-ciri w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300">
            </div>
            <div class="col-span-1 text-right">
                <button type="button" onclick="document.getElementById('${rowId}').remove()" class="text-rose-500 hover:text-rose-700 text-xs active:scale-90">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    function toggleAntarJemput(checked) {
        const panel = document.getElementById('panelAntarJemput');
        if (checked) {
            panel.classList.remove('hidden');
            const alamatCust = document.getElementById('inputAlamatPelanggan').value;
            if (alamatCust && !document.getElementById('inputAJAlamat').value) {
                document.getElementById('inputAJAlamat').value = alamatCust;
            }
        } else {
            panel.classList.add('hidden');
        }
    }

    // Modal Konfirmasi Sebelum Simpan Pesanan (Wajib Sesuai DFD)
    function openKonfirmasiPesananModal() {
        const nama = document.getElementById('inputNamaPelanggan').value.trim();
        const hp = document.getElementById('inputNoHp').value.trim();
        const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
        const total = calculatePriceClient();

        if (!nama || !hp || berat <= 0) {
            showToast('Mohon lengkapi data wajib: Nama, No. HP, dan Berat (> 0 kg).', 'warning', 'Validasi Data');
            return;
        }

        document.getElementById('konfNama').innerText = nama;
        document.getElementById('konfHp').innerText = hp;
        document.getElementById('konfLayanan').innerText = document.getElementById('selectJenisLayanan').value + ' (' + document.getElementById('selectLamaPengerjaan').value + ')';
        document.getElementById('konfBerat').innerText = berat + ' kg';
        document.getElementById('konfTotalHarga').innerText = 'Rp ' + total.toLocaleString('id-ID');

        document.getElementById('modalKonfirmasiPesanan').classList.remove('hidden');
    }

    function closeKonfirmasiPesananModal() {
        document.getElementById('modalKonfirmasiPesanan').classList.add('hidden');
    }

    // Submit Simpan Pesanan ke Backend API
    async function submitSimpanPesanan() {
        const btn = document.getElementById('btnSubmitPesananFinal');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        // Kumpulkan data rincian item (D3)
        const items = [];
        document.querySelectorAll('#containerRincianItem > div').forEach(row => {
            const jenis = row.querySelector('.item-jenis')?.value.trim();
            const jumlah = parseInt(row.querySelector('.item-jumlah')?.value) || 1;
            const ciri = row.querySelector('.item-ciri')?.value.trim();
            if (jenis) {
                items.push({ jenis, jumlah, ciri_barang: ciri });
            }
        });

        // Kumpulkan data antar-jemput (D5)
        const isAJ = document.getElementById('checkAntarJemput').checked;
        const antarJemput = isAJ ? {
            aktif: true,
            jenis_layanan: document.getElementById('selectAJJenis').value,
            alamat: document.getElementById('inputAJAlamat').value.trim()
        } : null;

        const payload = {
            nama: document.getElementById('inputNamaPelanggan').value.trim(),
            no_hp: document.getElementById('inputNoHp').value.trim(),
            alamat: document.getElementById('inputAlamatPelanggan').value.trim(),
            tanggal_titip: document.getElementById('inputTanggalTitip').value,
            berat: parseFloat(document.getElementById('inputBerat').value) || 0,
            jenis_layanan: document.getElementById('selectJenisLayanan').value,
            lama_pengerjaan: document.getElementById('selectLamaPengerjaan').value,
            rincian_item: items,
            antar_jemput: antarJemput
        };

        try {
            const res = await fetch('api/pesanan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.success && result.data) {
                closeKonfirmasiPesananModal();
                showNotaDigitalModal(result.data);
                showToast('Pesanan berhasil dicatat dan status awal "Diterima"!', 'success', 'Tersimpan');
                // Reset form
                document.getElementById('formCatatPesanan').reset();
                document.getElementById('containerRincianItem').innerHTML = '';
                document.getElementById('pelangganLamaNotice').classList.add('hidden');
                document.getElementById('panelAntarJemput').classList.add('hidden');
                calculatePriceClient();
            } else {
                showToast('Gagal mencatat pesanan: ' + (result.message || 'Kesalahan server.'), 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan koneksi ke server lokal.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Ya, Simpan Pesanan';
        }
    }

    // Tampilkan Modal Nota Digital dengan QR Code Mandiri
    function showNotaDigitalModal(data) {
        document.getElementById('notaNoPesanan').innerText = data.no_pesanan;
        document.getElementById('notaTanggal').innerText = data.tanggal_titip;
        document.getElementById('notaPelanggan').innerText = data.nama_pelanggan;
        document.getElementById('notaHp').innerText = data.no_hp;
        document.getElementById('notaLayananText').innerText = data.jenis_layanan;
        document.getElementById('notaBeratText').innerText = data.berat + ' kg';
        document.getElementById('notaDurasiText').innerText = data.lama_pengerjaan;
        document.getElementById('notaTotalHarga').innerText = 'Rp ' + Number(data.total_harga).toLocaleString('id-ID');

        // Buat Link Publik untuk Cek Status (Proses 4.0)
        const baseUrl = window.location.href.split('index.php')[0];
        const checkUrl = `${baseUrl}cek_status.php?no_pesanan=${encodeURIComponent(data.no_pesanan)}&no_hp=${encodeURIComponent(data.no_hp)}`;

        document.getElementById('notaLinkUrl').innerText = checkUrl;
        document.getElementById('notaBtnBukaCek').href = checkUrl;

        // Generate QR Code Offline
        const qrContainer = document.getElementById('notaQrCodeContainer');
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: checkUrl,
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });

        document.getElementById('modalNotaDigital').classList.remove('hidden');
    }

    function closeNotaModal() {
        document.getElementById('modalNotaDigital').classList.add('hidden');
    }

    // ----------------------------------------------------------------
    // PROSES 2.0: CARI PESANAN (KHUSUS PEMILIK)
    // ----------------------------------------------------------------
    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = document.getElementById('inputPencarian').value.trim();
            searchPesanan(query);
        }, 300);
    }

    function clearSearch() {
        document.getElementById('inputPencarian').value = '';
        searchPesanan('');
    }

    async function searchPesanan(keyword) {
        const alertNotFound = document.getElementById('searchNotFoundAlert');
        const resultsList = document.getElementById('searchResultsList');

        try {
            const url = keyword ? `api/pesanan.php?q=${encodeURIComponent(keyword)}` : 'api/pesanan.php';
            const res = await fetch(url);
            const json = await res.json();

            if (json.success && json.data && json.data.length > 0) {
                alertNotFound.classList.add('hidden');
                resultsList.innerHTML = json.data.map(order => createOrderCardHtml(order, 'search')).join('');
            } else {
                resultsList.innerHTML = '';
                // Menampilkan pesan "Pesanan tidak ditemukan" sesuai DFD jika ada pencarian tapi kosong
                if (keyword) {
                    alertNotFound.classList.remove('hidden');
                } else {
                    alertNotFound.classList.add('hidden');
                    resultsList.innerHTML = '<p class="text-xs text-center text-slate-400 py-6">Belum ada pesanan terdaftar.</p>';
                }
            }
        } catch (e) {
            resultsList.innerHTML = '<p class="text-xs text-rose-500 text-center py-4">Gagal memuat hasil pencarian.</p>';
        }
    }

    // ----------------------------------------------------------------
    // PROSES 3.0: KELOLA STATUS & ANTAR-JEMPUT (PEMILIK & PETUGAS)
    // ----------------------------------------------------------------
    async function loadStatusOrders() {
        const container = document.getElementById('statusOrdersList');
        if (!container) return;
        container.innerHTML = '<div class="text-center py-6 text-xs text-slate-400"><i class="fa-solid fa-spinner fa-spin text-base mb-1"></i><p>Memuat data pesanan...</p></div>';

        try {
            const res = await fetch('api/pesanan.php');
            const json = await res.json();
            if (json.success && json.data) {
                allOrdersCache = json.data;
                renderFilteredStatusOrders();
            }
        } catch (e) {
            container.innerHTML = '<p class="text-xs text-rose-500 text-center py-4">Gagal memuat status pesanan.</p>';
        }
    }

    function filterStatusTab(status) {
        currentFilterTab = status;
        document.querySelectorAll('.status-filter-btn').forEach(btn => {
            if (btn.dataset.filter === status) {
                btn.className = 'status-filter-btn active px-3 py-1.5 rounded-xl font-semibold bg-blue-600 text-white shadow-xs whitespace-nowrap active:scale-95';
            } else {
                btn.className = 'status-filter-btn px-3 py-1.5 rounded-xl font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap active:scale-95';
            }
        });
        renderFilteredStatusOrders();
    }

    function renderFilteredStatusOrders() {
        const container = document.getElementById('statusOrdersList');
        let filtered = allOrdersCache;

        if (currentFilterTab !== 'all') {
            filtered = allOrdersCache.filter(o => o.status_pengerjaan === currentFilterTab);
        }

        if (filtered.length === 0) {
            container.innerHTML = '<div class="p-6 text-center text-xs text-slate-400">Tidak ada pesanan dalam status ini.</div>';
            return;
        }

        container.innerHTML = filtered.map(order => createOrderCardHtml(order, 'manage')).join('');
    }

    // Helper Pembuat Card Pesanan HTML
    function createOrderCardHtml(order, mode) {
        const statusColors = {
            'Diterima': 'bg-blue-100 text-blue-700 border-blue-200',
            'Dicuci': 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'Disetrika': 'bg-purple-100 text-purple-700 border-purple-200',
            'Siap Diambil/Diantar': 'bg-amber-100 text-amber-700 border-amber-200',
            'Selesai': 'bg-emerald-100 text-emerald-700 border-emerald-200'
        };
        const colorClass = statusColors[order.status_pengerjaan] || 'bg-slate-100 text-slate-700';

        return `
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                        ${escapeHtml(order.no_pesanan)}
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border ${colorClass}">
                        ${escapeHtml(order.status_pengerjaan)}
                    </span>
                </div>

                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">${escapeHtml(order.nama_pelanggan)}</h4>
                        <p class="text-[11px] text-slate-500">${escapeHtml(order.jenis_layanan)} • ${order.berat} kg • ${escapeHtml(order.lama_pengerjaan)}</p>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Rp ${Number(order.total_harga).toLocaleString('id-ID')}</span>
                </div>

                ${order.status_antar_jemput ? `
                    <div class="flex items-center space-x-1.5 text-[10px] text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200">
                        <i class="fa-solid fa-truck text-[9px]"></i>
                        <span>Antar-Jemput: <strong>${escapeHtml(order.status_antar_jemput)}</strong></span>
                    </div>
                ` : ''}

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <button onclick="openDetailModal(${order.id})" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px] flex items-center space-x-1 active:scale-95 transition">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Lihat Detail</span>
                    </button>

                    <div class="flex space-x-1.5">
                        <button onclick="openUbahStatusModal(${order.id}, 'pengerjaan', '${order.status_pengerjaan}')" 
                                class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-2.5 py-1 rounded-lg text-[11px] font-semibold transition flex items-center space-x-1 active:scale-95">
                            <i class="fa-solid fa-pen"></i>
                            <span>Status</span>
                        </button>

                        <button onclick="openUbahStatusModal(${order.id}, 'antar_jemput', '${order.status_antar_jemput || 'Dijadwalkan'}')" 
                                class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg text-[11px] font-semibold transition flex items-center space-x-1 active:scale-95">
                            <i class="fa-solid fa-truck-fast"></i>
                            <span>Antar/Jemput</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // Modal Ubah Status Pengerjaan & Antar-Jemput (Validasi & Konfirmasi Sesuai DFD)
    function openUbahStatusModal(pesananId, tipe, currentVal) {
        document.getElementById('ubahStatusPesananId').value = pesananId;
        document.getElementById('ubahStatusTipe').value = tipe;

        const boxPengerjaan = document.getElementById('boxPilihanPengerjaan');
        const boxAJ = document.getElementById('boxPilihanAntarJemput');

        if (tipe === 'pengerjaan') {
            document.getElementById('ubahStatusModalTitle').innerText = 'Ubah Status Pengerjaan (D2)';
            document.getElementById('ubahStatusModalSubtitle').innerText = 'Pilih tahapan pengerjaan baru untuk dicatat ke D7';
            boxPengerjaan.classList.remove('hidden');
            boxAJ.classList.add('hidden');

            const container = document.getElementById('radioStatusOptions');
            container.innerHTML = STATUS_PENGERJAAN_BAKU.map(st => `
                <label class="flex items-center justify-between p-2 rounded-xl border ${st === currentVal ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200'} cursor-pointer hover:bg-slate-50 transition">
                    <span class="font-medium text-slate-700">${st}</span>
                    <input type="radio" name="optStatusPengerjaan" value="${st}" ${st === currentVal ? 'checked' : ''} class="w-4 h-4 text-blue-600">
                </label>
            `).join('');
        } else {
            document.getElementById('ubahStatusModalTitle').innerText = 'Ubah Status Antar-Jemput (D5)';
            document.getElementById('ubahStatusModalSubtitle').innerText = 'Pilih tahapan layanan antar-jemput untuk dicatat ke D7';
            boxPengerjaan.classList.add('hidden');
            boxAJ.classList.remove('hidden');

            const container = document.getElementById('radioAJOptions');
            container.innerHTML = STATUS_AJ_BAKU.map(st => `
                <label class="flex items-center justify-between p-2 rounded-xl border ${st === currentVal ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200'} cursor-pointer hover:bg-slate-50 transition">
                    <span class="font-medium text-slate-700">${st}</span>
                    <input type="radio" name="optStatusAJ" value="${st}" ${st === currentVal ? 'checked' : ''} class="w-4 h-4 text-emerald-600">
                </label>
            `).join('');
        }

        document.getElementById('modalUbahStatus').classList.remove('hidden');
    }

    function closeUbahStatusModal() {
        document.getElementById('modalUbahStatus').classList.add('hidden');
    }

    async function submitPerubahanStatus() {
        const pesananId = document.getElementById('ubahStatusPesananId').value;
        const tipe = document.getElementById('ubahStatusTipe').value;
        const btn = document.getElementById('btnSubmitStatusFinal');

        let payload = { pesanan_id: pesananId };

        if (tipe === 'pengerjaan') {
            const selected = document.querySelector('input[name="optStatusPengerjaan"]:checked');
            if (!selected) {
                showToast('Silakan pilih salah satu status pengerjaan.', 'warning');
                return;
            }
            payload.action = 'update_pengerjaan';
            payload.status_pengerjaan = selected.value;
        } else {
            const selected = document.querySelector('input[name="optStatusAJ"]:checked');
            if (!selected) {
                showToast('Silakan pilih salah satu status antar-jemput.', 'warning');
                return;
            }
            payload.action = 'update_antar_jemput';
            payload.status_antar_jemput = selected.value;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const res = await fetch('api/status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.success) {
                closeUbahStatusModal();
                showToast(result.message || 'Status berhasil diperbarui!', 'success', 'Tercatat di D7');
                loadStatusOrders();
                if (document.getElementById('inputPencarian').value.trim()) {
                    searchPesanan(document.getElementById('inputPencarian').value.trim());
                }
            } else {
                showToast('Gagal mengubah status: ' + (result.message || 'Error'), 'error');
            }
        } catch (e) {
            showToast('Kesalahan jaringan saat mengubah status.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Konfirmasi Ubah';
        }
    }

    // Modal Detail Lengkap Pesanan
    async function openDetailModal(orderId) {
        try {
            const res = await fetch(`api/pesanan.php?action=detail&id=${orderId}`);
            const json = await res.json();

            if (json.success && json.data) {
                const o = json.data;
                document.getElementById('detNoPesanan').innerText = o.no_pesanan;
                document.getElementById('detNamaPelanggan').innerText = o.nama_pelanggan;
                document.getElementById('detNoHp').innerText = o.no_hp;
                document.getElementById('detAlamat').innerText = o.alamat_pelanggan;
                document.getElementById('detTanggalTitip').innerText = o.tanggal_titip;
                document.getElementById('detJenisLayanan').innerText = o.jenis_layanan;
                document.getElementById('detBerat').innerText = o.berat;
                document.getElementById('detLamaPengerjaan').innerText = o.lama_pengerjaan;
                document.getElementById('detTotalHarga').innerText = 'Rp ' + Number(o.total_harga).toLocaleString('id-ID');
                document.getElementById('detStatusPengerjaan').innerText = o.status_pengerjaan;
                document.getElementById('detWaktuPembaruan').innerText = o.waktu_pembaruan;

                // Rincian Item (D3)
                const secD3 = document.getElementById('detSectionD3');
                const listD3 = document.getElementById('detListD3');
                if (o.rincian_item && o.rincian_item.length > 0) {
                    secD3.classList.remove('hidden');
                    listD3.innerHTML = o.rincian_item.map(item => `
                        <li class="py-1 flex justify-between">
                            <span><strong>${escapeHtml(item.jenis)}</strong> (${item.jumlah}x)</span>
                            <span class="text-slate-500">${escapeHtml(item.ciri_barang || '-')}</span>
                        </li>
                    `).join('');
                } else {
                    secD3.classList.add('hidden');
                }

                // Antar-Jemput (D5)
                const secD5 = document.getElementById('detSectionD5');
                if (o.antar_jemput) {
                    secD5.classList.remove('hidden');
                    document.getElementById('detAJJenis').innerText = o.antar_jemput.jenis_layanan;
                    document.getElementById('detAJAlamat').innerText = o.antar_jemput.alamat;
                    document.getElementById('detAJStatus').innerText = o.antar_jemput.status;
                } else {
                    secD5.classList.add('hidden');
                }

                // Riwayat Status (D7)
                const listD7 = document.getElementById('detListD7');
                if (o.riwayat_status && o.riwayat_status.length > 0) {
                    listD7.innerHTML = o.riwayat_status.map(rw => `
                        <div class="border-l-2 border-blue-500 pl-2 py-0.5">
                            <p class="font-semibold text-slate-800">${escapeHtml(rw.status)}</p>
                            <p class="text-[10px] text-slate-400">${rw.waktu_pembaruan} • Oleh: ${escapeHtml(rw.diubah_oleh)}</p>
                        </div>
                    `).join('');
                } else {
                    listD7.innerHTML = '<p class="text-slate-400">Belum ada riwayat perubahan.</p>';
                }

                document.getElementById('modalDetailPesanan').classList.remove('hidden');
            } else {
                showToast('Pesanan tidak ditemukan.', 'warning');
            }
        } catch (e) {
            showToast('Gagal mengambil detail pesanan.', 'error');
        }
    }

    function closeDetailModal() {
        document.getElementById('modalDetailPesanan').classList.add('hidden');
    }

    // ----------------------------------------------------------------
    // PROSES 5.0: KELOLA CUCIAN TANPA PEMILIK (KHUSUS PEMILIK)
    // ----------------------------------------------------------------
    async function loadCucianHilangList() {
        const container = document.getElementById('cucianHilangList');
        if (!container) return;
        container.innerHTML = '<div class="text-center py-4 text-xs text-slate-400"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';

        try {
            const res = await fetch('api/cucian_hilang.php');
            const json = await res.json();

            if (json.success && json.data) {
                if (json.data.length === 0) {
                    container.innerHTML = '<p class="text-xs text-slate-400 text-center py-4">Belum ada data cucian tanpa pemilik.</p>';
                    return;
                }

                container.innerHTML = json.data.map(item => `
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs space-y-2">
                        <div class="flex items-start space-x-3">
                            ${item.foto ? `
                                <img src="uploads/${escapeHtml(item.foto)}" alt="Foto Cucian" class="w-16 h-16 object-cover rounded-xl border border-slate-200 flex-shrink-0">
                            ` : `
                                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            `}
                            <div class="flex-1 text-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] text-slate-400">${item.waktu_ditemukan}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${item.status === 'Diserahkan' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">
                                        ${item.status}
                                    </span>
                                </div>
                                <p class="text-slate-700 font-medium leading-relaxed">${escapeHtml(item.deskripsi_barang)}</p>
                                ${item.waktu_diserahkan ? `
                                    <p class="text-[10px] text-emerald-600 mt-1">Diserahkan pada: ${item.waktu_diserahkan}</p>
                                ` : ''}
                            </div>
                        </div>

                        ${item.status === 'Ditemukan' ? `
                            <div class="pt-2 border-t border-slate-100 text-right">
                                <button onclick="updateStatusCucian(${item.id}, 'Diserahkan')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-xl text-xs font-semibold transition flex items-center space-x-1 ml-auto active:scale-95">
                                    <i class="fa-solid fa-hand-holding-hand"></i>
                                    <span>Tandai Diserahkan</span>
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `).join('');
            }
        } catch (e) {
            container.innerHTML = '<p class="text-xs text-rose-500 text-center py-2">Gagal memuat daftar cucian.</p>';
        }
    }

    async function submitCucianHilang() {
        const deskripsi = document.getElementById('inputDeskripsiCucian').value.trim();
        const fileInput = document.getElementById('inputFotoCucian');
        const btn = document.getElementById('btnSubmitCucianHilang');

        if (!deskripsi) {
            showToast('Deskripsi barang cucian tanpa pemilik wajib diisi.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'tambah');
        formData.append('deskripsi_barang', deskripsi);
        if (fileInput.files.length > 0) {
            formData.append('foto', fileInput.files[0]);
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const res = await fetch('api/cucian_hilang.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();

            if (json.success) {
                document.getElementById('formCucianHilang').reset();
                loadCucianHilangList();
                showToast('Cucian tanpa pemilik berhasil disimpan ke Data Store D4.', 'success');
            } else {
                showToast('Gagal: ' + (json.message || 'Error'), 'error');
            }
        } catch (e) {
            showToast('Kesalahan server saat menyimpan cucian tanpa pemilik.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> <span>Simpan ke Data Store D4</span>';
        }
    }

    function updateStatusCucian(id, statusBaru) {
        showConfirmDialog({
            title: 'Konfirmasi Penyerahan Barang',
            message: `Apakah Anda yakin ingin mengubah status cucian ini menjadi "${statusBaru}"?`,
            iconClass: 'fa-handshake',
            iconColor: 'text-emerald-600',
            iconBg: 'bg-emerald-50',
            confirmBtnClass: 'bg-emerald-600 hover:bg-emerald-700',
            confirmText: 'Ya, Tandai Diserahkan',
            onConfirm: async () => {
                const formData = new FormData();
                formData.append('action', 'update_status');
                formData.append('id', id);
                formData.append('status', statusBaru);

                try {
                    const res = await fetch('api/cucian_hilang.php', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();
                    if (json.success) {
                        showToast(`Status barang berhasil diubah ke "${statusBaru}".`, 'success');
                        loadCucianHilangList();
                    } else {
                        showToast('Gagal: ' + json.message, 'error');
                    }
                } catch (e) {
                    showToast('Kesalahan saat memperbarui status cucian.', 'error');
                }
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>

</body>
</html>
