<?php
// users_list.php
session_start();

// must be logged in and admin
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

include 'db_connect.php';

// handle deletion (POST)
$message = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];

    // do not allow deleting yourself
    if ($delete_id === (int)$_SESSION['user_id']) {
        $message = "You cannot delete your own admin account.";
        $message_type = 'error';
    } else {
        // fetch the target user's role to ensure it's not an admin
        $q = "SELECT id, email, role FROM users WHERE id = $delete_id LIMIT 1";
        $res = mysqli_query($conn, $q);
        if ($res && mysqli_num_rows($res) === 1) {
            $target = mysqli_fetch_assoc($res);
            if (isset($target['role']) && $target['role'] === 'admin') {
                $message = "Cannot delete another admin account.";
                $message_type = 'error';
            } else {
                // begin transaction to keep changes consistent
                mysqli_begin_transaction($conn);
                $ok = true;

                // 1) set services.user_id = NULL for bookings that belong to this user
                $stmt1 = "UPDATE services SET user_id = NULL WHERE user_id = " . intval($delete_id);
                if (!mysqli_query($conn, $stmt1)) {
                    $ok = false;
                    $message = "Failed to unlink user's bookings: " . mysqli_error($conn);
                    $message_type = 'error';
                }

                // 2) delete user row
                if ($ok) {
                    $stmt2 = "DELETE FROM users WHERE id = " . intval($delete_id) . " LIMIT 1";
                    if (!mysqli_query($conn, $stmt2)) {
                        $ok = false;
                        $message = "Failed to delete user: " . mysqli_error($conn);
                        $message_type = 'error';
                    }
                }

                if ($ok) {
                    mysqli_commit($conn);
                    $message = "User deleted successfully. Their bookings were unlinked (user_id set to NULL).";
                    $message_type = 'success';
                } else {
                    mysqli_rollback($conn);
                    if ($message === '') {
                        $message = "Unknown error while deleting user.";
                        $message_type = 'error';
                    }
                }
            }
        } else {
            $message = "User not found.";
            $message_type = 'error';
        }
    }
}

// Export CSV if requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sqlExport = "SELECT id, first_name, last_name, email, phone, role, created_at FROM users ORDER BY id DESC";
    $resExport = mysqli_query($conn, $sqlExport);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=washmycar_users_' . date('Ymd_Hi') . '.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','First Name','Last Name','Email','Phone','Role','Registered At']);
    while ($r = mysqli_fetch_assoc($resExport)) {
        fputcsv($out, [
            $r['id'],
            $r['first_name'],
            $r['last_name'],
            $r['email'],
            $r['phone'],
            $r['role'],
            $r['created_at'] ?? ''
        ]);
    }
    fclose($out);
    exit();
}

// fetch all users (for display)
$sql = "SELECT id, first_name, last_name, email, phone, role, created_at FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
if ($result === false) {
    die("DB error: " . mysqli_error($conn));
}

// build array
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users List - Admin</title>
<link rel="stylesheet" href="style.css">
<style>
/* page-specific tweaks */
.section {
    width: 80vw;
    margin: 2rem auto;
    padding: 1.2rem;
    background: #a8acb8;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    max-width: 1200px;
}

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

/* search box centered */
#searchBox {
    width: 60%;
    max-width: 520px;
    min-width: 180px;
    padding:8px 10px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* buttons and look */
.back-link { text-decoration:none; color: whitesmoke; background:#333; padding:8px 12px; border-radius:6px; }
.page-btn { padding:8px 10px; border-radius:6px; background:#667eea; border:1px solid #662f7f5e; cursor:pointer; font-weight:600; color:#fff; }
.page-btn.active { background:#5563d6; color:#fff; border-color:#444aa8; }

/* table / visual */
.users-table { width:100%; border-collapse: collapse; background:#d0d3db; border-radius:8px; overflow:hidden; }
.users-table thead { background:#8f95a2; color:#222; font-size:1rem; }
.users-table th, .users-table td { padding:0.6rem; border:1px solid #7f8594; text-align:center; font-size:0.92rem; }
.users-table tbody tr:nth-child(even) { background:#b5baca; }
.users-table tbody tr:hover { background:#acbadf85; }

/* hide ID column visually but keep it in DOM for JS */
.users-table th.id-col, .users-table td.id-col { display:none; }

/* actions */
.actions form { display:inline-block; margin:0; }
.delete-btn { background: rgba(176,0,0,0.06); border: none; color: #b00; cursor: pointer; font-weight: 700; padding: 4px 8px; border-radius: 4px; }
.delete-btn:hover { background: rgba(176, 0, 0, 0.14); }
.edit-btn {  background: rgba(0,176,0,0.06);; border: none; color: #0b5; cursor: pointer; font-weight: 700; padding: 4px 8px; border-radius: 4px; }
.edit-btn:hover { background: rgba(176, 249, 176, 0.33); }

/* message */
.message { padding: 10px; border-radius:6px; margin-bottom:12px; }
.message.success { background: #e6f8ee; color:#064a28; border:1px solid #bdeacb; }
.message.error { background: #fdecea; color:#7a0b0b; border:1px solid #f5c6c6; }



/* responsive adjustments */
.table-container { overflow-x:auto; }
@media (max-width:920px) {
    #searchBox { width: 70%; }
}
@media (max-width:640px) {
    .top-row { gap:6px; }
    .top-row .center { order:3; width:100%; margin-top:8px; }
    .top-row .right { order:2; }
    .top-row .left { order:1; }
    .pagination { flex-wrap:wrap; gap:8px; }
    .pagination .pages { margin:0; order:2; width:100%; justify-content:center; }
}
</style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="body-panel">
        <div class="section">
            <div class="top-row">
                <div class="left"><h2 style="margin:0;color:#222;">Registered Users</h2></div>
                <div class="center">
                    <input type="search" id="searchBox" placeholder="Search name, email or phone...">
                </div>
                <div class="right">
                    <button id="exportCsv" class="page-btn" title="Export CSV">Export CSV</button>
                    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table id="usersTable" class="users-table" data-pagesize="10">
                    <thead>
                        <tr>
                            <th class="id-col">#</th>
                            <th data-key="first_name" class="sortable">First Name ▲▼</th>
                            <th data-key="last_name" class="sortable">Last Name ▲▼</th>
                            <th data-key="email" class="sortable">Email ▲▼</th>
                            <th data-key="phone" class="sortable">Phone ▲▼</th>
                            <th data-key="role" class="sortable">Role ▲▼</th>
                            <th data-key="created_at" class="sortable">Registered At ▲▼</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit modal -->
    <div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
        <div style="background:#fff; padding:20px; width:420px; border-radius:8px; box-shadow:0 6px 30px rgba(0,0,0,0.2);">
            <h3 style="margin-top:0">Edit User</h3>
            <div id="editMsg" style="margin-bottom:8px;"></div>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <div style="margin-bottom:8px;">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="edit_first" style="width:100%; padding:6px;">
                </div>
                <div style="margin-bottom:8px;">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="edit_last" style="width:100%; padding:6px;">
                </div>
                <div style="margin-bottom:8px;">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit_phone" style="width:100%; padding:6px;">
                </div>
                <div style="margin-bottom:8px;">
                    <label>Role</label>
                    <select name="role" id="edit_role" style="width:100%; padding:6px;">
                        <option value="user">user</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
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
const users = <?php echo json_encode($users, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
const tbody = document.querySelector('#usersTable tbody');
const searchBox = document.getElementById('searchBox');
const paginationEl = document.getElementById('pagination');
const pageSize = parseInt(document.querySelector('#usersTable').dataset.pagesize || '10', 10);

let state = {
    items: users.slice(),
    filtered: users.slice(),
    sortKey: null,
    sortDir: 1,
    page: 1
};

function renderTable() {
    const start = (state.page - 1) * pageSize;
    const pageItems = state.filtered.slice(start, start + pageSize);

    tbody.innerHTML = '';
    for (const r of pageItems) {
        const tr = document.createElement('tr');

        const tdId = document.createElement('td'); tdId.className = 'id-col'; tdId.textContent = r.id; tr.appendChild(tdId);
        tr.appendChild(tdCell(r.first_name)); tr.appendChild(tdCell(r.last_name));
        tr.appendChild(tdCell(r.email)); tr.appendChild(tdCell(r.phone));
        tr.appendChild(tdCell(r.role)); tr.appendChild(tdCell(r.created_at || ''));

        const tdActions = document.createElement('td');
        const editBtn = document.createElement('button'); editBtn.className='edit-btn'; editBtn.textContent='Edit';
        editBtn.onclick = ()=> openEditModal(r);
        tdActions.appendChild(editBtn);

        if ((r.role || '') !== 'admin') {
            const delForm = document.createElement('form'); delForm.method='post';
            delForm.onsubmit = function(){ return confirm('Delete this user? This will not remove their bookings, only unlink them.'); };
            const input = document.createElement('input'); input.type='hidden'; input.name='delete_id'; input.value = r.id;
            const delBtn = document.createElement('button'); delBtn.type='submit'; delBtn.className='delete-btn'; delBtn.textContent='Delete';
            delForm.appendChild(input); delForm.appendChild(delBtn);
            tdActions.appendChild(delForm);
        } else {
            const span = document.createElement('span'); span.textContent='—'; tdActions.appendChild(span);
        }

        tr.appendChild(tdActions);
        tbody.appendChild(tr);
    }   
}

function tdCell(text) { const td=document.createElement('td'); td.textContent = text || ''; return td; }

function applySearch() {
    const q = (searchBox.value || '').toLowerCase().trim();
    if (!q) state.filtered = state.items.slice();
    else {
        state.filtered = state.items.filter(u => (
            (u.first_name||'').toLowerCase().includes(q) ||
            (u.last_name||'').toLowerCase().includes(q) ||
            (u.email||'').toLowerCase().includes(q) ||
            (u.phone||'').toLowerCase().includes(q) ||
            (u.role||'').toLowerCase().includes(q)
        ));
    }
    state.page = 1;
    applySort();
    renderTable();
}

document.querySelectorAll('#usersTable th.sortable').forEach(th => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
        const key = th.dataset.key;
        if (state.sortKey === key) state.sortDir = -state.sortDir;
        else { state.sortKey = key; state.sortDir = 1; }
        applySort();
        renderTable();
    });
});

function applySort() {
    if (!state.sortKey) return;
    const k = state.sortKey; const dir = state.sortDir;
    state.filtered.sort((a,b) => {
        let va = (a[k] || '').toString().toLowerCase();
        let vb = (b[k] || '').toString().toLowerCase();
        return va < vb ? -1*dir : va > vb ? 1*dir : 0;
    });
}

/* search & export */
searchBox.addEventListener('input', () => applySearch());
document.getElementById('exportCsv').addEventListener('click', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('export','csv');
    window.location = url.toString();
});

/* Edit modal logic (AJAX to edit_user.php) */
const editModal = document.getElementById('editModal');
const editForm = document.getElementById('editForm');
const editMsg = document.getElementById('editMsg');

function openEditModal(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_first').value = user.first_name || '';
    document.getElementById('edit_last').value = user.last_name || '';
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_role').value = user.role || 'user';
    editMsg.textContent = '';
    editModal.style.display = 'flex';
}

document.getElementById('cancelEdit').addEventListener('click', () => {
    editModal.style.display = 'none';
});

editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    editMsg.textContent = 'Saving...';
    const formData = new FormData(editForm);
    try {
        const res = await fetch('edit_user.php', { method: 'POST', body: formData });
        const j = await res.json();
        if (j.success) {
            const idx = state.items.findIndex(it => parseInt(it.id) === parseInt(formData.get('id')));
            if (idx !== -1) {
                state.items[idx].first_name = formData.get('first_name');
                state.items[idx].last_name = formData.get('last_name');
                state.items[idx].phone = formData.get('phone');
                state.items[idx].role = formData.get('role');
            }
            applySearch();
            renderTable();
            editMsg.style.color = '#064a28';
            editMsg.textContent = 'Saved.';
            setTimeout(() => { editModal.style.display = 'none'; }, 800);
        } else {
            editMsg.style.color = '#7a0b0b';
            editMsg.textContent = j.message || 'Save failed';
        }
    } catch (err) {
        editMsg.style.color = '#7a0b0b';
        editMsg.textContent = 'Network or server error';
    }
});

// initial render
applySearch();
renderTable();
</script>

<?php mysqli_close($conn); ?>
</body>
</html>
