<?php
// api/status.php - Proses 3.0: Kelola Status Pengerjaan & Antar-Jemput (Pemilik & Petugas Operasional)
require_once __DIR__ . '/../config.php';

$pdo = getDBConnection();
// Verifikasi Hak Akses Aktor (Proses 3.1): Pemilik dan Petugas Operasional berwenang
$currentUser = requireRole(['Pemilik', 'Petugas Operasional']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Metode HTTP tidak didukung.'], 405);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?? $_POST;

$action = $input['action'] ?? $_GET['action'] ?? 'update_pengerjaan';
$pesananId = isset($input['pesanan_id']) ? (int)$input['pesanan_id'] : 0;

if ($pesananId <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'ID Pesanan tidak valid.'], 400);
}

// Cek apakah pesanan ada
$stmtCheck = $pdo->prepare("SELECT id, no_pesanan, status_pengerjaan FROM pesanan WHERE id = ?");
$stmtCheck->execute([$pesananId]);
$pesanan = $stmtCheck->fetch();

if (!$pesanan) {
    sendJsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
}

$waktuSekarang = date('Y-m-d H:i:s');
$diubahOleh = $currentUser['username'] . ' (' . $currentUser['peran'] . ')';

// --------------------------------------------------------------------
// A. Pembaruan Status Pengerjaan (D2 & D7)
// Status Baku: Diterima, Dicuci, Disetrika, Siap Diambil/Diantar, Selesai
// --------------------------------------------------------------------
if ($action === 'update_pengerjaan') {
    $statusBaku = ['Diterima', 'Dicuci', 'Disetrika', 'Siap Diambil/Diantar', 'Selesai'];
    $statusBaru = trim($input['status_pengerjaan'] ?? '');

    if (!in_array($statusBaru, $statusBaku)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Status pengerjaan tidak valid. Pilihan baku: ' . implode(', ', $statusBaku)
        ], 400);
    }

    try {
        $pdo->beginTransaction();

        // 1. Simpan status dan waktu pembaruan ke D2 Pesanan
        $stmtUpd = $pdo->prepare("UPDATE pesanan SET status_pengerjaan = ?, waktu_pembaruan = ? WHERE id = ?");
        $stmtUpd->execute([$statusBaru, $waktuSekarang, $pesananId]);

        // 2. Catat riwayat perubahan ke D7 Riwayat Status
        $keteranganD7 = 'Status pengerjaan diubah ke: ' . $statusBaru;
        $stmtD7 = $pdo->prepare("INSERT INTO riwayat_status (pesanan_id, status, waktu_pembaruan, diubah_oleh) VALUES (?, ?, ?, ?)");
        $stmtD7->execute([$pesananId, $keteranganD7, $waktuSekarang, $diubahOleh]);

        $pdo->commit();

        sendJsonResponse([
            'success' => true,
            'message' => 'Status pengerjaan berhasil diperbarui menjadi "' . $statusBaru . '".',
            'data'    => [
                'pesanan_id'        => $pesananId,
                'status_pengerjaan' => $statusBaru,
                'waktu_pembaruan'   => $waktuSekarang,
                'diubah_oleh'       => $diubahOleh
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse([
            'success' => false,
            'message' => 'Gagal memperbarui status pengerjaan: ' . $e->getMessage()
        ], 500);
    }
}

// --------------------------------------------------------------------
// B. Pembaruan Status Antar-Jemput (D5 & D7)
// Status Baku: Dijadwalkan, Dijemput, Diantar, Selesai
// --------------------------------------------------------------------
elseif ($action === 'update_antar_jemput') {
    $statusBakuAJ = ['Dijadwalkan', 'Dijemput', 'Diantar', 'Selesai'];
    $statusBaruAJ = trim($input['status_antar_jemput'] ?? '');

    if (!in_array($statusBaruAJ, $statusBakuAJ)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Status antar-jemput tidak valid. Pilihan baku: ' . implode(', ', $statusBakuAJ)
        ], 400);
    }

    try {
        $pdo->beginTransaction();

        // Cek data di D5 Antar-Jemput
        $stmtAJ = $pdo->prepare("SELECT id FROM antar_jemput WHERE pesanan_id = ? LIMIT 1");
        $stmtAJ->execute([$pesananId]);
        $existingAJ = $stmtAJ->fetch();

        if ($existingAJ) {
            $stmtUpdAJ = $pdo->prepare("UPDATE antar_jemput SET status = ?, waktu_pembaruan = ? WHERE pesanan_id = ?");
            $stmtUpdAJ->execute([$statusBaruAJ, $waktuSekarang, $pesananId]);
        } else {
            // Jika belum ada, buat entri antar-jemput baru
            $alamatAJ = trim($input['alamat'] ?? 'Alamat Pelanggan');
            $jenisLayananAJ = trim($input['jenis_layanan'] ?? 'Antar');
            $beratAJ = floatval($input['berat'] ?? 1);
            $stmtInsAJ = $pdo->prepare("INSERT INTO antar_jemput (pesanan_id, jenis_layanan, alamat, berat_cucian, status, waktu_pembaruan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsAJ->execute([$pesananId, $jenisLayananAJ, $alamatAJ, $beratAJ, $statusBaruAJ, $waktuSekarang]);
        }

        // Catat ke D7 Riwayat Status
        $keteranganD7 = 'Status antar-jemput diubah ke: ' . $statusBaruAJ;
        $stmtD7 = $pdo->prepare("INSERT INTO riwayat_status (pesanan_id, status, waktu_pembaruan, diubah_oleh) VALUES (?, ?, ?, ?)");
        $stmtD7->execute([$pesananId, $keteranganD7, $waktuSekarang, $diubahOleh]);

        // Perbarui juga waktu pembaruan di D2 Pesanan
        $stmtUpdD2 = $pdo->prepare("UPDATE pesanan SET waktu_pembaruan = ? WHERE id = ?");
        $stmtUpdD2->execute([$waktuSekarang, $pesananId]);

        $pdo->commit();

        sendJsonResponse([
            'success' => true,
            'message' => 'Status antar-jemput berhasil diperbarui menjadi "' . $statusBaruAJ . '".',
            'data'    => [
                'pesanan_id'           => $pesananId,
                'status_antar_jemput'  => $statusBaruAJ,
                'waktu_pembaruan'      => $waktuSekarang,
                'diubah_oleh'          => $diubahOleh
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse([
            'success' => false,
            'message' => 'Gagal memperbarui status antar-jemput: ' . $e->getMessage()
        ], 500);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Aksi status tidak dikenali.'], 400);
}
