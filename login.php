<?php
session_start();
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = $_GET['error'] == 'invalid' ? 'Invalid email or password!' : 'Please log in first!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wash My Car</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- <header>
        <div class="navbar">
            <div class="logo">
                <a href="index.php">
                    <div class="logo-image"></div>
                    <div class="logo-text">Wash My Car</div>
                </a>
            </div>
            <div class="navbar-text">
                <div class="navbar-links">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="services.html">Services</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="navbar-button">
                    <button onclick="window.location.href='signup.php';">Sign Up</button>
                </div>
            </div>
        </div>
    </header> -->
    <?php include 'header.php'; ?>
    <div class="body-panel">
        <div class="login-container">
            <h1>Login</h1>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="login_process.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
    <footer>
        <div class="footer-panel">
            <p>© 2025 Wash My Car. All rights reserved.</p>
            <a href="contact.php">Contact Us</a>
        </div>
    </footer>
</body>
</html>