<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$users = db()->query('SELECT u.*, (SELECT COUNT(*) FROM applications a WHERE a.user_id = u.id) AS app_count FROM users u ORDER BY u.created_at DESC')->fetchAll();

$pageTitle = 'Users';
require __DIR__ . '/includes/header.php';
?>
<div class="panel">
  <?php if (!$users): ?>
    <p style="color:var(--muted)">No registered users yet.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Applications</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= e($u['full_name']) ?></strong></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e($u['phone'] ?: '—') ?></td>
          <td><?= (int) $u['app_count'] ?></td>
          <td><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
