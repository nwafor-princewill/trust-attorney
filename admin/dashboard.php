<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$totalUsers = (int) db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'];
$totalApps = (int) db()->query('SELECT COUNT(*) c FROM applications')->fetch()['c'];
$pending = (int) db()->query("SELECT COUNT(*) c FROM applications WHERE status='pending'")->fetch()['c'];
$approved = (int) db()->query("SELECT COUNT(*) c FROM applications WHERE status='approved'")->fetch()['c'];

$recent = db()->query('SELECT a.*, u.full_name AS user_name FROM applications a JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 8')->fetchAll();

$entityLabels = ['LLC' => 'LLC', 'CCORP' => 'C-Corp', 'CLOSE_LLC' => 'Close LLC', 'CLOSE_CORP' => 'Close Corp'];

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="kpi-grid">
  <div class="kpi-card"><div class="num"><?= $totalUsers ?></div><div class="label">Registered Users</div></div>
  <div class="kpi-card"><div class="num"><?= $totalApps ?></div><div class="label">Total Applications</div></div>
  <div class="kpi-card"><div class="num"><?= $pending ?></div><div class="label">Pending Review</div></div>
  <div class="kpi-card"><div class="num"><?= $approved ?></div><div class="label">Approved</div></div>
</div>

<div class="panel">
  <h3 style="margin-bottom:16px">Recent Applications</h3>
  <?php if (!$recent): ?>
    <p style="color:var(--muted)">No applications submitted yet.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Business Name</th><th>Applicant</th><th>Entity</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recent as $a): ?>
        <tr>
          <td><strong><?= e($a['business_name']) ?></strong></td>
          <td><?= e($a['user_name']) ?></td>
          <td><?= e($entityLabels[$a['entity_type']] ?? $a['entity_type']) ?></td>
          <td><?= status_badge($a['status']) ?></td>
          <td><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
          <td><a href="application_view.php?id=<?= (int) $a['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
