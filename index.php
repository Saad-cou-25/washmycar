<?php
include 'db_connect.php';
$today = date('Y-m-d');
$this_month_start = date('Y-m-01');
$this_month_end = date('Y-m-t');
$sql_today = "SELECT COUNT(*) as count FROM services WHERE service_date = '$today'";
$result_today = mysqli_query($conn, $sql_today);
$count_today = mysqli_fetch_assoc($result_today)['count'];
$sql_month = "SELECT COUNT(*) as count FROM services WHERE service_date BETWEEN '$this_month_start' AND '$this_month_end'";
$result_month = mysqli_query($conn, $sql_month);
$count_month = mysqli_fetch_assoc($result_month)['count'];
$sql_total = "SELECT COUNT(*) as count FROM services";
$result_total = mysqli_query($conn, $sql_total);
$count_total = mysqli_fetch_assoc($result_total)['count'];
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
                    <button onclick="window.location.href='login.php';">Login</button>
                </div>
            </div>
        </div>
    </header> -->
        <?php include 'header.php'; ?>
    <div class="body-panel">
        <div style="display: flex; flex-direction: row; gap: 20px; padding: 20px;">
            <div class="index-body-info-container">
                <h1>Welcome to Wash My Car</h1>
                <p>At Wash My Car, we offer a wide range of car washing services that cater to all types of vehicles. We are committed to providing you with the best possible experience and satisfaction.</p>
                <br>
                <button onclick="window.location.href='services.php';">Learn more about our services</button>
            </div>
    
            <div class="index-body-info-container">
                <h1>Let's see our successful services:</h1>

                <div class="counter-box">Today: <span><?php echo $count_today; ?></span></div>
                <div class="counter-box">This Month: <span><?php echo $count_month; ?></span></div>
                <div class="counter-box">All Time: <span><?php echo $count_total; ?></span></div>
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