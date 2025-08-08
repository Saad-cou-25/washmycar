<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';

// fetch bookings for this user
$user_id = (int)$_SESSION['user_id'];
$sql = "SELECT * FROM services WHERE user_id = $user_id ORDER BY service_date DESC, service_time DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Bookings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="body-panel">
        <div class="section">
            <h1>My Bookings</h1>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Car</th><th>Type</th><th>Service</th><th>Date</th><th>Time</th><th>Payment</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
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
            <p style="margin-top:1rem;"><a href="new_entry.php">Book a new service</a></p>
        </div>
    </div>
    <?php mysqli_close($conn); ?>
</body>
</html>
