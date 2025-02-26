<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_SESSION['user']['id'];
    $wisataId = $_GET['wisataId'];
    
    try {
        $stmt = $conn->prepare("
            SELECT rating 
            FROM user_ratings 
            WHERE id_user = :id_user AND id_wisata = :id_wisata
        ");
        
        $stmt->execute([
            ':id_user' => $userId,
            ':id_wisata' => $wisataId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'rating' => $result ? $result['rating'] : 0
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}
?>