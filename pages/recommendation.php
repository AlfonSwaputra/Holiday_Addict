<?php
// session_start();
require '../includes/function.php';

// if (!isset($_SESSION["user"])) {
//     header("Location: ../index.php");
//     exit;
// }

$places = [
    "Danau Toba",
    "Pulau Weh",
    "Bukit Lawang",
    "Taman Nasional Way..",
    "Pulau Belitung",
    "Gunung Krakatau"
];

$email = isset($_SESSION["email"]) ? $_SESSION["email"] : "Pengguna";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendation Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="recom-page">
        <nav class="nav-recom">
            <?php include '../layout/filter-sidebar.php'; ?> 
        </nav>
        <main class="content-recom">
            <div class="title">
                <h1>Rekomendasi Terbaik untuk Anda</h1>
            </div>
            <section class="for-you mb-2">
                <div class="sub-title">
                    <h2>Rekomendasi Terbaik untuk Anda</h2>
                    <button type="button" class="btn btn-outline-dark">
                        <i class="fa-solid fa-angles-right"></i>
                    </button>
                </div>
                <div class="container-scroll">
                    <?php foreach ($places as $index => $place) : ?>
                        <div class="scroll-card">
                            <?php
                            $rank = $index + 1;
                            $place = $places[$index];
                            include '../layout/card-normal.php'; 
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="your-pref">
                <div class="sub-title">
                    <h2>Rekomendasi Berdasarkan Preferensi Anda</h2>
                    <button type="button" class="btn btn-outline-dark">
                        <i class="fa-solid fa-angles-right"></i>
                    </button>
                </div>
                <div class="container-scroll">
                    <?php foreach ($places as $index => $place) : ?>
                        <div class="scroll-card">
                            <?php
                            $rank = $index + 1;
                            $place = $places[$index];
                            include '../layout/card-normal.php'; 
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </section>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="module" src="../asset/js/script.js"></script>

    <!-- Firebase -->
    <script type="module" src="../asset/js/firebase-auth.js"></script>
</body>
</html>
