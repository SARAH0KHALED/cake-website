<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if recipe ID is provided
if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$recipe_id = intval($_GET['id']);

// Get recipe details
$query = "SELECT r.*, c.name as category_name
          FROM recipes r
          LEFT JOIN categories c ON r.category_id = c.id
          WHERE r.id = $recipe_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: browse.php");
    exit;
}

$recipe = mysqli_fetch_assoc($result);

// Check if the recipe belongs to the logged-in user
if ($recipe['user_id'] != $_SESSION['user_id']) {
    header("Location: recipe.php?id=$recipe_id");
    exit;
}

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - Cake Recipes</title>
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
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="add-recipe-section">
            <h2 class="section-title">Edit Recipe</h2>

            <div id="message" class="form-message"></div>

            <form class="recipe-form" id="edit-recipe-form" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $recipe['id']; ?>">

                <div class="form-group">
                    <label for="title">Recipe Name</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($recipe['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="">Select Difficulty</option>
                        <option value="easy" <?php echo ($recipe['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                        <option value="medium" <?php echo ($recipe['difficulty'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="hard" <?php echo ($recipe['difficulty'] == 'hard') ? 'selected' : ''; ?>>Hard</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="preparation_time">Preparation Time (minutes)</label>
                        <input type="number" id="preparation_time" name="preparation_time" value="<?php echo $recipe['preparation_time']; ?>" required>
                    </div>

                    <div class="form-group half">
                        <label for="cooking_time">Cooking Time (minutes)</label>
                        <input type="number" id="cooking_time" name="cooking_time" value="<?php echo $recipe['cooking_time']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="servings">Servings</label>
                    <input type="number" id="servings" name="servings" value="<?php echo $recipe['servings']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="recipe_image">Recipe Image</label>
                    <?php if (!empty($recipe['image_url'])): ?>
                        <div class="current-image">
                            <p>Current image:</p>
                            <img src="<?php echo $recipe['image_url']; ?>" alt="<?php echo $recipe['title']; ?>" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="recipe_image" name="recipe_image" accept="image/*" onchange="previewImage(this)">
                    <p class="form-help">Upload a new image (optional)</p>
                    <div id="image-preview" class="image-preview"></div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="ingredients">Ingredients (one per line)</label>
                    <textarea id="ingredients" name="ingredients" rows="5" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="instructions">Instructions</label>
                    <textarea id="instructions" name="instructions" rows="8" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Recipe</button>
                <a href="recipe.php?id=<?php echo $recipe_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Cake Recipes. All rights reserved.</p>
        </div>
    </footer>

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

        // Handle form submission
        document.getElementById('edit-recipe-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageDiv = document.getElementById('message');

            // Show loading message
            messageDiv.textContent = 'Updating recipe...';
            messageDiv.className = 'form-message info';

            fetch('edit_recipe.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    messageDiv.textContent = data.error;
                    messageDiv.className = 'form-message error';
                } else if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'form-message success';
                    // Redirect to recipe page after successful update
                    setTimeout(() => {
                        window.location.href = 'recipe.php?id=<?php echo $recipe_id; ?>';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'form-message error';
            });
        });
    </script>
</body>
</html>
