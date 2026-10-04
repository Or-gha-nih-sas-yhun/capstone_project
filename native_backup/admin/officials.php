<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('staff');

$page_title = 'Barangay Officials';
$active_nav = 'officials';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['official_id'] ?? 0);
    $name     = sanitize($_POST['name']     ?? '');
    $position = sanitize($_POST['position'] ?? '');
    $status   = sanitize($_POST['status']   ?? 'active');
    $sort     = (int)($_POST['sort_order']  ?? 0);

    $photo_path = null;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photo_path = upload_file($_FILES['photo'], 'officials');
    }

    if ($id) {
        if ($photo_path) {
            db_execute("UPDATE officials SET name=?,position=?,status=?,sort_order=?,photo=? WHERE id=?",
                'sssssi', $name, $position, $status, $sort, $photo_path, $id);
        } else {
            db_execute("UPDATE officials SET name=?,position=?,status=?,sort_order=? WHERE id=?",
                'sssii', $name, $position, $status, $sort, $id);
        }
        set_flash('success','Official updated.');
    } else {
        if ($photo_path) {
            db_insert("INSERT INTO officials (name,position,status,sort_order,photo) VALUES (?,?,?,?,?)",
                'sssis', $name, $position, $status, $sort, $photo_path);
        } else {
            db_insert("INSERT INTO officials (name,position,status,sort_order) VALUES (?,?,?,?)",
                'sssi', $name, $position, $status, $sort);
        }
        set_flash('success','Official added.');
    }
    header('Location:'.BASE_URL.'/admin/officials.php'); exit;
}

if (isset($_GET['delete'])) {
    db_execute("DELETE FROM officials WHERE id=?", 'i', (int)$_GET['delete']);
    set_flash('success','Official removed.');
    header('Location:'.BASE_URL.'/admin/officials.php'); exit;
}

$edit      = isset($_GET['edit']) ? db_fetch_one("SELECT * FROM officials WHERE id=?", 'i', (int)$_GET['edit']) : null;
$officials = db_fetch_all("SELECT * FROM officials ORDER BY sort_order, name");

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
  <button class="btn btn-primary" onclick="openModal('officialModal')">
    <i class="fas fa-plus"></i> Add Official
  </button>
</div>

<div class="grid-3">
  <?php foreach ($officials as $o): ?>
  <div class="card" style="<?= $o['status']==='inactive'?'opacity:.55':'' ?>">
    <div class="card-body" style="text-align:center;padding:24px 16px;">
      <?php if (!empty($o['photo'])): ?>
      <div style="width:64px;height:64px;border-radius:50%;margin:0 auto 12px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
        <img src="<?= UPLOAD_URL . htmlspecialchars($o['photo']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="Photo">
      </div>
      <?php else: ?>
      <div style="width:64px;height:64px;border-radius:50%;margin:0 auto 12px;
                  background:linear-gradient(135deg,var(--primary),var(--accent));
                  display:flex;align-items:center;justify-content:center;
                  font-size:22px;font-weight:800;color:#fff;">
        <?= strtoupper(substr($o['name'],4,1)) ?>
      </div>
      <?php endif; ?>
      <div style="font-size:14px;font-weight:700;margin-bottom:2px;"><?= htmlspecialchars($o['name']) ?></div>
      <div style="font-size:12px;color:#6b7280;margin-bottom:12px;"><?= htmlspecialchars($o['position']) ?></div>
      <?= status_badge($o['status']) ?>
      <div style="display:flex;gap:6px;justify-content:center;margin-top:14px;">
        <a href="?edit=<?= $o['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
        <a href="?delete=<?= $o['id'] ?>" class="btn btn-danger btn-sm"
           onclick="return confirm('Remove this official?')"><i class="fas fa-trash"></i></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($officials)): ?>
  <div style="grid-column:span 3;text-align:center;padding:60px;color:#6b7280;">
    No officials found. Add one to get started.
  </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal-overlay <?= $edit?'show':'' ?>" id="officialModal">
  <div class="modal-box">
    <div class="modal-header">
      <h5><?= $edit?'Edit':'Add' ?> Official</h5>
      <button class="modal-close" onclick="<?= $edit?'window.location=\''.BASE_URL.'/admin/officials.php\'':'closeModal(\'officialModal\')' ?>">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="official_id" value="<?= $edit['id']??0 ?>">
        <div class="form-group">
          <label class="form-label">Full Name (with title) *</label>
          <input type="text" name="name" class="form-control" required
                 placeholder="HON. JUAN DELA CRUZ"
                 value="<?= htmlspecialchars($edit['name']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Position *</label>
          <input type="text" name="position" class="form-control" required
                 placeholder="Barangay Captain"
                 value="<?= htmlspecialchars($edit['position']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Photo</label>
          <input type="file" name="photo" class="form-control" accept="image/*">
          <?php if (!empty($edit['photo'])): ?>
            <div style="margin-top:8px;font-size:12px;color:#6b7280;">Current: <img src="<?= UPLOAD_URL . htmlspecialchars($edit['photo']) ?>" style="height:32px;vertical-align:middle;border-radius:4px;margin-left:4px;"></div>
          <?php endif; ?>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Display Order</label>
            <input type="number" name="sort_order" class="form-control" min="0" value="<?= $edit['sort_order']??0 ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active"   <?= ($edit['status']??'active')==='active'  ?'selected':'' ?>>Active</option>
              <option value="inactive" <?= ($edit['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
          onclick="<?= $edit?'window.location=\''.BASE_URL.'/admin/officials.php\'':'closeModal(\'officialModal\')' ?>">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
