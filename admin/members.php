<?php
require_once '../includes/config.php';
requireAdmin();
if (isset($_GET['delete']) && allowDelete()) {
    $mid = (int)$_GET['delete'];
    $active = db()->query("SELECT book_id FROM borrows WHERE member_id=$mid AND status='borrowed'");
    if ($active) while ($b = $active->fetch_assoc()) {
        db()->query("UPDATE books SET available=available+1 WHERE id=".(int)$b['book_id']);
    }
    db()->query("DELETE FROM borrows WHERE member_id=$mid");
    db()->query("DELETE FROM monthly_fees WHERE member_id=$mid");
    db()->query("DELETE FROM donations WHERE donor_id=$mid");
    db()->query("DELETE FROM finance WHERE member_id=$mid");
    db()->query("DELETE FROM members WHERE id=$mid");
    header('Location: members.php'); exit;
}
$s = escape($_GET['s'] ?? '');
$status = escape($_GET['status'] ?? '');
$w = 'WHERE 1=1';
if ($s) $w .= " AND (name LIKE '%$s%' OR member_id LIKE '%$s%' OR phone LIKE '%$s%')";
if ($status) $w .= " AND status='$status'";
// Sort: join_date ASC (oldest members first), NULL at end
$members = db()->query("SELECT * FROM members $w ORDER BY created_at DESC");
$total = db()->query("SELECT COUNT(*) FROM members WHERE status='active' AND is_donor=0")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>সদস্যতালিকা</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>সদস্যতালিকা <span style="font-size:0.78rem;color:var(--muted);font-weight:400">(পুরনো → নতুন)</span></h1>
    <a href="add_member.php" class="btn btn-primary btn-sm"><span class="material-icons">person_add</span> <span class="hide-xs">নতুন সদস্য</span></a>
  </div>
  <div class="content">
    <form method="GET" class="search-bar">
      <input type="text" name="s" class="form-control" placeholder="নাম, আইডি, ফোন..." value="<?= htmlspecialchars($s) ?>">
      <select name="status" class="form-control" style="width:150px" onchange="this.form.submit()">
        <option value="">সব অবস্থা</option>
        <option value="active" <?= $status=='active'?'selected':'' ?>>সক্রিয়</option>
        <option value="pending" <?= $status=='pending'?'selected':'' ?>>অনুমোদন বাকি</option>
        <option value="suspended" <?= $status=='suspended'?'selected':'' ?>>স্থগিত</option>
      </select>
      <button type="submit" class="btn btn-primary"><span class="material-icons">search</span></button>
      <?php if ($s||$status): ?><a href="members.php" class="btn btn-outline">✕</a><?php endif; ?>
    </form>

    <div class="card">
      <div class="card-header">
        <h3><span class="material-icons">groups</span> সকল সদস্য</h3>
        <span style="font-size:0.8rem;color:var(--muted)">সক্রিয়: <?= $total ?> জন</span>
      </div>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>আইডি</th>
              <th>নাম</th>
              <th data-hide-mobile>পিতার নাম</th>
              <th data-hide-mobile>ফোন</th>
              <th>যোগদান</th>
              <th>ধরন</th>
              <th>অবস্থা</th>
              <th>অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($members): while ($m = $members->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($m['member_id'] ?? '—') ?></strong></td>
              <td>
                <?= htmlspecialchars($m['name']) ?>
                <?php if ($m['is_donor']): ?><br><span class="badge badge-warning">দাতা</span><?php endif; ?>
              </td>
              <td data-hide-mobile><?= htmlspecialchars($m['father_name'] ?? '—') ?></td>
              <td data-hide-mobile><?= htmlspecialchars($m['phone'] ?? '—') ?></td>
              <td style="white-space:nowrap"><?= $m['join_date'] ?? '—' ?></td>
              <td>
                <?php $types=['regular'=>'সাধারণ','student'=>'শিক্ষার্থী','senior'=>'প্রবীণ','donor'=>'দাতা'];
                echo $types[$m['membership_type']] ?? $m['membership_type']; ?>
              </td>
              <td>
                <?php if ($m['status']==='active'): ?><span class="badge badge-success">সক্রিয়</span>
                <?php elseif ($m['status']==='pending'): ?><span class="badge badge-warning">অপেক্ষায়</span>
                <?php else: ?><span class="badge badge-danger">স্থগিত</span><?php endif; ?>
              </td>
              <td>
                <div class="action-btns">
                  <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm" title="সম্পাদনা"><span class="material-icons">edit</span></a>
                  <?php if ($m['member_id']): ?>
                  <a href="member_card.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm" title="আইডি কার্ড"><span class="material-icons">badge</span></a>
                  <?php endif; ?>
                  <?php if (allowDelete()): ?>
                  <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('মুছবেন?')"><span class="material-icons">delete</span></a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile;
          else: ?>
            <tr><td colspan="8"><div class="empty-state"><span class="material-icons">person_off</span><p>কোনো সদস্য নেই</p></div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
