<aside class="sidebar">
    <div class="profile">
        <img src="../asset/img/avatar.png" alt="User Avatar" class="profile-img">
        <img src="../asset/img/tulisan-logo.png" alt="Website Logo" class="logo-nav">
    </div>

    <nav class="navside">
        <form action="../includes/function.php" method="POST" role="search" class="search-form">
            <i class="fas fa-search" id="searchIcon"></i>
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" id="searchInput">
            <button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContent" aria-expanded="false" aria-controls="collapseContent">
                <i class="fa fa-sliders"></i>
            </button>
        </form>

        <section class="collapse" id="collapseContent">
            <div class="filter-category mb-2">
                <h5>Categories</h5>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="alam" id="categoryNature">
                    <label class="form-check-label" for="categoryNature">Alam</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="kuliner" id="categoryFood">
                    <label class="form-check-label" for="categoryFood">Kuliner</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="budaya_sejarah" id="categoryCulture">
                    <label class="form-check-label" for="categoryCulture">Budaya dan Sejarah</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="petualangan" id="categoryAdvanture">
                    <label class="form-check-label" for="categoryAdvanture">Petualangan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="pantai" id="categoryBeach">
                    <label class="form-check-label" for="categoryBeach">Pantai</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="relaksasi" id="categoryRelax">
                    <label class="form-check-label" for="categoryRelax">Relaksasi</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="kota_belanja" id="categoryCity">
                    <label class="form-check-label" for="categoryCity">Kota dan Pusat Perbelanjaan</label>
                </div>
            </div>

            <div class="filter-location mb-2">
                <h5>Location</h5>
                <select class="form-select" name="location" aria-label="Location Filter">
                    <option selected>Choose...</option>
                    <option value="1">City A</option>
                    <option value="2">City B</option>
                </select>
            </div>

            <div class="filter-price mb-2">
                <h5>Price</h5>
                <input type="range" min="0" max="100" step="1" id="slider" name="price" value="0">
                <span class="price" id="priceLabel">Rp 0 - 100.000</span>
            </div>

            <div class="filter-rating">
                <h5>Rating</h5>
                <div class="rating-stars">
                    <div class="rating-score">
                        <span id="rating-points">(0)</span>
                    </div>
                    <input class="form-check-input" type="radio" name="rating" value="5" id="star5">
                    <label for="star5">★</label>
                    <input class="form-check-input" type="radio" name="rating" value="4" id="star4">
                    <label for="star4">★</label>
                    <input class="form-check-input" type="radio" name="rating" value="3" id="star3">
                    <label for="star3">★</label>
                    <input class="form-check-input" type="radio" name="rating" value="2" id="star2">
                    <label for="star2">★</label>
                    <input class="form-check-input" type="radio" name="rating" value="1" id="star1">
                    <label for="star1">★</label>
                </div>
            </div>
        </section>

        <ul class="nav-top">
            <li><a href="home.php" class="btn btn-light"><i class="fa-solid fa-house fa-lg"></i>Home</a></li>
            <li><a href="recommendation.php" class="btn btn-light"><i class="fa-solid fa-location-dot fa-xl"></i> Recommendation</a></li>
            <li><a href="blog.php" class="btn btn-light"><i class="fa-solid fa-book fa-lg"></i> Blog</a></li>
            <li><a href="favorite.php" class="btn btn-light"><i class="fa-solid fa-heart fa-lg"></i> Favorite</a></li>
        </ul>

        <hr class="dashed-line">

        <ul class="nav-bottom">
            <li><a href="contact.php" class="btn btn-light"><i class="fa-solid fa-comment-dots fa-lg"></i> Contact Me</a></li>
            <li><a href="privacy-policy.php" class="btn btn-light"><i class="fa-solid fa-shield-halved fa-lg"></i> Privacy and Policy</a></li>
            <li><a href="#" class="btn btn-light" id="googleLogoutBtn"><i class="fa-solid fa-arrow-right-from-bracket fa-lg"></i> Sign Out</a></li>
        </ul>
    </nav>
</aside>