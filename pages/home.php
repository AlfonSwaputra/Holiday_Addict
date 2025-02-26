<?php
session_start();

require '../includes/db.php';
require '../includes/function.php';
require '../includes/search.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$userFavorites = getUserFavorites($conn, $user_id);
$favoritedWisataIds = getUserFavorites($conn, $user_id);
$recommendations = [];

// Cek apakah ada keyword pencarian
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$searchResults = [];

if (!empty($keyword)) {
    $searchResults = handleSearch($keyword);
} else {
    try {
        $recommendations = getCachedRecommendations($user_id, $conn);
    } catch (Exception $e) {
        error_log("Error in home: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="home-page">
        <nav class="nav-home">
            <?php include '../layout/sidebar.php'; ?>
        </nav>
        <main class="content-home">
            <div class="title">
                <?php if ($keyword): ?>
                    <h1>Hasil Pencarian untuk "<?= htmlspecialchars($keyword) ?>"</h1>
                <?php else: ?>
                    <h1>Rekomendasi Objek Wisata Berdasarkan Preferensi Anda</h1>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['search_alert'])): ?>
                <div class="alert alert-info">
                    <?= $_SESSION['search_alert'] ?>
                </div>
                <?php unset($_SESSION['search_alert']); ?>
            <?php endif; ?>

            <div class="row row-gap-4">
                <?php if (!empty($keyword)): ?>
                    <?php if (empty($searchResults)): ?>
                        <div class="col-12 text-center">
                            <p class="alert alert-warning">Tidak ada hasil untuk pencarian "<?= htmlspecialchars($keyword) ?>"</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($searchResults as $index => $wisata): ?>
                            <div class="col-md-4">
                                <?php
                                $image1 = $wisata['image_url_1'] ?? "../asset/img/default.jpg";
                                $image2 = $wisata['image_url_2'] ?? "../asset/img/default.jpg";
                                $image3 = $wisata['image_url_3'] ?? "../asset/img/default.jpg";
                                $rank = $index + 1;
                                $place = $wisata['nama_wisata'] ?? "Nama Objek Wisata Tidak Diketahui";
                                $isFavorited = in_array($wisata['id_wisata'], $favoritedWisataIds);
                                $showRank = false;
                                include '../layout/card.php';
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (empty($recommendations)): ?>
                        <div class="col-12 text-center">
                            <p class="alert alert-info">Tidak ada rekomendasi yang tersedia saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recommendations as $index => $wisata): ?>
                            <div class="col-md-4">
                                <?php
                                $image1 = $wisata['image_url_1'] ?? "../asset/img/default.jpg";
                                $image2 = $wisata['image_url_2'] ?? "../asset/img/default.jpg";
                                $image3 = $wisata['image_url_3'] ?? "../asset/img/default.jpg";
                                $rank = $index + 1;
                                $place = $wisata['nama_wisata'] ?? "Nama Objek Wisata Tidak Diketahui";
                                $isFavorited = in_array($wisata['id_wisata'], $favoritedWisataIds);
                                $showRank = true;
                                include '../layout/card.php';
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../asset/js/script.js"></script>

    <!-- Firebase -->
    <script type="module" src="../asset/js/firebase-auth.js"></script>
</body>
</html>
