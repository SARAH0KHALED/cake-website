<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to add a recipe']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $preparation_time = intval($_POST['preparation_time']);
    $cooking_time = intval($_POST['cooking_time']);
    $servings = intval($_POST['servings']);
    $difficulty = $_POST['difficulty'];
    $category = trim($_POST['category']);
    // Handle image upload
    $image_url = '';
    if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['recipe_image']['name'];
        // Handle Mac filenames with special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)) {
            // Create a unique filename
            $new_filename = uniqid('recipe_') . '.' . $ext;
            // Use directory separator constant for cross-platform compatibility
            $upload_dir = 'uploads';

            // Check if upload directory exists, if not create it
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $upload_path = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;

            // Check if the file was uploaded via HTTP POST
            if (is_uploaded_file($_FILES['recipe_image']['tmp_name'])) {
                if(move_uploaded_file($_FILES['recipe_image']['tmp_name'], $upload_path)) {
                    // Convert backslashes to forward slashes for URL consistency
                    $image_url = str_replace(DIRECTORY_SEPARATOR, '/', $upload_path);
                } else {
                    $error = error_get_last();
                    echo json_encode(['error' => 'Failed to upload image: ' . ($error ? $error['message'] : 'Unknown error') . '. Please check directory permissions.']);
                    exit;
                }
            } else {
                echo json_encode(['error' => 'Invalid upload operation. Please try again.']);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Invalid file type. Please upload JPG, PNG, or GIF images.']);
            exit;
        }
    }
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($title) || empty($description) || empty($ingredients) || empty($instructions)) {
        echo json_encode(['error' => 'Please fill in all required fields']);
        exit;
    }

    // Validate numeric fields
    if ($preparation_time <= 0 || $cooking_time <= 0 || $servings <= 0) {
        echo json_encode(['error' => 'Invalid time or servings values']);
        exit;
    }

    // Validate difficulty level
    if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
        echo json_encode(['error' => 'Invalid difficulty level']);
        exit;
    }

    // Get category_id from category name
    $category_query = "SELECT id FROM categories WHERE name LIKE '%$category%' LIMIT 1";
    $category_result = mysqli_query($conn, $category_query);

    if (mysqli_num_rows($category_result) > 0) {
        $category_row = mysqli_fetch_assoc($category_result);
        $category_id = $category_row['id'];
    } else {
        // If category doesn't exist, create it
        $insert_category = "INSERT INTO categories (name) VALUES ('$category')";
        mysqli_query($conn, $insert_category);
        $category_id = mysqli_insert_id($conn);
    }

    // Prepare query with correct field names
    $query = "INSERT INTO recipes (title, description, ingredients, instructions, preparation_time, cooking_time, servings, difficulty, image_url, user_id, category_id)
              VALUES ('$title', '$description', '$ingredients', '$instructions', $preparation_time, $cooking_time, $servings, '$difficulty', '$image_url', $user_id, $category_id)";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Recipe added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add recipe: ' . mysqli_error($conn)]);
    }
    exit;
}
?>
