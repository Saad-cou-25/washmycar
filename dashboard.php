<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$sql = "SELECT * FROM services";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        <div class="section">
            <h1>Your Bookings</h1>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Phone</th>
                            <th>Car Name</th>
                            <th>Car Type</th>
                            <th>Service Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['car_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                                <td><?php echo $row['service_date']; ?></td>
                                <td><?php echo $row['service_time']; ?></td>
                                <td><?php echo $row['payment']; ?></td>
                                <td>
                                    <a href="update_entry.php?id=<?php echo $row['id']; ?>">Edit</a>
                                    <a href="delete_entry.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
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