<?php
require 'db.php';

function register($data) {
    global $conn;

    $username = strtolower($data['usernameReg']);
    $email = $data['emailReg'];
    $password = $data["passwordReg"];
    $birthdate = $data['birthdate'];
    $gender = $data['gender'];

    // Cek email sudah terdaftar ?
    $stmt = $conn->prepare("SELECT email_user FROM users WHERE email_user = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Location: register.php?error=email_exists");
        exit();
    }

    // Enkripsi password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (name_user, email_user, password_user, birthdate_user, gender_user) 
                            VALUES (:username, :email, :password, :birthdate, :gender)");
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashedPassword);
    $stmt->bindParam(":birthdate", $birthdate);
    $stmt->bindParam(":gender", $gender);

    $stmt->execute();
    
    return "success";
}
?>
