<?php
session_start();
require '../includes/db.php';
require '../includes/function.php';

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login']);
    exit;
}

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['wisata_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID Wisata tidak valid']);
    exit;
}

$userId = $_SESSION['user']['id'];
$wisataId = $data['wisata_id'];

// Tambahkan ke favorites
$result = addToFavorites($conn, $userId, $wisataId);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke favorites']);
} else {
    echo json_encode(['success' => false, 'message' => 'Wisata sudah ada di favorites']);
}
?>