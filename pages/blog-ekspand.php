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
    <title>Blog Ekspand Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="blog-ekspand-page">
        <nav class="nav-blog-ekspand">
            <?php include '../layout/sidebar.php'; ?> 
        </nav>
        <main class="content-blog-ekspand">
            <div class="card-ekspand">
                <div class="img-blog-ekspand">
                    <div class="views">
                        <i class="fa-solid fa-eye"></i>
                        <span>20 Views</span>
                    </div>
                    <img src="../asset/img/blog-footage.jpg" class="card-blog-ekspand w-100" alt="...">
                </div>
                <div class="information">
                    <div class="location">
                        <i class="fa-solid fa-location-dot fa-lg"></i>
                        <a href="">Lokasi Publikasi Blog</a>
                    </div>
                    <div class="date">
                        <i class="fa-solid fa-calendar-days fa-lg"></i>
                        <span>12 Januari 2020</span>
                    </div>
                </div>
                <hr class="blog-line">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Card title</h5>
                    <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut lectus eget est scelerisque fermentum. Mauris aliquam ac nunc in cursus. Proin urna lacus, viverra a ipsum efficitur, tristique egestas eros. Nunc quis turpis vitae mauris varius sagittis in id eros. Integer feugiat tellus et hendrerit interdum. Mauris pharetra est lorem, id fringilla magna cursus non. Donec convallis mauris in ultrices cursus. Duis ultricies enim vitae condimentum euismod. Cras et libero et tortor congue bibendum. Nullam interdum augue accumsan velit fermentum aliquam. Nunc elementum, mauris eget euismod elementum, leo risus porta odio, vestibulum facilisis tellus nibh a leo. Sed vulputate neque sed efficitur tempus. Nam maximus metus a interdum auctor. Fusce porta ex eu est congue, vel aliquet lectus imperdiet. Quisque eros risus, ullamcorper nec mauris quis, venenatis tristique nibh. Fusce semper eu nisl quis tempor.</p>
                </div>
            </div>
        </main>
    </section>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="module" src="../asset/js/main.js"></script>
</body>
</html>