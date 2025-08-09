<?php
// get_user_by_email.php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php';

// quick helper for JSON error
function out($ok, $payload = []) {
    echo json_encode(array_merge(['success' => $ok], $payload));
    exit;
}

if (empty($_GET['email'])) {
    out(false, ['message' => 'No email provided']);
}

$raw = trim($_GET['email']);
if ($raw === '') out(false, ['message' => 'Empty email']);

$email = mysqli_real_escape_string($conn, $raw);

// 1) Try case-insensitive match in users table
$sql = "SELECT first_name, last_name, email, phone FROM users WHERE LOWER(email) = LOWER('$email') LIMIT 1";
$res = mysqli_query($conn, $sql);
if ($res && mysqli_num_rows($res) === 1) {
    $row = mysqli_fetch_assoc($res);
    out(true, ['data' => $row, 'found_in' => 'users']);
}

// 2) Try direct match (just in case)
$sql2 = "SELECT first_name, last_name, email, phone FROM users WHERE email = '$email' LIMIT 1";
$res2 = mysqli_query($conn, $sql2);
if ($res2 && mysqli_num_rows($res2) === 1) {
    $row = mysqli_fetch_assoc($res2);
    out(true, ['data' => $row, 'found_in' => 'users-exact']);
}

// 3) Fallback: last-known from services table (for historical bookings)
$sql3 = "SELECT first_name, last_name, phone, email FROM services WHERE LOWER(email) = LOWER('$email') AND (first_name <> '' OR phone IS NOT NULL) ORDER BY id DESC LIMIT 1";
$res3 = mysqli_query($conn, $sql3);
if ($res3 && mysqli_num_rows($res3) === 1) {
    $row = mysqli_fetch_assoc($res3);
    out(true, ['data' => $row, 'found_in' => 'services']);
}

// 4) Try LIKE fuzzy match (in case of small differences)
$like = mysqli_real_escape_string($conn, "%$raw%");
$sql4 = "SELECT first_name, last_name, phone, email FROM users WHERE email LIKE '$like' LIMIT 1";
$res4 = mysqli_query($conn, $sql4);
if ($res4 && mysqli_num_rows($res4) === 1) {
    $row = mysqli_fetch_assoc($res4);
    out(true, ['data' => $row, 'found_in' => 'users-like']);
}

// nothing found
out(false, ['message' => 'User not found']);
?>
