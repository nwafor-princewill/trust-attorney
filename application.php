<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$errors = [];

$entityLabels = [
    'LLC' => 'Limited Liability Company (LLC)',
    'CCORP' => 'Corporation (C-CORP)',
    'CLOSE_LLC' => 'Close LLC',
    'CLOSE_CORP' => 'Close Corporation',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user) {
        // Preserve the submitted data across the login redirect
        $_SESSION['pending_application'] = $_POST;
        header('Location: login.php?next=' . urlencode('application.php?resume=1'));
        exit;
    }
    if (!csrf_check()) {
        $errors[] = 'Your session expired, please resubmit the form.';
    } else {
        $entity_type = $_POST['entity_type'] ?? '';
        $business_name = trim($_POST['business_name'] ?? '');
        $state = trim($_POST['state'] ?? 'Wyoming');
        $owner_name = trim($_POST['owner_name'] ?? $user['full_name']);
        $owner_email = trim($_POST['owner_email'] ?? $user['email']);
        $owner_phone = trim($_POST['owner_phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!isset($entityLabels[$entity_type]) || $business_name === '') {
            $errors[] = 'Please complete all required fields before submitting.';
        } else {
            $stmt = db()->prepare('INSERT INTO applications (user_id, entity_type, business_name, state, owner_name, owner_email, owner_phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $entity_type, $business_name, $state, $owner_name, $owner_email, $owner_phone, $address]);
            unset($_SESSION['pending_application']);
            flash_set('Application submitted! We will review it shortly.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

// Resume data after a login redirect
$resumeData = [];
if (!empty($_GET['resume']) && !empty($_SESSION['pending_application'])) {
    $resumeData = $_SESSION['pending_application'];
}
$presetType = $_GET['type'] ?? ($resumeData['entity_type'] ?? 'LLC');
if (!isset($entityLabels[$presetType])) $presetType = 'LLC';

$__base = '';
$pageTitle = 'Start Your Application';
require __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top:52px">
  <div class="container">
    <div class="section-head">
      <h2>Choose Your Entity Type</h2>
      <p>Select the business structure that best fits your operational and liability protection needs.</p>
    </div>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error" style="max-width:640px;margin:0 auto 18px"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" id="appForm">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="entity_type" id="entity_type" value="<?= e($presetType) ?>">

      <!-- Step 1: entity type -->
      <div class="step-panel" data-step="1">
        <div class="grid grid-2" style="max-width:900px;margin:0 auto 30px">
          <?php foreach ($entityLabels as $key => $label):
            $desc = [
              'LLC' => 'Flexible business structure with simplified management and tax benefits. Perfect for small to medium businesses.',
              'CCORP' => 'Traditional business structure ideal for raising capital, going public, and scaling operations globally.',
              'CLOSE_LLC' => 'Unique to Decentralized Trust with the same asset protection, tax and privacy benefits as a regular LLC. Reduced requirements.',
              'CLOSE_CORP' => 'Same asset protection, privacy and tax features as a Corporation, but with less maintenance.',
            ][$key];
          ?>
          <div class="pick-card <?= $presetType === $key ? 'selected' : '' ?>" data-entity="<?= e($key) ?>" onclick="selectEntity('<?= e($key) ?>')">
            <div class="check">&#10003;</div>
            <h3><?= e($label) ?></h3>
            <p><?= e($desc) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="text-align:center">
          <button type="button" class="btn btn-primary" onclick="goStep(2)">Continue &rarr;</button>
        </div>
      </div>

      <!-- Step 2: business details -->
      <div class="step-panel" data-step="2" style="display:none">
        <div class="form-card">
          <h3 style="margin-bottom:20px">Business &amp; Owner Details</h3>
          <div class="field">
            <label>Business Name *</label>
            <input type="text" name="business_name" required placeholder="Acme Holdings LLC" value="<?= e($resumeData['business_name'] ?? '') ?>">
          </div>
          <div class="field">
            <label>State of Formation</label>
            <select name="state">
              <?php foreach (['Wyoming','Delaware','Nevada','Texas','Florida'] as $st): ?>
                <option <?= ($resumeData['state'] ?? 'Wyoming') === $st ? 'selected' : '' ?>><?= $st ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row-2">
            <div class="field">
              <label>Owner Full Name *</label>
              <input type="text" name="owner_name" required value="<?= e($resumeData['owner_name'] ?? ($user['full_name'] ?? '')) ?>">
            </div>
            <div class="field">
              <label>Owner Email *</label>
              <input type="email" name="owner_email" required value="<?= e($resumeData['owner_email'] ?? ($user['email'] ?? '')) ?>">
            </div>
          </div>
          <div class="form-row-2">
            <div class="field">
              <label>Owner Phone</label>
              <input type="text" name="owner_phone" value="<?= e($resumeData['owner_phone'] ?? '') ?>">
            </div>
            <div class="field">
              <label>Mailing Address</label>
              <input type="text" name="address" value="<?= e($resumeData['address'] ?? '') ?>">
            </div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:10px">
            <button type="button" class="btn btn-outline" onclick="goStep(1)">&larr; Back</button>
            <button type="button" class="btn btn-primary" onclick="reviewAndGo()">Review Application &rarr;</button>
          </div>
        </div>
      </div>

      <!-- Step 3: review -->
      <div class="step-panel" data-step="3" style="display:none">
        <div class="form-card">
          <h3 style="margin-bottom:20px">Review &amp; Submit</h3>
          <div id="reviewBox"></div>
          <?php if (!$user): ?>
            <div class="alert alert-info" style="margin-top:20px">You'll be asked to log in or create a free account to submit your application.</div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;margin-top:20px">
            <button type="button" class="btn btn-outline" onclick="goStep(2)">&larr; Back</button>
            <button type="submit" class="btn btn-gold">Submit Application</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
const labels = <?= json_encode($entityLabels) ?>;

function selectEntity(key) {
  document.getElementById('entity_type').value = key;
  document.querySelectorAll('.pick-card').forEach(c => c.classList.toggle('selected', c.dataset.entity === key));
}

function goStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.style.display = (p.dataset.step == n) ? '' : 'none');
  window.scrollTo({top: 0, behavior: 'smooth'});
}

function reviewAndGo() {
  const f = document.getElementById('appForm');
  if (!f.business_name.value.trim() || !f.owner_name.value.trim() || !f.owner_email.value.trim()) {
    alert('Please fill in the required fields marked with *.');
    return;
  }
  const type = document.getElementById('entity_type').value;
  const rows = [
    ['Entity Type', labels[type]],
    ['Business Name', f.business_name.value],
    ['State of Formation', f.state.value],
    ['Owner Name', f.owner_name.value],
    ['Owner Email', f.owner_email.value],
    ['Owner Phone', f.owner_phone.value || '—'],
    ['Mailing Address', f.address.value || '—'],
  ];
  document.getElementById('reviewBox').innerHTML = rows.map(r =>
    `<div class="review-row"><span class="k">${r[0]}</span><span class="v">${r[1]}</span></div>`
  ).join('');
  goStep(3);
}

<?php if (!empty($resumeData)): ?>
  // Data was preserved across a login redirect — jump straight to review
  window.addEventListener('DOMContentLoaded', () => { reviewAndGo(); });
<?php endif; ?>
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
