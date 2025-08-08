<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $car_name = mysqli_real_escape_string($conn, $_POST['car_name']);
    $car_type = mysqli_real_escape_string($conn, $_POST['car_type']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $service_date = $_POST['service_date'];
    $service_time = $_POST['service_time'];
    $payment = $_POST['payment'];
    if ($first_name && $last_name && $phone && $car_name && $car_type && $service_type && $service_date && $service_time && $payment) {
        $sql = "INSERT INTO services (first_name, last_name, phone, car_name, car_type, service_type, service_time, service_date, payment) 
                VALUES ('$first_name', '$last_name', '$phone', '$car_name', '$car_type', '$service_type', '$service_time', '$service_date', '$payment')";
        if (mysqli_query($conn, $sql)) {
            if ($_SESSION['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: user_history.php");
            }
            exit();
        }else {
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