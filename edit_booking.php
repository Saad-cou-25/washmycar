<?php
// edit_booking.php — receives POST from dashboard edit modal and updates services row
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success'=>false,'message'=>'Admin only']);
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$first = isset($_POST['first_name']) ? mysqli_real_escape_string($conn, trim($_POST['first_name'])) : '';
$last  = isset($_POST['last_name']) ? mysqli_real_escape_string($conn, trim($_POST['last_name'])) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : null;
$car_name = isset($_POST['car_name']) ? mysqli_real_escape_string($conn, trim($_POST['car_name'])) : '';
$car_type = isset($_POST['car_type']) ? mysqli_real_escape_string($conn, trim($_POST['car_type'])) : '';
$service_type = isset($_POST['service_type']) ? mysqli_real_escape_string($conn, trim($_POST['service_type'])) : '';
$service_date = isset($_POST['service_date']) ? mysqli_real_escape_string($conn, trim($_POST['service_date'])) : '';
$service_time = isset($_POST['service_time']) ? mysqli_real_escape_string($conn, trim($_POST['service_time'])) : '';
$payment = isset($_POST['payment']) ? mysqli_real_escape_string($conn, trim($_POST['payment'])) : '0.00';

if ($id <= 0 || $first === '' || $last === '' || $email === '') {
    echo json_encode(['success'=>false,'message'=>'Missing required fields']);
    exit();
}

// Update user_id if email matches users table
$user_id_sql = "NULL";
$u_res = mysqli_query($conn, "SELECT id FROM users WHERE LOWER(email) = LOWER('".$email."') LIMIT 1");
if ($u_res && mysqli_num_rows($u_res) === 1) {
    $u_row = mysqli_fetch_assoc($u_res);
    $user_id_sql = intval($u_row['id']);
    $user_id_sql = "'" . $user_id_sql . "'";
}

// Build update query
$sql = "UPDATE services SET user_id = $user_id_sql, email = '".mysqli_real_escape_string($conn,$email)."', first_name = '$first', last_name = '$last', phone = " . ($phone!==null? "'$phone'":"NULL") . ", car_name = '$car_name', car_type = '$car_type', service_type = '$service_type', service_date = '$service_date', service_time = '$service_time', payment = '$payment' WHERE id = $id LIMIT 1";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success'=>true]);
    exit();
} else {
    echo json_encode(['success'=>false,'message'=>'DB error: '.mysqli_error($conn)]);
    exit();
}
