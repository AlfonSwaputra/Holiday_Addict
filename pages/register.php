<?php
require '../includes/function.php';

if(isset($_POST["register"])) {
    $result = register($_POST);
    if ($result === "success") {
        echo "
        <div id='successAlert' class='alert alert-primary d-flex align-items-center' role='alert'>
            <i class='fa-solid fa-circle-exclamation fa-lg'></i>
            <div>
                Registrasi Akun Anda Berhasil!
            </div>
        </div>";
    }
}

if (isset($_GET['error']) && $_GET['error'] == 'email_exists') {
    echo "
    <div id='errorAlert' class='alert alert-danger d-flex align-items-center' role='alert'>
        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
        <div>
            Email sudah terdaftar!
        </div>
    </div>";
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>

    <!-- Style CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <section class="register-page">
        <!-- Left Section -->
        <div class="left-container">
            <img src="../asset/img/welcome-logo.png" alt="Welcome Logo" class="img-fluid">
        </div>

        <!-- Right Section -->
        <div class="right-container">
            <div class="welcome mb-4">
                <img src="../asset/img/tulisan-logo.png" alt="Logo" class="img-fluid mb-2">
                <p class="text-muted">Registrasikan akun anda untuk mendapatkan rekomendasi</p>
            </div>

            <form class="register-form" action="" method="POST">
                <div class="name-form mb-2">
                    <label for="fullname" class="form-label">Input nama lengkap <span class="colored-entity">&#42;</span></label>
                    <input type="text" id="fullname" class="form-control" required name="usernameReg">
                </div>

                <div class="email-form mb-2">
                    <label for="email" class="form-label">Email <span class="colored-entity">&#42;</span></label>
                    <input type="email" id="email" class="form-control" required name="emailReg">
                </div>

                <div class="pass-form mb-2">
                    <label for="password" class="form-label">Password <span class="colored-entity">&#42;</span></label>
                    <input type="password" id="password" class="form-control" required name="passwordReg">
                </div>

                <div class="birthdate-form mb-2">
                    <label for="birthdate" class="form-label">Tanggal lahir <span class="colored-entity">&#42;</span></label>
                    <input type="date" id="birthdate" class="form-control" required name="birthdate">
                </div>

                <div class="gender-form mb-4">
                    <label for="gender" class="form-label">Jenis kelamin <span class="colored-entity">&#42;</span></label>
                    <select id="gender" class="form-select" required name="gender">
                        <option value="" disabled selected>Pilih</option>
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>

                <div class="register-btn mb-3">
                    <button type="submit" class="btn btn-dark" name="register">Register</button>
                </div>

                <div class="bottom-link">
                    <p class="mb-0 text-secondary ">Ready to go <a href="../index.php" class="text-decoration-none">Back</a></p>
                </div>
            </form>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../asset/js/script.js"></script>
</body>
</html>