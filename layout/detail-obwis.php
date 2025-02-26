<div id="list-example" class="list-group row">
    <a href="#list-item-1" class="btn btn-outline-dark col">Detail-Lengkap</a>
    <a href="#list-item-2" class="btn btn-outline-dark col">Detail Mobilitas</a>
    <a href="#list-item-3" class="btn btn-outline-dark col">Detail Fasilitas</a>
    <a href="#list-item-4" class="btn btn-outline-dark col">Ulasan</a>
</div>
<div data-bs-spy="scroll" data-bs-target="#list-example" data-bs-offset="0" class="scrollspy-example" tabindex="0">
    <h4 id="list-item-1" class="mt-5">Detail Objek Wisata</h4>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut lectus eget est scelerisque fermentum. Mauris aliquam ac nunc in cursus. Proin urna lacus, viverra a ipsum efficitur, tristique egestas eros. Nunc quis turpis vitae mauris varius sagittis in id eros. Integer feugiat tellus et hendrerit interdum. Mauris pharetra est lorem, id fringilla magna cursus non. Nullam efficitur vulputate purus, eu faucibus nisl vestibulum vel. Maecenas massa nulla, viverra non lorem varius, suscipit pretium lacus. Sed sollicitudin maximus cursus. Curabitur vel diam quis ante viverra interdum. Cras ultricies luctus efficitur. Nam laoreet, enim sit amet tincidunt iaculis, velit purus consequat lacus, non aliquet augue nulla sit amet arcu. Phasellus ultrices fermentum porta. Nulla lobortis libero tortor, eget maximus diam aliquet luctus. Proin suscipit aliquam maximus. Donec sit amet malesuada tellus, non aliquet velit.</p>
    <h4 id="list-item-2" class="mt-5">Transportasi Menuju Objek Wisata</h4>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut lectus eget est scelerisque fermentum. Mauris aliquam ac nunc in cursus. Proin urna lacus, viverra a ipsum efficitur, tristique egestas eros. Nunc quis turpis vitae mauris varius sagittis in id eros. Integer feugiat tellus et hendrerit interdum. Mauris pharetra est lorem, id fringilla magna cursus non. Nullam efficitur vulputate purus, eu faucibus nisl vestibulum vel. Maecenas massa nulla, viverra non lorem varius, suscipit pretium lacus. Sed sollicitudin maximus cursus. Curabitur vel diam quis ante viverra interdum. Cras ultricies luctus efficitur. Nam laoreet, enim sit amet tincidunt iaculis, velit purus consequat lacus, non aliquet augue nulla sit amet arcu. Phasellus ultrices fermentum porta. Nulla lobortis libero tortor, eget maximus diam aliquet luctus. Proin suscipit aliquam maximus. Donec sit amet malesuada tellus, non aliquet velit.</p>
    <h4 id="list-item-3" class="mt-5">Fasilitas Tersedia</h4>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut lectus eget est scelerisque fermentum. Mauris aliquam ac nunc in cursus. Proin urna lacus, viverra a ipsum efficitur, tristique egestas eros. Nunc quis turpis vitae mauris varius sagittis in id eros. Integer feugiat tellus et hendrerit interdum. Mauris pharetra est lorem, id fringilla magna cursus non. Nullam efficitur vulputate purus, eu faucibus nisl vestibulum vel. Maecenas massa nulla, viverra non lorem varius, suscipit pretium lacus. Sed sollicitudin maximus cursus. Curabitur vel diam quis ante viverra interdum. Cras ultricies luctus efficitur. Nam laoreet, enim sit amet tincidunt iaculis, velit purus consequat lacus, non aliquet augue nulla sit amet arcu. Phasellus ultrices fermentum porta. Nulla lobortis libero tortor, eget maximus diam aliquet luctus. Proin suscipit aliquam maximus. Donec sit amet malesuada tellus, non aliquet velit.</p>
    <h4 id="list-item-4" class="mt-5">Berikan Ulasan Anda</h4>
    <form method="POST" class="form-detail mt-3">
        <div class="f1">
            <input type="text" class="form-control" 
                   value="<?= $_SESSION['user']['name'] ?? '' ?>" 
                   disabled>
        </div>

        <div class="rating-input mt-3">
            <label>Berikan Rating:</label>
            <div class="star-rating">
                <?php for($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>">
                    <label for="star<?= $i ?>"><?= $i ?> Bintang</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="f2 mt-3">
            <textarea class="form-control" name="review" rows="5" 
                      placeholder="Berikan Ulasan Anda"></textarea>
        </div>

        <div class="f3 mt-3">
            <button type="submit" name="submit_review" class="btn btn-dark">
                <i class="fa-solid fa-paper-plane"></i>
                Kirim Ulasan
            </button>
        </div>
    </form>

    <div class="reviews-section mt-5">
        <h4>Ulasan Sebelumnya</h4>
        <?php if (empty($reviews)): ?>
            <p>Belum ada ulasan</p>
        <?php else: ?>
            <?php foreach($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <strong><?= htmlspecialchars($review['name_user']) ?></strong>
                        <span class="text-muted">
                            <?= date('d M Y', strtotime($review['timestamp'])) ?>
                        </span>
                        <span class="rating">
                            <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                        </span>
                    </div>
                    <p><?= htmlspecialchars($review['review']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>