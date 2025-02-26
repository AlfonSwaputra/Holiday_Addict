<?php
$host = "localhost";
$dbname = "holiday_addict";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=localhost;dbname=holiday_addict", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Kesalahan Koneksi Database: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}
?>
