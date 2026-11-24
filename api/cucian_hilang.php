<?php
// api/cucian_hilang.php - Proses 5.0: Kelola Cucian Tanpa Pemilik (Khusus Pemilik)
require_once __DIR__ . '/../config.php';

$pdo = getDBConnection();
// Verifikasi Hak Akses: Hanya Pemilik yang berhak mengelola D4
$currentUser = requireRole('Pemilik');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Ambil daftar cucian tanpa pemilik dari D4
    $stmt = $pdo->query("SELECT id, deskripsi_barang, foto, status, waktu_ditemukan, waktu_diserahkan FROM cucian_tanpa_pemilik ORDER BY id DESC");
    $items = $stmt->fetchAll();

    sendJsonResponse([
        'success' => true,
        'data'    => $items
    ]);
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'tambah';

    // 1. Tambah Data Cucian Tanpa Pemilik (Deskripsi + Upload Foto)
    if ($action === 'tambah') {
        $deskripsi = trim($_POST['deskripsi_barang'] ?? '');

        if (empty($deskripsi)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Deskripsi barang cucian tanpa pemilik wajib diisi.'
            ], 400);
        }

        $fotoNama = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['foto']['tmp_name'];
            $fileName      = $_FILES['foto']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Format file foto harus berupa JPG, PNG, atau WEBP.'
                ], 400);
            }

            // Generate nama file unik
            $newFileName = 'cucian_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
            $destPath = UPLOAD_DIR . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $fotoNama = $newFileName;
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Gagal mengunggah foto.'
                ], 500);
            }
        }

        $statusAwal = 'Ditemukan';
        $waktuDitemukan = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO cucian_tanpa_pemilik (deskripsi_barang, foto, status, waktu_ditemukan) VALUES (?, ?, ?, ?)");
        $stmt->execute([$deskripsi, $fotoNama, $statusAwal, $waktuDitemukan]);

        sendJsonResponse([
            'success' => true,
            'message' => 'Data cucian tanpa pemilik berhasil disimpan.',
            'data'    => [
                'id'               => (int)$pdo->lastInsertId(),
                'deskripsi_barang' => $deskripsi,
                'foto'             => $fotoNama,
                'status'           => $statusAwal,
                'waktu_ditemukan'  => $waktuDitemukan
            ]
        ], 201);
    }

    // 2. Ubah Status Cucian (Ditemukan / Diserahkan)
    elseif ($action === 'update_status') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $statusBaru = trim($_POST['status'] ?? '');

        if ($id <= 0 || !in_array($statusBaru, ['Ditemukan', 'Diserahkan'])) {
            sendJsonResponse([
                'success' => false,
                'message' => 'ID atau status tidak valid (Pilihan: Ditemukan / Diserahkan).'
            ], 400);
        }

        $waktuDiserahkan = ($statusBaru === 'Diserahkan') ? date('Y-m-d H:i:s') : null;

        $stmt = $pdo->prepare("UPDATE cucian_tanpa_pemilik SET status = ?, waktu_diserahkan = ? WHERE id = ?");
        $stmt->execute([$statusBaru, $waktuDiserahkan, $id]);

        sendJsonResponse([
            'success' => true,
            'message' => 'Status cucian berhasil diubah menjadi "' . $statusBaru . '".',
            'data'    => [
                'id'               => $id,
                'status'           => $statusBaru,
                'waktu_diserahkan' => $waktuDiserahkan
            ]
        ]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Aksi POST tidak valid.'], 400);
    }
}

sendJsonResponse(['success' => false, 'message' => 'Metode HTTP tidak didukung.'], 405);
