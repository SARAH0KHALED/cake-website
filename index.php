<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cake Recipes - Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
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
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="browse.php">Browse</a></li>
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

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h2>Discover Delicious Cake Recipes</h2>
                    <p>Find the perfect cake for any occasion</p>
                    <a href="browse.php" class="btn btn-primary">Browse Recipes</a>
                </div>
            </div>
        </section>

        <!-- Most Popular Section -->
        <section class="recipes-section popular-section">
            <div class="container">
                <h2 class="section-title">Most Popular</h2>
                <div class="recipe-cards">
                    <?php

                    // Get recipes from database (limit to 4 for the homepage)
                    $query = "SELECT r.*, c.name as category_name
                              FROM recipes r
                              LEFT JOIN categories c ON r.category_id = c.id
                              ORDER BY r.id DESC
                              LIMIT 4";

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
                        // If no recipes in database, show a message
                        echo '<div class="no-recipes"><p>No recipes found. Be the first to add a recipe!</p></div>';
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Latest Recipes Section -->
        <section class="recipes-section latest-section">
            <div class="container">
                <h2 class="section-title">Latest Recipes</h2>
                <div class="recipe-cards">
                    <?php
                    // Get latest recipes from database (skip the first 4 that are already shown in Most Popular)
                    $query = "SELECT r.*, c.name as category_name
                              FROM recipes r
                              LEFT JOIN categories c ON r.category_id = c.id
                              ORDER BY r.created_at DESC
                              LIMIT 4, 4";

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
                    } else if (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM recipes LIMIT 1")) > 0) {
                        // If there are recipes but not enough for this section
                        echo '<div class="no-recipes"><p>Check back soon for more delicious recipes!</p></div>';
                    }
                    ?>
                </div>

                <div class="view-all-link">
                    <a href="browse.php" class="btn btn-primary">View All Recipes</a>
                </div>
            </div>
        </section>

        <!-- Join Us Section -->
        <section class="join-us">
            <div class="container">
                <div class="join-content">
                    <h2>Join Our Cake Loving Community</h2>
                    <p>Create an account to share your own recipes, save your favorites, and connect with other bakers.</p>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="add-recipe.php" class="btn btn-primary">Add Your Recipe</a>
                    <?php else: ?>
                        <a href="signup.php" class="btn btn-primary">Register Now</a>
                    <?php endif; ?>
                </div>
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
