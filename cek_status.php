<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Cek Status Laundry - Pelacakan Publik</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        primaryDark: '#1d4ed8',
                        secondary: '#0ea5e9'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <header class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 sticky top-0 z-30 shadow-md flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm text-white shadow-inner">
                    <i class="fa-solid fa-shirt text-base"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold leading-tight">LaundryCare</h1>
                    <p class="text-[11px] text-blue-100 font-medium">Pelacakan Status Pesanan (Publik)</p>
                </div>
            </div>
            <a href="index.php" class="text-xs bg-white/15 hover:bg-white/25 px-3 py-1.5 rounded-full transition font-semibold flex items-center space-x-1 active:scale-95">
                <i class="fa-solid fa-user-lock text-[10px]"></i>
                <span>Staf Login</span>
            </a>
        </div>
    </header>

    <!-- CONTENT SCROLLABLE AREA -->
    <main class="flex-1 overflow-y-auto p-4 hide-scrollbar relative bg-slate-50/50 pb-8">
        <!-- Form Pencarian Publik (Proses 4.0) -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs mb-5">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center">
                <i class="fa-solid fa-qrcode text-blue-600 mr-2"></i> Lacak Status Laundry Anda
            </h2>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Masukkan Nomor Pesanan dan Nomor HP Anda sesuai yang tertera pada Nota Laundry.
            </p>

            <form id="trackForm" onsubmit="event.preventDefault(); fetchOrderStatus();" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Pesanan</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-receipt text-sm"></i>
                        </span>
                        <input type="text" id="no_pesanan" required placeholder="Contoh: LND-20261120-001" 
                               class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase font-mono transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor HP Terdaftar</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-phone text-sm"></i>
                        </span>
                        <input type="tel" id="no_hp" required placeholder="Contoh: 081234567890" 
                               class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <button type="submit" id="btnSubmit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md shadow-blue-500/20 active:scale-[0.98] transition flex items-center justify-center space-x-2 text-xs mt-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Cek Status Sekarang</span>
                </button>
            </form>
        </div>

        <!-- Alert Pesan Error / Tidak Tersedia Sesuai DFD Revisi -->
        <div id="alertContainer" class="hidden mb-5 animate-scale-up">
            <div class="bg-rose-50 border border-rose-200 rounded-3xl p-4 text-center">
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-2 text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-sm font-bold text-rose-800" id="alertTitle">Data tidak tersedia</h3>
                <p class="text-xs text-rose-600 mt-1 leading-relaxed" id="alertDesc">
                    Nomor pesanan atau nomor HP tidak cocok dengan catatan sistem. Pastikan data yang dimasukkan benar.
                </p>
            </div>
        </div>

        <!-- Live Refresh Countdown Bar (Auto-refresh ± 30 detik) -->
        <div id="liveTrackerBar" class="hidden mb-4 bg-blue-50 border border-blue-200 rounded-2xl p-3 flex items-center justify-between text-xs text-blue-800">
            <div class="flex items-center space-x-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-600"></span>
                </span>
                <span class="font-medium">Auto Refresh: <span id="countdownText" class="font-bold">30s</span></span>
            </div>
            <button onclick="fetchOrderStatus(true);" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center space-x-1 active:scale-95 transition">
                <i class="fa-solid fa-rotate text-xs"></i>
                <span>Refresh</span>
            </button>
        </div>

        <!-- Order Result Card -->
        <div id="resultCard" class="hidden space-y-4">
            <!-- Header Ringkasan Pesanan -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-4 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200" id="resNoPesanan">
                        -
                    </span>
                    <span id="resBadgeStatus" class="text-xs font-bold px-3 py-1 rounded-full bg-blue-600 text-white shadow-xs">
                        -
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-800" id="resNamaPelanggan">-</h3>
                <p class="text-xs text-slate-500" id="resDetailLayanan">-</p>

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">Pembaruan Terakhir:</span>
                    <span class="font-semibold text-slate-700" id="resWaktuPembaruan">-</span>
                </div>
            </div>

            <!-- Progress Timeline Pengerjaan Baku Sesuai DFD -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-4 shadow-xs">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-4 flex items-center">
                    <i class="fa-solid fa-timeline text-blue-600 mr-2"></i> Tahapan Pengerjaan
                </h4>
                
                <div class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200" id="timelineList">
                    <!-- Dynamic timeline steps will be injected here -->
                </div>
            </div>

            <!-- Info Layanan Antar-Jemput (D5) Jika Ada -->
            <div id="antarJemputCard" class="hidden bg-emerald-50 border border-emerald-200 rounded-3xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center">
                        <i class="fa-solid fa-truck-fast mr-2"></i> Layanan Antar-Jemput
                    </h4>
                    <span id="ajBadgeStatus" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-600 text-white shadow-xs">
                        -
                    </span>
                </div>
                <div class="text-xs text-emerald-900 space-y-1">
                    <p><strong>Jenis Layanan:</strong> <span id="ajJenis">-</span></p>
                    <p><strong>Alamat Tujuan:</strong> <span id="ajAlamat">-</span></p>
                    <p><strong>Status Antar-Jemput:</strong> <span id="ajStatusText" class="font-bold">-</span></p>
                </div>
            </div>

            <!-- Rincian Item Cucian (D3) Jika Ada -->
            <div id="rincianItemCard" class="hidden bg-white border border-slate-200/80 rounded-3xl p-4 shadow-xs">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center">
                    <i class="fa-solid fa-list-check text-blue-600 mr-2"></i> Rincian Item Cucian (D3)
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400">
                                <th class="pb-2 font-medium">Jenis</th>
                                <th class="pb-2 font-medium text-center">Jml</th>
                                <th class="pb-2 font-medium">Ciri Barang / Merek</th>
                            </tr>
                        </thead>
                        <tbody id="rincianItemBody" class="divide-y divide-slate-100 text-slate-700">
                            <!-- Items inserted dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- SIMULATED PHONE BOTTOM HOME INDICATOR (DESKTOP PREVIEW) -->
    <div class="phone-home-indicator py-1.5 bg-white select-none flex-shrink-0 z-40">
        <div class="w-28 h-1 bg-slate-300 rounded-full mx-auto"></div>
    </div>

</div>

<script>
    // Status Baku Pengerjaan Sesuai DFD
    const STATUS_FLOW = [
        { key: 'Diterima', label: 'Diterima', icon: 'fa-box-archive' },
        { key: 'Dicuci', label: 'Dicuci', icon: 'fa-soap' },
        { key: 'Disetrika', label: 'Disetrika', icon: 'fa-wind' },
        { key: 'Siap Diambil/Diantar', label: 'Siap Diambil/Diantar', icon: 'fa-circle-check' },
        { key: 'Selesai', label: 'Selesai', icon: 'fa-handshake' }
    ];

    let timerInterval = null;
    let countdownSec = 30;

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

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-20px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

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

    // Baca parameter dari URL untuk auto-fill (misal dari QR Code nota)
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const paramNoPesanan = urlParams.get('no_pesanan');
        const paramNoHp = urlParams.get('no_hp');

        if (paramNoPesanan) {
            document.getElementById('no_pesanan').value = paramNoPesanan;
        }
        if (paramNoHp) {
            document.getElementById('no_hp').value = paramNoHp;
        }

        // Jika kedua parameter tersedia, lakukan cek status otomatis secara instan
        if (paramNoPesanan && paramNoHp) {
            fetchOrderStatus();
        }
    });

    async function fetchOrderStatus(isManualRefresh = false) {
        const noPesanan = document.getElementById('no_pesanan').value.trim();
        const noHp = document.getElementById('no_hp').value.trim();
        const alertContainer = document.getElementById('alertContainer');
        const resultCard = document.getElementById('resultCard');
        const liveTrackerBar = document.getElementById('liveTrackerBar');
        const btnSubmit = document.getElementById('btnSubmit');

        if (!noPesanan || !noHp) {
            showToast('Silakan masukkan Nomor Pesanan dan Nomor HP Anda.', 'warning', 'Data Belum Lengkap');
            return;
        }

        if (!isManualRefresh) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Memeriksa Data...</span>';
        }

        try {
            const response = await fetch(`api/cek_status.php?no_pesanan=${encodeURIComponent(noPesanan)}&no_hp=${encodeURIComponent(noHp)}`);
            const result = await response.json();

            if (result.success && result.data) {
                alertContainer.classList.add('hidden');
                renderOrderData(result.data);
                resultCard.classList.remove('hidden');
                liveTrackerBar.classList.remove('hidden');
                if (isManualRefresh) {
                    showToast('Status berhasil disegarkan secara real-time.', 'success');
                }
                startAutoRefresh();
            } else {
                // Tampilkan pesan "Data tidak tersedia" sesuai DFD revisi
                resultCard.classList.add('hidden');
                liveTrackerBar.classList.add('hidden');
                stopAutoRefresh();
                showAlert(result.message || 'Data tidak tersedia', 'Nomor Pesanan atau Nomor HP yang Anda masukkan tidak cocok dengan catatan sistem kami.');
                showToast(result.message || 'Data tidak tersedia', 'error');
            }
        } catch (error) {
            showAlert('Gagal Menghubungi Server', 'Terjadi kesalahan jaringan. Pastikan koneksi server lokal aktif.');
            showToast('Koneksi ke server lokal terputus.', 'error');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> <span>Cek Status Sekarang</span>';
        }
    }

    function showAlert(title, desc) {
        const alertContainer = document.getElementById('alertContainer');
        document.getElementById('alertTitle').innerText = title;
        document.getElementById('alertDesc').innerText = desc;
        alertContainer.classList.remove('hidden');
    }

    function renderOrderData(order) {
        document.getElementById('resNoPesanan').innerText = order.no_pesanan;
        document.getElementById('resBadgeStatus').innerText = order.status_pengerjaan;
        document.getElementById('resNamaPelanggan').innerText = order.nama_pelanggan;
        document.getElementById('resDetailLayanan').innerText = `${order.jenis_layanan} (${order.berat} kg) • ${order.lama_pengerjaan}`;
        document.getElementById('resWaktuPembaruan').innerText = order.waktu_pembaruan;

        // Render Timeline
        const currentIdx = STATUS_FLOW.findIndex(s => s.key === order.status_pengerjaan);
        const timelineEl = document.getElementById('timelineList');
        timelineEl.innerHTML = '';

        STATUS_FLOW.forEach((step, idx) => {
            const isCompleted = idx <= currentIdx;
            const isCurrent = idx === currentIdx;

            const stepDiv = document.createElement('div');
            stepDiv.className = 'relative flex items-start space-x-3';

            const dotClass = isCurrent 
                ? 'bg-blue-600 text-white ring-4 ring-blue-100 shadow' 
                : (isCompleted ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-400');

            const textClass = isCurrent
                ? 'text-blue-700 font-bold'
                : (isCompleted ? 'text-slate-800 font-medium' : 'text-slate-400');

            stepDiv.innerHTML = `
                <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full flex items-center justify-center text-[10px] ${dotClass}">
                    <i class="fa-solid ${step.icon}"></i>
                </div>
                <div>
                    <p class="text-xs ${textClass}">${step.label}</p>
                    ${isCurrent ? `<span class="inline-block text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full mt-0.5 animate-pulse">Sedang dalam tahap ini</span>` : ''}
                </div>
            `;
            timelineEl.appendChild(stepDiv);
        });

        // Render Antar-Jemput (D5)
        const ajCard = document.getElementById('antarJemputCard');
        if (order.antar_jemput) {
            ajCard.classList.remove('hidden');
            document.getElementById('ajBadgeStatus').innerText = order.antar_jemput.status;
            document.getElementById('ajJenis').innerText = order.antar_jemput.jenis_layanan;
            document.getElementById('ajAlamat').innerText = order.antar_jemput.alamat;
            document.getElementById('ajStatusText').innerText = order.antar_jemput.status;
        } else {
            ajCard.classList.add('hidden');
        }

        // Render Rincian Item (D3)
        const rincianCard = document.getElementById('rincianItemCard');
        const rincianBody = document.getElementById('rincianItemBody');
        if (order.rincian_item && order.rincian_item.length > 0) {
            rincianCard.classList.remove('hidden');
            rincianBody.innerHTML = order.rincian_item.map(item => `
                <tr class="hover:bg-slate-50">
                    <td class="py-2 font-medium">${escapeHtml(item.jenis)}</td>
                    <td class="py-2 text-center">${item.jumlah}</td>
                    <td class="py-2 text-slate-500">${escapeHtml(item.ciri_barang || '-')}</td>
                </tr>
            `).join('');
        } else {
            rincianCard.classList.add('hidden');
        }
    }

    function startAutoRefresh() {
        stopAutoRefresh();
        countdownSec = 30;
        document.getElementById('countdownText').innerText = `${countdownSec}s`;

        timerInterval = setInterval(() => {
            countdownSec--;
            if (countdownSec <= 0) {
                countdownSec = 30;
                document.getElementById('countdownText').innerText = `Memperbarui...`;
                fetchOrderStatus(true);
            } else {
                document.getElementById('countdownText').innerText = `${countdownSec}s`;
            }
        }, 1000);
    }

    function stopAutoRefresh() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
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
