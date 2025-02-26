<?php
session_start();
require '../includes/db.php';
require '../includes/function.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

// Mengambil ID pengguna dari session
$user_id = $_SESSION['user']['id'];

// Langkah 1: Verifikasi apakah user ID ada di tabel users
$stmt = $conn->prepare("SELECT * FROM users WHERE id_user = :id_user");
$stmt->bindParam(':id_user', $user_id);
$stmt->execute();

// Jika ID pengguna tidak ditemukan, redirect atau tampilkan pesan error
if ($stmt->rowCount() == 0) {
    echo "User tidak ditemukan.";
    exit;
}

// Langkah 2: Jika user ID valid, lanjutkan dengan menyimpan preferensi
$stmtPref = $conn->prepare("SELECT * FROM preferences WHERE id_user = :id_user");
$stmtPref->bindParam(':id_user', $user_id);
$stmtPref->execute();

// Jika preferensi untuk user_id belum ada, lakukan insert
if ($stmtPref->rowCount() == 0) {
    try {
        $stmt = $conn->prepare("INSERT INTO preferences (id_user) VALUES (:id_user)");
        $stmt->bindParam(':id_user', $user_id);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error inserting preferences: " . $e->getMessage());
        // Tambahkan penanganan error yang sesuai
    }
}

// Jika metode POST dijalankan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $categories = [
        'alam' => isset($_POST['alam']) ? 1 : 0,
        'budaya_sejarah' => isset($_POST['budaya_sejarah']) ? 1 : 0,
        'pantai' => isset($_POST['pantai']) ? 1 : 0,
        'kota_belanja' => isset($_POST['kota_belanja']) ? 1 : 0,
        'kuliner' => isset($_POST['kuliner']) ? 1 : 0,
        'petualangan' => isset($_POST['petualangan']) ? 1 : 0,
        'relaksasi' => isset($_POST['relaksasi']) ? 1 : 0
    ];

    try {
        // Reset semua preferensi ke 0 terlebih dahulu
        $resetStmt = $conn->prepare("
            UPDATE preferences 
            SET alam = 0,
                budaya_sejarah = 0,
                pantai = 0,
                kota_belanja = 0,
                kuliner = 0,
                petualangan = 0,
                relaksasi = 0
            WHERE id_user = :id_user
        ");
        $resetStmt->execute([':id_user' => $user_id]);

        // Update dengan preferensi baru
        $stmt = $conn->prepare("
            UPDATE preferences
            SET alam = :alam,
                budaya_sejarah = :budaya_sejarah,
                pantai = :pantai,
                kota_belanja = :kota_belanja,
                kuliner = :kuliner,
                petualangan = :petualangan,
                relaksasi = :relaksasi
            WHERE id_user = :id_user
        ");

        $stmt->bindParam(':id_user', $user_id);
        $stmt->bindParam(':alam', $categories['alam']);
        $stmt->bindParam(':budaya_sejarah', $categories['budaya_sejarah']);
        $stmt->bindParam(':pantai', $categories['pantai']);
        $stmt->bindParam(':kota_belanja', $categories['kota_belanja']);
        $stmt->bindParam(':kuliner', $categories['kuliner']);
        $stmt->bindParam(':petualangan', $categories['petualangan']);
        $stmt->bindParam(':relaksasi', $categories['relaksasi']);

        $stmt->execute();

        // Hapus cache rekomendasi jika ada
        $cacheFile = __DIR__ . "/../cache/recommendations_{$user_id}.json";
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        // Redirect ke halaman home
        header("Location: ../pages/home.php");
        exit;

    } catch (PDOException $e) {
        error_log("Error updating preferences: " . $e->getMessage());
        $error_message = "Terjadi kesalahan saat menyimpan preferensi. Silakan coba lagi.";
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preference Page</title>

    <!-- Style CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="pref-page">
        <header class="top-pref">
            <h1>Preferensi Pertama Anda</h1>
            <p class="text-muted">Pilihlah beberapa kategori yang sesuai dengan kebutuhan wisata dan minat anda !</p>
            <hr>
        </header>

        <form class="preference-form" method="POST">
            <div class="row">
                <div class="col-2">
                    <img src="../asset/img/alam.jpeg" alt="Nature">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryNature" name="alam">
                        <label class="form-check-label" for="categoryNature">Alam</label>
                    </div>
                </div>
                <div class="col-2">
                    <img src="../asset/img/budaya_dan_sejarah.jpg" alt="Culture and History">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryCulture" name="budaya_sejarah">
                        <label class="form-check-label" for="categoryCulture">Budaya dan Sejarah</label>
                    </div>
                </div>
                <div class="col-2">
                    <img src="../asset/img/pantai.jpg" alt="Beach">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryBeach" name="pantai">
                        <label class="form-check-label" for="categoryBeach">Pantai</label>
                    </div>
                </div>
                <div class="col-2">
                    <img src="../asset/img/kota.png" alt="City">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryCity" name="kota_belanja">
                        <label class="form-check-label" for="categoryCity">Kota dan Pusat perbelanjaan</label>
                    </div>
                </div>
                <div class="col-2">
                    <img src="../asset/img/kuliner.jpg" alt="Culinary">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryCulinary" name="kuliner">
                        <label class="form-check-label" for="categoryCulinary">Kuliner</label>
                    </div>
                </div>
                <div class="col-2">
                    <img src="../asset/img/petualangan.jpg" alt="Adventure">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryAdventure" name="petualangan">
                        <label class="form-check-label" for="categoryAdventure">petualangan</label>
                    </div>
                </div>
                <div class="col-2">
                    <img src="../asset/img/relaksasi.jpg" alt="Relaks">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="categoryRelaks" name="relaksasi">
                        <label class="form-check-label" for="categoryRelaks">Relaksasi</label>
                    </div>
                </div>
                <div class="col btn-bottom">
                    <button type="submit" class="btn btn-link">
                        Selanjutnya
                        <i class="fa-solid fa-angles-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../asset/js/script.js"></script>
</body>
</html>