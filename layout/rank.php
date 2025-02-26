<?php
// Tambahkan validasi rank
$rank = isset($rank) ? (int)$rank : 1;

$trendingImages = [];
for ($i = 1; $i <= 9; $i++) {
    $imagePath = "../asset/img/trending{$i}.jpg";
    // Tambahkan pengecekan file exists
    if (file_exists($imagePath)) {
        $trendingImages[] = $imagePath;
    }
}

$trendingImage = !empty($trendingImages) ? $trendingImages[($rank - 1) % count($trendingImages)] : null;
?>

<?php if ($trendingImage && file_exists($trendingImage)) : ?>                          
    <section class="trending">
        <img src="<?= htmlspecialchars($trendingImage) ?>" alt="Trending Image <?= $rank ?>">
    </section>
<?php endif; ?>
