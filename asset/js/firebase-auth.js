import { auth } from "./firebase-config.js";
import { GoogleAuthProvider, signInWithPopup, signOut } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const provider = new GoogleAuthProvider();

// Login dengan Google
function googleLogin() {
    signInWithPopup(auth, provider)
        .then((result) => {
            const user = result.user;
            console.log("User:", user);

            fetch("includes/login.php", {
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
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log(data);

                    // Periksa apakah login berhasil
                    if (data.message === "Login berhasil") {
                        // Buat elemen untuk alert
                        const successAlert = document.createElement('div');
                        successAlert.id = 'successAlert';
                        successAlert.className = 'alert alert-primary d-flex align-items-center';
                        successAlert.role = 'alert';
                        successAlert.innerHTML = `
                            <i class='fa-solid fa-circle-exclamation fa-lg'></i>
                            <div>
                                Login Berhasil!
                            </div>
                        `;

                        // Menambahkan alert ke halaman
                        document.body.appendChild(successAlert);

                        // Redirect setelah alert
                        setTimeout(() => {
                            window.location.href = "pages/first-preference.php";
                        }, 2000);
                    } else {
                        throw new Error(data.error || "Login gagal");
                    }
                } catch (error) {
                    console.error('JSON Parse Error:', error);
                    throw error;
                }
            })
            .catch(error => {
                console.error("Error:", error);
               
                // Buat elemen untuk alert error
                const errorAlert = document.createElement('div');
                errorAlert.id = 'errorAlert';
                errorAlert.className = 'alert alert-danger d-flex align-items-center';
                errorAlert.role = 'alert';
                errorAlert.innerHTML = `
                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                    <div>
                        Terjadi Kesalahan Saat Login: ${error.message}
                    </div>
                `;

                // Menambahkan alert ke halaman
                document.body.appendChild(errorAlert);
            });
        })
        .catch((error) => {
            console.error(error);

            // Buat elemen untuk alert error saat login gagal
            const errorAlert = document.createElement('div');
            errorAlert.id = 'errorAlert';
            errorAlert.className = 'alert alert-danger d-flex align-items-center';
            errorAlert.role = 'alert';
            errorAlert.innerHTML = `
                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                <div>
                    Terjadi Kesalahan Saat Login: ${error.message}
                </div>
            `;

            // Menambahkan alert ke halaman
            document.body.appendChild(errorAlert);
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const googleLoginButton = document.getElementById("googleLogin");
    if (googleLoginButton) {
        googleLoginButton.addEventListener("click", googleLogin);
    }
});

// Fungsi Logout
window.googleLogout = function() {
    signOut(auth)
        .then(() => {
            // Buat elemen untuk alert logout berhasil
            const successAlert = document.createElement('div');
            successAlert.id = 'successAlert';
            successAlert.className = 'alert alert-primary d-flex align-items-center';
            successAlert.role = 'alert';
            successAlert.innerHTML = `
                <i class='fa-solid fa-circle-exclamation fa-lg'></i>
                <div>
                    Logout Berhasil!
                </div>
            `;

            // Menambahkan alert ke halaman
            document.body.appendChild(successAlert);

            // Redirect setelah logout
            window.location.href = "../index.php";
        })
        .catch((error) => {
            console.error("Error saat logout:", error);

            // Buat elemen untuk alert error saat logout gagal
            const errorAlert = document.createElement('div');
            errorAlert.id = 'errorAlert';
            errorAlert.className = 'alert alert-danger d-flex align-items-center';
            errorAlert.role = 'alert';
            errorAlert.innerHTML = `
                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                <div>
                    Gagal Logout!
                </div>
            `;

            // Menambahkan alert ke halaman
            document.body.appendChild(errorAlert);
        });
};