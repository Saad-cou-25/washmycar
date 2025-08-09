<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$id = (int)$_GET['id'];
$sql = "SELECT * FROM services WHERE id = $id";
$result = mysqli_query($conn, $sql);
$service = mysqli_fetch_assoc($result);
if (!$service) { header("Location: dashboard.php"); exit(); }

// authorize: admin or owner (owner - by email or user_id)
$isOwner = false;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $isOwner = true;
} else {
    if (isset($_SESSION['email']) && $service['email'] == $_SESSION['email']) $isOwner = true;
    if (isset($_SESSION['user_id']) && $service['user_id'] == $_SESSION['user_id']) $isOwner = true;
}
if (!$isOwner && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php?error=unauthorized");
    exit();
}

$error_message = '';
if (isset($_GET['error'])) {
    $error_message = 'Please fill all fields correctly!';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $car_name = mysqli_real_escape_string($conn, $_POST['car_name']);
    $car_type = mysqli_real_escape_string($conn, $_POST['car_type']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $service_date = $_POST['service_date'];
    $service_time = $_POST['service_time'];
    $payment = $_POST['payment'];

    if (empty($first_name) || empty($last_name) || empty($phone) || empty($car_name) || empty($car_type) || empty($service_type) || empty($service_date) || empty($service_time) || empty($payment) || empty($email)) {
        header("Location: update_entry.php?id=$id&error=1");
        exit();
    }

    // find user_id if exists for this email
    $user_id = "NULL";
    $u_res = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
    if ($u_res && mysqli_num_rows($u_res) === 1) {
        $u_row = mysqli_fetch_assoc($u_res);
        $user_id = intval($u_row['id']);
    }
    $user_id_sql = ($user_id === "NULL") ? "NULL" : "'" . intval($user_id) . "'";
    $email_sql = mysqli_real_escape_string($conn, $email);

    $sql_update = "UPDATE services SET user_id=$user_id_sql, email='$email_sql', first_name='$first_name', last_name='$last_name', phone='$phone', car_name='$car_name', car_type='$car_type', service_type='$service_type', service_time='$service_time', service_date='$service_date', payment='$payment' WHERE id=$id";
    if (mysqli_query($conn, $sql_update)) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: user_history.php");
        }
        exit();
    } else {
        $error_message = "Error updating service: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Update Service</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="body-panel">
        <div class="form-container">
            <h1>Update Service Booking</h1>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form action="update_entry.php?id=<?php echo $id; ?>" method="post" id="updateForm">
                <div class="name-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($service['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($service['last_name']); ?>" required>
                    </div>
                </div>

                <div class="name-row" style="margin-top:.5rem;">
                    <div class="form-group" style="flex:1">
                        <label for="email">Email (user)</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($service['email']); ?>" required>
                    </div>
                </div>

                <div class="name-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($service['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="car_name">Car Name</label>
                        <input type="text" id="car_name" name="car_name" value="<?php echo htmlspecialchars($service['car_name']); ?>" required>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="car_type">Car Type</label>
                        <select id="car_type" name="car_type" required>
                            <option value="Sedan" <?php if ($service['car_type'] == 'Sedan') echo 'selected'; ?>>Sedan</option>
                            <option value="SUV" <?php if ($service['car_type'] == 'SUV') echo 'selected'; ?>>SUV</option>
                            <option value="Truck" <?php if ($service['car_type'] == 'Truck') echo 'selected'; ?>>Truck</option>
                            <option value="Bike" <?php if ($service['car_type'] == 'Bike') echo 'selected'; ?>>Bike</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="service_type">Service Type</label>
                        <select id="service_type" name="service_type" required>
                            <option value="Wash" <?php if ($service['service_type'] == 'Wash') echo 'selected'; ?>>Wash</option>
                            <option value="Scratch Remove" <?php if ($service['service_type'] == 'Scratch Remove') echo 'selected'; ?>>Scratch Remove</option>
                            <option value="Painting" <?php if ($service['service_type'] == 'Painting') echo 'selected'; ?>>Painting</option>
                            <option value="Interior Cleaning" <?php if ($service['service_type'] == 'Interior Cleaning') echo 'selected'; ?>>Interior Cleaning</option>
                            <option value="Engine Cleaning" <?php if ($service['service_type'] == 'Engine Cleaning') echo 'selected'; ?>>Engine Cleaning</option>
                        </select>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="service_date">Service Date</label>
                        <input type="date" id="service_date" name="service_date" value="<?php echo $service['service_date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="service_time">Service Time</label>
                        <input type="time" id="service_time" name="service_time" value="<?php echo $service['service_time']; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payment">Payment</label>
                    <input type="number" id="payment" name="payment" step="0.01" value="<?php echo $service['payment']; ?>" required>
                </div>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>
    <footer>
        <div class="footer-panel">
            <p>© 2025 Wash My Car. All rights reserved.</p>
            <a href="contact.html">Contact Us</a>
        </div>
    </footer>

<script>
let emailEl = document.getElementById('email');
let timer = null;
emailEl.addEventListener('input', function(){
    clearTimeout(timer);
    timer = setTimeout(() => { fetchUserByEmail(); }, 700);
});
emailEl.addEventListener('blur', fetchUserByEmail);

function fetchUserByEmail() {
    const email = emailEl.value.trim();
    if (!email) return;
    fetch('get_user_by_email.php?email=' + encodeURIComponent(email))
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const u = data.data;
            if (u.first_name) document.getElementById('first_name').value = u.first_name;
            if (u.last_name) document.getElementById('last_name').value = u.last_name;
            if (u.phone) document.getElementById('phone').value = u.phone;
        }
    })
    .catch(err => console.error(err));
}
</script>
</body>
</html>
<?php mysqli_close($conn); ?>
