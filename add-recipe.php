<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe - Cake Recipes</title>
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
                    <li><a href="add-recipe.php" class="active">Add Recipe</a></li>
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
        <section class="add-recipe-section">
            <h2 class="section-title">Add New Recipe</h2>

            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="login-message">
                    <p>Please <a href="login.php">login</a> to add a recipe.</p>
                </div>
            <?php else: ?>
                <form class="recipe-form" action="add_recipe.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Recipe Name</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Chocolate">Chocolate</option>
                            <option value="Fruit">Fruit</option>
                            <option value="Birthday">Birthday</option>
                            <option value="No-Bake">No-Bake</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="difficulty">Difficulty</label>
                        <select id="difficulty" name="difficulty" required>
                            <option value="">Select Difficulty</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="preparation_time">Preparation Time (minutes)</label>
                            <input type="number" id="preparation_time" name="preparation_time" required>
                        </div>

                        <div class="form-group half">
                            <label for="cooking_time">Cooking Time (minutes)</label>
                            <input type="number" id="cooking_time" name="cooking_time" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="servings">Servings</label>
                        <input type="number" id="servings" name="servings" required>
                    </div>

                    <div class="form-group">
                        <label for="recipe_image">Recipe Image</label>
                        <input type="file" id="recipe_image" name="recipe_image" accept="image/*" onchange="previewImage(this)">
                        <p class="form-help">Upload an image of your recipe (JPG, PNG, or GIF)</p>
                        <div id="image-preview" class="image-preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="ingredients">Ingredients (one per line)</label>
                        <textarea id="ingredients" name="ingredients" rows="5" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="instructions">Instructions</label>
                        <textarea id="instructions" name="instructions" rows="8" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Recipe</button>
                    <div id="message" class="form-message"></div>
                </form>

                <script>
                    // Function to preview the uploaded image
                    function previewImage(input) {
                        const preview = document.getElementById('image-preview');
                        preview.innerHTML = '';

                        if (input.files && input.files[0]) {
                            const reader = new FileReader();

                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'preview-img';
                                preview.appendChild(img);
                            }

                            reader.readAsDataURL(input.files[0]);
                        }
                    }

                    document.querySelector('.recipe-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);

                        fetch('add_recipe.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            const messageDiv = document.getElementById('message');

                            if (data.error) {
                                messageDiv.textContent = data.error;
                                messageDiv.className = 'form-message error';
                            } else if (data.success) {
                                messageDiv.textContent = data.message;
                                messageDiv.className = 'form-message success';
                                // Redirect to home page after successful recipe submission
                                setTimeout(() => {
                                    window.location.href = 'index.php';
                                }, 2000);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('message').textContent = 'An error occurred. Please try again.';
                            document.getElementById('message').className = 'form-message error';
                        });
                    });
                </script>
            <?php endif; ?>
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
