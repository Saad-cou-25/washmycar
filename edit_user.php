<?php
// edit_user.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$first_name = isset($_POST['first_name']) ? mysqli_real_escape_string($conn, trim($_POST['first_name'])) : '';
$last_name  = isset($_POST['last_name']) ? mysqli_real_escape_string($conn, trim($_POST['last_name'])) : '';
$phone      = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : null;
$role       = isset($_POST['role']) ? mysqli_real_escape_string($conn, trim($_POST['role'])) : 'user';

if ($id <= 0 || empty($first_name) || empty($last_name)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Prevent changing your own role to non-admin (safety)
if ($id === (int)$_SESSION['user_id'] && $role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'You cannot remove admin role from your own account']);
    exit();
}

// Update user
$sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', phone = " . ($phone !== null ? "'$phone'" : "NULL") . ", role = '$role' WHERE id = $id LIMIT 1";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: '. mysqli_error($conn)]);
    exit();
}
