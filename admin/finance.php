<?php
require_once '../includes/config.php';
requireAdmin();

// Delete finance entry AND linked donation if it's a donation entry
if (isset($_GET['delete']) && allowDelete()) {
    $fid = (int)$_GET['delete'];
    $f = db()->query("SELECT * FROM finance WHERE id=$fid")->fetch_assoc();
    if ($f) {
        if ($f['donation_id']) {
            // Also delete the donation record and ALL finance entries linked to it
            $did = (int)$f['donation_id'];
            db()->query("DELETE FROM finance WHERE donation_id=$did");
            db()->query("DELETE FROM donations WHERE id=$did");
        } else {
            db()->query("DELETE FROM finance WHERE id=$fid");
        }
    }
    header('Location: finance.php'); exit;
}

$type_f = escape($_GET['type'] ?? '');
$month_f = escape($_GET['month'] ?? '');
$w = 'WHERE 1=1';
if ($type_f) $w .= " AND f.type='$type_f'";
if ($month_f) $w .= " AND DATE_FORMAT(f.date,'%Y-%m')='$month_f'";

$income  = (int)db()->query("SELECT COALESCE(SUM(amount),0) FROM finance WHERE type='income'")->fetch_row()[0];
$expense = (int)db()->query("SELECT COALESCE(SUM(amount),0) FROM finance WHERE type='expense'")->fetch_row()[0];
$balance = $income - $expense;

$entries = db()->query("SELECT f.*, m.name as mname FROM finance f LEFT JOIN members m ON f.member_id=m.id $w ORDER BY f.date DESC, f.id DESC");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>আর্থিক হিসাব</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>আর্থিক হিসাব</h1>
    <div style="display:flex;gap:6px">
      <a href="add_finance.php" class="btn btn-primary btn-sm"><span class="material-icons">add_card</span> <span class="hide-xs">এন্ট্রি যোগ</span></a>
      <a href="export_finance.php" class="btn btn-outline btn-sm"><span class="material-icons">download</span> CSV</a>
    </div>
  </div>
  <div class="content">
    <div class="fin-grid">
      <div class="fin-card"><h3 class="text-success">৳<?= number_format($income) ?></h3><p>মোট আয়</p></div>
      <div class="fin-card"><h3 class="text-danger">৳<?= number_format($expense) ?></h3><p>মোট ব্যয়</p></div>
      <div class="fin-card"><h3 class="<?= $balance>=0?'text-success':'text-danger' ?>">৳<?= number_format($balance) ?></h3><p>ব্যালেন্স</p></div>
    </div>

    <div class="alert alert-info" style="font-size:0.82rem">
      <span class="material-icons" style="font-size:1rem">info</span>
      বই দানের আনুমানিক মূল্য <strong>আয় ও ব্যয় উভয়ে</strong> দেখায় — ব্যালেন্সে কোনো প্রভাব নেই।
      দান মুছলে সংশ্লিষ্ট সব এন্ট্রি মুছে যাবে।
    </div>

    <form method="GET" style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap">
      <select name="type" class="form-control" style="width:150px" onchange="this.form.submit()">
        <option value="">সব ধরন</option>
        <option value="income" <?= $type_f=='income'?'selected':'' ?>>আয়</option>
        <option value="expense" <?= $type_f=='expense'?'selected':'' ?>>ব্যয়</option>
      </select>
      <input type="month" name="month" class="form-control" style="width:170px" value="<?= $month_f ?>" onchange="this.form.submit()">
      <?php if ($type_f||$month_f): ?><a href="finance.php" class="btn btn-outline">✕</a><?php endif; ?>
    </form>

    <div class="card"><div class="table-responsive"><table style="min-width:650px">
      <thead><tr><th>তারিখ</th><th>ধরন</th><th>বিভাগ</th><th>বিবরণ</th><th>সদস্য</th><th>পরিমাণ</th><th></th></tr></thead>
      <tbody>
      <?php while ($f = $entries->fetch_assoc()): ?>
        <tr>
          <td><?= $f['date'] ?></td>
          <td><?= $f['type']==='income'?"<span class='badge badge-success'>আয়</span>":"<span class='badge badge-danger'>ব্যয়</span>" ?></td>
          <td><?= htmlspecialchars($f['category'] ?? '—') ?></td>
          <td>
            <?= htmlspecialchars($f['description']) ?>
            <?php if ($f['donation_id']): ?><br><small class="badge badge-muted">দান #<?= $f['donation_id'] ?></small><?php endif; ?>
          </td>
          <td><?= htmlspecialchars($f['mname'] ?? '—') ?></td>
          <td><strong>৳<?= number_format($f['amount']) ?></strong></td>
          <td><?php if (allowDelete()): ?>
            <a href="?delete=<?= $f['id'] ?><?= $type_f?"&type=$type_f":'' ?><?= $month_f?"&month=$month_f":'' ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('<?= $f['donation_id']?'এই দানের সব হিসাব মুছে যাবে।':'এই এন্ট্রি মুছবেন?' ?>')">
              <span class="material-icons">delete</span></a>
          <?php endif; ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table></div></div>
  </div>
</div>
</body></html>
