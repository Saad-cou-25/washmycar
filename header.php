<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php';
$logged_in = isset($_SESSION['email']) || isset($_SESSION['user_id']);
$role = $logged_in && isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$display_name = $logged_in ? (isset($_SESSION['email']) ? $_SESSION['email'] : 'User') : '';
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
                        <a href="users_list.php">View Registered Users</a>
                        <li><a href="admin_messages.php">Messages</a></li>
                    <?php elseif ($logged_in && $role === 'user'): ?>
                        <li><a href="user_history.php">My Bookings</a></li>
                        <li><a href="user_messages.php">Messages</a></li>
                    <?php else: ?>
                        <li><a href="contact.php">Contact Us</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="navbar-button">
                <?php if ($logged_in): ?>
                    <div style="display:inline-block;color:#fff;margin-right:10px;"><?php echo htmlspecialchars($display_name); ?></div>
                    <button onclick="window.location.href='logout.php';">Logout</button>
                <?php else: ?>
                    <button onclick="window.location.href='login.php';">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
