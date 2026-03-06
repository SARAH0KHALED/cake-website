<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to edit a recipe']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $preparation_time = intval($_POST['preparation_time']);
    $cooking_time = intval($_POST['cooking_time']);
    $servings = intval($_POST['servings']);
    $difficulty = $_POST['difficulty'];
    $category_id = intval($_POST['category']);
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($title) || empty($description) || empty($ingredients) || empty($instructions)) {
        echo json_encode(['error' => 'Please fill in all required fields']);
        exit;
    }

    // Check if recipe exists and belongs to the user
    $check_query = "SELECT user_id FROM recipes WHERE id = $id";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) == 0) {
        echo json_encode(['error' => 'Recipe not found']);
        exit;
    }

    $recipe = mysqli_fetch_assoc($check_result);
    if ($recipe['user_id'] != $user_id) {
        echo json_encode(['error' => 'You do not have permission to edit this recipe']);
        exit;
    }

    // Handle image upload
    $image_url = '';
    if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['recipe_image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if(in_array(strtolower($ext), $allowed)) {
            // Create a unique filename
            $new_filename = uniqid('recipe_') . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;

            if(move_uploaded_file($_FILES['recipe_image']['tmp_name'], $upload_path)) {
                $image_url = $upload_path;

                // Get the old image to delete it later
                $old_image_query = "SELECT image_url FROM recipes WHERE id = $id";
                $old_image_result = mysqli_query($conn, $old_image_query);
                $old_image_row = mysqli_fetch_assoc($old_image_result);
                $old_image_url = $old_image_row['image_url'];

                // Update query with new image
                $query = "UPDATE recipes SET
                          title = '$title',
                          description = '$description',
                          ingredients = '$ingredients',
                          instructions = '$instructions',
                          preparation_time = $preparation_time,
                          cooking_time = $cooking_time,
                          servings = $servings,
                          difficulty = '$difficulty',
                          image_url = '$image_url',
                          category_id = $category_id
                          WHERE id = $id";

                // Delete the old image if update is successful
                if (mysqli_query($conn, $query)) {
                    if (!empty($old_image_url) && file_exists($old_image_url) && $old_image_url != $image_url) {
                        unlink($old_image_url);
                    }
                    echo json_encode(['success' => true, 'message' => 'Recipe updated successfully']);
                } else {
                    echo json_encode(['error' => 'Failed to update recipe: ' . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['error' => 'Failed to upload image. Please try again.']);
            }
        } else {
            echo json_encode(['error' => 'Invalid file type. Please upload JPG, PNG, or GIF images.']);
        }
    } else {
        // Update query without changing the image
        $query = "UPDATE recipes SET
                  title = '$title',
                  description = '$description',
                  ingredients = '$ingredients',
                  instructions = '$instructions',
                  preparation_time = $preparation_time,
                  cooking_time = $cooking_time,
                  servings = $servings,
                  difficulty = '$difficulty',
                  category_id = $category_id
                  WHERE id = $id";

        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Recipe updated successfully']);
        } else {
            echo json_encode(['error' => 'Failed to update recipe: ' . mysqli_error($conn)]);
        }
    }
    exit;
}
?>