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
    <style>
        @media print {
            .navbar, .footer-panel, button {
                display: none;
            }
            .section {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .dashboard-table {
                font-size: 12pt;
            }
        }
    </style>
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
            <h1>Service Bookings</h1>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Car</th>
                            <th>Type</th>
                            <th>Time</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_type']); ?></td>
                            <td><?php echo $row['service_time']; ?></td>
                            <td><?php echo $row['service_date']; ?></td>
                            <td><?php echo number_format($row['payment'], 2); ?></td>
                            <td>
                                <a href="update_entry.php?id=<?php echo $row['id']; ?>" style="color: #667eea;">Update</a>
                                <a href="delete_entry.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" style="color: #721c24;">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button onclick="window.print();">Print Report</button>
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