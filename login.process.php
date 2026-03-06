<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo json_encode(["error" => "Please fill in all fields."]);
        exit;
    }

    // Simple email validation
    if (!strpos($email, '@')) {
        echo json_encode(["error" => "Invalid email format."]);
        exit;
    }

    // Check if user exists and verify password
    $query = "SELECT id, username, password_hash FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($user = mysqli_fetch_assoc($result)) {
        // Simple password check (not using password_verify)
        if ($password == $user["password_hash"]) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];

            echo json_encode(["success" => true, "message" => "Login successful!"]);
        } else {
            echo json_encode(["error" => "Invalid password."]);
        }
    } else {
        echo json_encode(["error" => "User not found."]);
    }
    exit;
}
?>