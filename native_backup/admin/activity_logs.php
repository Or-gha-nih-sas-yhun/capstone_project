<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('admin');

$page_title = 'Activity Logs';
$active_nav = 'logs';

$page     = max(1,(int)($_GET['page']??1));
$per_page = 20;
$search   = sanitize($_GET['search'] ?? '');
$module   = sanitize($_GET['module'] ?? '');
$action   = sanitize($_GET['action'] ?? '');

$where = "WHERE 1=1";
$params = []; $types = '';
if ($search) { $like="%$search%"; $where.=" AND (l.action LIKE ? OR l.description LIKE ? OR u.username LIKE ?)"; $params=array_merge($params,[$like,$like,$like]); $types.='sss'; }
if ($module) { $where.=" AND l.module=?"; $params[]=$module; $types.='s'; }
if ($action) { $where.=" AND l.action=?"; $params[]=$action; $types.='s'; }

$total = (int)db_fetch_one("SELECT COUNT(*) c FROM activity_logs l LEFT JOIN users u ON l.user_id=u.id $where",$types,...$params)['c'];
$pg    = paginate($total,$per_page,$page);
$pp    = array_merge($params,[$per_page,$pg['offset']]); $tp=$types.'ii';

$logs = db_fetch_all(
    "SELECT l.*, u.username FROM activity_logs l
     LEFT JOIN users u ON l.user_id=u.id
     $where ORDER BY l.created_at DESC LIMIT ? OFFSET ?",
    $tp,...$pp
);
$modules = db_fetch_all("SELECT DISTINCT module FROM activity_logs WHERE module IS NOT NULL AND module!='' ORDER BY module");
$actions = db_fetch_all("SELECT DISTINCT action FROM activity_logs WHERE action IS NOT NULL AND action!='' ORDER BY action");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="padding:16px 20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" placeholder="Action, description, user…" value="<?= htmlspecialchars($search) ?>">
      </div>
      <div style="min-width:150px;">
        <label class="form-label">Module</label>
        <select name="module" class="form-select">
          <option value="">All Modules</option>
          <?php foreach ($modules as $m): ?>
          <option value="<?= htmlspecialchars($m['module']) ?>" <?= $module===$m['module']?'selected':'' ?>>
            <?= htmlspecialchars($m['module']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="min-width:190px;">
        <label class="form-label">Activity</label>
        <select name="action" class="form-select">
          <option value="">All Activities</option>
          <?php foreach ($actions as $a): ?>
          <option value="<?= htmlspecialchars($a['action']) ?>" <?= $action===$a['action']?'selected':'' ?>>
            <?= htmlspecialchars($a['action']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
      <a href="?" class="btn btn-secondary">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-history" style="color:var(--primary);margin-right:8px;"></i>
        Activity Logs <span style="font-size:13px;font-weight:400;color:#6b7280;">(<?= $total ?>)</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr><th>Date/Time</th><th>User</th><th>Action</th><th>Module</th><th>Description</th></tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
        <tr><td colspan="5" class="text-center text-muted" style="padding:40px;">No logs found.</td></tr>
        <?php else: ?>
        <?php foreach ($logs as $log): ?>
        <tr>
          <td><small><?= format_datetime($log['created_at']) ?></small></td>
          <td>
            <?php if ($log['username']): ?>
            <span class="badge bg-secondary"><?= htmlspecialchars($log['username']) ?></span>
            <?php else: ?>
            <span class="text-muted">System</span>
            <?php endif; ?>
          </td>
          <td>
            <?php
            $action_colors = ['LOGIN'=>'success','LOGOUT'=>'secondary','APPROVE_REQUEST'=>'success',
                             'REJECT_REQUEST'=>'danger','NEW_REQUEST'=>'primary','UPDATE_RESIDENT'=>'warning'];
            $ac = $action_colors[$log['action']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $ac ?>"><?= htmlspecialchars($log['action']) ?></span>
          </td>
          <td><small style="color:#6b7280;"><?= htmlspecialchars($log['module']??'—') ?></small></td>
          <td style="max-width:360px;font-size:12.5px;"><?= htmlspecialchars($log['description']??'') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pg['total_pages']>1): ?>
  <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
    <div class="pagination">
      <?php for($i=1;$i<=$pg['total_pages'];$i++): ?>
      <a href="?page=<?=$i?>&search=<?=urlencode($search)?>&module=<?=urlencode($module)?>&action=<?=urlencode($action)?>"
         class="page-btn <?=$i===$pg['current']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
