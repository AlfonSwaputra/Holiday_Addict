<?php
require 'includes/db.php';

$userId = 116; // Ganti dengan user ID yang valid
$stmt = $conn->prepare("CALL GetWisataRecommendations(?)");
$stmt->execute([$userId]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($result);
?>