<?php
// Jika $wisata belum didefinisikan tapi $wisataId tersedia
if (!isset($wisata) && isset($wisataId)) {
    $wisata = getWisataDetail($wisataId);
    if (!$wisata) {
        die("Data wisata tidak ditemukan");
    }
}

// Ambil rating user saat ini jika ada
if (!isset($userRating) && isset($_SESSION['user']['id'])) {
    try {
        $ratingStmt = $conn->prepare("
            SELECT * FROM user_ratings 
            WHERE user_id = :user_id AND wisata_id = :wisata_id
        ");
        $ratingStmt->execute([
            ':user_id' => $_SESSION['user']['id'],
            ':wisata_id' => $wisataId
        ]);
        $userRating = $ratingStmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Jika terjadi error, set $userRating ke null
        $userRating = null;
    }
}

// Jika masih belum ada, inisialisasi sebagai array kosong
if (!isset($userRating)) {
    $userRating = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($wisata['nama_wisata']) ?> - Detail Wisata Alam</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="detail-page">
        <main class="content-detail-ekspand">
            <div class="container detail-wisata-alam">
                <!-- Hero Section -->
                <div class="hero-section">
                    <div class="wisata-info">
                        <div class="title-detail-obwis">
                            <h5><?= htmlspecialchars($wisata['nama_wisata']) ?></h5>
                        </div>
                        <div class="wisata-meta mt-2">
                            <span class="badge bg-success"><?= ucfirst(str_replace('_', ' ', $wisata['kategori'])) ?></span>
                            <span class="badge bg-info favorites">
                                <?= $wisata['total_favorites'] ?> Favorite
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mt-4" id="wisataTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="deskripsi-tab" data-bs-toggle="tab" data-bs-target="#deskripsi" type="button" role="tab" aria-controls="deskripsi" aria-selected="true">Deskripsi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lokasi-tab" data-bs-toggle="tab" data-bs-target="#lokasi" type="button" role="tab" aria-controls="lokasi" aria-selected="false">Lokasi & Transportasi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas" type="button" role="tab" aria-controls="fasilitas" aria-selected="false">Fasilitas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ulasan-tab" data-bs-toggle="tab" data-bs-target="#ulasan" type="button" role="tab" aria-controls="ulasan" aria-selected="false">Ulasan</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="wisataTabContent">
                    <!-- Deskripsi Tab -->
                    <div class="tab-pane fade show active" id="deskripsi" role="tabpanel" aria-labelledby="deskripsi-tab">
                        <div class="p-3">
                            <h3>Tentang <?= htmlspecialchars($wisata['nama_wisata']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($wisata['deskripsi'])) ?></p>
                            
                            <h4 class="mt-4">Informasi Kunjungan</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-clock fa-lg"></i>
                                        <div>
                                            <strong>Jam Operasional</strong>
                                            <p><?= htmlspecialchars($wisata['jam_operasional'] ?? 'Tidak tersedia') ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fa-solid fa-ticket fa-xl"></i>
                                        <div>
                                            <strong>Harga Tiket</strong>
                                            <p><?= htmlspecialchars($wisata['harga_tiket'] ?? 'Tidak tersedia') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="mt-4">Tips Kunjungan</h4>
                            <p><?= nl2br(htmlspecialchars($wisata['tips_kunjungan'] ?? 'Tidak tersedia')) ?></p>
                        </div>
                    </div>
                    
                    <!-- Lokasi Tab -->
                    <div class="tab-pane fade" id="lokasi" role="tabpanel" aria-labelledby="lokasi-tab">
                        <div class="p-3">
                            <h3>Lokasi</h3>
                            <p><?= htmlspecialchars($wisata['lokasi']) ?></p>
                            
                            <?php 
                            // Gunakan fungsi baru untuk mendapatkan URL embed
                            $mapsUrl = getGoogleMapsEmbedUrl($wisata['lokasi'], 'YOUR_GOOGLE_MAPS_API_KEY');
                            ?>

                            <?php if (!empty($mapsUrl)): ?>
                                <div class="maps-container mt-3 mb-4">
                                    <iframe 
                                        src="<?= htmlspecialchars($mapsUrl) ?>" 
                                        width="100%" 
                                        height="450" 
                                        style="border:0;" 
                                        allowfullscreen 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade"
                                        title="Lokasi <?= htmlspecialchars($wisata['nama_wisata']) ?>">
                                    </iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3">
                                    <p>Lokasi Google Maps tidak tersedia</p>
                                </div>
                            <?php endif; ?>

                            <h4 class="mt-4">Transportasi</h4>
                            <p><?= nl2br(htmlspecialchars($wisata['transportasi'] ?? 'Informasi transportasi tidak tersedia')) ?></p>
                        </div>
                    </div>

                    <!-- Fasilitas Tab -->
                    <div class="tab-pane fade" id="fasilitas" role="tabpanel" aria-labelledby="fasilitas-tab">
                        <div class="p-3">
                            <h3>Fasilitas</h3>
                            <?php if (!empty($wisata['fasilitas'])): ?>
                                <div class="fasilitas-list">
                                    <?php foreach(explode(',', $wisata['fasilitas']) as $fasilitas): ?>
                                        <div class="fasilitas-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?= htmlspecialchars(trim($fasilitas)) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>Informasi fasilitas tidak tersedia</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Ulasan Tab -->
                    <div class="tab-pane fade" id="ulasan" role="tabpanel" aria-labelledby="ulasan-tab">
                        <div class="p-3">
                            <h3>Berikan Ulasan Anda</h3>
                            <form method="POST" action="../includes/user_actions.php?action=submit_review" class="form-detail mt-3">
                                <input type="hidden" name="wisata_id" value="<?= $wisataId ?>">
                                
                                <div class="f1">
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" 
                                           disabled>
                                </div>

                                <div class="rating-input mt-3">
                                    <label>Berikan Rating:</label>
                                    <div class="rating-stars" data-wisata-id="<?= $wisata['id_wisata'] ?>">
                                        <div class="rating-score">
                                            <span id="rating-points">(0)</span>
                                        </div>
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input class="form-check-input" type="radio" name="rating<?= $rank ?>" id="star<?= $i ?>-<?= $rank ?>">
                                            <label for="star<?= $i ?>-<?= $rank ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="f2 mt-3">
                                    <textarea 
                                        class="form-control" 
                                        name="review" 
                                        rows="5" 
                                        placeholder="Berikan Ulasan Anda"
                                    ><?= htmlspecialchars($userRating['review'] ?? '') ?></textarea>
                                </div>

                                <div class="f3 mt-3">
                                    <button type="submit" name="submit_review" class="btn btn-dark">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        Kirim Ulasan
                                    </button>
                                </div>
                            </form>

                            <!-- Ulasan Sebelumnya -->
                            <div class="reviews-section mt-5">
                                <h4>Ulasan Sebelumnya</h4>
                                <?php if (empty($wisata['reviews'])): ?>
                                    <p>Belum ada ulasan</p>
                                <?php else: ?>
                                    <?php foreach($wisata['reviews'] as $review): ?>
                                        <div class="review">
                                            <div class="review-header">
                                                <strong><?= htmlspecialchars($review['name_user']) ?></strong>
                                                <span class="text-muted">
                                                    <?= date('d M Y', strtotime($review['created_at'] ?? $review['rated_at'])) ?>
                                                </span>
                                                <span class="rating">
                                                    <?= str_repeat('★', $review['rating'] / 2) . 
                                                        str_repeat('☆', (10 - $review['rating']) / 2) ?>
                                                </span>
                                            </div>
                                            <p><?= htmlspecialchars($review['review']) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../asset/js/main.js"></script>
</body>
</html>
