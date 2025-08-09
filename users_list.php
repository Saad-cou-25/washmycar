<?php
// users_list.php
session_start();

// must be logged in and admin
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

include 'db_connect.php';

// handle deletion (POST)
$message = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];

    // do not allow deleting yourself
    if ($delete_id === (int)$_SESSION['user_id']) {
        $message = "You cannot delete your own admin account.";
        $message_type = 'error';
    } else {
        // fetch the target user's role to ensure it's not an admin
        $q = "SELECT id, email, role FROM users WHERE id = $delete_id LIMIT 1";
        $res = mysqli_query($conn, $q);
        if ($res && mysqli_num_rows($res) === 1) {
            $target = mysqli_fetch_assoc($res);
            if (isset($target['role']) && $target['role'] === 'admin') {
                $message = "Cannot delete another admin account.";
                $message_type = 'error';
            } else {
                // begin transaction to keep changes consistent
                mysqli_begin_transaction($conn);
                $ok = true;

                // 1) set services.user_id = NULL for bookings that belong to this user
                $stmt1 = "UPDATE services SET user_id = NULL WHERE user_id = " . intval($delete_id);
                if (!mysqli_query($conn, $stmt1)) {
                    $ok = false;
                    $message = "Failed to unlink user's bookings: " . mysqli_error($conn);
                    $message_type = 'error';
                }

                // 2) delete user row
                if ($ok) {
                    $stmt2 = "DELETE FROM users WHERE id = " . intval($delete_id) . " LIMIT 1";
                    if (!mysqli_query($conn, $stmt2)) {
                        $ok = false;
                        $message = "Failed to delete user: " . mysqli_error($conn);
                        $message_type = 'error';
                    }
                }

                if ($ok) {
                    mysqli_commit($conn);
                    $message = "User deleted successfully. Their bookings were unlinked (user_id set to NULL).";
                    $message_type = 'success';
                } else {
                    mysqli_rollback($conn);
                    if ($message === '') {
                        $message = "Unknown error while deleting user.";
                        $message_type = 'error';
                    }
                }
            }
        } else {
            $message = "User not found.";
            $message_type = 'error';
        }
    }
}

// fetch all users
$sql = "SELECT id, first_name, last_name, email, phone, role, created_at FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
if ($result === false) {
    die("DB error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users List - Admin</title>
<link rel="stylesheet" href="style.css">
<style>
/* page-specific tweaks */
.section {
    width: 80vw;
    margin: 2rem auto;
    padding: 1.5rem;
    background: #a8acb8;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    max-width: 1200px;
}
.top-row {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom: 1rem;
}
.users-table { width:100%; border-collapse: collapse; background:#d0d3db; border-radius:8px; overflow:hidden; }
.users-table thead { background:#8f95a2; color:#222; font-size:1rem; }
.users-table th, .users-table td { padding:0.6rem; border:1px solid #7f8594; text-align:center; font-size:0.92rem; }
.users-table tbody tr:nth-child(even) { background:#b5baca; }
.users-table tbody tr:hover { background:#acbadf85; }

.actions form { display:inline-block; margin:0; }
.delete-btn {
    background: transparent;
    border: none;
    color: #b00;
    cursor: pointer;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 4px;
}
.delete-btn:hover { background: rgba(176,0,0,0.06); }
.back-link { text-decoration:none; color: whitesmoke; background:#222; padding:8px 12px; border-radius:6px; }
.message { padding: 10px; border-radius:6px; margin-bottom:12px; }
.message.success { background: #e6f8ee; color:#064a28; border:1px solid #bdeacb; }
.message.error { background: #fdecea; color:#7a0b0b; border:1px solid #f5c6c6; }

/* make table responsive on small screens */
.table-container { overflow-x:auto; }
@media (max-width:720px) {
    .section { padding: 1rem; width: 95vw; }
    .users-table th, .users-table td { font-size:0.8rem; padding:0.4rem; }
}
</style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="body-panel">
        <div class="section">
            <div class="top-row">
                <h2 style="margin:0;color:#222;">Registered Users</h2>
                <div>
                    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Registered At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                                <td class="actions">
                                    <?php if ($row['role'] !== 'admin'): ?>
                                        <form method="post" onsubmit="return confirm('Delete this user? This will not remove their bookings, only unlink them.');">
                                            <input type="hidden" name="delete_id" value="<?php echo (int)$row['id']; ?>">
                                            <button type="submit" class="delete-btn">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#333;font-weight:600;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php mysqli_free_result($result); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- footer (same markup used across your site) -->
    <footer>
        <div class="footer-panel">
            <p>© 2025 Wash My Car. All rights reserved.</p>
            <a href="contact.html">Contact Us</a>
        </div>
    </footer>

<?php mysqli_close($conn); ?>
</body>
</html>
