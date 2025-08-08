<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = 'Please fill all fields correctly!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
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
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="new_entry.php">Book Service</a></li>
                    </ul>
                </div>
                <div class="navbar-button">
                    <button onclick="window.location.href='logout.php';">Logout</button>
                </div>
            </div>
        </div>
    </header>
    <div class="body-panel">
        <div class="form-container">
            <h1>Book a Car Wash</h1>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form action="add_entry.php" method="post">
                <div class="name-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="car_name">Car Name</label>
                        <input type="text" id="car_name" name="car_name" required>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="car_type">Car Type</label>
                        <select id="car_type" name="car_type" required>
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Truck">Truck</option>
                            <option value="Bike">Bike</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="service_type">Service Type</label>
                        <select id="service_type" name="service_type" required>
                            <option value="Wash">Wash</option>
                            <option value="Scratch Remove">Scratch Remove</option>
                            <option value="Painting">Painting</option>
                            <option value="Interior Cleaning">Interior Cleaning</option>
                            <option value="Engine Cleaning">Engine Cleaning</option>
                        </select>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="service_date">Service Date</label>
                        <input type="date" id="service_date" name="service_date" required>
                    </div>
                    <div class="form-group">
                        <label for="service_time">Service Time</label>
                        <input type="time" id="service_time" name="service_time" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payment">Payment</label>
                    <input type="number" id="payment" name="payment" step="0.01" required>
                </div>
                <button type="submit">Book Now</button>
            </form>
        </div>
    </div>
    <footer>
        <div class="footer-panel">
            <p>© 2025 Wash My Car. All rights reserved.</p>
            <a href="contact.html">Contact Us</a>
        </div>
    </footer>
</body>
</html>