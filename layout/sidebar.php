<aside class="sidebar">
    <div class="profile">
        <img src="../asset/img/avatar.png" alt="User Avatar" class="profile-img">
        <img src="../asset/img/tulisan-logo.png" alt="Website Logo" class="logo-nav">
    </div>
    <nav class="navside">
        <form role="search" class="search-form" id="searchForm" method="GET" action="../pages/home.php">
            <i class="fas fa-search" id="searchIcon"></i>
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" id="searchInput" name="q">
        </form>

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
            <li><a href="#" class="btn btn-light" id="googleLogoutBtn"><i class="fa-solid fa-arrow-right-from-bracket fa-lg" onclick="googleLogout()"></i> Sign Out</a></li>
        </ul>
    </nav>
</aside>