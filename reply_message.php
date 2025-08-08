<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message_id = (int)$_POST['message_id'];
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    $sql = "UPDATE messages SET reply = '$reply', replied_at = NOW(), is_read_by_user = 0 WHERE id = $message_id";
    mysqli_query($conn, $sql);
}
header("Location: admin_messages.php");
exit();
?>
