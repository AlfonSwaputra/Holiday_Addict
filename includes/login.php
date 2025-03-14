<?php
// Set header JSON di awal file
header('Content-Type: application/json');
error_reporting(0);

session_start();
require 'function.php';

// Pastikan koneksi database tersedia
global $conn;

try {
    if (!$conn) {
        throw new Exception("Koneksi database gagal");
    }

    // Tangkap data dari request
    $data = json_decode(file_get_contents("php://input"), true);

    // Login dengan Google Firebase
    if (isset($data["uid"], $data["email"], $data["name"])) {
        $uid = $data["uid"];
        $email = $data["email"];
        $name = $data["name"];

        // Validasi input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Format email tidak valid"]);
            exit;
        }

        // Cek atau buat user baru
        $stmt = $conn->prepare("
            INSERT INTO users (id_user, name_user, email_user) 
            VALUES (:uid, :name, :email) 
            ON DUPLICATE KEY UPDATE 
            name_user = :name
        ");

        $stmt->execute([
            ':uid' => $uid,
            ':name' => $name,
            ':email' => $email
        ]);

        // Ambil data user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email_user = :email");
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Set session
            $_SESSION["user"] = [
                "id" => $user["id_user"],
                "name" => $user["name_user"],
                "email" => $user["email_user"]
            ];

            // Pastikan preferensi ada
            ensureUserPreferences($user["id_user"], $conn);

            echo json_encode([
                "success" => true,
                "message" => "Login berhasil",
                "user" => [
                    "id" => $user["id_user"],
                    "name" => $user["name_user"],
                    "email" => $user["email_user"]
                ]
            ]);
            exit;
        }
    }

    // Jika tidak ada request yang valid
    http_response_code(400);
    echo json_encode(["error" => "Request tidak valid"]);
    
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "error" => "Terjadi kesalahan sistem",
        "message" => $e->getMessage()
    ]);
}
?>
