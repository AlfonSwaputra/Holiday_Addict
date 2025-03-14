<?php

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <!-- Style CSS & Icon-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <?php if(isset($error)) : ?>
        <p>Password Salah</p>
    <?php endif; ?>
    <section class="login-page">
        <!-- Left Section -->
        <div class="left-container">
            <img src="../asset/img/welcome-logo.png" alt="Welcome Logo" class="img-fluid">
        </div>

        <!-- Right Section -->
        <div class="right-container">
            <div class="welcome mb-4">
                <img src="../asset/img/tulisan-logo.png" alt="Logo" class="img-fluid mb-2">
                <p class="text-muted">Rekomendasi Objek Wisata sesuai Keinginan Anda</p>
            </div>

            <form class="login-form" action="" method="POST">
                <div class="email-form mb-3">
                    <label for="email" class="form-label">Input email</label>
                    <input type="email" id="email" class="form-control" required name="emailLog" autocomplete="username">
                </div>

                <div class="pass-form mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" required name="passwordLog" autocomplete="current-password">
                </div>

                <div class="form-check mb-3">
                    <div class="check-box">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <a href="#">Forgot Password?</a>
                </div>

                <div class="btn-sign-in mb-3">
                    <button type="submit" class="btn btn-dark" name="signIn">Sign In</button>
                </div>

                <div class="btn-google-sign mb-4">
                    <button type="button" class="btn btn-light" id="googleLogin">
                        <img src="../asset/img/google-logo.png" alt="Google Logo">
                        Sign in with Google
                    </button>
                </div>

                <div class="bottom-link">
                    <p class="text-secondary">Don't have an account? <a href="../pages/register.php" class="text-decoration-none">Register</a></p>
                </div>
            </form>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>>

    <!-- Config dan Main JS -->
    <script type="module" src="asset/js/firebase-config.js"></script>
    <script type="module" src="asset/js/main.js"></script>
</body>
</html>