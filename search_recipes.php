<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get search term from URL parameter
    $search = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (empty($search)) {
        echo json_encode(['error' => 'Please enter a search term']);
        exit;
    }

    // Get filter parameters
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $difficulty = isset($_GET['difficulty']) ? trim($_GET['difficulty']) : '';

    // Build the search query with filters
    $query = "SELECT r.*, c.name as category_name
              FROM recipes r
              LEFT JOIN categories c ON r.category_id = c.id
              WHERE (r.title LIKE '%$search%'
              OR r.description LIKE '%$search%'
              OR r.ingredients LIKE '%$search%'
              OR r.instructions LIKE '%$search%'
              OR c.name LIKE '%$search%')";

    // Add category filter if specified
    if (!empty($category)) {
        $query .= " AND c.name LIKE '%$category%'";
    }

    // Add difficulty filter if specified
    if (!empty($difficulty)) {
        $query .= " AND r.difficulty = '$difficulty'";
    }

    // Order by creation date (newest first)
    $query .= " ORDER BY r.created_at DESC";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo json_encode(['error' => 'Failed to search recipes: ' . mysqli_error($conn)]);
        exit;
    }

    $recipes = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $recipes[] = $row;
    }

    echo json_encode($recipes);
}
?>
