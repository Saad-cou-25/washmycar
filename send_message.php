<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    if (!empty($message)) {
        $sql = "INSERT INTO messages (user_id, message, is_read_by_admin) VALUES ($user_id, '$message', 0)";
        mysqli_query($conn, $sql);
    }
}
header("Location: user_messages.php?sent=1");
exit();
?>
