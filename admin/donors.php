<?php
require_once '../includes/config.php';
requireAdmin();
if (isset($_GET['delete']) && allowDelete()) {
    $did = (int)$_GET['delete'];
    db()->query("DELETE FROM donations WHERE donor_id=$did");
    db()->query("DELETE FROM finance WHERE member_id=$did AND category IN ('Donation','Book Donation')");
    db()->query("DELETE FROM borrows WHERE member_id=$did");
    db()->query("DELETE FROM monthly_fees WHERE member_id=$did");
    db()->query("DELETE FROM members WHERE id=$did AND is_donor=1");
    header('Location: donors.php'); exit;
}
$s = escape($_GET['s'] ?? '');
$w = "WHERE m.is_donor=1";
if ($s) $w .= " AND (m.name LIKE '%$s%' OR m.phone LIKE '%$s%' OR m.member_id LIKE '%$s%')";

$donors = db()->query("
    SELECT m.*,
        COALESCE(SUM(CASE WHEN d.type='money' THEN d.amount ELSE 0 END),0) as money_total,
        COALESCE(SUM(CASE WHEN d.type='book' THEN d.book_count ELSE 0 END),0) as book_total,
        COALESCE(SUM(CASE WHEN d.type='other' THEN d.amount ELSE 0 END),0) as other_total,
        COALESCE(SUM(d.amount),0) as grand_total
    FROM members m LEFT JOIN donations d ON d.donor_id=m.id
    $w GROUP BY m.id ORDER BY m.created_at DESC
");
$total_donors  = db()->query("SELECT COUNT(*) FROM members WHERE is_donor=1")->fetch_row()[0];
$total_money   = db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE type='money'")->fetch_row()[0];
$total_books   = db()->query("SELECT COALESCE(SUM(book_count),0) FROM donations WHERE type='book'")->fetch_row()[0];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দাতা তালিকা</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>দাতা তালিকা</h1>
    <a href="add_donor.php" class="btn btn-primary btn-sm"><span class="material-icons">favorite</span> <span class="hide-xs">নতুন দাতা</span></a>
  </div>
  <div class="content">
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:500px">
      <div class="stat-card"><div class="stat-icon orange"><span class="material-icons">groups</span></div>
        <div class="stat-info"><h3><?= $total_donors ?></h3><p>মোট দাতা</p></div></div>
      <div class="stat-card"><div class="stat-icon green"><span class="material-icons">payments</span></div>
        <div class="stat-info"><h3>৳<?= number_format($total_money) ?></h3><p>মোট অর্থ</p></div></div>
      <div class="stat-card"><div class="stat-icon blue"><span class="material-icons">menu_book</span></div>
        <div class="stat-info"><h3><?= $total_books ?>টি</h3><p>মোট বই</p></div></div>
    </div>

    <form method="GET" class="search-bar">
      <input type="text" name="s" class="form-control" placeholder="নাম, আইডি, ফোন..." value="<?= htmlspecialchars($s) ?>">
      <button type="submit" class="btn btn-primary"><span class="material-icons">search</span></button>
      <?php if ($s): ?><a href="donors.php" class="btn btn-outline">✕</a><?php endif; ?>
    </form>

    <div class="card">
      <div class="card-header"><h3><span class="material-icons">volunteer_activism</span> সকল দাতা (সাম্প্রতিক প্রথমে)</h3></div>
      <div class="table-responsive"><table style="min-width:700px">
        <thead><tr><th>ক্রম</th><th>নাম</th><th data-hide-mobile>ফোন</th><th data-hide-mobile>যোগদান</th>
          <th>অর্থ দান</th><th>বই দান</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
        <?php $i=1; if ($donors): while ($d = $donors->fetch_assoc()): ?>
          <tr>
            <td><strong><?= $i++ ?></strong></td>
            <td><strong><?= htmlspecialchars($d['name']) ?></strong>
              <?php if ($d['father_name']): ?><br><small style="color:var(--muted)"><?= htmlspecialchars($d['father_name']) ?></small><?php endif; ?></td>
            <td data-hide-mobile><?= htmlspecialchars($d['phone'] ?? '—') ?></td>
            <td data-hide-mobile><?= $d['join_date'] ?? '—' ?></td>
            <td><strong class="text-success">৳<?= number_format($d['money_total']) ?></strong></td>
            <td>
              <?php if ($d['book_total'] > 0): ?>
                <span class="badge badge-info"><?= $d['book_total'] ?>টি বই</span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <div class="action-btns">
                <a href="donor_detail.php?id=<?= $d['id'] ?>" class="btn btn-outline btn-sm" title="বিবরণ"><span class="material-icons">visibility</span></a>
                <a href="donor_certificate.php?id=<?= $d['id'] ?>" class="btn btn-outline btn-sm" title="সনদ"><span class="material-icons">workspace_premium</span></a>
                <a href="donor_statement.php?id=<?= $d['id'] ?>" class="btn btn-outline btn-sm" title="বিবরণী"><span class="material-icons">receipt_long</span></a>
                <a href="add_donation.php?donor_id=<?= $d['id'] ?>" class="btn btn-success btn-sm" title="দান যোগ"><span class="material-icons">add</span></a>
                <?php if (allowDelete()): ?><a href="?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('মুছবেন?')"><span class="material-icons">delete</span></a><?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="7"><div class="empty-state"><span class="material-icons">volunteer_activism</span><p>কোনো দাতা নেই</p></div></td></tr>
        <?php endif; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</div>
</body></html>
