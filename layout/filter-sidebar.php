<aside class="sidebar">
    <div class="profile">
        <img src="../asset/img/avatar.png" alt="User Avatar" class="profile-img">
        <img src="../asset/img/tulisan-logo.png" alt="Website Logo" class="logo-nav">
    </div>

    <nav class="navside">
        <form role="search" class="search-form">
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
                    <input class="form-check-input" type="checkbox" id="categoryNature">
                    <label class="form-check-label" for="categoryNature">Alam</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="categoryFood">
                    <label class="form-check-label" for="categoryFood">Kuliner</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="categoryCulture">
                    <label class="form-check-label" for="categoryCulture">Budaya dan Sejarah</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="categoryAdvanture">
                    <label class="form-check-label" for="categoryAdvanture">Petualangan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="categoryBeach">
                    <label class="form-check-label" for="categoryBeach">Pantai</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="categoryRelax">
                    <label class="form-check-label" for="categoryRelax">Relaksasi</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="categoryCity">
                    <label class="form-check-label" for="categoryCity">Kota dan Pusat Perbelanjaan</label>
                </div>
            </div>

            <div class="filter-location mb-2">
                <h5>Location</h5>
                <select class="form-select" aria-label="Location Filter">
                    <option selected>Choose...</option>
                    <option value="1">City A</option>
                    <option value="2">City B</option>
                </select>
            </div>

            <div class="filter-price mb-2">
                <h5>Price</h5>
                <input type="range" min="0" max="100" step="1" id="slider" value="0">
                <span class="price" id="priceLabel">Rp 0 - 100.000</span>
            </div>

            <div class="filter-rating">
                <h5>Rating</h5>
                <div class="rating-stars">
                    <div class="rating-score">
                        <span id="rating-points">(0)</span>
                    </div>
                    <input class="form-check-input" type="radio" name="rating" id="star5">
                    <label for="star5">&#9733;</label>
                    <input class="form-check-input" type="radio" name="rating" id="star4">
                    <label for="star4">&#9733;</label>
                    <input class="form-check-input" type="radio" name="rating" id="star3">
                    <label for="star3">&#9733;</label>
                    <input class="form-check-input" type="radio" name="rating" id="star2">
                    <label for="star2">&#9733;</label>
                    <input class="form-check-input" type="radio" name="rating" id="star1">
                    <label for="star1">&#9733;</label>
                </div>
            </div>
        </section>

        <ul class="nav-top">
        <li><a href="../pages/home.php" class="btn btn-light"><i class="fa-solid fa-house fa-lg"></i>Home</a></li>
            <li><a href="../pages/recommendation.php" class="btn btn-light"><i class="fa-solid fa-location-dot fa-xl"></i> Recommendation</a></li>
            <li><a href="../pages/blog.php" class="btn btn-light"><i class="fa-solid fa-book fa-lg"></i> Blog</a></li>
            <li><a href="../pages/favorite.php" class="btn btn-light"><i class="fa-solid fa-heart fa-lg"></i> Favorite</a></li>
        </ul>

        <hr class="dashed-line">

        <ul class="nav-bottom">
            <li><a href="#" class="btn btn-light"><i class="fa-solid fa-comment-dots fa-lg"></i> Contact Me</a></li>
            <li><a href="#" class="btn btn-light"><i class="fa-solid fa-shield-halved fa-lg"></i> Privacy and Policy</a></li>
            <li><a href="#" class="btn btn-light" id="googleLogoutBtn"><i class="fa-solid fa-arrow-right-from-bracket fa-lg"></i> Sign Out</a></li>
        </ul>
    </nav>
</aside>