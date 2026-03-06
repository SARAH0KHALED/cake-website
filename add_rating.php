<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to rate a recipe']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_id = intval($_POST['recipe_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    if ($recipe_id <= 0) {
        echo json_encode(['error' => 'Invalid recipe ID']);
        exit;
    }
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['error' => 'Rating must be between 1 and 5']);
        exit;
    }
    
    // Check if recipe exists
    $check_query = "SELECT id FROM recipes WHERE id = $recipe_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        echo json_encode(['error' => 'Recipe not found']);
        exit;
    }
    
    // Check if user already rated this recipe
    $check_query = "SELECT id FROM ratings WHERE recipe_id = $recipe_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing rating
        $query = "UPDATE ratings SET rating = $rating, comment = '$comment' WHERE recipe_id = $recipe_id AND user_id = $user_id";
    } else {
        // Insert new rating
        $query = "INSERT INTO ratings (recipe_id, user_id, rating, comment) VALUES ($recipe_id, $user_id, $rating, '$comment')";
    }
    
    if (mysqli_query($conn, $query)) {
        // Calculate average rating
        $avg_query = "SELECT AVG(rating) as avg_rating FROM ratings WHERE recipe_id = $recipe_id";
        $avg_result = mysqli_query($conn, $avg_query);
        $avg_row = mysqli_fetch_assoc($avg_result);
        $avg_rating = round($avg_row['avg_rating'], 1);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rating added successfully',
            'avg_rating' => $avg_rating
        ]);
    } else {
        echo json_encode(['error' => 'Failed to add rating: ' . mysqli_error($conn)]);
    }
    exit;
}
?>
