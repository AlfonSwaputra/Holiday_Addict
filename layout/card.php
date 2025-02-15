<?php
$image = $image ?? "../asset/img/default.jpg";
$rank = $rank ?? "?";
$place = $place ?? "Nama Objek Wisata Tidak Diketahui";

echo "<!-- DEBUG in card.php: image = $image, rank = $rank, place = $place -->";
?>
<section class="card">
    <div class="carousel slide" id="carouselCardRecom<?= $rank ?>" data-bs-ride="carousel">
        <div class="favorite-btn">
            <button type="button" class="btn btn-link" onClick="favClick(this)">
                <i class="fa-regular fa-heart fa-lg"></i>
            </button>
            <?php include 'rank.php'; ?>
        </div>

        <button class="carousel-control-prev" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="../asset/img/footage1.jpg" class="d-block w-100" alt="footage 1">
            </div>
            <div class="carousel-item">
                <img src="../asset/img/footage2.jpg" class="d-block w-100" alt="footage 2">
            </div>
            <div class="carousel-item">
                <img src="../asset/img/footage3.jpg" class="d-block w-100" alt="footage 3">
            </div>
        </div>

        <button class="carousel-control-next" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="card-body">
        <div class="card-title">
            <h5><?= $place ?></h5>
            <div class="carousel-idn">
                <button type="button" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
        </div>

        <div class="rating-stars">
            <div class="rating-score">
                <span id="rating-points">(0)</span>
            </div>
            <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star5-<?= $rank ?>">
            <label for="star5-<?= $rank ?>">&#9733;</label>
            <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star4-<?= $rank ?>">
            <label for="star4-<?= $rank ?>">&#9733;</label>
            <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star3-<?= $rank ?>">
            <label for="star3-<?= $rank ?>">&#9733;</label>
            <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star2-<?= $rank ?>">
            <label for="star2-<?= $rank ?>">&#9733;</label>
            <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star1-<?= $rank ?>">
            <label for="star1-<?= $rank ?>">&#9733;</label>
        </div>
        <a href="#" class="btn btn-dark">Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</section>
