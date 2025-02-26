<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $_SESSION['user']['id'];
    $wisataId = $data['wisataId'];
    $rating = $data['rating'];
    
    try {
        // Simpan/update rating pengguna
        $stmt = $conn->prepare("
            INSERT INTO user_ratings (id_user, id_wisata, rating)
            VALUES (:id_user, :id_wisata, :rating)
            ON DUPLICATE KEY UPDATE rating = :rating
        ");
        
        $stmt->execute([
            ':id_user' => $userId,
            ':id_wisata' => $wisataId,
            ':rating' => $rating
        ]);

        // Update rata-rata rating di tabel wisata
        $updatePopularity = $conn->prepare("
            UPDATE wisata
            SET popularity = (
                SELECT AVG(rating)
                FROM user_ratings
                WHERE id_wisata = :id_wisata
            )
            WHERE id_wisata = :id_wisata
        ");
        $updatePopularity->execute([':id_wisata' => $wisataId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Kesalahan update rating: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Kesalahan database']);
    }
}
?>