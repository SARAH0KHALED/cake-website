<?php
session_start();
include 'db.php';

// Handle rating submission
$rating_message = '';
$rating_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_id']) && isset($_SESSION['user_id'])) {
    $recipe_id = intval($_POST['recipe_id']);
    $user_id = $_SESSION['user_id'];

    // Check if rating is set
    if (!isset($_POST['rating']) || empty($_POST['rating'])) {
        $rating_error = 'Please select a rating before submitting.';
    } else {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment'] ?? '');

        // Validate rating
        if ($rating < 1 || $rating > 5) {
            $rating_error = 'Rating must be between 1 and 5.';
        } else {
            // Check if recipe exists and if user is not the creator
            $check_query = "SELECT id, user_id FROM recipes WHERE id = $recipe_id";
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) == 0) {
                $rating_error = 'Recipe not found.';
            } else {
                $recipe_data = mysqli_fetch_assoc($check_result);

                // Check if user is trying to rate their own recipe
                if ($recipe_data['user_id'] == $user_id) {
                    $rating_error = 'You cannot rate your own recipe.';
                } else {
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

                    $rating_message = 'Thank you! Your rating has been submitted successfully.';
                } else {
                    $rating_error = 'Failed to add rating: ' . mysqli_error($conn);
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Details - Cake Recipes</title>
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

    <main class="container">
        <?php
        // Check if we have an ID parameter (for database lookup)
        if(isset($_GET['id'])) {
            $id = intval($_GET['id']);

            // Get recipe details from database
            $query = "SELECT r.*, c.name as category_name, u.username as author
                      FROM recipes r
                      LEFT JOIN categories c ON r.category_id = c.id
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.id = $id";

            $result = mysqli_query($conn, $query);

            if($result && mysqli_num_rows($result) > 0) {
                $recipe = mysqli_fetch_assoc($result);
                ?>
                <section class="recipe-details">
                    <div class="recipe-header">
                        <h1><?php echo $recipe['title']; ?></h1>
                        <div class="recipe-meta">
                            <span><i class="fas fa-clock"></i> <?php echo $recipe['preparation_time'] + $recipe['cooking_time']; ?> mins</span>
                            <span><i class="fas fa-signal"></i> <?php echo ucfirst($recipe['difficulty'] ?? 'Medium'); ?></span>
                            <span><i class="fas fa-layer-group"></i> <?php echo $recipe['category_name']; ?></span>
                            <?php if(!empty($recipe['author'])): ?>
                                <span><i class="fas fa-user"></i> By <?php echo $recipe['author']; ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']): ?>
                        <div class="recipe-actions">
                            <a href="edit-recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-edit"></i> Edit Recipe
                            </a>
                            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $recipe['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete Recipe
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="recipe-content">
                        <div class="recipe-image">
                            <img src="<?php echo $recipe['image_url'] ?: 'default-recipe.jpg'; ?>" alt="<?php echo $recipe['title']; ?>">
                        </div>
                        <div class="recipe-info">
                            <div class="ingredients">
                                <h3>Ingredients</h3>
                                <?php echo nl2br($recipe['ingredients']); ?>
                            </div>
                            <div class="instructions">
                                <h3>Instructions</h3>
                                <?php echo nl2br($recipe['instructions']); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Progress Section -->
                    <div class="rating-progress-section">
                        <h3>Recipe Ratings</h3>
                        <?php
                        // Get rating statistics
                        $rating_stats_query = "SELECT rating, COUNT(*) as count FROM ratings WHERE recipe_id = $id GROUP BY rating ORDER BY rating DESC";
                        $rating_stats_result = mysqli_query($conn, $rating_stats_query);

                        // Get total ratings
                        $total_ratings_query = "SELECT COUNT(*) as total FROM ratings WHERE recipe_id = $id";
                        $total_ratings_result = mysqli_query($conn, $total_ratings_query);
                        $total_ratings = mysqli_fetch_assoc($total_ratings_result)['total'];

                        // Initialize rating counts
                        $rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

                        // Fill in actual counts
                        if ($rating_stats_result) {
                            while ($row = mysqli_fetch_assoc($rating_stats_result)) {
                                $rating_counts[$row['rating']] = $row['count'];
                            }
                        }

                        // Get average rating
                        $avg_rating_query = "SELECT AVG(rating) as avg_rating FROM ratings WHERE recipe_id = $id";
                        $avg_rating_result = mysqli_query($conn, $avg_rating_query);
                        $avg_rating = 0;

                        if ($avg_rating_result && mysqli_num_rows($avg_rating_result) > 0) {
                            $avg_rating = round(mysqli_fetch_assoc($avg_rating_result)['avg_rating'], 1);
                        }
                        ?>

                        <div class="rating-summary">
                            <div class="average-rating">
                                <span class="rating-number"><?php echo $avg_rating; ?></span>
                                <div class="rating-stars-display">
                                    <?php
                                    // Display filled and empty stars based on average rating
                                    $filled = floor($avg_rating);
                                    $half = $avg_rating - $filled >= 0.5;

                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $filled) {
                                            echo '<i class="fas fa-star"></i>'; // Filled star
                                        } elseif ($i == $filled + 1 && $half) {
                                            echo '<i class="fas fa-star-half-alt"></i>'; // Half star
                                        } else {
                                            echo '<i class="far fa-star"></i>'; // Empty star
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="total-ratings"><?php echo $total_ratings; ?> ratings</span>
                            </div>

                            <div class="rating-bars">
                                <?php for ($i = 5; $i >= 1; $i--):
                                    $percentage = $total_ratings > 0 ? ($rating_counts[$i] / $total_ratings) * 100 : 0;
                                ?>
                                <div class="rating-bar-row">
                                    <span class="rating-label"><?php echo $i; ?> stars</span>
                                    <div class="rating-bar">
                                        <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="rating-count"><?php echo $rating_counts[$i]; ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_id'] != $recipe['user_id']): ?>
                        <div class="rating-section">
                            <h3>Rate this Recipe</h3>
                            <?php if(!empty($rating_message)): ?>
                                <div class="success-message"><?php echo $rating_message; ?></div>
                            <?php endif; ?>
                            <?php if(!empty($rating_error)): ?>
                                <div class="error-message"><?php echo $rating_error; ?></div>
                            <?php endif; ?>
                            <form id="rating-form" action="recipe.php?id=<?php echo $id; ?>" method="post">
                                <input type="hidden" name="recipe_id" value="<?php echo $id; ?>">
                                <div class="rating-stars">
                                    <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="Excellent"></label>
                                    <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="Very Good"></label>
                                    <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="Good"></label>
                                    <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="Fair"></label>
                                    <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="Poor"></label>
                                </div>
                                <div class="rating-help">Click on a star to rate this recipe</div>
                                <textarea name="comment" placeholder="Leave a comment (optional)"></textarea>
                                <button type="submit" class="btn btn-primary">Submit Rating</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
                <?php
            } else {
                echo '<div class="error-message">Recipe not found.</div>';
            }
        } else {
            // Use the name parameter for static recipes (as in the original HTML)
            ?>
            <!-- Chocolate Fudge Cake -->
            <section id="Chocolate Fudge Cake" class="recipe-details">
                <div class="recipe-header">
                    <h1>Chocolate Fudge Cake</h1>
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> 45 mins</span>
                        <span><i class="fas fa-signal"></i> Easy</span>
                        <span><i class="fas fa-layer-group"></i> Chocolate</span>
                    </div>
                </div>
                <div class="recipe-content">
                    <div class="recipe-image">
                        <img src="chocoCake copy.jpeg" alt="Chocolate Cake">
                    </div>
                    <div class="recipe-info">
                        <div class="ingredients">
                            <h3>Ingredients</h3>
                            <ul>
                                <li>2 cups all-purpose flour</li>
                                <li>2 cups sugar</li>
                                <li>3/4 cup cocoa powder</li>
                                <li>2 tsp baking soda</li>
                                <li>1 tsp baking powder</li>
                                <li>1/2 tsp salt</li>
                                <li>1 cup buttermilk</li>
                                <li>1 cup coffee</li>
                                <li>1/2 cup oil</li>
                                <li>2 eggs</li>
                            </ul>
                        </div>
                        <div class="instructions">
                            <h3>Instructions</h3>
                            <ol>
                                <li>Preheat oven to 175°C.</li>
                                <li>Mix dry ingredients, then add wet.</li>
                                <li>Bake for 30-35 mins.</li>
                                <li>Let cool and frost.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Classic Vanilla Cake -->
            <section id="Classic Vanilla Cake" class="recipe-details">
                <div class="recipe-header">
                    <h1>Classic Vanilla Cake</h1>
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> 55 mins</span>
                        <span><i class="fas fa-signal"></i> Easy</span>
                        <span><i class="fas fa-layer-group"></i> Vanilla</span>
                    </div>
                </div>
                <div class="recipe-content">
                    <div class="recipe-image">
                        <img src="Perfect Vanilla Cake Recipe- Moist & Easy -Baking a Moment.jpeg" alt="Vanilla Cake">
                    </div>
                    <div class="recipe-info">
                        <div class="ingredients">
                            <h3>Ingredients</h3>
                            <ul>
                                <li>2.5 cups flour</li>
                                <li>2 cups sugar</li>
                                <li>3 tsp baking powder</li>
                                <li>1 tsp salt</li>
                                <li>1 cup milk</li>
                                <li>1/2 cup oil</li>
                                <li>1 tbsp vanilla extract</li>
                                <li>2 eggs</li>
                                <li>1 cup boiling water</li>
                            </ul>
                        </div>
                        <div class="instructions">
                            <h3>Instructions</h3>
                            <ol>
                                <li>Preheat oven to 175°C.</li>
                                <li>Mix dry ingredients, then wet.</li>
                                <li>Add boiling water carefully.</li>
                                <li>Bake for 30-35 mins.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Carrot Cake -->
            <section id="Carrot Cake" class="recipe-details">
                <div class="recipe-header">
                    <h1>Carrot Cake</h1>
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> 70 mins</span>
                        <span><i class="fas fa-signal"></i> Medium</span>
                        <span><i class="fas fa-layer-group"></i> Vegetable</span>
                    </div>
                </div>
                <div class="recipe-content">
                    <div class="recipe-image">
                        <img src="Easy Carrot Cake Recipe - Preppy Kitchen.jpeg" alt="Carrot Cake">
                    </div>
                    <div class="recipe-info">
                        <div class="ingredients">
                            <h3>Ingredients</h3>
                            <ul>
                                <li>2 cups grated carrots</li>
                                <li>1.5 cups flour</li>
                                <li>1.5 cups sugar</li>
                                <li>1 tsp cinnamon</li>
                                <li>1 tsp baking soda</li>
                                <li>1/2 tsp salt</li>
                                <li>3/4 cup oil</li>
                                <li>3 eggs</li>
                            </ul>
                        </div>
                        <div class="instructions">
                            <h3>Instructions</h3>
                            <ol>
                                <li>Preheat oven to 180°C.</li>
                                <li>Mix all ingredients.</li>
                                <li>Bake for 40-45 mins.</li>
                                <li>Cool and frost with cream cheese.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Lemon Cake -->
            <section id="Lemon Cake" class="recipe-details">
                <div class="recipe-header">
                    <h1>Lemon Cake</h1>
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> 65 mins</span>
                        <span><i class="fas fa-signal"></i> Medium</span>
                        <span><i class="fas fa-layer-group"></i> Fruit</span>
                    </div>
                </div>
                <div class="recipe-content">
                    <div class="recipe-image">
                        <img src="Lemon Sponge Cake.jpeg" alt="Lemon Cake">
                    </div>
                    <div class="recipe-info">
                        <div class="ingredients">
                            <h3>Ingredients</h3>
                            <ul>
                                <li>2 cups flour</li>
                                <li>1.5 cups sugar</li>
                                <li>1 tbsp lemon zest</li>
                                <li>1/2 cup lemon juice</li>
                                <li>1/2 cup butter</li>
                                <li>2 eggs</li>
                                <li>1 tsp baking powder</li>
                                <li>1/2 tsp salt</li>
                            </ul>
                        </div>
                        <div class="instructions">
                            <h3>Instructions</h3>
                            <ol>
                                <li>Preheat oven to 180°C.</li>
                                <li>Cream butter and sugar.</li>
                                <li>Add rest of ingredients.</li>
                                <li>Bake 35-40 mins.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Classic Cheesecake -->
            <section id="Classic Cheesecake" class="recipe-details">
                <div class="recipe-header">
                    <h1>Classic Cheesecake</h1>
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> 120 mins</span>
                        <span><i class="fas fa-signal"></i> Hard</span>
                        <span><i class="fas fa-layer-group"></i> No-Bake</span>
                    </div>
                </div>
                <div class="recipe-content">
                    <div class="recipe-image">
                        <img src="New York Cheeseceake mit Himbeeren • Käsekuchen & Cheesecakes.jpeg" alt="Cheesecake">
                    </div>
                    <div class="recipe-info">
                        <div class="ingredients">
                            <h3>Ingredients</h3>
                            <ul>
                                <li>2 cups cream cheese</li>
                                <li>1 cup sugar</li>
                                <li>1 tsp vanilla</li>
                                <li>3 eggs</li>
                                <li>1 cup sour cream</li>
                                <li>Graham cracker crust</li>
                            </ul>
                        </div>
                        <div class="instructions">
                            <h3>Instructions</h3>
                            <ol>
                                <li>Preheat oven to 160°C.</li>
                                <li>Mix ingredients until smooth.</li>
                                <li>Pour into crust and bake 60 mins.</li>
                                <li>Chill for 3+ hours.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Birthday Cake -->
            <section id="Birthday Cake" class="recipe-details">
                <div class="recipe-header">
                    <h1>Birthday Cake</h1>
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> 90 mins</span>
                        <span><i class="fas fa-signal"></i> Medium</span>
                        <span><i class="fas fa-layer-group"></i> Birthday</span>
                    </div>
                </div>
                <div class="recipe-content">
                    <div class="recipe-image">
                        <img src="Vanilla Cake with Vanilla Buttercream Frosting.jpeg" alt="Birthday Cake">
                    </div>
                    <div class="recipe-info">
                        <div class="ingredients">
                            <h3>Ingredients</h3>
                            <ul>
                                <li>2.5 cups flour</li>
                                <li>2 cups sugar</li>
                                <li>1 cup milk</li>
                                <li>1/2 cup butter</li>
                                <li>2 tsp vanilla</li>
                                <li>3 eggs</li>
                                <li>1 tbsp sprinkles</li>
                            </ul>
                        </div>
                        <div class="instructions">
                            <h3>Instructions</h3>
                            <ol>
                                <li>Preheat oven to 175°C.</li>
                                <li>Cream butter and sugar.</li>
                                <li>Add remaining ingredients and mix.</li>
                                <li>Bake for 35-40 mins. Cool and decorate.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
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
                        <li><a href="browse.php?category=Fruit">Fruit</a></li>
                        <li><a href="browse.php?category=Birthday">Birthday</a></li>
                        <li><a href="browse.php?category=No-Bake">No-Bake</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Cake Recipes. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    // Function to confirm recipe deletion
    function confirmDelete(recipeId) {
        if (confirm('Are you sure you want to delete this recipe? This action cannot be undone.')) {
            // Send delete request
            fetch('delete_recipe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + recipeId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Redirect to home page after successful deletion
                    window.location.href = 'index.php';
                } else {
                    alert(data.error || 'An error occurred while deleting the recipe.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const params = new URLSearchParams(window.location.search);
        const recipeName = params.get("name");

        if (recipeName) {
            document.querySelectorAll(".recipe-details").forEach(section => {
                section.style.display = section.id === recipeName ? "block" : "none";
            });
        }

        // Handle star rating selection
        const ratingForm = document.getElementById('rating-form');
        if (ratingForm) {
            const stars = ratingForm.querySelectorAll('.rating-stars input');
            const ratingHelp = ratingForm.querySelector('.rating-help');

            stars.forEach(star => {
                star.addEventListener('change', function() {
                    const rating = this.value;
                    ratingHelp.textContent = `You selected ${rating} star${rating > 1 ? 's' : ''}`;
                    ratingHelp.style.fontWeight = 'bold';
                    ratingHelp.style.color = '#FFD700';
                });
            });

            // Form validation
            ratingForm.addEventListener('submit', function(e) {
                const selectedRating = ratingForm.querySelector('input[name="rating"]:checked');
                if (!selectedRating) {
                    e.preventDefault();
                    ratingHelp.textContent = 'Please select a rating before submitting';
                    ratingHelp.style.color = 'red';
                    ratingHelp.style.fontWeight = 'bold';
                }
            });
        }
    });
    </script>
</body>
</html>
