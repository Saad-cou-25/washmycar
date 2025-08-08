<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}
include 'db_connect.php';
$user_id = (int)$_SESSION['user_id'];

if (isset($_GET['sent']) && $_GET['sent'] == '1') {
    $notice = "Message sent to admin.";
}

// fetch messages for user
$sql = "SELECT * FROM messages WHERE user_id = $user_id ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Messages</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'header.php'; ?>
<div class="body-panel">
  <div class="section">
    <h1>Messages with Admin</h1>
    <?php if (!empty($notice)) echo '<div class="error-message" style="background:#e6ffe6;color:#064;">' . htmlspecialchars($notice) . '</div>'; ?>
    <form action="send_message.php" method="post">
        <div class="form-group">
            <label for="message">Write your message / complaint</label>
            <textarea id="message" name="message" rows="4" style="width:100%;" required></textarea>
        </div>
        <button type="submit">Send to Admin</button>
    </form>

    <h2 style="margin-top:1.5rem;">Previous messages</h2>
    <div class="table-container">
      <table class="dashboard-table">
        <thead><tr><th>When</th><th>Message</th><th>Reply</th></tr></thead>
        <tbody>
        <?php while ($m = mysqli_fetch_assoc($res)): ?>
          <tr>
            <td><?php echo $m['created_at']; ?></td>
            <td><?php echo nl2br(htmlspecialchars($m['message'])); ?></td>
            <td>
              <?php if ($m['reply']): ?>
                <div style="background:#fff;padding:.5rem;border-radius:6px;"><?php echo nl2br(htmlspecialchars($m['reply'])); ?>
                  <div style="font-size:0.85rem;color:#666;margin-top:.5rem;">Replied at: <?php echo $m['replied_at']; ?></div>
                </div>
              <?php else: ?>
                <em>No reply yet</em>
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
