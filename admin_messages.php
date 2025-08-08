<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';

// admin sees all messages
$sql = "SELECT m.*, u.first_name, u.last_name, u.email FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Messages</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'header.php'; ?>
<div class="body-panel">
  <div class="section">
    <h1>Messages</h1>
    <div class="table-container">
      <table class="dashboard-table">
        <thead><tr><th>User</th><th>When</th><th>Message</th><th>Reply</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($m = mysqli_fetch_assoc($res)): ?>
          <tr>
            <td><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) . "<br><small>".$m['email']."</small>"; ?></td>
            <td><?php echo $m['created_at']; ?></td>
            <td><?php echo nl2br(htmlspecialchars($m['message'])); ?></td>
            <td>
              <?php if ($m['reply']): ?>
                <?php echo nl2br(htmlspecialchars($m['reply'])); ?>
                <div style="font-size:.85rem;color:#666">Replied at: <?php echo $m['replied_at']; ?></div>
              <?php else: ?>
                <em>No reply yet</em>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!$m['reply']): ?>
                <form action="reply_message.php" method="post" style="display:inline-block;">
                  <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                  <textarea name="reply" rows="2" required style="width:220px;"></textarea><br>
                  <button type="submit">Send Reply</button>
                </form>
              <?php else: ?>
                <form action="reply_message.php" method="post" style="display:inline-block;">
                  <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                  <textarea name="reply" rows="2" required style="width:220px;"><?php echo htmlspecialchars($m['reply']); ?></textarea><br>
                  <button type="submit">Update Reply</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php mysqli_close($conn); ?>
</body>
</html>
