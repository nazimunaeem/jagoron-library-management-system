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
$donations = db()->query("SELECT * FROM donations WHERE donor_id=$id ORDER BY date ASC");
$lib_name = libName(); $lib_tagline = libTagline(); $lib_address = libAddress(); $lib_logo = libLogo();
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দান বিবরণী — <?= htmlspecialchars($d['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Hind Siliguri',sans-serif;background:#eee;padding:20px;color:#111}
.no-print{text-align:center;margin-bottom:20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.btn{padding:8px 18px;border-radius:6px;cursor:pointer;border:none;font-family:inherit;font-size:0.9rem;text-decoration:none;display:inline-flex;gap:5px;font-weight:500}
.btn-primary{background:#111;color:#fff}.btn-outline{background:#fff;border:1px solid #ccc;color:#333}
.stmt{max-width:700px;margin:0 auto;background:#fff;border:1px solid #333;padding:32px}
.stmt-top{text-align:center;border-bottom:2px solid #111;padding-bottom:14px;margin-bottom:16px}
.stmt-top img{height:45px;filter:grayscale(100%);margin-bottom:5px}
.stmt-top h1{font-family:'Merriweather',serif;font-size:1.1rem;margin-bottom:2px}
.stmt-top .tl{font-size:0.78rem;color:#444}.stmt-top .ad{font-size:0.72rem;color:#555;margin-top:2px}
.stmt-title{font-size:0.95rem;font-weight:700;text-align:center;margin:14px 0 10px;letter-spacing:1px;border:1px solid #ddd;padding:7px;background:#f9f9f9}
.info-box{background:#f9f9f9;border:1px solid #ddd;padding:12px;margin-bottom:14px;font-size:0.85rem;line-height:1.9}
.summary-row{display:flex;gap:20px;margin-bottom:14px;flex-wrap:wrap}
.sum-item{text-align:center;background:#f0f0f0;border:1px solid #ddd;padding:10px 20px;border-radius:4px;flex:1;min-width:120px}
.sum-item strong{display:block;font-size:1.1rem;font-weight:700}
.sum-item span{font-size:0.72rem;color:#555}
table{width:100%;border-collapse:collapse;font-size:0.82rem;margin-bottom:12px}
th{background:#111;color:#fff;padding:7px 10px;text-align:left;font-size:0.75rem}
td{padding:7px 10px;border-bottom:1px solid #ddd}
tfoot td{font-weight:700;background:#f0f0f0;border-top:2px solid #111}
.stmt-footer{margin-top:20px;font-size:0.78rem;color:#666;text-align:right}
@media print{.no-print{display:none!important}body{background:#fff!important;padding:0!important}}
</style>
</head><body>
<div class="no-print">
  <button onclick="window.print()" class="btn btn-primary">🖨️ বিবরণী প্রিন্ট করুন</button>
  <a href="donor_certificate.php?id=<?= $id ?>" class="btn btn-outline">📜 সনদ</a>
  <a href="donors.php" class="btn btn-outline">← ফিরে যান</a>
</div>
<div class="stmt">
  <div class="stmt-top">
    <?php if ($lib_logo): ?><img src="<?= htmlspecialchars($lib_logo) ?>" alt="logo"><br><?php endif; ?>
    <h1><?= htmlspecialchars($lib_name) ?></h1>
    <div class="tl"><?= htmlspecialchars($lib_tagline) ?></div>
    <div class="ad"><?= htmlspecialchars($lib_address) ?></div>
  </div>
  <div class="stmt-title">দান বিবরণী — Donation Statement</div>
  <div class="info-box">
    <div><strong>নাম:</strong> <?= htmlspecialchars($d['name']) ?></div>
    <?php if ($d['father_name']): ?><div><strong>পিতা:</strong> <?= htmlspecialchars($d['father_name']) ?></div><?php endif; ?>
    <?php if ($d['address']): ?><div><strong>ঠিকানা:</strong> <?= htmlspecialchars($d['address']) ?></div><?php endif; ?>
    <?php if ($d['phone']): ?><div><strong>ফোন:</strong> <?= htmlspecialchars($d['phone']) ?></div><?php endif; ?>
    <div><strong>সদস্য আইডি:</strong> <?= htmlspecialchars($d['member_id'] ?? '—') ?></div>
    <div><strong>বিবরণীর তারিখ:</strong> <?= date('d/m/Y') ?></div>
  </div>

  <div class="summary-row">
    <div class="sum-item"><strong>৳<?= number_format($d['money_total']) ?></strong><span>মোট অর্থ দান</span></div>
    <div class="sum-item"><strong><?= $d['book_total'] ?>টি</strong><span>মোট বই দান</span></div>
    <?php if ($d['grand_total'] > 0): ?>
    <div class="sum-item"><strong>৳<?= number_format($d['grand_total']) ?></strong><span>সর্বমোট (আনুমানিক)</span></div>
    <?php endif; ?>
  </div>

  <table>
    <thead><tr><th>#</th><th>তারিখ</th><th>ধরন</th><th>পরিমাণ / বই</th><th>বিবরণ</th></tr></thead>
    <tbody>
    <?php $i = 1; while ($r = $donations->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= $r['date'] ?></td>
        <td><?= $r['type']==='money'?'অর্থ':($r['type']==='book'?'বই':'অন্যান্য') ?></td>
        <td>
          <?php if ($r['type']==='money'): ?>৳<?= number_format($r['amount']) ?>
          <?php elseif ($r['type']==='book'): ?>
            <?= ($r['book_count']??0) ?>টি বই<?= $r['amount']>0?' (≈৳'.number_format($r['amount']).')':'' ?>
          <?php else: ?>৳<?= number_format($r['amount']) ?><?php endif; ?>
        </td>
        <td><?= htmlspecialchars($r['description'] ?? '—') ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="3">মোট অর্থ দান</td><td>৳<?= number_format($d['money_total']) ?></td><td></td></tr>
      <tr><td colspan="3">মোট বই দান</td><td><?= $d['book_total'] ?>টি বই</td><td></td></tr>
    </tfoot>
  </table>
  <div class="stmt-footer">মুদ্রণের তারিখ: <?= date('d/m/Y H:i') ?></div>
</div>
</body></html>
