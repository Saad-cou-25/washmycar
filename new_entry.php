<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = 'Please fill all fields correctly!';
}
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book Service</title>
<link rel="stylesheet" href="style.css">
<style>
    #autofill-debug {
        font-size: .9rem;
        color: #333;
        margin: 6px 0;
        position: relative;
        top: -10px;
    }
</style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="body-panel">
        <div class="form-container">
            <h1>Book a Car Wash</h1>

            <!-- Debug message moved ABOVE form -->
            <div id="autofill-debug"></div>

            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="add_entry.php" method="post" id="bookingForm">
                <div class="name-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <!-- email between last name and phone -->
                <div class="name-row" style="margin-top:.5rem;">
                    <div class="form-group" style="flex:1">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="user@example.com" required>
                    </div>
                </div>

                <div class="name-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="car_name">Car Name</label>
                        <input type="text" id="car_name" name="car_name" required>
                    </div>
                </div>

                <div class="name-row">
                    <div class="form-group">
                        <label for="car_type">Car Type</label>
                        <select id="car_type" name="car_type" required>
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Truck">Truck</option>
                            <option value="Bike">Bike</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="service_type">Service Type</label>
                        <select id="service_type" name="service_type" required>
                            <option value="Wash">Wash</option>
                            <option value="Scratch Remove">Scratch Remove</option>
                            <option value="Painting">Painting</option>
                            <option value="Interior Cleaning">Interior Cleaning</option>
                            <option value="Engine Cleaning">Engine Cleaning</option>
                        </select>
                    </div>
                </div>
                <div class="name-row">
                    <div class="form-group">
                        <label for="service_date">Service Date</label>
                        <input type="date" id="service_date" name="service_date" required>
                    </div>
                    <div class="form-group">
                        <label for="service_time">Service Time</label>
                        <input type="time" id="service_time" name="service_time" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payment">Payment</label>
                    <input type="number" id="payment" name="payment" step="0.01" required>
                </div>
                <button type="submit">Book Now</button>
            </form>
        </div>
    </div>
    <footer>
        <div class="footer-panel">
            <p>© 2025 Wash My Car. All rights reserved.</p>
            <a href="contact.php">Contact Us</a>
        </div>
    </footer>

<script>
const emailEl = document.getElementById('email');
const firstEl = document.getElementById('first_name');
const lastEl = document.getElementById('last_name');
const phoneEl = document.getElementById('phone');
const debugEl = document.getElementById('autofill-debug');
let debounce = null;
let hideMsgTimeout = null;

function logDebug(msg, isError=false) {
    console.log('autofill:', msg);
    debugEl.textContent = msg;
    debugEl.style.color = isError ? '#a00' : '#333';

    // Remove after 1.5 seconds
    clearTimeout(hideMsgTimeout);
    hideMsgTimeout = setTimeout(() => {
        debugEl.textContent = '';
    }, 1500);
}

emailEl.addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => { fetchUserByEmail(); }, 600);
});

emailEl.addEventListener('blur', () => {
    fetchUserByEmail();
});

async function fetchUserByEmail() {
    const email = (emailEl.value || '').trim();
    if (!email) {
        logDebug('Email is empty — cannot lookup');
        return;
    }
    logDebug('Looking up ' + email + ' ...');
    try {
        const res = await fetch('get_user_by_email.php?email=' + encodeURIComponent(email));
        const j = await res.json();
        if (j.success) {
            const u = j.data;
            logDebug('Found in: ' + (j.found_in || 'unknown'));
            if (u.first_name) firstEl.value = u.first_name;
            if (u.last_name) lastEl.value = u.last_name;
            if (u.phone) phoneEl.value = u.phone;
        } else {
            logDebug('No user found for this email', true);
        }
    } catch (err) {
        console.error('Autofill error', err);
        logDebug('Network or server error while lookup', true);
    }
}

<?php if (isset($_SESSION['email']) && !empty($_SESSION['email'])): ?>
document.addEventListener('DOMContentLoaded', function(){
    const myEmail = "<?php echo htmlspecialchars($_SESSION['email']); ?>";
    emailEl.value = myEmail;
    setTimeout(fetchUserByEmail, 200);
});
<?php endif; ?>
</script>
</body>
</html>
