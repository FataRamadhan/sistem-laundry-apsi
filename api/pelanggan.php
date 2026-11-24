<?php
// api/pelanggan.php - Pengelolaan & Pencarian Data Pelanggan (D1 Pelanggan)
// Digunakan pada Proses 1.0 (Catat Pesanan) untuk auto-fill pelanggan lama
require_once __DIR__ . '/../config.php';

// Verifikasi hak akses: Hanya Pemilik yang mencatat pesanan & mengelola D1
$user = requireRole('Pemilik');
$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? $_GET['no_hp'] ?? '');

    if (empty($search)) {
        // Ambil daftar pelanggan terbaru (limit 20)
        $stmt = $pdo->query("SELECT id, nama, no_hp, alamat, created_at FROM pelanggan ORDER BY id DESC LIMIT 20");
        $pelanggan = $stmt->fetchAll();
        sendJsonResponse([
            'success' => true,
            'data'    => $pelanggan
        ]);
    } else {
        // Cari berdasarkan nomor HP persis atau substring nama
        $stmt = $pdo->prepare("SELECT id, nama, no_hp, alamat, created_at FROM pelanggan WHERE no_hp = :no_hp OR nama LIKE :nama LIMIT 5");
        $stmt->execute([
            ':no_hp' => $search,
            ':nama'  => '%' . $search . '%'
        ]);
        $pelanggan = $stmt->fetchAll();

        sendJsonResponse([
            'success' => true,
            'data'    => $pelanggan
        ]);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Metode HTTP tidak didukung.'], 405);
}
