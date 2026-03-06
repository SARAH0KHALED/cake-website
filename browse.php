<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Recipes - Cake Recipes</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>Cake Recipes</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="browse.php" class="active">Browse</a></li>
                    <li><a href="search.php">Search</a></li>
                    <li><a href="add-recipe.php">Add Recipe</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="recipes-section">
            <h2 class="section-title">All Cake Recipes</h2>

            <div class="filters">
                <div class="filter-category">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        // Get all categories from database
                        $categories_query = "SELECT DISTINCT name FROM categories ORDER BY name";
                        $categories_result = mysqli_query($conn, $categories_query);

                        // Current selected category
                        $current_category = isset($_GET['category']) ? $_GET['category'] : '';

                        // Add 'All' option
                        echo '<li><a href="browse.php"' . (empty($current_category) && !isset($_GET['difficulty']) ? ' class="active"' : '') . '>All</a></li>';

                        // Add categories from database
                        if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                            while ($category = mysqli_fetch_assoc($categories_result)) {
                                $category_name = $category['name'];
                                $is_active = ($current_category == $category_name) ? ' class="active"' : '';
                                echo "<li><a href=\"browse.php?category=$category_name\"$is_active>$category_name</a></li>";
                            }
                        } else {
                            // Default categories if none in database
                            $default_categories = ['Chocolate', 'Fruit', 'Birthday', 'Cheesecakes', 'No-Bake'];
                            foreach ($default_categories as $category_name) {
                                $is_active = ($current_category == $category_name) ? ' class="active"' : '';
                                echo "<li><a href=\"browse.php?category=$category_name\"$is_active>$category_name</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </div>

                <div class="filter-difficulty">
                    <h3>Difficulty</h3>
                    <ul>
                        <?php
                        // Current selected difficulty
                        $current_difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

                        // Difficulty levels
                        $difficulties = ['easy', 'medium', 'hard'];

                        foreach ($difficulties as $diff) {
                            $is_active = (strtolower($current_difficulty) == $diff) ? ' class="active"' : '';
                            $diff_display = ucfirst($diff);
                            echo "<li><a href=\"browse.php?difficulty=$diff\"$is_active>$diff_display</a></li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="recipe-cards">
                <?php

                // Build the query based on filters
                $query = "SELECT r.*, c.name as category_name
                          FROM recipes r
                          LEFT JOIN categories c ON r.category_id = c.id";

                $where_clauses = array();

                // Filter by category if specified
                if (isset($_GET['category']) && !empty($_GET['category'])) {
                    $category = mysqli_real_escape_string($conn, $_GET['category']);
                    $where_clauses[] = "c.name LIKE '%$category%'";
                }

                // Filter by difficulty if specified
                if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
                    $difficulty = mysqli_real_escape_string($conn, $_GET['difficulty']);
                    $where_clauses[] = "r.difficulty = '$difficulty'";
                }

                // Add WHERE clause if filters are applied
                if (!empty($where_clauses)) {
                    $query .= " WHERE " . implode(" AND ", $where_clauses);
                }

                // Order by creation date (newest first)
                $query .= " ORDER BY r.created_at DESC";

                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($recipe = mysqli_fetch_assoc($result)) {
                        // Calculate total time
                        $total_time = $recipe['preparation_time'];
                        if (isset($recipe['cooking_time'])) {
                            $total_time += $recipe['cooking_time'];
                        }

                        // Set default image if none exists
                        $image_url = !empty($recipe['image_url']) ? $recipe['image_url'] : 'default-recipe.jpg';

                        // Get difficulty level
                        $difficulty = isset($recipe['difficulty']) ? $recipe['difficulty'] : 'medium';

                        // Truncate description if too long
                        $description = substr($recipe['description'], 0, 100);
                        if (strlen($recipe['description']) > 100) {
                            $description .= '...';
                        }
                ?>
                <div class="recipe-card">
                    <div class="recipe-image">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    </div>
                    <div class="recipe-content">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <div class="recipe-meta">
                            <span><i class="fas fa-clock"></i> <?php echo $total_time; ?> mins</span>
                            <span><i class="fas fa-signal"></i> <?php echo ucfirst($difficulty); ?></span>
                            <?php if (!empty($recipe['category_name'])): ?>
                            <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($recipe['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo htmlspecialchars($description); ?></p>
                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">View Recipe</a>
                    </div>
                </div>
                <?php
                    }
                } else {
                    // If no recipes found, show a message
                    echo '<div class="no-recipes"><p>No recipes found matching your criteria.</p>';
                    if (!empty($where_clauses)) {
                        echo '<p><a href="browse.php" class="btn btn-primary">View All Recipes</a></p>';
                    } else {
                        echo '<p>Be the first to <a href="add-recipe.php">add a recipe</a>!</p>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Cake Recipes</h3>
                    <p>Delicious recipes for every occasion</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="browse.php">Browse Recipes</a></li>
                        <li><a href="add-recipe.php">Add Recipe</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-categories">
                    <h4>Categories</h4>
                    <ul>
                        <li><a href="browse.php?category=Chocolate">Chocolate</a></li>
                        <li><a href="browse.php?category=Vanilla">Vanilla</a></li>
                        <li><a href="browse.php?category=Birthday">Birthday</a></li>
                        <li><a href="browse.php?category=No-Bake">No-Bake</a></li>
                    </ul>
                </div>
                <div class="footer-newsletter">
                    <h4>Newsletter</h4>
                    <p>Subscribe to receive new recipes</p>
                    <form class="newsletter-form" action="subscribe.php" method="post">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Cake Recipes. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
