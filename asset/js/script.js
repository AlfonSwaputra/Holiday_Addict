// Favorite System
function favClick(button) {
    var icon = button.querySelector("i");
    if (icon) {
        icon.classList.toggle("fa-solid");
        icon.classList.toggle("fa-regular");
    }
}

window.favClick = favClick;

// DOMContentLoaded untuk semua event listener
document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ DOMContentLoaded triggered");

    // Rating Score System
    const ratingContainers = document.querySelectorAll(".rating-stars");

    ratingContainers.forEach(container => {
        const ratingPoints = container.querySelector("#rating-points");
        const stars = container.querySelectorAll("input[type='radio']");

        if (ratingPoints && stars.length > 0) {
            stars.forEach(star => {
                star.addEventListener("change", function () {
                    const starValue = this.id.match(/\d+/)?.[0] || 0;
                    ratingPoints.textContent = `(${starValue * 2})`;
                });
            });
        }
    });

    // Form Search
    const searchInput = document.getElementById('searchInput');
    const searchIcon = document.getElementById('searchIcon');

    if (searchInput && searchIcon) {
        searchInput.addEventListener('input', function() {
            searchIcon.style.opacity = this.value.length > 0 ? "0" : "1";
            this.setAttribute("type", this.value.length > 0 ? "text" : "search");
        });
    }

    // Button Navbar
    const buttonNavigation = document.querySelectorAll(".navside .btn-light");

    if (buttonNavigation.length > 0) {
        buttonNavigation.forEach(button => {
            button.addEventListener("click", function () {
                buttonNavigation.forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");
                // Tidak ada preventDefault(), sehingga akan redirect
            });
        });
    }
    

    // Fungsi Alert Animation
    const alerts = [
        { id: "successAlert", timeout: 5000 },
        { id: "errorAlert", timeout: 5000 }
    ];

    alerts.forEach(alertInfo => {
        let alertElement = document.getElementById(alertInfo.id);
        if (alertElement) {
            setTimeout(() => {
                alertElement.style.transition = "opacity 0.5s ease";
                alertElement.style.opacity = "0";
                setTimeout(() => alertElement.remove(), 500);
            }, alertInfo.timeout);
        }
    });

    // Update Button Favorite
    const button = document.getElementById("update-btn");
    const icon = document.getElementById("icon-rotate");

    if (button && icon) {
        button.addEventListener("click", function() {
            icon.classList.add("spin");
            setTimeout(() => icon.classList.remove("spin"), 1000);
        });
    }

    // Logout Button
    const logoutBtn = document.getElementById("googleLogoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(event) {
            event.preventDefault();
            googleLogout();
        });
    }

    // Filter Price
    const slider = document.getElementById("slider");
    const priceLabel = document.getElementById("priceLabel");

    slider.addEventListener("input", function () {
        let value = this.value;
        let maxPrice = value * 1000;
        priceLabel.textContent = `Rp 0 - ${maxPrice.toLocaleString("id-ID")}`;
    });
});
