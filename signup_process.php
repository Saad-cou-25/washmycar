<?php
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : null;
    $password_raw = $_POST['password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password_raw)) {
        header("Location: signup.php?error=failed");
        exit();
    }

    // check email exists
    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    if (mysqli_num_rows(mysqli_query($conn, $sql_check)) > 0) {
        header("Location: signup.php?error=email_exists");
        exit();
    }

    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES ('$first_name', '$last_name', '$email', " . ($phone !== null ? "'$phone'" : "NULL") . ", '$password', 'user')";
    if (mysqli_query($conn, $sql)) {
        header("Location: login.php?signup=success");
        exit();
    }
    header("Location: signup.php?error=failed");
    exit();
}
mysqli_close($conn);
?>
