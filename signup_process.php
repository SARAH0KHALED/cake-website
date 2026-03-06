<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate required fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(["error" => "Please fill in all fields."]);
        exit;
    }

    // Simple email validation
    if (!strpos($email, '@')) {
        echo json_encode(["error" => "Invalid email format."]);
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo json_encode(["error" => "Passwords do not match."]);
        exit;
    }

    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(["error" => "Email already in use."]);
        exit;
    }

    // Check if username already exists
    $check_query = "SELECT id FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(["error" => "Username already taken."]);
        exit;
    }

    // Store password directly (not hashed for simplicity)

    // Insert new user
    $query = "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$password')";

    if (mysqli_query($conn, $query)) {
        // Get the new user's ID
        $user_id = mysqli_insert_id($conn);

        // Set session variables
        $_SESSION["user_id"] = $user_id;
        $_SESSION["username"] = $username;
        $_SESSION["email"] =  $email;

        echo json_encode(["success" => true, "message" => "Registration successful!"]);
    } else {
        echo json_encode(["error" => "Registration failed: " . mysqli_error($conn)]);
    }
    exit;
}
?>
