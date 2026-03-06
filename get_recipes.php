<?php
include 'db.php';

// Simple query without filtering
$query = "SELECT r.*, c.name as category_name 
          FROM recipes r
          LEFT JOIN categories c ON r.category_id = c.id";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'Failed to fetch recipes: ' . mysqli_error($conn)]);
    exit;
}

$recipes = array();
while ($row = mysqli_fetch_assoc($result)) {
    $recipes[] = $row;
}

echo json_encode($recipes);
?>
