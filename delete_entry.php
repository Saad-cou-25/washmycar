<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$id = (int)$_GET['id'];
// fetch service to check owner
$res = mysqli_query($conn, "SELECT * FROM services WHERE id = $id");
$service = mysqli_fetch_assoc($res);
if (!$service) {
    header("Location: dashboard.php");
    exit();
}
if ($_SESSION['role'] !== 'admin' && $service['user_id'] != $_SESSION['user_id']) {
    header("Location: login.php?error=unauthorized");
    exit();
}
$sql = "DELETE FROM services WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: user_history.php");
    }
    exit();
} else {
    echo "Error deleting service.";
}
mysqli_close($conn);
?>
