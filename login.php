<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cake Recipes</title>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="browse.php">Browse</a></li>
                    <li><a href="search.php">Search</a></li>
                    <li><a href="add-recipe.php">Add Recipe</a></li>
                    <li><a href="login.php" class="active">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Login Form -->
    <div class="container">
        <div class="login-form">
            <h2>Login to Your Account</h2>
            <form id="login-form" action="login.process.php" method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn btn-primary">Login</button>
                <div id="message" class="form-message"></div>
            </form>
            <script>
                document.getElementById('login-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('login.process.php', {
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
                            // Redirect to home page after successful login
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
            <p>Don't have an account? <a href="signup.php">Create a new account</a></p>
        </div>
    </div>

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
