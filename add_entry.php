<?php
session_start();
if (!isset($_SESSION['email']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // email from form or session
    $email_input = isset($_POST['email']) ? trim($_POST['email']) : '';
    $email = filter_var($email_input, FILTER_VALIDATE_EMAIL) ? mysqli_real_escape_string($conn, $email_input) : null;
    if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
        $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    }

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $car_name = mysqli_real_escape_string($conn, $_POST['car_name']);
    $car_type = mysqli_real_escape_string($conn, $_POST['car_type']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $service_date = $_POST['service_date'];
    $service_time = $_POST['service_time'];
    $payment = $_POST['payment'];

    if ($first_name && $last_name && $phone && $car_name && $car_type && $service_type && $service_date && $service_time && $payment && $email) {
        // find user_id if exists for this email
        $user_id = "NULL";
        $u_res = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
        if ($u_res && mysqli_num_rows($u_res) === 1) {
            $u_row = mysqli_fetch_assoc($u_res);
            $user_id = intval($u_row['id']);
        }
        $user_id_sql = ($user_id === "NULL") ? "NULL" : "'" . intval($user_id) . "'";
        $email_sql = mysqli_real_escape_string($conn, $email);

        $sql = "INSERT INTO services (user_id, email, first_name, last_name, phone, car_name, car_type, service_type, service_time, service_date, payment) 
                VALUES ($user_id_sql, '$email_sql', '$first_name', '$last_name', '$phone', '$car_name', '$car_type', '$service_type', '$service_time', '$service_date', '$payment')";
        if (mysqli_query($conn, $sql)) {
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: user_history.php");
            }
            exit();
        } else {
            header("Location: new_entry.php?error=database");
            exit();
        }
    } else {
        header("Location: new_entry.php?error=invalid");
        exit();
    }
}
mysqli_close($conn);
?>
