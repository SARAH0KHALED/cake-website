<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid recipe ID']);
        exit;
    }
    
    // Get recipe details
    $query = "SELECT r.*, c.name as category_name, u.username as author
              FROM recipes r
              LEFT JOIN categories c ON r.category_id = c.id
              LEFT JOIN users u ON r.user_id = u.id
              WHERE r.id = $id";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['error' => 'Failed to fetch recipe: ' . mysqli_error($conn)]);
        exit;
    }
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['error' => 'Recipe not found']);
        exit;
    }
    
    $recipe = mysqli_fetch_assoc($result);
    
    // Get ratings
    $ratings_query = "SELECT r.*, u.username 
                      FROM ratings r
                      JOIN users u ON r.user_id = u.id
                      WHERE r.recipe_id = $id
                      ORDER BY r.created_at DESC";
    
    $ratings_result = mysqli_query($conn, $ratings_query);
    
    $ratings = array();
    if ($ratings_result) {
        while ($row = mysqli_fetch_assoc($ratings_result)) {
            $ratings[] = $row;
        }
    }
    
    $recipe['ratings'] = $ratings;
    
    // Calculate average rating
    $avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
                  FROM ratings 
                  WHERE recipe_id = $id";
    
    $avg_result = mysqli_query($conn, $avg_query);
    
    if ($avg_result) {
        $avg_row = mysqli_fetch_assoc($avg_result);
        $recipe['avg_rating'] = round($avg_row['avg_rating'], 1);
        $recipe['total_ratings'] = $avg_row['total_ratings'];
    }
    
    echo json_encode($recipe);
}
?>
