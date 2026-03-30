<?php
require_once '../includes/config.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$d  = db()->query("SELECT m.*,
    COALESCE(SUM(CASE WHEN dn.type='money' THEN dn.amount ELSE 0 END),0) as money_total,
    COALESCE(SUM(CASE WHEN dn.type='book' THEN dn.book_count ELSE 0 END),0) as book_total,
    COALESCE(SUM(dn.amount),0) as grand_total
    FROM members m LEFT JOIN donations dn ON dn.donor_id=m.id
    WHERE m.id=$id AND m.is_donor=1 GROUP BY m.id")->fetch_assoc();
if (!$d) { header('Location: donors.php'); exit; }
$donations = db()->query("SELECT * FROM donations WHERE donor_id=$id ORDER BY date DESC");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দাতার বিবরণ</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>দাতার বিবরণ — <?= htmlspecialchars($d['name']) ?></h1>
    <div style="display:flex;gap:6px">
      <a href="add_donation.php?donor_id=<?= $id ?>" class="btn btn-success btn-sm"><span class="material-icons">add</span> দান যোগ</a>
      <a href="donor_certificate.php?id=<?= $id ?>" class="btn btn-outline btn-sm"><span class="material-icons">workspace_premium</span> সনদ</a>
      <a href="donor_statement.php?id=<?= $id ?>" class="btn btn-outline btn-sm"><span class="material-icons">receipt_long</span> বিবরণী</a>
      <a href="donors.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
    </div>
  </div>
  <div class="content">
    <div class="card"><div class="card-body">
      <div class="form-row">
        <div>
          <p><strong>নাম:</strong> <?= htmlspecialchars($d['name']) ?></p>
          <p><strong>সদস্য আইডি:</strong> <?= htmlspecialchars($d['member_id'] ?? '—') ?></p>
          <p><strong>পিতার নাম:</strong> <?= htmlspecialchars($d['father_name'] ?? '—') ?></p>
          <p><strong>যোগদান:</strong> <?= $d['join_date'] ?? '—' ?></p>
        </div>
        <div>
          <p><strong>ফোন:</strong> <?= htmlspecialchars($d['phone'] ?? '—') ?></p>
          <p><strong>ঠিকানা:</strong> <?= htmlspecialchars($d['address'] ?? '—') ?></p>
          <p><strong>মোট অর্থ দান:</strong> <span style="font-size:1.1rem;font-weight:700;color:var(--success)">৳<?= number_format($d['money_total']) ?></span></p>
          <p><strong>মোট বই দান:</strong> <span style="font-size:1.1rem;font-weight:700;color:#1565c0"><?= $d['book_total'] ?>টি বই</span></p>
        </div>
      </div>
    </div></div>

    <div class="card">
      <div class="card-header"><h3><span class="material-icons">history</span> দানের ইতিহাস</h3></div>
      <div class="table-responsive"><table style="min-width:500px">
        <thead><tr><th>তারিখ</th><th>ধরন</th><th>পরিমাণ / বই</th><th>বিবরণ</th></tr></thead>
        <tbody>
        <?php while ($r = $donations->fetch_assoc()): ?>
          <tr>
            <td><?= $r['date'] ?></td>
            <td><?php if ($r['type']==='money'): ?><span class="badge badge-success">অর্থ</span>
              <?php elseif ($r['type']==='book'): ?><span class="badge badge-info">বই</span>
              <?php else: ?><span class="badge badge-muted">অন্যান্য</span><?php endif; ?></td>
            <td>
              <?php if ($r['type']==='money'): ?>৳<?= number_format($r['amount']) ?>
              <?php elseif ($r['type']==='book'): ?><?= ($r['book_count']??0) ?>টি বই<?= $r['amount']>0?' (≈৳'.number_format($r['amount']).')':'' ?>
              <?php else: ?>৳<?= number_format($r['amount']) ?><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($r['description'] ?? '—') ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</div></body></html>
