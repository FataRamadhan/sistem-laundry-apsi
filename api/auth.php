<?php
// api/auth.php - Autentikasi Pengguna (D6 Akun Pengguna) Sesuai DFD
require_once __DIR__ . '/../config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    if ($action === 'login') {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Username dan kata sandi wajib diisi.'
            ], 400);
        }

        // Verifikasi akun ke D6 Akun Pengguna
        $stmt = $pdo->prepare("SELECT id, username, password, peran FROM akun_pengguna WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'       => (int)$user['id'],
                'username' => $user['username'],
                'peran'    => $user['peran']
            ];

            sendJsonResponse([
                'success' => true,
                'message' => 'Login berhasil sebagai ' . $user['peran'],
                'data'    => [
                    'id'       => (int)$user['id'],
                    'username' => $user['username'],
                    'peran'    => $user['peran']
                ]
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Username atau kata sandi tidak cocok.'
            ], 401);
        }
    } elseif ($action === 'logout') {
        $_SESSION = [];
        if (session_id() !== '') {
            session_destroy();
        }
        sendJsonResponse([
            'success' => true,
            'message' => 'Berhasil logout.'
        ]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Aksi POST tidak valid.'], 400);
    }
} elseif ($method === 'GET') {
    // Cek status sesi login saat ini
    $user = getCurrentUser();
    if ($user) {
        sendJsonResponse([
            'success'       => true,
            'authenticated' => true,
            'data'          => $user
        ]);
    } else {
        sendJsonResponse([
            'success'       => true,
            'authenticated' => false,
            'data'          => null
        ]);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Metode HTTP tidak didukung.'], 405);
}
