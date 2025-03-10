<?php
session_start();
require '../includes/db.php';
require '../includes/function.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

// Ambil ID wisata dari parameter URL
$wisataId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validasi ID wisata
if ($wisataId <= 0) {
    die("ID Wisata tidak valid");
}

// Ambil detail wisata
$stmt = $conn->prepare("SELECT * FROM wisata WHERE id_wisata = :id");
$stmt->execute([':id' => $wisataId]);
$wisata = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wisata) {
    die("Wisata tidak ditemukan");
}

// Track view interaction
trackUserInteraction($conn, $_SESSION['user']['id'], $wisataId, 'view');

// Ambil analytics
$analytics = getWisataAnalytics($conn, $wisataId);

// Ambil ulasan
$reviewStmt = $conn->prepare("
    SELECT ur.*, u.name_user 
    FROM user_ratings ur
    JOIN users u ON ur.user_id = u.id_user
    WHERE ur.wisata_id = :wisata_id
    ORDER BY ur.timestamp DESC
");
$reviewStmt->execute([':wisata_id' => $wisataId]);
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

// Proses submit ulasan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'] ?? 0;
    $review = $_POST['review'] ?? '';
    
    $result = addUserRating(
        $conn, 
        $_SESSION['user']['id'], 
        $wisataId, 
        $rating, 
        $review
    );
    
    if ($result) {
        // Redirect untuk mencegah pengiriman ulang
        header("Location: detail.php?id=$wisataId&success=1");
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Wisata - <?= htmlspecialchars($wisata['nama_wisata']) ?></title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="detail-page">
        <nav class="nav-detail">
            <?php include '../layout/sidebar.php'; ?>
        </nav>

        <main class="content-detail">
            <div class="card-detail">
                <div class="img-detail">
                    <div class="views">
                        <i class="fa-solid fa-eye"></i>
                        <span><?= $analytics['total_views'] ?? 0 ?> Views</span>
                    </div>

                    <h5 class="img-title"><?= htmlspecialchars($wisata['nama_wisata']) ?></h5>

                    <img src="<?= htmlspecialchars($wisata['image_url'] ?? '../asset/img/blog-footage.jpg') ?>" class="card-detail w-100" alt="<?= htmlspecialchars($wisata['nama_wisata']) ?>">

                    <div class="favorite-btn">
                        <button type="button" class="btn btn-link" onClick="favClick(this)" data-wisata-id="<?= $wisataId ?>">
                            <i class="fa-regular fa-heart fa-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="information">
                    <div class="location">
                        <i class="fa-solid fa-location-dot fa-lg"></i>
                        <a href="#"><?= htmlspecialchars($wisata['lokasi'] ?? 'Lokasi Tidak Diketahui') ?></a>
                    </div>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i>
                        <span><?= number_format($analytics['average_rating'] ?? 0, 1) ?>/5 
                            (<?= $analytics['total_ratings'] ?? 0 ?> ulasan)</span>
                    </div>
                </div>

                <hr class="blog-line">

                <div class="card-body">
                    <h5 class="card-title fw-bold">Deskripsi</h5>
                    <p class="card-text"><?= htmlspecialchars($wisata['description'] ?? 'Tidak ada deskripsi') ?></p>
                </div>

                <div class="detail-obwis">
                    <?php include '../layout/detail-obwis.php'; ?>
                </div>
            </div>
        </main>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../asset/js/script.js" defer></script>

    <!-- Firebase -->
    <script type="module" src="../asset/js/firebase-auth.js"></script>

</body>
</html>
