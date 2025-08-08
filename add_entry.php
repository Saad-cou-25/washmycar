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
    $service_date = $_POST['service_date'];
    $service_time = $_POST['service_time'];
    $payment = $_POST['payment'];
    $sql = "INSERT INTO services (first_name, last_name, phone, car_name, car_type, service_time, service_date, payment) 
            VALUES ('$first_name', '$last_name', '$phone', '$car_name', '$car_type', '$service_time', '$service_date', '$payment')";
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>