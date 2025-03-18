<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 1);

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
    
    // Tambahkan error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Log awal proses
    error_log("Favorite Action Started: " . json_encode($data));
    
    if (!isset($_SESSION['user'])) {
        error_log("Unauthorized access attempt");
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $wisataId = $data['wisataId'];

    try {
        // Log detail user dan wisata
        error_log("User ID: $userId, Wisata ID: $wisataId");

        // Cek apakah sudah di-favorite dengan logging tambahan
        $checkStmt = $conn->prepare("
            SELECT id_favorite 
            FROM user_favorites 
            WHERE user_id = :user_id AND wisata_id = :wisata_id
        ");
        $checkStmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        error_log("Favorite exists check: " . ($exists ? 'Yes' : 'No'));

        if (!$exists) {
            // Tambah ke favorit
            $insertStmt = $conn->prepare("
                INSERT INTO user_favorites (user_id, wisata_id, added_at) 
                VALUES (:user_id, :wisata_id, NOW())
            ");
            $insertResult = $insertStmt->execute([
                ':user_id' => $userId,
                ':wisata_id' => $wisataId
            ]);

            error_log("Insert favorite result: " . ($insertResult ? 'Success' : 'Failed'));

            // Hitung total likes setelah ditambahkan
            $likesStmt = $conn->prepare("
                SELECT COUNT(*) as total_likes 
                FROM user_favorites 
                WHERE wisata_id = :wisata_id
            ");
            $likesStmt->execute([':wisata_id' => $wisataId]);
            $totalLikes = $likesStmt->fetchColumn();

            error_log("Total likes after adding: $totalLikes");

            echo json_encode([
                'success' => true,
                'action' => 'added',
                'totalLikes' => $totalLikes
            ]);
        } else {
            // Hapus dari favorit dengan logging detail
            $deleteStmt = $conn->prepare("
                DELETE FROM user_favorites 
                WHERE user_id = :user_id AND wisata_id = :wisata_id
            ");
            $deleteResult = $deleteStmt->execute([
                ':user_id' => $userId,
                ':wisata_id' => $wisataId
            ]);

            error_log("Delete favorite result: " . ($deleteResult ? 'Success' : 'Failed'));

            // Hitung total likes setelah dihapus
            $likesStmt = $conn->prepare("
                SELECT COUNT(*) as total_likes 
                FROM user_favorites 
                WHERE wisata_id = :wisata_id
            ");
            $likesStmt->execute([':wisata_id' => $wisataId]);
            $totalLikes = $likesStmt->fetchColumn();

            error_log("Total likes after removing: $totalLikes");

            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'totalLikes' => $totalLikes
            ]);
        }
    } catch (Exception $e) {
        // Log error secara detail
        error_log("Favorite Action Error: " . $e->getMessage());
        error_log("Error Trace: " . $e->getTraceAsString());

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
function handleRating($data) {
    global $conn;
    
    // Debug logging
    error_log("Received rating data: " . print_r($data, true));
    
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $wisataId = $data['wisataId'] ?? null;
    $rating = $data['rating'] ?? null;

    // Validasi input
    if ($wisataId === null || $rating === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    try {
        // Cek atau buat entri di user_ratings
        $stmt = $conn->prepare("
            INSERT INTO user_ratings (id_user, ratings_data, created_at, updated_at) 
            VALUES (:user_id, :ratings_data, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
            ratings_data = JSON_SET(ratings_data, :json_path, :rating),
            updated_at = NOW()
        ");

        // Siapkan data JSON
        $jsonPath = "$." . $wisataId;
        
        $stmt->execute([
            ':user_id' => $userId,
            ':ratings_data' => json_encode([$wisataId => $rating]),
            ':json_path' => $jsonPath,
            ':rating' => $rating
        ]);

        error_log("Rating updated: User $userId, Wisata $wisataId, Rating $rating");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rating berhasil diupdate'
        ]);
    } catch (Exception $e) {
        error_log("Rating update error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

?>