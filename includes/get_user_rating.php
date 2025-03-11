<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$wisataId = $_GET['wisataId'] ?? null;

if (!$wisataId) {
    echo json_encode(['success' => false, 'message' => 'Invalid wisata ID']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT ratings_data 
        FROM user_ratings 
        WHERE id_user = :user_id
        ORDER BY updated_at DESC
        LIMIT 1
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $ratingsData = json_decode($result['ratings_data'], true);
        $rating = $ratingsData[$wisataId] ?? 0;
        
        echo json_encode([
            'success' => true, 
            'rating' => $rating
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'rating' => 0
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
