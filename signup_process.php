<?php
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    if (mysqli_num_rows(mysqli_query($conn, $sql_check)) > 0) {
        header("Location: signup.php?error=email_exists");
        exit();
    }
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')";
    if (mysqli_query($conn, $sql)) {
        header("Location: login.php?signup=success");
        exit();
    }
    header("Location: signup.php?error=failed");
    exit();
}
mysqli_close($conn);
?>