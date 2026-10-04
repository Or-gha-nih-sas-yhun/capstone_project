<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('staff');

$page_title = 'Certificate Types';
$active_nav = 'certificates';

// Save / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['cert_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $cat  = sanitize($_POST['category'] ?? certificate_category_for($name));
    $fee  = (float)($_POST['fee'] ?? 0);
    $days = (int)($_POST['processing_days'] ?? 1);
    $reqs = sanitize($_POST['requirements'] ?? '');
    $stat = sanitize($_POST['status'] ?? 'active');
    $template = certificate_template_for($name);

    if ($id) {
        db_execute("UPDATE certificates SET name=?,description=?,category=?,fee=?,processing_days=?,template_file=?,requirements=?,status=?,archived_at=NULL,archived_by=NULL WHERE id=?",
            'sssdisssi', $name, $desc, $cat, $fee, $days, $template, $reqs, $stat, $id);
        set_flash('success','Certificate type updated.');
    } else {
        db_insert("INSERT INTO certificates (name,description,category,fee,processing_days,template_file,requirements,status) VALUES (?,?,?,?,?,?,?,?)",
            'sssdisss', $name, $desc, $cat, $fee, $days, $template, $reqs, $stat);
        set_flash('success','Certificate type added.');
    }
    header('Location:'.BASE_URL.'/admin/certificates.php'); exit;
}

// Delete
if (isset($_GET['delete'])) {
    $uid = (int)$_SESSION['user_id'];
    db_execute("UPDATE certificates SET status='inactive', archived_at=NOW(), archived_by=? WHERE id=?", 'ii', $uid, (int)$_GET['delete']);
    set_flash('success','Certificate type archived. You can restore it from Archive.');
    header('Location:'.BASE_URL.'/admin/certificates.php'); exit;
}

$edit  = isset($_GET['edit']) ? db_fetch_one("SELECT * FROM certificates WHERE id=?", 'i', (int)$_GET['edit']) : null;
$certs = db_fetch_all("SELECT c.*, (SELECT COUNT(*) FROM requests r WHERE r.certificate_id=c.id) AS req_count
                        FROM certificates c WHERE c.archived_at IS NULL ORDER BY c.category, c.name");

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
  <button class="btn btn-primary" onclick="openModal('certModal')">
    <i class="fas fa-plus"></i> Add Certificate Type
  </button>
</div>

<div class="grid-3" style="margin-bottom:24px;">
  <?php foreach ($certs as $c): ?>
  <div class="card" style="<?= $c['status']==='inactive'?'opacity:.55':'' ?>">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
        <div>
          <div style="font-size:15px;font-weight:700;margin-bottom:4px;"><?= htmlspecialchars($c['name']) ?></div>
          <div class="badge bg-secondary" style="margin-bottom:8px;"><?= htmlspecialchars($c['category'] ?? certificate_category_for($c['name'])) ?></div>
          <div style="font-size:12px;color:#6b7280;margin-bottom:10px;"><?= htmlspecialchars($c['description'] ?? '') ?></div>
        </div>
        <?= status_badge($c['status']) ?>
      </div>
      <div style="display:flex;gap:16px;font-size:13px;flex-wrap:wrap;margin-bottom:14px;">
        <span><i class="fas fa-peso-sign" style="color:var(--primary);margin-right:4px;"></i>
          <strong><?= $c['fee']>0 ? '₱'.number_format($c['fee'],2) : 'FREE' ?></strong></span>
        <span><i class="fas fa-clock" style="color:#f59e0b;margin-right:4px;"></i>
          <?= $c['processing_days'] ?> day<?= $c['processing_days']>1?'s':'' ?></span>
        <span><i class="fas fa-file-alt" style="color:#6b7280;margin-right:4px;"></i>
          <?= $c['req_count'] ?> requests</span>
      </div>
      <?php if ($c['requirements']): ?>
      <div style="font-size:11.5px;color:#6b7280;border-top:1px solid #f3f4f6;padding-top:10px;margin-bottom:12px;">
        <strong>Requires:</strong> <?= htmlspecialchars($c['requirements']) ?>
      </div>
      <?php endif; ?>
      <div style="display:flex;gap:6px;">
        <a href="?edit=<?= $c['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
        <?php if ($c['status']==='active'): ?>
        <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm"
           onclick="return confirm('Deactivate this certificate type?')">
          <i class="fas fa-ban"></i>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay <?= $edit?'show':'' ?>" id="certModal">
  <div class="modal-box">
    <div class="modal-header">
      <h5><?= $edit?'Edit':'Add' ?> Certificate Type</h5>
      <button class="modal-close" onclick="<?= $edit?'window.location=\''.BASE_URL.'/admin/certificates.php\'':'closeModal(\'certModal\')' ?>">&times;</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="cert_id" value="<?= $edit['id']??0 ?>">
        <div class="form-group">
          <label class="form-label">Certificate Name *</label>
          <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit['name']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($edit['description']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Certificate Group</label>
          <select name="category" class="form-select">
            <?php foreach (['Clearance','Certification','Social Services','Employment','Business','General'] as $group): ?>
            <option value="<?= $group ?>" <?= ($edit['category']??certificate_category_for($edit['name']??''))===$group?'selected':'' ?>><?= $group ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Processing Fee (₱)</label>
            <input type="number" name="fee" class="form-control" min="0" step="0.01" value="<?= $edit['fee']??0 ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Processing Days</label>
            <input type="number" name="processing_days" class="form-control" min="1" value="<?= $edit['processing_days']??1 ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Requirements</label>
          <input type="text" name="requirements" class="form-control"
                 placeholder="e.g. Valid ID, Proof of residency"
                 value="<?= htmlspecialchars($edit['requirements']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="active" <?= ($edit['status']??'active')==='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= ($edit['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
          onclick="<?= $edit?'window.location=\''.BASE_URL.'/admin/certificates.php\'':'closeModal(\'certModal\')' ?>">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
