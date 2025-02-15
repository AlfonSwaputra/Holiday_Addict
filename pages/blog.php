<?php
// session_start();
require '../includes/function.php';

// if (!isset($_SESSION["user"])) {
//     header("Location: ../index.php");
//     exit;
// }

$blogs = [
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
    <title>Blog Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="blog-page">
        <nav class="nav-blog">
            <?php include '../layout/sidebar.php'; ?> 
        </nav>
        <main class="content-blog">
            <div class="title">
                <h1>Blog Section</h1>
                <span>Memberikan tulisan seputar objek wisata dan perkembangan yang terjadi pada pengembangan potensi wisata Provinsi Riau</span>
            </div>
            <div class="blog-article">
                <div class="container top-container">
                    <div class="row m-0">
                        <div class="col-md-6 main-card border-primary">
                            <?php include '../layout/blog-card.php'; ?> 
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-12 side-card">
                                    <?php include '../layout/blog-card.php'; ?>
                                </div>
                                <div class="col-12 side-card">
                                    <?php include '../layout/blog-card.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container bottom-container row">
                    <?php foreach ($blogs as $index => $blog) : ?>
                    <div class="col-4 mb-3 me-3 ms-3">
                    <?php
                    $rank = $index + 1;
                    $blog = $blogs[$index];
                    include '../layout/blog-card-normal.php'; 
                    ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </section>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="module" src="../asset/js/script.js" defer></script>

    <!-- Firebase -->
    <script type="module" src="../asset/js/firebase-auth.js"></script>
</body>
</html>