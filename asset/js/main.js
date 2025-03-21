// Import Firebase modules
import { auth } from "./firebase-config.js";
import { GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const provider = new GoogleAuthProvider();

// Firebase Error Handler
function handleFirebaseError(error, action) {
    fetch("../includes/function.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            timestamp: new Date().toISOString(),
            action: action,
            error_code: error.code,
            error_message: error.message,
            stack_trace: error.stack
        })
    });
}

// Fungsi Login Google
function googleLogin() {
    signInWithPopup(auth, provider)
        .then((result) => {
            const user = result.user;
            
            fetch("includes/user_actions.php?action=login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    uid: user.uid,
                    email: user.email,
                    name: user.displayName
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text() || '{"message": "No content"}';
            })
            .then(text => {
                const data = text ? JSON.parse(text) : {"message": "No content"};
                if (data.message === "Login berhasil" || data.message === "No content") {
                    const successAlert = document.createElement('div');
                    successAlert.id = 'successAlert';
                    successAlert.className = 'alert alert-primary d-flex align-items-center';
                    successAlert.innerHTML = `
                        <i class='fa-solid fa-circle-exclamation fa-lg'></i>
                        <div>Login Berhasil!</div>
                    `;
                    document.body.appendChild(successAlert);
                    setTimeout(() => {
                        window.location.href = "pages/first-preference.php";
                    }, 2000);
                }
            });
        })
        .catch((error) => {
            handleFirebaseError(error, 'LOGIN');
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger';
            errorAlert.textContent = 'Gagal login: ' + error.message;
            document.body.appendChild(errorAlert);
            setTimeout(() => errorAlert.remove(), 5000);
        });
}

function updateRating(wisataId, rating) {
    fetch('../includes/user_actions.php?action=update_rating', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            wisataId: wisataId,
            rating: rating
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Rating berhasil diupdate');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Fungsi Favorite System
function favClick(button) {
    const wisataId = button.dataset.wisataId;
    const icon = button.querySelector('i');
    const likesCountElement = document.querySelector(`.likes-count[data-wisata-id="${wisataId}"]`);

    // Log awal proses
    console.log('Favorite clicked:', {
        wisataId: wisataId,
        currentState: icon.classList.contains('fa-solid') ? 'favorited' : 'not favorited'
    });

    // Optimistic UI update
    const isCurrentlyFavorited = icon.classList.contains('fa-solid');
    
    // Ubah status like secara langsung
    icon.classList.toggle('fa-solid', !isCurrentlyFavorited);
    icon.classList.toggle('fa-regular', isCurrentlyFavorited);

    // Update likes count secara langsung
    if (likesCountElement) {
        let currentLikes = parseInt(likesCountElement.textContent);
        if (isCurrentlyFavorited) {
            currentLikes--;
        } else {
            currentLikes++;
        }
        likesCountElement.textContent = `${currentLikes} Likes`;
    }

    // Kirim request ke server
    fetch('../includes/user_actions.php?action=toggle_favorite', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            wisataId: wisataId
        })
    })
    .then(response => {
        // Log response status
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        // Log server response
        console.log('Server response:', data);

        if (!data.success) {
            // Jika request gagal, kembalikan UI ke kondisi semula
            icon.classList.toggle('fa-solid', isCurrentlyFavorited);
            icon.classList.toggle('fa-regular', !isCurrentlyFavorited);

            if (likesCountElement) {
                let currentLikes = parseInt(likesCountElement.textContent);
                if (isCurrentlyFavorited) {
                    currentLikes++;
                } else {
                    currentLikes--;
                }
                likesCountElement.textContent = `${currentLikes} Likes`;
            }

            // Tampilkan pesan error
            console.error('Gagal mengubah status favorit');
            alert('Gagal mengubah status favorit');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Kembalikan UI ke kondisi semula jika terjadi error
        icon.classList.toggle('fa-solid', isCurrentlyFavorited);
        icon.classList.toggle('fa-regular', !isCurrentlyFavorited);

        if (likesCountElement) {
            let currentLikes = parseInt(likesCountElement.textContent);
            if (isCurrentlyFavorited) {
                currentLikes++;
            } else {
                currentLikes--;
            }
            likesCountElement.textContent = `${currentLikes} Likes`;
        }

        alert('Terjadi kesalahan');
    });
}

// Event Listeners
document.addEventListener("DOMContentLoaded", function() {
    // Google Login Button
    const googleLoginBtn = document.getElementById('googleLogin');
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', googleLogin);
    }

    // Favorite buttons
    document.querySelectorAll('.favorite-btn button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            favClick(this);
        });
    });

    // Rating system
    document.querySelectorAll(".rating-stars").forEach(container => {
        const ratingInputs = container.querySelectorAll("input[type='radio']");
        const ratingPoints = container.querySelector("#rating-points");
        const wisataId = container.dataset.wisataId || 
                         container.closest('[data-wisata-id]')?.dataset.wisataId;
        const rank = container.querySelector("input[type='radio']").name.replace('rating', '');
    
        // Load existing rating
        fetch(`../includes/get_user_rating.php?wisataId=${wisataId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.rating > 0) {
                    const starValue = Math.ceil(data.rating / 2);
                    const starInput = container.querySelector(`#star${starValue}-${rank}`);
                    if (starInput) {
                        starInput.checked = true;
                        ratingPoints.textContent = `(${data.rating})`;
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching rating:', error);
            });
    
        // Rating click handlers
        ratingInputs.forEach(star => {
            star.addEventListener("click", function() {
                const starNumber = parseInt(this.id.split('-')[0].replace('star', ''));
                const newRating = starNumber * 2;
                const currentRating = parseInt(ratingPoints.textContent.replace(/[\(\)]/g, '')) || 0;
                
                if (newRating === currentRating) {
                    ratingPoints.style.opacity = '0';
                    setTimeout(() => {
                        ratingInputs.forEach(input => input.checked = false);
                        ratingPoints.textContent = "(0)";
                        ratingPoints.style.opacity = '1';
                        updateRating(wisataId, 0);
                    }, 300);
                } else {
                    ratingPoints.style.opacity = '0';
                    setTimeout(() => {
                        ratingPoints.textContent = `(${newRating})`;
                        ratingPoints.style.opacity = '1';
                        updateRating(wisataId, newRating);
                    }, 300);
                }
            });
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchIcon = document.getElementById('searchIcon');
    if (searchInput && searchIcon) {
        searchInput.addEventListener('input', function() {
            searchIcon.style.opacity = this.value.length ? "0" : "1";
        });
    }

    // Navigation
    document.querySelectorAll(".navside .btn-light").forEach(button => {
        button.addEventListener("click", function () {
            document.querySelectorAll(".navside .btn-light").forEach(btn => 
                btn.classList.remove("active"));
            this.classList.add("active");
        });
    });

    // Alerts
    [
        { id: "successAlert", timeout: 5000 }, 
        { id: "errorAlert", timeout: 5000 }
    ].forEach(alertInfo => {
        let alertElement = document.getElementById(alertInfo.id);
        if (alertElement) {
            setTimeout(() => {
                alertElement.style.opacity = "0";
                setTimeout(() => alertElement.remove(), 500);
            }, alertInfo.timeout);
        }
    });
});

// Export di akhir file
export { googleLogin, favClick };
