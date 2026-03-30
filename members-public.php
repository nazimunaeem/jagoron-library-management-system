<?php
require_once 'includes/config.php';
$members = db()->query("SELECT member_id, name, membership_type, join_date, is_donor FROM members WHERE status='active' ORDER BY CASE WHEN join_date IS NULL THEN 1 ELSE 0 END, join_date ASC, id ASC");
$total = db()->query("SELECT COUNT(*) FROM members WHERE status='active'")->fetch_row()[0];
$lib_name = libName();
$types = ['regular'=>'সাধারণ','student'=>'শিক্ষার্থী','senior'=>'প্রবীণ','donor'=>'দাতা'];
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>সদস্য তালিকা — <?= htmlspecialchars($lib_name) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style>
.pub-container{max-width:900px;margin:0 auto;padding:24px 14px}
.page-hero{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;padding:28px 20px;text-align:center}
.page-hero h1{font-family:'Merriweather',serif;font-size:1.4rem;color:var(--accent);margin-bottom:4px}
.member-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px}
.member-tile{background:#fff;border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;align-items:center;gap:10px;transition:box-shadow 0.2s}
.member-tile:hover{box-shadow:0 4px 16px rgba(0,0,0,0.1)}
.member-avatar{width:40px;height:40px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;flex-shrink:0}
.member-tile-info h4{font-size:0.85rem;font-weight:600;margin-bottom:2px}
.member-tile-info p{font-size:0.7rem;color:var(--muted)}
@media(max-width:480px){.member-grid{grid-template-columns:1fr 1fr}}
</style>
</head><body>
<?php include 'includes/pub_nav.php'; ?>
<div class="page-hero">
  <h1>সদস্য তালিকা</h1>
  <p style="color:rgba(255,255,255,0.7);font-size:0.85rem">মোট সক্রিয় সদস্য: <strong style="color:var(--accent)"><?= $total ?> জন</strong></p>
</div>
<div class="pub-container">
  <div class="member-grid">
  <?php if ($members): while ($m=$members->fetch_assoc()):
    $initial = mb_strtoupper(mb_substr($m['name'],0,1));
  ?>
    <div class="member-tile">
      <div class="member-avatar"><?= $initial ?></div>
      <div class="member-tile-info">
        <h4><?= htmlspecialchars($m['name']) ?></h4>
        <p><?= htmlspecialchars($m['member_id']??'—') ?></p>
        <p><?= $types[$m['membership_type']]??'সাধারণ' ?> <?= $m['is_donor']?'· <span style="color:#e65100">দাতা</span>':'' ?></p>
        <p style="color:var(--muted);font-size:0.68rem">যোগদান: <?= $m['join_date']??'—' ?></p>
      </div>
    </div>
  <?php endwhile; else: ?>
    <div class="empty-state"><span class="material-icons">person_off</span><p>কোনো সদস্য নেই</p></div>
  <?php endif; ?>
  </div>
</div>
<footer style="background:#0f2d1e;color:rgba(255,255,255,0.5);text-align:center;padding:12px;font-size:0.78rem;margin-top:24px">
  <?= htmlspecialchars($lib_name) ?> — <?= htmlspecialchars(libAddress()) ?>
</footer>
</body></html>
