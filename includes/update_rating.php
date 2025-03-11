<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$data = json_decode(file_get_contents('php://input'), true);
$wisataId = $data['wisataId'] ?? null;
$rating = $data['rating'] ?? null;

if (!$wisataId) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT ratings_data 
        FROM user_ratings 
        WHERE id_user = :user_id
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $ratingsData = json_decode($result['ratings_data'], true) ?: [];
        
        if ($rating === 0) {
            // Hapus rating untuk wisata tertentu
            unset($ratingsData[$wisataId]);
        } else {
            $ratingsData[$wisataId] = $rating;
        }

        $updatedRatingsJson = json_encode($ratingsData);

        $updateStmt = $conn->prepare("
            UPDATE user_ratings 
            SET ratings_data = :ratings_data 
            WHERE id_user = :user_id
        ");
        $updateStmt->bindParam(':ratings_data', $updatedRatingsJson);
        $updateStmt->bindParam(':user_id', $userId);
        $updateStmt->execute();
    }

    echo json_encode([
        'success' => true, 
        'message' => $rating === 0 ? 'Rating dihapus' : 'Rating updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
