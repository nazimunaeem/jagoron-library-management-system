<?php
require_once 'includes/config.php';
$donors = db()->query("
    SELECT m.name, m.member_id, m.join_date, m.address,
        COALESCE(SUM(CASE WHEN d.type='money' THEN d.amount ELSE 0 END),0) as money_total,
        COALESCE(SUM(CASE WHEN d.type='book' THEN d.book_count ELSE 0 END),0) as book_total
    FROM members m LEFT JOIN donations d ON d.donor_id=m.id
    WHERE m.is_donor=1 GROUP BY m.id ORDER BY money_total DESC, m.join_date ASC
");
$lib_name     = libName();
$total_money  = db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE type='money'")->fetch_row()[0];
$total_books  = db()->query("SELECT COALESCE(SUM(book_count),0) FROM donations WHERE type='book'")->fetch_row()[0];
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দাতা তালিকা — <?= htmlspecialchars($lib_name) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style>
.donor-card{display:flex;align-items:center;gap:14px;padding:14px 16px;border-bottom:1px solid var(--border);transition:background 0.15s}
.donor-card:hover{background:#f7fbf7}.donor-card:last-child{border-bottom:none}
.donor-rank{min-width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0}
.donor-rank.top1{background:#B8860B}.donor-rank.top2{background:#888}.donor-rank.top3{background:#8B4513}
.donor-info{flex:1}
.donor-info h4{font-size:0.9rem;font-weight:600;margin-bottom:2px}
.donor-info p{font-size:0.75rem;color:var(--muted)}
.donor-amounts{text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:3px}
.pub-container{max-width:900px;margin:0 auto;padding:24px 14px}
.page-hero{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;padding:32px 20px;text-align:center}
.page-hero h1{font-family:'Merriweather',serif;font-size:1.5rem;color:var(--accent);margin-bottom:6px}
.hero-stats-row{display:flex;gap:28px;justify-content:center;margin-top:12px;flex-wrap:wrap}
.hero-stat2 strong{display:block;font-size:1.3rem;font-weight:700;color:var(--accent)}
.hero-stat2 span{font-size:0.75rem;color:rgba(255,255,255,0.7)}
</style>
</head><body>
<?php include 'includes/pub_nav.php'; ?>
<div class="page-hero">
  <h1>সম্মানিত দাতা সদস্য</h1>
  <p style="color:rgba(255,255,255,0.7);font-size:0.85rem">তাঁদের মহৎ অবদানে পাঠাগার সমৃদ্ধ হচ্ছে</p>
  <div class="hero-stats-row">
    <div class="hero-stat2"><strong>৳<?= number_format($total_money) ?></strong><span>মোট অর্থ দান</span></div>
    <div class="hero-stat2"><strong><?= $total_books ?>টি</strong><span>মোট বই দান</span></div>
  </div>
</div>
<div class="pub-container">
  <div class="card">
    <?php $i = 1; if ($donors): while ($d = $donors->fetch_assoc()):
      $cls = $i==1?'top1':($i==2?'top2':($i==3?'top3':''));
    ?>
    <div class="donor-card">
      <div class="donor-rank <?= $cls ?>"><?= $i++ ?></div>
      <div class="donor-info">
        <h4><?= htmlspecialchars($d['name']) ?></h4>
        <p><?= htmlspecialchars(mb_substr($d['address'] ?? '—', 0, 40)) ?> &nbsp;·&nbsp; <?= $d['join_date'] ?? '—' ?></p>
      </div>
      <div class="donor-amounts">
        <?php if ($d['money_total'] > 0): ?>
          <span class="badge badge-success">৳<?= number_format($d['money_total']) ?> অর্থ</span>
        <?php endif; ?>
        <?php if ($d['book_total'] > 0): ?>
          <span class="badge badge-info"><?= $d['book_total'] ?>টি বই</span>
        <?php endif; ?>
        <?php if ($d['money_total'] == 0 && $d['book_total'] == 0): ?>
          <span class="badge badge-muted">দাতা সদস্য</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state"><span class="material-icons">volunteer_activism</span><p>কোনো দাতার তথ্য নেই</p></div>
    <?php endif; ?>
  </div>
</div>
<footer style="background:#0f2d1e;color:rgba(255,255,255,0.5);text-align:center;padding:12px;font-size:0.78rem;margin-top:20px">
  <?= htmlspecialchars($lib_name) ?> — <?= htmlspecialchars(libAddress()) ?>
</footer>
</body></html>
