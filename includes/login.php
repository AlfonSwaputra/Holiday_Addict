<?php
// Tambahkan di paling atas file
header('Content-Type: application/json');

session_start();
require 'function.php';

// Pastikan koneksi database tersedia
global $conn;

try {
    if (!$conn) {
        throw new Exception("Koneksi database gagal");
    }

    // Login Manual
    if (isset($_POST["signIn"])) {
        $email = $_POST["emailLog"] ?? "";
        $password = $_POST["passwordLog"] ?? "";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "Format email tidak valid atau password kosong"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE email_user = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($password, $row["password_user"])) {
            // Set session untuk user yang login
            $_SESSION["user"] = [
                "id" => $row["id_user"],
                "name" => $row["name_user"],
                "email" => $row["email_user"]
            ];

            // Periksa dan buat preferensi jika belum ada
            ensureUserPreferences($row["id_user"], $conn);

            echo json_encode([
                "message" => "Login berhasil",
                "user" => [
                    "id" => $row["id_user"],
                    "name" => $row["name_user"],
                    "email" => $row["email_user"]
                ]
            ]);
            exit;
        }

        // Jika login gagal
        http_response_code(401);
        echo json_encode(["error" => "Email atau password salah"]);
        exit;
    }

    // Login dengan Google Firebase
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["uid"], $data["email"], $data["name"])) {
        $uid = $data["uid"];
        $email = $data["email"];
        $name = $data["name"];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($name)) {
            http_response_code(400);
            echo json_encode(["error" => "Format email tidak valid atau nama kosong"]);
            exit;
        }

        // Cek atau buat user
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

        // Ambil data pengguna
        $stmt = $conn->prepare("SELECT * FROM users WHERE email_user = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(500);
            echo json_encode(["error" => "Gagal mengambil data pengguna"]);
            exit;
        }

        // Set session
        $_SESSION["user"] = [
            "id" => $user["id_user"],
            "name" => $user["name_user"],
            "email" => $user["email_user"]
        ];

        // Pastikan preferensi ada
        ensureUserPreferences($user["id_user"], $conn);

        echo json_encode([
            "message" => "Login berhasil",
            "user" => [
                "id" => $user["id_user"],
                "name" => $user["name_user"],
                "email" => $user["email_user"]
            ]
        ]);
        exit;
    }

    // Jika tidak ada request yang cocok
    http_response_code(400);
    echo json_encode(["error" => "Permintaan tidak valid"]);
    exit;

} catch (Exception $e) {
    // Tangkap semua kesalahan yang mungkin terjadi
    error_log("Login Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Terjadi kesalahan: " . $e->getMessage()]);
    exit;
}
?>
