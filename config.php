<?php
// config.php - Konfigurasi Database & Helper Sistem Laundry APSI

// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database XAMPP Default
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_laundry_apsi');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

// Folder Upload Foto Cucian Tanpa Pemilik (D4)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Koneksi PDO
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal koneksi ke database: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    return $pdo;
}

// Helper Response JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Helper Mendapatkan User Login Saat Ini (D6)
function getCurrentUser() {
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

// Helper Verifikasi Login (Pemilik atau Petugas Operasional)
function requireLogin() {
    $user = getCurrentUser();
    if (!$user) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
        ], 401);
    }
    return $user;
}

// Helper Verifikasi Hak Akses Berdasarkan Peran Aktor
function requireRole($allowedRoles = []) {
    $user = requireLogin();
    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    if (!in_array($user['peran'], $allowedRoles)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Akses ditolak. Peran ' . $user['peran'] . ' tidak memiliki wewenang untuk tindakan ini.'
        ], 403);
    }
    return $user;
}

// Helper Sanitasi Input
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}
