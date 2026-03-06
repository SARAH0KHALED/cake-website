<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to delete a recipe']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid recipe ID']);
        exit;
    }

    // Check if recipe exists and belongs to the user
    $check_query = "SELECT user_id, image_url FROM recipes WHERE id = $id";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) == 0) {
        echo json_encode(['error' => 'Recipe not found']);
        exit;
    }

    $recipe = mysqli_fetch_assoc($check_result);
    if ($recipe['user_id'] != $user_id) {
        echo json_encode(['error' => 'You do not have permission to delete this recipe']);
        exit;
    }

    // Delete the recipe
    $query = "DELETE FROM recipes WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        // Delete the image file if it exists
        if (!empty($recipe['image_url']) && file_exists($recipe['image_url'])) {
            unlink($recipe['image_url']);
        }

        echo json_encode(['success' => true, 'message' => 'Recipe deleted successfully']);
    } else {
        echo json_encode(['error' => 'Error deleting recipe: ' . mysqli_error($conn)]);
    }
    exit;
}
?>