<?php
session_start();
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user']['id'];
$wisataId = $data['wisata_id'];

try {
    // Cek apakah sudah ada di favorites
    $checkStmt = $conn->prepare("
        SELECT id_favorite FROM user_favorites 
        WHERE user_id = :user_id AND wisata_id = :wisata_id
    ");
    $checkStmt->execute([
        ':user_id' => $userId,
        ':wisata_id' => $wisataId
    ]);
    
    $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        // Jika sudah ada, hapus dari favorites
        $deleteStmt = $conn->prepare("
            DELETE FROM user_favorites 
            WHERE user_id = :user_id AND wisata_id = :wisata_id
        ");
        $deleteStmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Jika belum ada, tambahkan ke favorites
        $insertStmt = $conn->prepare("
            INSERT INTO user_favorites (user_id, wisata_id) 
            VALUES (:user_id, :wisata_id)
        ");
        $insertStmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>