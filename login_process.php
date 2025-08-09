<?php
session_start();
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email']; // important
            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'user';
            if ($_SESSION['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: user_history.php");
            }
            exit();
        }
    }
    header("Location: login.php?error=invalid");
    exit();
}
mysqli_close($conn);
?>
