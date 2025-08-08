<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php'; // safe: it only defines $conn if not already defined
$logged_in = isset($_SESSION['user_id']);
$role = $logged_in && isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
?>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <?php if ($logged_in && $role === 'admin'): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="new_entry.php">New Entry</a></li>
                        <li><a href="admin_messages.php">Messages</a></li>
                        <?php elseif ($logged_in && $role === 'user'): ?>
                            <li><a href="user_history.php">My Bookings</a></li>
                            <li><a href="new_entry.php">New Entry</a></li>
                        <li><a href="user_messages.php">Messages</a></li>
                    <?php else: ?>
                        <li><a href="contact.php">Contact Us</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="navbar-button">
                <?php if ($logged_in): ?>
                    <button onclick="window.location.href='logout.php';">Logout</button>
                <?php else: ?>
                    <button onclick="window.location.href='login.php';">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
