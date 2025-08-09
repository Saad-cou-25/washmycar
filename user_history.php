<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Fetch bookings matching this email
$sql = "SELECT * FROM services WHERE email = '$email' ORDER BY service_date DESC, service_time DESC";
$result = mysqli_query($conn, $sql);

// Also compute earnings for this user
$earn_sql = "SELECT IFNULL(SUM(payment),0) AS earnings FROM services WHERE email = '$email'";
$earn_res = mysqli_query($conn, $earn_sql);
$earn_row = mysqli_fetch_assoc($earn_res);
$user_earnings = $earn_row['earnings'];
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
            <h1>My Bookings (<?php echo htmlspecialchars($_SESSION['email']); ?>)</h1>
            <div class="table-container">
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <p>No bookings found. <a href="new_entry.php">Book your first service</a></p>
                <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Phone</th>
                            <th>Car Name</th>
                            <th>Car Type</th>
                            <th>Service Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Payment</th>
                            <!-- note: no ID and no actions for users -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['car_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                            <td><?php echo $row['service_date']; ?></td>
                            <td><?php echo $row['service_time']; ?></td>
                            <td><?php echo $row['payment']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="earnings-box" style="margin-top:.5rem; padding:.5rem;  background:#677182; border-radius:8px; text-align:center;">
                <div style=" margin-bottom:.5rem;">   
                    <strong >Your Total Payments:</strong> ৳ <?php echo number_format($user_earnings, 2); ?>
                </div>
                <button onclick="window.location.href='new_entry.php';"  ">Book a new service</button>
            </div>
        </div>
    </div>
    <?php mysqli_close($conn); ?>
</body>
</html>
