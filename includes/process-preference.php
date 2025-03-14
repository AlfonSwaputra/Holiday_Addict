<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';
require 'function.php';

$logFile = __DIR__ . '/error_log.txt';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

writeLog("=== Starting Preference Process ===");
writeLog("Method: " . $_SERVER['REQUEST_METHOD']);

// Validate user session
if (!isset($_SESSION['user'])) {
    writeLog("User session not found");
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_categories = $_POST['categories'] ?? [];
    writeLog("POST Data: " . print_r($_POST, true));
    writeLog("User ID: " . $user_id);

    try {
        writeLog("Starting database transaction");
        $conn->beginTransaction();

        // First check if user preferences exist
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM preferences WHERE id_user = :user_id");
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->execute();
        $exists = $check_stmt->fetchColumn();

        $sql = $exists 
            ? "UPDATE preferences SET 
                alam = :wisata_alam,
                budaya_sejarah = :wisata_budaya_sejarah,
                pantai = :wisata_pantai,
                kota_belanja = :wisata_belanja,
                kuliner = :wisata_kuliner,
                petualangan = :wisata_petualangan,
                relaksasi = :wisata_relaksasi
               WHERE id_user = :user_id"
            : "INSERT INTO preferences 
               (id_user, alam, budaya_sejarah, pantai, kota_belanja, kuliner, petualangan, relaksasi)
               VALUES 
               (:user_id, :wisata_alam, :wisata_budaya_sejarah, :wisata_pantai, :wisata_belanja, 
                :wisata_kuliner, :wisata_petualangan, :wisata_relaksasi)";

        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $preferences = [
            'alam' => in_array('alam', $selected_categories),
            'budaya_sejarah' => in_array('budaya_sejarah', $selected_categories),
            'pantai' => in_array('pantai', $selected_categories),
            'kota_belanja' => in_array('kota_belanja', $selected_categories),
            'kuliner' => in_array('kuliner', $selected_categories),
            'petualangan' => in_array('petualangan', $selected_categories),
            'relaksasi' => in_array('relaksasi', $selected_categories)
        ];

        foreach ($preferences as $key => $value) {
            $stmt->bindValue(':wisata_' . $key, $value ? 1 : 0, PDO::PARAM_INT);
        }
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        $stmt->execute();
        $conn->commit();
        
        writeLog("Transaction successful");
        header("Location: ../pages/home.php");
        exit;

    } catch (PDOException $e) {
        $conn->rollBack();
        writeLog("ERROR: " . $e->getMessage());
        writeLog("Stack trace: " . $e->getTraceAsString());
        header("Location: ../pages/first-preference.php?error=1");
        exit;
    }
}
?>
