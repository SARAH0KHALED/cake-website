<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Recipes - Cake Recipes</title>
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
                    <li><a href="search.php" class="active">Search</a></li>
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
        <section class="search-section">
            <h2 class="section-title">Search Recipes</h2>

            <div class="search-box">
                <form action="search.php" method="get">
                    <input type="text" placeholder="Search for recipes..." name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>

            <div class="search-filters">
                <div class="filter">
                    <label for="category">Category:</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php
                        // Get all categories from database
                        $categories_query = "SELECT DISTINCT name FROM categories ORDER BY name";
                        $categories_result = mysqli_query($conn, $categories_query);

                        if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                            while ($category = mysqli_fetch_assoc($categories_result)) {
                                $category_name = $category['name'];
                                $selected = (isset($_GET['category']) && $_GET['category'] == $category_name) ? ' selected' : '';
                                echo "<option value=\"$category_name\"$selected>$category_name</option>";
                            }
                        } else {
                            // Default categories if none in database
                            $default_categories = ['Chocolate', 'Fruit', 'Birthday', 'Cheesecakes', 'No-Bake'];
                            foreach ($default_categories as $category_name) {
                                $selected = (isset($_GET['category']) && $_GET['category'] == $category_name) ? ' selected' : '';
                                echo "<option value=\"$category_name\"$selected>$category_name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="filter">
                    <label for="difficulty">Difficulty:</label>
                    <select id="difficulty" name="difficulty">
                        <option value="">Any Difficulty</option>
                        <?php
                        // Difficulty levels
                        $difficulties = ['easy', 'medium', 'hard'];

                        foreach ($difficulties as $diff) {
                            $selected = (isset($_GET['difficulty']) && strtolower($_GET['difficulty']) == $diff) ? ' selected' : '';
                            $diff_display = ucfirst($diff);
                            echo "<option value=\"$diff\"$selected>$diff_display</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="search-results">
                <?php if(isset($_GET['q'])): ?>
                    <h3>Search results for: <?php echo htmlspecialchars($_GET['q']); ?></h3>
                    <div id="results-container">
                        <!-- Results will be loaded here via AJAX -->
                        <p>Loading results...</p>
                    </div>
                    <script>
                        // Load search results via AJAX
                        document.addEventListener('DOMContentLoaded', function() {
                            const query = '<?php echo isset($_GET['q']) ? $_GET['q'] : ""; ?>';
                            const category = '<?php echo isset($_GET['category']) ? $_GET['category'] : ""; ?>';
                            const difficulty = '<?php echo isset($_GET['difficulty']) ? $_GET['difficulty'] : ""; ?>';

                            // Add event listeners to filter dropdowns
                            document.getElementById('category').addEventListener('change', updateFilters);
                            document.getElementById('difficulty').addEventListener('change', updateFilters);

                            function updateFilters() {
                                const newCategory = document.getElementById('category').value;
                                const newDifficulty = document.getElementById('difficulty').value;

                                // Redirect to the same page with updated filters
                                window.location.href = `search.php?q=${query}&category=${newCategory}&difficulty=${newDifficulty}`;
                            }

                            if(query) {
                                fetch(`search_recipes.php?q=${query}&category=${category}&difficulty=${difficulty}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        const container = document.getElementById('results-container');

                                        if(data.error) {
                                            container.innerHTML = `<p class="error">${data.error}</p>`;
                                            return;
                                        }

                                        if(data.length === 0) {
                                            container.innerHTML = '<p>No recipes found matching your search.</p>';
                                            return;
                                        }

                                        let html = '<div class="recipe-cards">';

                                        data.forEach(recipe => {
                                            // Calculate total time
                                            let totalTime = parseInt(recipe.preparation_time) || 0;
                                            if (recipe.cooking_time) {
                                                totalTime += parseInt(recipe.cooking_time);
                                            }

                                            // Get difficulty level
                                            const difficulty = recipe.difficulty || 'medium';

                                            // Truncate description if too long
                                            let description = recipe.description || '';
                                            if (description.length > 100) {
                                                description = description.substring(0, 100) + '...';
                                            }

                                            // Set default image if none exists
                                            const imageUrl = recipe.image_url || 'default-recipe.jpg';

                                            html += `
                                                <div class="recipe-card">
                                                    <div class="recipe-image">
                                                        <img src="${imageUrl}" alt="${recipe.title}">
                                                    </div>
                                                    <div class="recipe-content">
                                                        <h3>${recipe.title}</h3>
                                                        <div class="recipe-meta">
                                                            <span><i class="fas fa-clock"></i> ${totalTime} mins</span>
                                                            <span><i class="fas fa-signal"></i> ${difficulty.charAt(0).toUpperCase() + difficulty.slice(1)}</span>
                                                            ${recipe.category_name ? `<span><i class="fas fa-layer-group"></i> ${recipe.category_name}</span>` : ''}
                                                        </div>
                                                        <p>${description}</p>
                                                        <a href="recipe.php?id=${recipe.id}" class="btn btn-secondary">View Recipe</a>
                                                    </div>
                                                </div>
                                            `;
                                        });

                                        html += '</div>';
                                        container.innerHTML = html;
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        document.getElementById('results-container').innerHTML =
                                            '<p class="error">An error occurred while searching. Please try again.</p>';
                                    });
                            }
                        });
                    </script>
                <?php else: ?>
                    <p>Enter search terms to find recipes</p>
                <?php endif; ?>
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
