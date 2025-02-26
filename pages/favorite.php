<?php
session_start();
require '../includes/db.php';
require '../includes/function.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$favorites = getFavorites($conn, $userId);

$email = isset($_SESSION["email"]) ? $_SESSION["email"] : "Pengguna";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="favorite-page">
        <nav class="nav-fav">
            <?php include '../layout/sidebar.php'; ?>
        </nav>

        <main class="content-fav">
            <div class="title">
                <h1>Daftar Favorite</h1>
            </div>

            <div class="row row-gap-4">
                <div class="sub-title">
                    <h2>Daftar Favorit</h2>
                    <button type="button" class="btn btn-outline-dark" id="update-btn">
                        Update
                        <i class="fa-solid fa-arrows-rotate" id="icon-rotate"></i>
                    </button>
                </div>

                <?php if (empty($favorites)): ?>
                    <div class="col-12 text-center">
                        <p class="alert alert-info">Anda belum memiliki wisata favorite</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($favorites as $index => $wisata): ?>
                        <div class="col-md-3">
                            <?php
                            $rank = $index + 1;
                            $place = $wisata['nama_wisata'];
                            $image = $wisata['image_url'] ?? '../asset/img/default.jpg';
                            
                            // Simpan variabel untuk digunakan di card-normal.php
                            $_SESSION['current_wisata'] = $wisata;
                            
                            include '../layout/card-normal.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../asset/js/script.js"></script>

    <!-- Firebase -->
    <script type="module" src="../asset/js/firebase-auth.js"></script>

    <script>
        // Optional: Tambahkan fungsi refresh
        document.getElementById('update-btn').addEventListener('click', function() {
            location.reload();
        });
    </script>
</body>
</html>