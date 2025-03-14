<?php
session_start();
require 'db.php';
require 'function.php';

header('Content-Type: application/json');

// Validasi user login
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$data = json_decode(file_get_contents('php://input'), true);
$wisataId = $data['wisataId'];

try {
    // Cek status favorit
    $checkStmt = $conn->prepare("SELECT id_favorite FROM user_favorites WHERE user_id = :user_id AND wisata_id = :wisata_id");
    $checkStmt->execute([
        ':user_id' => $userId,
        ':wisata_id' => $wisataId
    ]);
    $exists = $checkStmt->fetch();

    if (!$exists) {
        // Tambah ke favorit
        $insertStmt = $conn->prepare("INSERT INTO user_favorites (user_id, wisata_id, added_at) VALUES (:user_id, :wisata_id, NOW())");
        $insertStmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);

        // Track interaksi
        trackUserInteraction($conn, $userId, $wisataId, 'favorite');

        echo json_encode([
            'success' => true,
            'action' => 'added'
        ]);
    } else {
        // Hapus dari favorit
        $deleteStmt = $conn->prepare("DELETE FROM user_favorites WHERE user_id = :user_id AND wisata_id = :wisata_id");
        $deleteStmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);

        echo json_encode([
            'success' => true,
            'action' => 'removed'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>