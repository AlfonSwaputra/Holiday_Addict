<?php
// session_start();
require '../includes/function.php';

// if (!isset($_SESSION["user"])) {
//     header("Location: ../index.php");
//     exit;
// }

$images = [
    "../asset/img/trending1.jpg",
    "../asset/img/trending2.jpg",
    "../asset/img/trending3.jpg",
    "../asset/img/trending4.jpg",
    "../asset/img/trending5.jpg",
    "../asset/img/trending6.jpg"
];

$places = [
    "Danau Toba",
    "Pulau Weh",
    "Bukit Lawang",
    "Taman Nasional Way Kambas",
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
    <title>Home Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="home-page">
        <nav class="nav-home">
            <?php include '../layout/sidebar.php'; ?> 
        </nav>
        <main class="content-home">
            <div class="title">
                <h1>Objek Wisata Paling Populer Provinsi Riau</h1>
            </div>
            <div class="row row-gap-4">
                <?php foreach ($images as $index => $imgSrc) : ?>
                <div class="col-md-4">
                    <?php
                    $rank = $index + 1;
                    $image = $imgSrc;
                    $place = $places[$index];
                    include '../layout/card.php'; 
                    ?>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </section>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="module" src="../asset/js/script.js"></script>

    <!-- Firebase -->
    <script type="module" src="../asset/js/firebase-auth.js"></script>
</body>
</html>