<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$id = (int)$_GET['id'];
$sql = "SELECT * FROM services WHERE id = $id";
$result = mysqli_query($conn, $sql);
$service = mysqli_fetch_assoc($result);

// Initialize error message
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = 'Please fill all fields correctly!';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $car_name = mysqli_real_escape_string($conn, $_POST['car_name']);
    $car_type = mysqli_real_escape_string($conn, $_POST['car_type']);
    $service_date = $_POST['service_date'];
    $service_time = $_POST['service_time'];
    $payment = $_POST['payment'];

    // Server-side validation to check for empty fields
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($car_name) || empty($car_type) || empty($service_date) || empty($service_time) || empty($payment)) {
        header("Location: update_entry.php?id=$id&error=1");
        exit();
    }

    $sql_update = "UPDATE services SET first_name='$first_name', last_name='$last_name', phone='$phone', car_name='$car_name', car_type='$car_type', service_time='$service_time', service_date='$service_date', payment='$payment' WHERE id=$id";
    if (mysqli_query($conn, $sql_update)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Service</title>
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
            <h1>Update Service Booking</h1>
            <form action="update_entry.php?id=<?php echo $id; ?>" method="post">
                <!-- Display error message if it exists -->
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <div class="name-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($service['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($service['last_name']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($service['phone']); ?>" required>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="car_name">Car Name</label>
                        <input type="text" id="car_name" name="car_name" value="<?php echo htmlspecialchars($service['car_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="car_type">Car Type</label>
                        <select id="car_type" name="car_type" required>
                            <option value="Sedan" <?php if ($service['car_type'] == 'Sedan') echo 'selected'; ?>>Sedan</option>
                            <option value="SUV" <?php if ($service['car_type'] == 'SUV') echo 'selected'; ?>>SUV</option>
                            <option value="Truck" <?php if ($service['car_type'] == 'Truck') echo 'selected'; ?>>Truck</option>
                        </select>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="service_date">Service Date</label>
                        <input type="date" id="service_date" name="service_date" value="<?php echo $service['service_date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="service_time">Service Time</label>
                        <input type="time" id="service_time" name="service_time" value="<?php echo $service['service_time']; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payment">Payment</label>
                    <input type="number" id="payment" name="payment" step="0.01" value="<?php echo $service['payment']; ?>" required>
                </div>
                <button type="submit">Update</button>
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
<?php mysqli_close($conn); ?>