<?php
// dashboard.php (admin bookings page with earnings counters + total + book button)
session_start();

// require admin
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

include 'db_connect.php';

// handle booking deletion (POST)
$message = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking_id'])) {
    $bid = (int) $_POST['delete_booking_id'];
    $q = "DELETE FROM services WHERE id = $bid LIMIT 1";
    if (mysqli_query($conn, $q)) {
        $message = "Booking #$bid deleted.";
        $message_type = 'success';
    } else {
        $message = "Failed to delete booking: " . mysqli_error($conn);
        $message_type = 'error';
    }
}

// Export CSV if requested (numbers only)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sqlExport = "SELECT id, user_id, email, first_name, last_name, phone, car_name, car_type, service_type, service_date, service_time, payment FROM services ORDER BY id DESC";
    $resExport = mysqli_query($conn, $sqlExport);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=washmycar_bookings_' . date('Ymd_Hi') . '.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','User ID','Email','First Name','Last Name','Phone','Car Name','Car Type','Service Type','Service Date','Service Time','Payment']);
    while ($r = mysqli_fetch_assoc($resExport)) {
        fputcsv($out, [
            $r['id'],
            $r['user_id'],
            $r['email'],
            $r['first_name'],
            $r['last_name'],
            $r['phone'],
            $r['car_name'],
            $r['car_type'],
            $r['service_type'],
            $r['service_date'],
            $r['service_time'],
            $r['payment'] // keep numeric for CSV
        ]);
    }
    fclose($out);
    exit();
}

// fetch bookings
$sql = "SELECT id, user_id, email, first_name, last_name, phone, car_name, car_type, service_type, service_date, service_time, payment FROM services ORDER BY id DESC";
$res = mysqli_query($conn, $sql);
if ($res === false) die("DB error: " . mysqli_error($conn));
$bookings = [];
while ($r = mysqli_fetch_assoc($res)) $bookings[] = $r;

// --- total earnings calculation ---
$earn_res = mysqli_query($conn, "SELECT IFNULL(SUM(payment), 0) AS total FROM services");
$earn_row = $earn_res ? mysqli_fetch_assoc($earn_res) : null;
$total_earnings = $earn_row ? $earn_row['total'] : 0.00;

// --- earnings for Today and This Month ---
$today = date('Y-m-d');
$this_month_start = date('Y-m-01');
$this_month_end = date('Y-m-t');

$today_q = "SELECT IFNULL(SUM(payment),0) AS total FROM services WHERE service_date = '$today'";
$tm_q = "SELECT IFNULL(SUM(payment),0) AS total FROM services WHERE service_date BETWEEN '$this_month_start' AND '$this_month_end'";
$all_q = "SELECT IFNULL(SUM(payment),0) AS total FROM services";

$today_res = mysqli_query($conn, $today_q);
$tm_res = mysqli_query($conn, $tm_q);
$all_res = mysqli_query($conn, $all_q);

$today_total = ($today_res && $row = mysqli_fetch_assoc($today_res)) ? $row['total'] : 0.00;
$this_month_total = ($tm_res && $row = mysqli_fetch_assoc($tm_res)) ? $row['total'] : 0.00;
$all_time_total = ($all_res && $row = mysqli_fetch_assoc($all_res)) ? $row['total'] : 0.00;

function fmt_money($n) {
    return number_format((float)$n, 2) . " ৳";
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard - Bookings</title>
<link rel="stylesheet" href="style.css">
<style>
/* page-specific tweaks (keeps your look) */
.section { width:80vw; margin:2rem auto; padding:1.2rem; background:#a8acb8; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.2); max-width:1200px; }

/* header layout: left(title) | center(search) | right(back+export) */
.top-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    margin-bottom: 1rem;
    flex-wrap:wrap;
}
.top-row .left { flex:0 0 auto; }
.top-row .center { flex:1 1 480px; display:flex; justify-content:center; }
.top-row .right { flex:0 0 auto; display:flex; gap:8px; align-items: center; flex-wrap:wrap; }   
.top-row .right button { width: 8rem; }


/* actions */
.actions form { display:inline-block; margin:0; }
.delete-btn { background: rgba(176,0,0,0.06); border: none; color: #b00; cursor: pointer; font-weight: 700; padding: 4px 8px; border-radius: 4px; }
.delete-btn:hover { background: rgba(176, 0, 0, 0.14); }
.edit-btn {  background: rgba(0,176,0,0.06);; border: none; color: #0b5; cursor: pointer; font-weight: 700; padding: 4px 8px; border-radius: 4px; }
.edit-btn:hover { background: rgba(176, 249, 176, 0.33); }

/* search box centered */
#searchBox {
    width: 60%;
    max-width: 520px;
    min-width: 180px;
    padding:8px 10px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* counter boxes (like index counter-box) */
.earnings-row {
    display:flex;
    gap:12px;
    margin-bottom:14px;
    flex-wrap:wrap;
    align-items:center;
}
.counter-box-small {
    background: #2f855a;
    color: #fff;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 700;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

/* total earnings and book button */
.total-earnings-wrap {
    margin-top: 14px;
    display: flex;
    justify-content:space-between;
    align-items: center;
    gap: 12px;
    flex-wrap:wrap;
}
.book-now-btn {
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:10px 16px;
    background:#2b6cb0;
    color:#fff;
    border-radius:8px;
    font-weight:700;
}

.total-box {
    background: #2f855a;
    color: #fff;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 700;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

/* table */
.bookings-table { width:100%; border-collapse: collapse; background:#d0d3db; border-radius:8px; overflow:hidden; }
.bookings-table thead { background:#8f95a2; color:#222; font-size:1rem; }
.bookings-table th, .bookings-table td { padding:0.55rem; border:1px solid #7f8594; text-align:center; font-size:0.9rem; }
.bookings-table tbody tr:nth-child(even) { background:#b5baca; }
.bookings-table tbody tr:hover { background:#acbadf85; }
.bookings-table th.id-col, .bookings-table td.id-col { display:none; }
.table-container { overflow-x:auto; }

/* buttons */
.back-link { text-decoration:none; color: whitesmoke; background:#333; padding:8px 12px; border-radius:6px; }
.page-btn { padding:8px 10px; border-radius:6px; background:#667eea; border:1px solid #5a3b6e76; cursor:pointer; color:#fff; font-weight:600; }
.page-btn:active { transform: translateY(1px); }

/* modal */
#editModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; }
#editModal .modal-inner { background:#fff; padding:16px; width:520px; border-radius:8px; box-shadow:0 6px 30px rgba(0,0,0,0.2); }

@media (max-width:920px) {
    #searchBox { width: 70%; }
}
@media (max-width:640px) {
    .top-row .center { order:3; width:100%; margin-top:8px; }
    .top-row .right { order:2; }
    .top-row .left { order:1; }
    .total-earnings-wrap, .earnings-row { justify-content:center; }
}
</style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="body-panel">
        <div class="section">
            <div class="top-row">
                <div class="left"><h2 style="margin:0;color:#222;">Bookings</h2></div>
                <div class="center">
                    <input type="search" id="searchBox" placeholder="Search name, email, car, phone...">
                </div>
                <div class="right">
                    <button id="exportCsv" class="page-btn">Export CSV</button>
                    <a href="index.php" class="back-link">← Back to Home</a>
                </div>
            </div>



            <?php if ($message): ?>
                <div style="padding:10px;border-radius:6px;margin-bottom:12px;<?php echo $message_type==='success' ? 'background:#e6f8ee;color:#064a28;border:1px solid #bdeacb;' : 'background:#fdecea;color:#7a0b0b;border:1px solid #f5c6c6;'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table id="bookingsTable" class="bookings-table" data-pagesize="10">
                    <thead>
                        <tr>
                            <th class="id-col">#</th>
                            <th data-key="first_name" class="sortable">First Name ▲▼</th>
                            <th data-key="last_name" class="sortable">Last Name ▲▼</th>
                            <th data-key="email" class="sortable">Email ▲▼</th>
                            <th data-key="phone" class="sortable">Phone ▲▼</th>
                            <th data-key="car_name" class="sortable">Car ▲▼</th>
                            <th data-key="car_type" class="sortable">Type ▲▼</th>
                            <th data-key="service_type" class="sortable">Service ▲▼</th>
                            <th data-key="service_date" class="sortable">Date ▲▼</th>
                            <th data-key="service_time" class="sortable">Time ▲▼</th>
                            <th data-key="payment" class="sortable">Payment ▲▼</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Book button + total earnings display -->
            <div class="total-earnings-wrap">
            <!-- Earnings counters (Today / This Month / All Time) -->
            <!-- <div class="earnings-row"> -->
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div class="counter-box-small">Today: <?php echo fmt_money($today_total); ?></div>
                    <div class="counter-box-small">This Month: <?php echo fmt_money($this_month_total); ?></div>
                    <div class="counter-box-small">All Time: <?php echo fmt_money($all_time_total); ?></div>
                </div>
                <!-- <a href="new_entry.php" class="book-now-btn">Book Service</a> -->
                <button onclick="window.location.href='new_entry.php'; " style="width:17rem; font-weight:700">Book Service</button>
            <!-- </div> -->
                <!-- <div class="total-box">Total Earnings: <?php echo number_format((float)$total_earnings, 2); ?> ৳</div> -->
            </div>

        </div>
    </div>

    <!-- Edit booking modal -->
    <div id="editModal">
        <div class="modal-inner">
            <h3 style="margin-top:0">Edit Booking</h3>
            <div id="editMsg" style="margin-bottom:8px;"></div>
            <form id="editForm">
                <input type="hidden" id="edit_id" name="id">
                <div style="display:flex; gap:8px;">
                    <div style="flex:1;">
                        <label>First Name</label>
                        <input type="text" id="edit_first" name="first_name" style="width:100%;padding:6px;">
                    </div>
                    <div style="flex:1;">
                        <label>Last Name</label>
                        <input type="text" id="edit_last" name="last_name" style="width:100%;padding:6px;">
                    </div>
                </div>
                <div style="margin-top:8px; display:flex; gap:8px;">
                    <div style="flex:1;">
                        <label>Email</label>
                        <input type="email" id="edit_email" name="email" style="width:100%;padding:6px;">
                    </div>
                    <div style="flex:1;">
                        <label>Phone</label>
                        <input type="text" id="edit_phone" name="phone" style="width:100%;padding:6px;">
                    </div>
                </div>

                <div style="margin-top:8px; display:flex; gap:8px;">
                    <div style="flex:1;">
                        <label>Car Name</label>
                        <input type="text" id="edit_car" name="car_name" style="width:100%;padding:6px;">
                    </div>
                    <div style="flex:1;">
                        <label>Car Type</label>
                        <select id="edit_car_type" name="car_type" style="width:100%;padding:6px;">
                            <option>Sedan</option><option>SUV</option><option>Truck</option><option>Bike</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:8px; display:flex; gap:8px;">
                    <div style="flex:1;">
                        <label>Service Type</label>
                        <select id="edit_service_type" name="service_type" style="width:100%;padding:6px;">
                            <option>Wash</option><option>Scratch Remove</option><option>Painting</option><option>Interior Cleaning</option><option>Engine Cleaning</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Payment</label>
                        <input type="number" step="0.01" id="edit_payment" name="payment" style="width:100%;padding:6px;">
                    </div>
                </div>

                <div style="margin-top:8px; display:flex; gap:8px;">
                    <div style="flex:1;">
                        <label>Service Date</label>
                        <input type="date" id="edit_date" name="service_date" style="width:100%;padding:6px;">
                    </div>
                    <div style="flex:1;">
                        <label>Service Time</label>
                        <input type="time" id="edit_time" name="service_time" style="width:100%;padding:6px;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
                    <button type="button" id="cancelEdit" class="page-btn">Cancel</button>
                    <button type="submit" class="page-btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- footer -->
    <footer>
        <div class="footer-panel">
            <p>© 2025 Wash My Car. All rights reserved.</p>
            <a href="contact.php">Contact Us</a>
        </div>
    </footer>

<script>
const bookings = <?php echo json_encode($bookings, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
const tbody = document.querySelector('#bookingsTable tbody');
const searchBox = document.getElementById('searchBox');

let state = {
    items: bookings.slice(),
    filtered: bookings.slice(),
    sortKey: null,
    sortDir: 1,
    page: 1
};

function formatPayment(val) {
    const n = parseFloat(val || 0);
    return n.toFixed(2) + ' ৳';
}

function renderTable() {
    tbody.innerHTML = '';
    for (const r of state.filtered) {
        const tr = document.createElement('tr');

        const tdId = document.createElement('td'); tdId.className='id-col'; tdId.textContent = r.id; tr.appendChild(tdId);
        tr.appendChild(tdCell(r.first_name)); tr.appendChild(tdCell(r.last_name));
        tr.appendChild(tdCell(r.email)); tr.appendChild(tdCell(r.phone));
        tr.appendChild(tdCell(r.car_name)); tr.appendChild(tdCell(r.car_type));
        tr.appendChild(tdCell(r.service_type)); tr.appendChild(tdCell(r.service_date));
        tr.appendChild(tdCell(r.service_time));
        const payTd = document.createElement('td'); payTd.textContent = (r.payment !== null && r.payment !== undefined && r.payment !== '') ? formatPayment(r.payment) : '';
        tr.appendChild(payTd);

        const tdActions = document.createElement('td');
        const editBtn = document.createElement('button'); editBtn.className='edit-btn'; editBtn.textContent='Edit';
        editBtn.onclick = () => openEditModal(r);
        tdActions.appendChild(editBtn);

        const delForm = document.createElement('form'); delForm.method='post';
        delForm.onsubmit = function(){ return confirm('Delete this booking?'); };
        const input = document.createElement('input'); input.type='hidden'; input.name='delete_booking_id'; input.value = r.id;
        const delBtn = document.createElement('button'); delBtn.type='submit'; delBtn.className='delete-btn'; delBtn.textContent='Delete';
        delForm.appendChild(input); delForm.appendChild(delBtn);
        tdActions.appendChild(delForm);

        tr.appendChild(tdActions);
        tbody.appendChild(tr);
    }
}

function tdCell(text) { const td=document.createElement('td'); td.textContent = text || ''; return td; }

function applySearch(){
    const q = (searchBox.value||'').toLowerCase().trim();
    if(!q) state.filtered = state.items.slice();
    else {
        state.filtered = state.items.filter(u=>{
            return (u.first_name||'').toLowerCase().includes(q)
                || (u.last_name||'').toLowerCase().includes(q)
                || (u.email||'').toLowerCase().includes(q)
                || (u.phone||'').toLowerCase().includes(q)
                || (u.car_name||'').toLowerCase().includes(q)
                || (u.car_type||'').toLowerCase().includes(q)
                || (u.service_type||'').toLowerCase().includes(q);
        });
    }
    applySort();
    renderTable();
}

document.querySelectorAll('#bookingsTable th.sortable').forEach(th=>{
    th.style.cursor='pointer';
    th.addEventListener('click', ()=>{
        const key = th.dataset.key;
        if(state.sortKey===key) state.sortDir = -state.sortDir; else { state.sortKey=key; state.sortDir=1; }
        applySort(); renderTable();
    });
});

function applySort(){
    if(!state.sortKey) return;
    const k = state.sortKey; const dir = state.sortDir;
    state.filtered.sort((a,b)=>{
        let va = (a[k]||'').toString().toLowerCase();
        let vb = (b[k]||'').toString().toLowerCase();
        if(k === 'service_date' || k === 'service_time' || k === 'payment') {
            if (k === 'payment') { va = parseFloat(a[k]||0); vb = parseFloat(b[k]||0); return (va < vb ? -1*dir : va>vb ? 1*dir : 0); }
            return va < vb ? -1*dir : va > vb ? 1*dir : 0;
        }
        return va < vb ? -1*dir : va > vb ? 1*dir : 0;
    });
}

/* search & export */
searchBox.addEventListener('input', ()=> applySearch());
document.getElementById('exportCsv').addEventListener('click', ()=> {
    const url = new URL(window.location.href); url.searchParams.set('export','csv'); window.location = url.toString();
});

/* Edit modal logic (AJAX to edit_booking.php) */
const editModal = document.getElementById('editModal');
const editForm = document.getElementById('editForm');
const editMsg = document.getElementById('editMsg');

function openEditModal(b){
    document.getElementById('edit_id').value = b.id;
    document.getElementById('edit_first').value = b.first_name || '';
    document.getElementById('edit_last').value = b.last_name || '';
    document.getElementById('edit_email').value = b.email || '';
    document.getElementById('edit_phone').value = b.phone || '';
    document.getElementById('edit_car').value = b.car_name || '';
    document.getElementById('edit_car_type').value = b.car_type || 'Sedan';
    document.getElementById('edit_service_type').value = b.service_type || 'Wash';
    document.getElementById('edit_date').value = b.service_date || '';
    document.getElementById('edit_time').value = b.service_time || '';
    document.getElementById('edit_payment').value = b.payment || '';
    editMsg.textContent = '';
    editModal.style.display = 'flex';
}

document.getElementById('cancelEdit').addEventListener('click', ()=> { editModal.style.display = 'none'; });

editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    editMsg.textContent = 'Saving...';
    const fd = new FormData(editForm);
    try {
        const res = await fetch('edit_booking.php', { method:'POST', body: fd });
        const j = await res.json();
        if (j.success) {
            const id = parseInt(fd.get('id'));
            const idx = state.items.findIndex(it => parseInt(it.id) === id);
            if (idx !== -1) {
                state.items[idx].first_name = fd.get('first_name');
                state.items[idx].last_name = fd.get('last_name');
                state.items[idx].email = fd.get('email');
                state.items[idx].phone = fd.get('phone');
                state.items[idx].car_name = fd.get('car_name');
                state.items[idx].car_type = fd.get('car_type');
                state.items[idx].service_type = fd.get('service_type');
                state.items[idx].service_date = fd.get('service_date');
                state.items[idx].service_time = fd.get('service_time');
                state.items[idx].payment = fd.get('payment');
            }
            applySearch(); renderTable();
            editMsg.style.color = '#064a28'; editMsg.textContent = 'Saved.'; setTimeout(()=> editModal.style.display='none',700);
        } else {
            editMsg.style.color = '#7a0b0b'; editMsg.textContent = j.message || 'Save failed';
        }
    } catch(err) {
        editMsg.style.color = '#7a0b0b'; editMsg.textContent = 'Network/server error';
    }
});

/* initial */
applySearch(); renderTable();
</script>

<?php mysqli_close($conn); ?>
</body>
</html>
