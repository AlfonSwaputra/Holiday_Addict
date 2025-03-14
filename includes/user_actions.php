<?php
header('Content-Type: application/json');
error_reporting(0);

session_start();
require 'function.php';
require 'db.php';

// Tangkap data dari request
$data = json_decode(file_get_contents("php://input"), true);

// Router sederhana berdasarkan action
$action = $_GET['action'] ?? '';

switch($action) {
    case 'login':
        handleLogin($data);
        break;
    case 'toggle_favorite':
        handleFavorite($data);
        break;
    case 'update_rating':    // Tambahkan ini
        handleRating($data);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Action tidak valid"]);
}

function handleLogin($data) {
    global $conn;
    
    try {
        if (!$conn) {
            throw new Exception("Koneksi database gagal");
        }

        if (isset($data["uid"], $data["email"], $data["name"])) {
            $uid = $data["uid"];
            $email = $data["email"];
            $name = $data["name"];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "Format email tidak valid"]);
                exit;
            }

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

            $stmt = $conn->prepare("SELECT * FROM users WHERE email_user = :email");
            $stmt->execute([":email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION["user"] = [
                    "id" => $user["id_user"],
                    "name" => $user["name_user"],
                    "email" => $user["email_user"]
                ];

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
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "error" => "Terjadi kesalahan sistem",
            "message" => $e->getMessage()
        ]);
    }
}

function handleFavorite($data) {
    global $conn;
    
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $wisataId = $data['wisataId'];

    try {
        $checkStmt = $conn->prepare("SELECT id_favorite FROM user_favorites WHERE user_id = :user_id AND wisata_id = :wisata_id");
        $checkStmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);
        $exists = $checkStmt->fetch();

        if (!$exists) {
            $insertStmt = $conn->prepare("INSERT INTO user_favorites (user_id, wisata_id, added_at) VALUES (:user_id, :wisata_id, NOW())");
            $insertStmt->execute([
                ':user_id' => $userId,
                ':wisata_id' => $wisataId
            ]);

            trackUserInteraction($conn, $userId, $wisataId, 'favorite');

            echo json_encode([
                'success' => true,
                'action' => 'added'
            ]);
        } else {
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
}
function handleRating($data) {
    global $conn;
    
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $wisataId = $data['wisataId'];
    $rating = $data['rating'];

    try {
        $stmt = $conn->prepare("
            INSERT INTO user_ratings (user_id, wisata_id, rating, rated_at) 
            VALUES (:user_id, :wisata_id, :rating, NOW())
            ON DUPLICATE KEY UPDATE 
            rating = :rating,
            rated_at = NOW()
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId,
            ':rating' => $rating
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>