<?php
// Default values with null coalescing
$image1 = $wisata['image_url_1'] ?? "../asset/img/default.jpg";
$image2 = $wisata['image_url_2'] ?? "../asset/img/default.jpg";
$image3 = $wisata['image_url_3'] ?? "../asset/img/default.jpg";
$rank = $rank ?? "?";
$place = $wisata['nama_wisata'] ?? "Nama Objek Wisata Tidak Diketahui";

// Debugging
error_log("Card Debug: images = $image1, $image2, $image3, rank = $rank, place = $place");

// Tambahkan array gambar trending
$trendingImages = [];
for ($i = 1; $i <= 9; $i++) {
    $trendingImages[] = "../asset/img/trending{$i}.jpg";
}
$isFavorited = isWisataFavorited($conn, $_SESSION['user']['id'], $wisata['id_wisata']);

// Gunakan gambar trending berdasarkan rank (siklus dari 1-9)
$trendingImage = $trendingImages[($rank - 1) % 9];
?>

<section class="card" data-wisata-id="<?= $wisata['id_wisata'] ?? '' ?>">
    <div class="carousel slide" id="carouselCardRecom<?= $rank ?>" data-bs-ride="carousel">
        <div class="favorite-btn">
            <button data-wisata-id="<?= $wisata['id_wisata'] ?>" class="btn">
                <i class="<?= $isFavorited ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
            </button>
            <?php if ($showRank): ?>
                <?php include 'rank.php'; ?>
            <?php endif; ?>
        </div>

        <button class="carousel-control-prev" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="<?= htmlspecialchars($image1) ?>" class="d-block w-100" alt="<?= htmlspecialchars($place) ?>">
            </div>
            <div class="carousel-item">
                <img src="<?= htmlspecialchars($image2) ?>" class="d-block w-100" alt="<?= htmlspecialchars($place) ?>">
            </div>
            <div class="carousel-item">
                <img src="<?= htmlspecialchars($image3) ?>" class="d-block w-100" alt="<?= htmlspecialchars($place) ?>">
            </div>
        </div>

        <button class="carousel-control-next" data-bs-target="#carouselCardRecom<?= $rank ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="card-body">
        <div class="card-title">
            <h5><?= htmlspecialchars($place) ?></h5>
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
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star<?= $i ?>-<?= $rank ?>">
                <label for="star<?= $i ?>-<?= $rank ?>">&#9733;</label>
            <?php endfor; ?>
        </div>
        <a href="#" class="btn btn-dark">Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</section>
