<?php
require_once __DIR__ . '/auth.php';
$user = require_login();

$wdErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'withdraw') {
    if (!csrf_check()) {
        $wdErrors[] = 'Your session expired, please try again.';
    } else {
        $amount = (float) ($_POST['amount'] ?? 0);
        $wallet = trim($_POST['wallet_address'] ?? '');
        $method = trim($_POST['method'] ?? 'crypto');
        if ($amount <= 0) {
            $wdErrors[] = 'Enter a valid withdrawal amount.';
        } elseif ($amount > (float) $user['balance']) {
            $wdErrors[] = 'Withdrawal amount exceeds your available balance.';
        } elseif ($wallet === '') {
            $wdErrors[] = 'Please provide a destination wallet address or payout detail.';
        } else {
            $stmt = db()->prepare('INSERT INTO withdrawals (user_id, amount, method, wallet_address) VALUES (?,?,?,?)');
            $stmt->execute([$user['id'], $amount, $method, $wallet]);
            flash_set('Withdrawal request submitted. An admin will review it shortly.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

$stmt = db()->prepare('SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$apps = $stmt->fetchAll();

$stmt = db()->prepare('SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$withdrawals = $stmt->fetchAll();

$entityLabels = [
    'LLC' => 'Limited Liability Company',
    'CCORP' => 'Corporation (C-Corp)',
    'CLOSE_LLC' => 'Close LLC',
    'CLOSE_CORP' => 'Close Corporation',
];

$__base = '';
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top:44px">
  <div class="container">
    <div class="dash-header">
      <div>
        <h2 style="margin-bottom:4px">Welcome, <?= e($user['full_name']) ?></h2>
        <p style="margin:0">Track your applications, balance, and withdrawal requests.</p>
      </div>
      <a href="application.php" class="btn btn-gold">+ New Application</a>
    </div>

    <div id="crypto-ticker" class="ticker-bar"><div class="ticker-item" style="color:#94a3b8">Loading live prices&hellip;</div></div>

    <div class="balance-card">
      <div>
        <div class="label">Available Balance</div>
        <div class="amount"><?= fmt_money((float) $user['balance']) ?></div>
      </div>
      <button class="btn btn-gold" onclick="document.getElementById('wdModal').style.display='flex'">Request Withdrawal</button>
    </div>

    <?php foreach ($wdErrors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <h3 style="margin:34px 0 16px">Your Applications</h3>
    <?php if (!$apps): ?>
      <div class="empty-state">
        <h3 style="margin-bottom:8px">No applications yet</h3>
        <p>Start your first business formation application to see it tracked here.</p>
        <a href="application.php" class="btn btn-primary" style="margin-top:10px">Start Your Formation</a>
      </div>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>Business Name</th><th>Entity Type</th><th>State</th><th>Status</th><th>Submitted</th></tr></thead>
        <tbody>
          <?php foreach ($apps as $a): ?>
          <tr>
            <td><strong><?= e($a['business_name']) ?></strong></td>
            <td><?= e($entityLabels[$a['entity_type']] ?? $a['entity_type']) ?></td>
            <td><?= e($a['state']) ?></td>
            <td><?= status_badge($a['status']) ?></td>
            <td><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h3 style="margin:34px 0 16px">Withdrawal History</h3>
    <?php if (!$withdrawals): ?>
      <div class="empty-state"><p style="margin:0">No withdrawal requests yet.</p></div>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>Amount</th><th>Method</th><th>Destination</th><th>Status</th><th>Requested</th></tr></thead>
        <tbody>
          <?php foreach ($withdrawals as $w): ?>
          <tr>
            <td><strong><?= fmt_money((float) $w['amount']) ?></strong></td>
            <td><?= e(ucfirst($w['method'])) ?></td>
            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($w['wallet_address']) ?></td>
            <td><?= wd_status_badge($w['status']) ?></td>
            <td><?= e(date('M j, Y', strtotime($w['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<div id="wdModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);align-items:center;justify-content:center;z-index:60">
  <div class="auth-card" style="max-width:420px">
    <h2 style="font-size:22px">Request Withdrawal</h2>
    <p class="auth-sub">Available balance: <?= fmt_money((float) $user['balance']) ?></p>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="withdraw">
      <div class="field"><label>Amount (USD)</label><input type="number" step="0.01" min="0.01" max="<?= e($user['balance']) ?>" name="amount" required></div>
      <div class="field"><label>Method</label>
        <select name="method">
          <option value="crypto">Crypto Wallet</option>
          <option value="bank">Bank Transfer</option>
        </select>
      </div>
      <div class="field"><label>Wallet Address / Payout Details</label><input type="text" name="wallet_address" placeholder="e.g. 0x... or bank details" required></div>
      <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
      <button type="button" class="btn btn-outline btn-block" style="margin-top:8px" onclick="document.getElementById('wdModal').style.display='none'">Cancel</button>
    </form>
  </div>
</div>

<script src="assets/js/crypto-ticker.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
