<?php
// api/cek_status.php - Proses 4.0: Lihat Status Pesanan (Pelanggan - Publik Tanpa Login)
require_once __DIR__ . '/../config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Menerima input via GET atau POST
$noPesanan = trim($_REQUEST['no_pesanan'] ?? '');
$noHp = trim($_REQUEST['no_hp'] ?? '');

if (empty($noPesanan) || empty($noHp)) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Nomor Pesanan dan Nomor HP wajib diisi.'
    ], 400);
}

// Format nomor HP agar perbandingan fleksibel (menghilangkan spasi dan tanda minus)
$cleanHp = preg_replace('/[^0-9]/', '', $noHp);

// Verifikasi kecocokan data ke D1 Pelanggan dan D2 Pesanan (Proses 4.2)
$sql = "SELECT p.id, p.no_pesanan, p.tanggal_titip, p.berat, p.jenis_layanan,
               p.lama_pengerjaan, p.total_harga, p.status_pengerjaan, p.waktu_pembaruan,
               c.nama AS nama_pelanggan, c.no_hp, c.alamat AS alamat_pelanggan
        FROM pesanan p
        JOIN pelanggan c ON p.pelanggan_id = c.id
        WHERE p.no_pesanan = :no_pesanan
          AND (c.no_hp = :no_hp OR REPLACE(REPLACE(c.no_hp, '-', ''), ' ', '') = :clean_hp)
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':no_pesanan' => $noPesanan,
    ':no_hp'      => $noHp,
    ':clean_hp'   => $cleanHp
]);

$pesanan = $stmt->fetch();

// Bila data tidak cocok, kirimkan pesan baku sesuai DFD: "Data tidak tersedia"
if (!$pesanan) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Data tidak tersedia'
    ], 404);
}

// Ambil D3: Rincian Item Cucian
$stmtD3 = $pdo->prepare("SELECT jenis, jumlah, ciri_barang FROM rincian_item WHERE pesanan_id = ?");
$stmtD3->execute([$pesanan['id']]);
$pesanan['rincian_item'] = $stmtD3->fetchAll();

// Ambil D5: Status Antar-Jemput (jika ada)
$stmtD5 = $pdo->prepare("SELECT jenis_layanan, alamat, berat_cucian, status, waktu_pembaruan FROM antar_jemput WHERE pesanan_id = ? LIMIT 1");
$stmtD5->execute([$pesanan['id']]);
$pesanan['antar_jemput'] = $stmtD5->fetch() ?: null;

// Ambil D7: Riwayat Perubahan Status untuk Timeline
$stmtD7 = $pdo->prepare("SELECT status, waktu_pembaruan FROM riwayat_status WHERE pesanan_id = ? ORDER BY id ASC");
$stmtD7->execute([$pesanan['id']]);
$pesanan['riwayat_status'] = $stmtD7->fetchAll();

// Kembalikan data lengkap untuk tampilan pelanggan
sendJsonResponse([
    'success' => true,
    'message' => 'Data status pesanan ditemukan.',
    'data'    => $pesanan
]);
