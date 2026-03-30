<?php
require_once '../includes/config.php';
requireAdmin();

$msg = '';

if (isset($_GET['approve'])) {
    $id  = (int)$_GET['approve'];
    $last = db()->query("SELECT member_id FROM members WHERE member_id IS NOT NULL ORDER BY id DESC LIMIT 1")->fetch_row();
    $num  = $last ? ((int)preg_replace('/[^0-9]/', '', ($last[0] ?? '0')) + 1) : 1;
    $mid  = 'JP' . str_pad($num, 4, '0', STR_PAD_LEFT);
    $join = date('Y-m-d');
    $rfee = (int)($_POST['reg_fee'] ?? regFee());

    db()->query("UPDATE members SET status='active', member_id='$mid', join_date='$join', reg_fee_paid=1 WHERE id=$id");
    $m = db()->query("SELECT name FROM members WHERE id=$id")->fetch_assoc();
    $mname = escape($m['name'] ?? '');
    if ($rfee > 0) {
        db()->query("INSERT INTO finance (date, type, category, description, amount, member_id)
                     VALUES ('$join','income','Registration','নিবন্ধন ফি — $mname ($mid)',$rfee,$id)");
    }
    $msg = "✅ অনুমোদন হয়েছে! সদস্য আইডি: <strong>$mid</strong>" . ($rfee > 0 ? " | ফি গ্রহণ: ৳$rfee" : '');
}

if (isset($_GET['reject']) && allowDelete()) {
    $id = (int)$_GET['reject'];
    db()->query("DELETE FROM members WHERE id=$id AND status='pending'");
    header('Location: approve_member.php');
    exit;
}

$pending = db()->query("SELECT * FROM members WHERE status='pending' ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>সদস্যপদ অনুমোদন — <?= htmlspecialchars(libName()) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>সদস্যপদ অনুমোদন</h1>
  </div>
  <div class="content">
    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

    <div class="card">
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>নাম</th>
              <th>ফোন</th>
              <th>ঠিকানা</th>
              <th>নিবন্ধনের তারিখ</th>
              <th>নিবন্ধন ফি</th>
              <th>অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($pending && $pending->num_rows > 0):
            while ($m = $pending->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
              <td><?= htmlspecialchars($m['phone'] ?? '—') ?></td>
              <td><?= htmlspecialchars($m['address'] ?? '—') ?></td>
              <td><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
              <td>
                <!-- Custom fee input per approval -->
                <form method="POST" action="?approve=<?= $m['id'] ?>" style="display:flex;gap:6px;align-items:center">
                  <input type="number" name="reg_fee" value="<?= regFee() ?>" min="0" class="form-control" style="width:90px">
                  <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('অনুমোদন করবেন?')">
                    <span class="material-icons">check</span> অনুমোদন
                  </button>
                </form>
              </td>
              <td>
                <?php if (allowDelete()): ?>
                <a href="?reject=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('বাতিল করবেন?')">
                  <span class="material-icons">close</span>
                </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">অনুমোদনের অপেক্ষায় কোনো সদস্য নেই।</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
