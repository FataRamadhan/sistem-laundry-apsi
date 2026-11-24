<?php
// api/pesanan.php - Backend API untuk Proses 1.0 (Catat Pesanan) & Proses 2.0 (Cari Pesanan)
require_once __DIR__ . '/../config.php';

$pdo = getDBConnection();
$currentUser = requireLogin();
$method = $_SERVER['REQUEST_METHOD'];

// Helper kalkulasi harga standar
function calculateTotalPrice($berat, $jenisLayanan, $lamaPengerjaan) {
    $basePrice = 7000;
    if ($jenisLayanan === 'Cuci Kering') {
        $basePrice = 5000;
    } elseif ($jenisLayanan === 'Setrika Saja') {
        $basePrice = 4500;
    }

    $extraPerKg = 0;
    if (strpos($lamaPengerjaan, 'Kilat') !== false) {
        $extraPerKg = 3000;
    } elseif (strpos($lamaPengerjaan, 'Express') !== false) {
        $extraPerKg = 6000;
    }

    return round($berat * ($basePrice + $extraPerKg));
}

// --------------------------------------------------------------------
// PROSES 1.0: CATAT PESANAN (KHUSUS PEMILIK)
// --------------------------------------------------------------------
if ($method === 'POST') {
    // Verifikasi hak akses: Hanya Pemilik yang boleh mencatat pesanan
    if ($currentUser['peran'] !== 'Pemilik') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Akses ditolak. Hanya Pemilik yang berhak mencatat pesanan baru.'
        ], 403);
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    // Validasi data wajib
    $namaPelanggan  = trim($input['nama'] ?? '');
    $noHp           = trim($input['no_hp'] ?? '');
    $alamat         = trim($input['alamat'] ?? '');
    $tanggalTitip   = trim($input['tanggal_titip'] ?? date('Y-m-d'));
    $berat          = floatval($input['berat'] ?? 0);
    $jenisLayanan   = trim($input['jenis_layanan'] ?? 'Cuci Komplit');
    $lamaPengerjaan = trim($input['lama_pengerjaan'] ?? 'Reguler (2 Hari)');
    $rincianItem    = $input['rincian_item'] ?? []; // Array D3 opsional
    $antarJemput    = $input['antar_jemput'] ?? null; // D5 opsional

    if (empty($namaPelanggan) || empty($noHp) || empty($alamat)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Data pelanggan (Nama, No. HP, Alamat) wajib diisi lengkap.'
        ], 400);
    }

    if ($berat <= 0) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Berat cucian harus lebih besar dari 0 kg.'
        ], 400);
    }

    // Hitung total harga otomatis
    $totalHarga = calculateTotalPrice($berat, $jenisLayanan, $lamaPengerjaan);

    try {
        $pdo->beginTransaction();

        // 1. Cek & Simpan ke D1 Pelanggan
        $stmtCek = $pdo->prepare("SELECT id FROM pelanggan WHERE no_hp = ? LIMIT 1");
        $stmtCek->execute([$noHp]);
        $pelanggan = $stmtCek->fetch();

        if ($pelanggan) {
            $pelangganId = $pelanggan['id'];
            // Perbarui nama atau alamat jika ada pembaruan
            $stmtUpd = $pdo->prepare("UPDATE pelanggan SET nama = ?, alamat = ? WHERE id = ?");
            $stmtUpd->execute([$namaPelanggan, $alamat, $pelangganId]);
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO pelanggan (nama, no_hp, alamat) VALUES (?, ?, ?)");
            $stmtIns->execute([$namaPelanggan, $noHp, $alamat]);
            $pelangganId = $pdo->lastInsertId();
        }

        // 2. Generate Nomor Pesanan Unik: LND-YYYYMMDD-XXXX
        $dateCode = date('Ymd');
        $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM pesanan WHERE tanggal_titip = ?");
        $stmtCount->execute([$tanggalTitip]);
        $nextNum = ((int)$stmtCount->fetch()['total']) + 1;
        $noPesanan = sprintf('LND-%s-%03d', date('Ymd', strtotime($tanggalTitip)), $nextNum);

        // Pastikan nomor pesanan benar-benar unik
        $stmtCheckNo = $pdo->prepare("SELECT id FROM pesanan WHERE no_pesanan = ?");
        $stmtCheckNo->execute([$noPesanan]);
        if ($stmtCheckNo->fetch()) {
            $noPesanan = 'LND-' . date('Ymd') . '-' . substr(uniqid(), -4);
        }

        // 3. Simpan ke D2 Pesanan dengan status awal otomatis "Diterima"
        $statusAwal = 'Diterima';
        $waktuPembaruan = date('Y-m-d H:i:s');
        $stmtPesanan = $pdo->prepare("INSERT INTO pesanan (
            no_pesanan, pelanggan_id, tanggal_titip, berat, jenis_layanan, 
            lama_pengerjaan, total_harga, status_pengerjaan, waktu_pembaruan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtPesanan->execute([
            $noPesanan, $pelangganId, $tanggalTitip, $berat, $jenisLayanan,
            $lamaPengerjaan, $totalHarga, $statusAwal, $waktuPembaruan
        ]);
        $pesananId = $pdo->lastInsertId();

        // 4. Simpan ke D3 Rincian Item Cucian (opsional)
        if (is_array($rincianItem) && count($rincianItem) > 0) {
            $stmtItem = $pdo->prepare("INSERT INTO rincian_item (pesanan_id, jenis, jumlah, ciri_barang) VALUES (?, ?, ?, ?)");
            foreach ($rincianItem as $item) {
                $jenisItem = trim($item['jenis'] ?? '');
                $jumlahItem = intval($item['jumlah'] ?? 1);
                $ciriBarang = trim($item['ciri_barang'] ?? '');
                if (!empty($jenisItem)) {
                    $stmtItem->execute([$pesananId, $jenisItem, max(1, $jumlahItem), $ciriBarang]);
                }
            }
        }

        // 5. Simpan ke D5 Antar-Jemput (jika dipilih)
        if ($antarJemput && !empty($antarJemput['aktif'])) {
            $layananAJ = trim($antarJemput['jenis_layanan'] ?? 'Antar');
            $alamatAJ = trim($antarJemput['alamat'] ?? $alamat);
            $statusAJ = 'Dijadwalkan';
            $stmtAJ = $pdo->prepare("INSERT INTO antar_jemput (pesanan_id, jenis_layanan, alamat, berat_cucian, status, waktu_pembaruan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtAJ->execute([$pesananId, $layananAJ, $alamatAJ, $berat, $statusAJ, $waktuPembaruan]);
        }

        // 6. Catat Status Awal ke D7 Riwayat Status
        $diubahOleh = $currentUser['username'] . ' (' . $currentUser['peran'] . ')';
        $keteranganD7 = 'Status pengerjaan diatur ke: Diterima';
        $stmtD7 = $pdo->prepare("INSERT INTO riwayat_status (pesanan_id, status, waktu_pembaruan, diubah_oleh) VALUES (?, ?, ?, ?)");
        $stmtD7->execute([$pesananId, $keteranganD7, $waktuPembaruan, $diubahOleh]);

        $pdo->commit();

        sendJsonResponse([
            'success' => true,
            'message' => 'Pesanan berhasil dicatat.',
            'data'    => [
                'id'                => (int)$pesananId,
                'no_pesanan'        => $noPesanan,
                'nama_pelanggan'    => $namaPelanggan,
                'no_hp'             => $noHp,
                'alamat'            => $alamat,
                'tanggal_titip'     => $tanggalTitip,
                'berat'             => $berat,
                'jenis_layanan'     => $jenisLayanan,
                'lama_pengerjaan'   => $lamaPengerjaan,
                'total_harga'       => $totalHarga,
                'status_pengerjaan' => $statusAwal,
                'waktu_pembaruan'   => $waktuPembaruan
            ]
        ], 201);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse([
            'success' => false,
            'message' => 'Gagal mencatat pesanan: ' . $e->getMessage()
        ], 500);
    }
}

// --------------------------------------------------------------------
// PROSES 2.0 & LISTING: CARI PESANAN & DETAIL PESANAN
// --------------------------------------------------------------------
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $noPesanan = trim($_GET['no_pesanan'] ?? '');

    // Detail Lengkap Sebuah Pesanan (D1, D2, D3, D5, D7)
    if ($action === 'detail' || $id > 0 || !empty($noPesanan)) {
        $sql = "SELECT p.*, c.nama AS nama_pelanggan, c.no_hp, c.alamat AS alamat_pelanggan 
                FROM pesanan p 
                JOIN pelanggan c ON p.pelanggan_id = c.id ";
        if ($id > 0) {
            $stmt = $pdo->prepare($sql . " WHERE p.id = ? LIMIT 1");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare($sql . " WHERE p.no_pesanan = ? LIMIT 1");
            $stmt->execute([$noPesanan]);
        }
        $order = $stmt->fetch();

        if (!$order) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        // Ambil D3: Rincian Item
        $stmtD3 = $pdo->prepare("SELECT id, jenis, jumlah, ciri_barang FROM rincian_item WHERE pesanan_id = ?");
        $stmtD3->execute([$order['id']]);
        $order['rincian_item'] = $stmtD3->fetchAll();

        // Ambil D5: Antar-Jemput
        $stmtD5 = $pdo->prepare("SELECT id, jenis_layanan, alamat, berat_cucian, status, waktu_pembaruan FROM antar_jemput WHERE pesanan_id = ? LIMIT 1");
        $stmtD5->execute([$order['id']]);
        $order['antar_jemput'] = $stmtD5->fetch() ?: null;

        // Ambil D7: Riwayat Status
        $stmtD7 = $pdo->prepare("SELECT id, status, waktu_pembaruan, diubah_oleh FROM riwayat_status WHERE pesanan_id = ? ORDER BY id DESC");
        $stmtD7->execute([$order['id']]);
        $order['riwayat_status'] = $stmtD7->fetchAll();

        sendJsonResponse([
            'success' => true,
            'data'    => $order
        ]);
    }

    // Pencarian Pesanan (Proses 2.0)
    // Kata Kunci: Nama Pelanggan, Tanggal Titip, Ciri Barang, atau Nomor Pesanan
    $keyword = trim($_GET['q'] ?? $_GET['search'] ?? '');

    if (!empty($keyword)) {
        // Hanya Pemilik yang memiliki hak akses penuh ke Proses 2.0
        // Namun Petugas Operasional juga dapat melihat daftar pesanan untuk Proses 3.0
        $sql = "SELECT DISTINCT p.id, p.no_pesanan, p.tanggal_titip, p.berat, p.jenis_layanan,
                               p.lama_pengerjaan, p.total_harga, p.status_pengerjaan, p.waktu_pembaruan,
                               c.nama AS nama_pelanggan, c.no_hp, c.alamat AS alamat_pelanggan,
                               aj.status AS status_antar_jemput
                FROM pesanan p
                JOIN pelanggan c ON p.pelanggan_id = c.id
                LEFT JOIN rincian_item r ON r.pesanan_id = p.id
                LEFT JOIN antar_jemput aj ON aj.pesanan_id = p.id
                WHERE (c.nama LIKE ?
                   OR p.no_pesanan LIKE ?
                   OR p.tanggal_titip LIKE ?
                   OR r.ciri_barang LIKE ?)
                ORDER BY p.id DESC";

        $stmt = $pdo->prepare($sql);
        $kwTerm = '%' . $keyword . '%';
        $stmt->execute([$kwTerm, $kwTerm, $kwTerm, $kwTerm]);
        $orders = $stmt->fetchAll();

        if (empty($orders)) {
            sendJsonResponse([
                'success' => false,
                'data'    => [],
                'message' => 'Pesanan tidak ditemukan'
            ]);
        } else {
            sendJsonResponse([
                'success' => true,
                'data'    => $orders
            ]);
        }
    } else {
        // Ambil semua daftar pesanan
        $sql = "SELECT p.id, p.no_pesanan, p.tanggal_titip, p.berat, p.jenis_layanan,
                       p.lama_pengerjaan, p.total_harga, p.status_pengerjaan, p.waktu_pembaruan,
                       c.nama AS nama_pelanggan, c.no_hp, c.alamat AS alamat_pelanggan,
                       aj.status AS status_antar_jemput
                FROM pesanan p
                JOIN pelanggan c ON p.pelanggan_id = c.id
                LEFT JOIN antar_jemput aj ON aj.pesanan_id = p.id
                ORDER BY p.id DESC";
        $stmt = $pdo->query($sql);
        $orders = $stmt->fetchAll();

        sendJsonResponse([
            'success' => true,
            'data'    => $orders
        ]);
    }
}

sendJsonResponse(['success' => false, 'message' => 'Metode HTTP tidak didukung.'], 405);
