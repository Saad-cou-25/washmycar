<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$id = (int)$_GET['id'];
$sql = "DELETE FROM services WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    header("Location: dashboard.php");
    exit();
} else {
    echo "Error deleting service.";
}
mysqli_close($conn);
?>
