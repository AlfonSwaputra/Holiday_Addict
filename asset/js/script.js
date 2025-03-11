// Favorite System
function favClick(button) {
    const wisataId = button.dataset.wisataId;
    const icon = button.querySelector('i');
          fetch('../includes/update_rating.php', {
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
                  // Rating berhasil diupdate di database
                  console.log('Rating updated:', rating)
        
                  // Refresh tampilan rating
                  ratingPoints.textContent = `(${rating})`
        
                  // Update status checked pada bintang
                  ratingInputs.forEach(input => {
                      const inputStarNumber = input.id.split('-')[0].replace('star', '')
                      input.checked = inputStarNumber <= starNumber
                  })
              }
          })
          .catch(error => {
              console.error('Error updating rating:', error)
          })    .then(response => response.json())
    .then(data => {
        if (data.success) {
            icon.classList.toggle('fa-solid', data.action !== 'removed');
            icon.classList.toggle('fa-regular', data.action === 'removed');

            if (data.action === 'removed' && window.location.pathname.includes('favorite.php')) {
                const card = button.closest('.col-md-3');
                if (card) {
                    card.remove();
                    if (!document.querySelector('.col-md-3')) {
                        document.querySelector('.row').innerHTML = `
                            <div class="col-12 text-center">
                                <p class="alert alert-info">Anda belum memiliki wisata favorite</p>
                            </div>`;
                    }
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ DOMContentLoaded triggered");

    document.querySelectorAll('.favorite-btn button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            favClick(this);
        });
    });
    

    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            if (!email || !password) return alert('Email dan password harus diisi');

            firebase.auth().signInWithEmailAndPassword(email, password)
                .then(userCredential => {
                    fetch('../includes/login.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            uid: userCredential.user.uid,
                            email: userCredential.user.email,
                            name: userCredential.user.displayName || email
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'Login berhasil') window.location.href = '../pages/home.php';
                    })
                    .catch(error => alert('Terjadi kesalahan saat login'));
                })
                .catch(error => alert(`Login gagal: ${error.message}`));
        });
    }

    const googleLoginBtn = document.getElementById('googleLogin');
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', function() {
            firebase.auth().signInWithPopup(new firebase.auth.GoogleAuthProvider())
                .then(result => {
                    fetch('../includes/login.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            uid: result.user.uid,
                            email: result.user.email,
                            name: result.user.displayName
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message === 'Login berhasil') window.location.href = '../pages/home.php';
                    })
                    .catch(error => alert('Terjadi kesalahan saat login'));
                })
                .catch(error => alert(`Google login gagal: ${error.message}`));
        });
    }

    document.querySelectorAll(".rating-stars").forEach(container => {
        const ratingInputs = container.querySelectorAll("input[type='radio']");
        const ratingPoints = container.querySelector("#rating-points");
        const wisataId = container.closest('.card').dataset.wisataId;
        const rank = container.querySelector("input[type='radio']").name.replace('rating', '');
    
        // Load existing rating dari server
        fetch(`../includes/get_user_rating.php?wisataId=${wisataId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Rating data:', data); // Tambahkan log untuk debugging
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
    
        // Handle rating changes
        ratingInputs.forEach(star => {
            star.addEventListener("click", function() {
                const starNumber = parseInt(this.id.split('-')[0].replace('star', ''));
                const newRating = starNumber * 2;
                const currentRating = parseInt(ratingPoints.textContent.replace(/[\(\)]/g, '')) || 0;
                
                if (newRating === currentRating) {
                    // Efek kedip
                    ratingPoints.style.opacity = '0';
                    
                    setTimeout(() => {
                        ratingInputs.forEach(input => input.checked = false);
                        ratingPoints.textContent = "(0)";
                        ratingPoints.style.opacity = '1';
        
                        fetch('../includes/update_rating.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                wisataId: wisataId,
                                rating: 0
                            })
                        });
                    }, 300);
                } else {
                    // Atur rating baru
                    ratingPoints.style.opacity = '0';
                    
                    setTimeout(() => {
                        ratingPoints.textContent = `(${newRating})`;
                        ratingPoints.style.opacity = '1';
        
                        fetch('../includes/update_rating.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                wisataId: wisataId,
                                rating: newRating
                            })
                        });
                    }, 300);
                }
            });
        });                             
    });
    
    const searchInput = document.getElementById('searchInput');
    const searchIcon = document.getElementById('searchIcon');
    if (searchInput && searchIcon) {
        searchInput.addEventListener('input', function() {
            searchIcon.style.opacity = this.value.length ? "0" : "1";
        });
    }

    document.querySelectorAll(".navside .btn-light").forEach(button => {
        button.addEventListener("click", function () {
            document.querySelectorAll(".navside .btn-light").forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
        });
    });

    [{ id: "successAlert", timeout: 5000 }, { id: "errorAlert", timeout: 5000 }].forEach(alertInfo => {
        let alertElement = document.getElementById(alertInfo.id);
        if (alertElement) {
            setTimeout(() => {
                alertElement.style.opacity = "0";
                setTimeout(() => alertElement.remove(), 500);
            }, alertInfo.timeout);
        }
    });

    const button = document.getElementById("update-btn");
    const icon = document.getElementById("icon-rotate");
    if (button && icon) {
        button.addEventListener("click", function() {
            icon.classList.add("spin");
            setTimeout(() => icon.classList.remove("spin"), 1000);
        });
    }

    const logoutBtn = document.getElementById("googleLogoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
            e.preventDefault();
            googleLogout();
        });
    }

    document.querySelectorAll("#slider").forEach(slider => {
        const priceLabel = slider.closest('.filter-section')?.querySelector("#priceLabel");
        if (priceLabel) {
            slider.addEventListener("input", function () {
                priceLabel.textContent = `Rp 0 - ${(this.value * 1000).toLocaleString("id-ID")}`;
            });
        }
    });
});
