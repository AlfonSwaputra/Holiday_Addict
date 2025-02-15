<?php
session_start();
require 'function.php';
global $conn;

// Login Manual
if (isset($_POST["signIn"])) {
    $email = $_POST["emailLog"];
    $password = $_POST["passwordLog"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email_user = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        if (password_verify($password, $row["password_user"])) {
            $_SESSION["user"] = [
                "id" => $row["id_user"],
                "name" => $row["name_user"],
                "email" => $row["email_user"]
            ];
            header("Location: ../pages/home.php");
            exit;
        }
    }
    $error = true;
}

// Login dengan Google Firebase
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data["uid"]) && isset($data["email"]) && isset($data["name"])) {
    $uid = $data["uid"];
    $email = $data["email"];
    $name = $data["name"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email_user = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $stmt = $conn->prepare("INSERT INTO users (id_user, name_user, email_user) VALUES (:uid, :name, :email)");
        $stmt->bindParam(":uid", $uid);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
    }

    $_SESSION["user"] = [
        "id" => $uid,
        "name" => $name,
        "email" => $email
    ];

    echo json_encode(["message" => "Login berhasil"]);
    exit;
}
?>