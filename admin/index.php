<?php
require_once '../includes/config.php';
requireAdmin();

$total_books    = db()->query("SELECT COALESCE(SUM(copies),0) FROM books")->fetch_row()[0] ?? 0;
$total_members  = db()->query("SELECT COUNT(*) FROM members WHERE status='active'")->fetch_row()[0] ?? 0;
$pending        = db()->query("SELECT COUNT(*) FROM members WHERE status='pending'")->fetch_row()[0] ?? 0;
$total_borrowed = db()->query("SELECT COUNT(*) FROM borrows WHERE status='borrowed'")->fetch_row()[0] ?? 0;
$overdue_count  = db()->query("SELECT COUNT(*) FROM borrows WHERE status='borrowed' AND due_date < CURDATE()")->fetch_row()[0] ?? 0;
$income         = db()->query("SELECT COALESCE(SUM(amount),0) FROM finance WHERE type='income'")->fetch_row()[0] ?? 0;
$expense        = db()->query("SELECT COALESCE(SUM(amount),0) FROM finance WHERE type='expense'")->fetch_row()[0] ?? 0;
$balance        = $income - $expense;

// Monthly fee this month
$ym = date('Y'); $mm = date('m');
$month_paid = db()->query("SELECT COUNT(*) FROM monthly_fees WHERE year='$ym' AND month='$mm'")->fetch_row()[0] ?? 0;
$month_due  = max(0, $total_members - $month_paid);

// Top lists — safe with num_rows check
$r_top_books   = db()->query("SELECT bk.title, COUNT(*) as cnt FROM borrows br JOIN books bk ON br.book_id=bk.id GROUP BY br.book_id ORDER BY cnt DESC LIMIT 5");
$r_top_members = db()->query("SELECT m.name, m.member_id as mid, COUNT(*) as cnt FROM borrows br JOIN members m ON br.member_id=m.id GROUP BY br.member_id ORDER BY cnt DESC LIMIT 5");
$r_top_donors  = db()->query("SELECT m.name, COALESCE(SUM(CASE WHEN d.type='money' THEN d.amount ELSE 0 END),0) as money_total, COALESCE(SUM(CASE WHEN d.type='book' THEN d.book_count ELSE 0 END),0) as book_total FROM members m LEFT JOIN donations d ON d.donor_id=m.id WHERE m.is_donor=1 GROUP BY m.id ORDER BY money_total DESC LIMIT 5");

// Recent borrows
$r_recent = db()->query("SELECT br.due_date, br.status, bk.title, m.name FROM borrows br JOIN books bk ON br.book_id=bk.id JOIN members m ON br.member_id=m.id ORDER BY br.created_at DESC LIMIT 6");

// Book list with issued_to
$r_books = db()->query("SELECT * FROM books ORDER BY title LIMIT 10");
$issued_map = [];
$ir = db()->query("SELECT br.book_id, m.name FROM borrows br JOIN members m ON br.member_id=m.id WHERE br.status='borrowed'");
if ($ir) while ($row = $ir->fetch_assoc()) $issued_map[$row['book_id']] = $row['name'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ড্যাশবোর্ড — <?= htmlspecialchars(libName()) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>ড্যাশবোর্ড</h1>
    <div class="topbar-right">
      <span style="font-size:0.82rem;color:var(--muted)">👤 <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
      <a href="logout.php" class="btn-logout">বের হন</a>
    </div>
  </div>
  <div class="content">

    <?php if ($pending > 0): ?>
    <div class="alert alert-warning">
      <span class="material-icons" style="font-size:1rem">info</span>
      <strong><?= $pending ?>টি</strong> নতুন নিবন্ধন অনুমোদনের অপেক্ষায়।
      <a href="approve_member.php" style="color:var(--warning);font-weight:600;margin-left:8px">দেখুন →</a>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><span class="material-icons">menu_book</span></div>
        <div class="stat-info"><h3><?= $total_books ?></h3><p>মোট বই</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><span class="material-icons">groups</span></div>
        <div class="stat-info"><h3><?= $total_members ?></h3><p>সক্রিয় সদস্য</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><span class="material-icons">outbox</span></div>
        <div class="stat-info"><h3><?= $total_borrowed ?></h3><p>ইস্যুকৃত বই</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><span class="material-icons">warning</span></div>
        <div class="stat-info"><h3><?= $overdue_count ?></h3><p>মেয়াদোত্তীর্ণ</p></div>
      </div>
    </div>

    <!-- Finance summary -->
    <div class="fin-grid">
      <div class="fin-card"><h3 class="text-success">৳<?= number_format($income) ?></h3><p>মোট আয়</p></div>
      <div class="fin-card"><h3 class="text-danger">৳<?= number_format($expense) ?></h3><p>মোট ব্যয়</p></div>
      <div class="fin-card"><h3 class="<?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">৳<?= number_format($balance) ?></h3><p>ব্যালেন্স</p></div>
    </div>

    <!-- Top 3 panels -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-bottom:20px">

      <div class="card">
        <div class="card-header"><h3><span class="material-icons">trending_up</span> সর্বাধিক ইস্যুকৃত</h3><a href="books.php" class="btn btn-outline btn-sm">সব</a></div>
        <table>
          <?php if ($r_top_books && $r_top_books->num_rows > 0):
            while ($b = $r_top_books->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($b['title']) ?></td><td><span class="badge badge-info"><?= $b['cnt'] ?>×</span></td></tr>
          <?php endwhile; else: ?>
            <tr><td style="color:var(--muted)">তথ্য নেই</td></tr>
          <?php endif; ?>
        </table>
      </div>

      <div class="card">
        <div class="card-header"><h3><span class="material-icons">star</span> শীর্ষ সদস্য</h3><a href="members.php" class="btn btn-outline btn-sm">সব</a></div>
        <table>
          <?php if ($r_top_members && $r_top_members->num_rows > 0):
            while ($m = $r_top_members->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($m['name']) ?><br><small class="badge badge-muted"><?= $m['mid'] ?></small></td><td><span class="badge badge-success"><?= $m['cnt'] ?></span></td></tr>
          <?php endwhile; else: ?>
            <tr><td style="color:var(--muted)">তথ্য নেই</td></tr>
          <?php endif; ?>
        </table>
      </div>

      <div class="card">
        <div class="card-header"><h3><span class="material-icons">volunteer_activism</span> শীর্ষ দাতা</h3><a href="donors.php" class="btn btn-outline btn-sm">সব</a></div>
        <table>
          <?php if ($r_top_donors && $r_top_donors->num_rows > 0):
            $i = 1; while ($d = $r_top_donors->fetch_assoc()): ?>
            <tr><td><?= $i++ ?>. <?= htmlspecialchars($d['name']) ?></td><td style="white-space:nowrap"><span class="badge badge-warning">৳<?= number_format($d['money_total']) ?></span><?php if($d['book_total']>0):?> <span class="badge badge-info"><?=$d['book_total']?>বই</span><?php endif;?></td></tr>
          <?php endwhile; else: ?>
            <tr><td style="color:var(--muted)">তথ্য নেই</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Book list with issued status -->
    <div class="card">
      <div class="card-header"><h3><span class="material-icons">library_books</span> বইয়ের অবস্থা</h3><a href="books.php" class="btn btn-outline btn-sm">সব দেখুন</a></div>
      <div class="table-responsive">
        <table>
          <thead><tr><th>বইয়ের নাম</th><th>লেখক</th><th>কপি</th><th>অবস্থা</th></tr></thead>
          <tbody>
          <?php if ($r_books && $r_books->num_rows > 0):
            while ($b = $r_books->fetch_assoc()):
              $issued_to = $issued_map[$b['id']] ?? null; ?>
            <tr>
              <td><?= htmlspecialchars($b['title']) ?></td>
              <td><?= htmlspecialchars($b['author']) ?></td>
              <td><?= $b['copies'] ?></td>
              <td>
                <?php if ($b['available'] > 0): ?>
                  <span class="badge badge-success">পাওয়া যাচ্ছে (<?= $b['available'] ?>)</span>
                <?php else: ?>
                  <span class="badge badge-danger">নেই<?= $issued_to ? ' — '.$issued_to : '' ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="4" style="text-align:center;color:var(--muted)">কোনো বই যোগ করা হয়নি।</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
</body>
</html>
