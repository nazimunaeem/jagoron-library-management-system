<?php
require_once '../includes/config.php';
requireMember();
$mid = (int)$_SESSION['member_id'];
// Only allow if this member is a donor
$d = db()->query("SELECT m.*,
    COALESCE(SUM(CASE WHEN dn.type='money' THEN dn.amount ELSE 0 END),0) as money_total,
    COALESCE(SUM(CASE WHEN dn.type='book' THEN dn.book_count ELSE 0 END),0) as book_total,
    COALESCE(SUM(dn.amount),0) as grand_total
    FROM members m LEFT JOIN donations dn ON dn.donor_id=m.id
    WHERE m.id=$mid AND m.is_donor=1 GROUP BY m.id")->fetch_assoc();
if (!$d) { header('Location: dashboard.php'); exit; }
$donations = db()->query("SELECT * FROM donations WHERE donor_id=$mid ORDER BY date ASC");
$lib_name = libName(); $lib_tagline = libTagline(); $lib_address = libAddress(); $lib_logo = libLogo();
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দাতা সনদ</title>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Hind Siliguri',sans-serif;background:#eee;padding:20px;color:#111}
.no-print{text-align:center;margin-bottom:20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.btn{padding:8px 18px;border-radius:6px;cursor:pointer;border:none;font-family:inherit;font-size:0.9rem;text-decoration:none;display:inline-flex;gap:5px;font-weight:500}
.btn-primary{background:#111;color:#fff}.btn-outline{background:#fff;border:1px solid #ccc;color:#333}
.cert{max-width:680px;margin:0 auto;background:#fff;border:3px double #333;padding:44px;position:relative;overflow:hidden}
.cert::before{content:'';position:absolute;top:8px;left:8px;right:8px;bottom:8px;border:1px solid #ccc;pointer-events:none}
.wm{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;z-index:0}
.wm img{width:180px;height:180px;object-fit:contain;opacity:0.06;filter:grayscale(100%)}
.wm-text{font-size:4rem;font-weight:700;color:#000;opacity:0.05;transform:rotate(-30deg)}
.cert>*:not(.wm){position:relative;z-index:1}
.cert-top{text-align:center;border-bottom:2px solid #111;padding-bottom:16px;margin-bottom:18px}
.cert-top img{height:50px;filter:grayscale(100%);margin-bottom:6px}
.cert-top h1{font-family:'Merriweather',serif;font-size:1.25rem;margin-bottom:3px}
.cert-top .tl{font-size:0.82rem;color:#444}.cert-top .ad{font-size:0.75rem;color:#555;margin-top:2px}
.cert-title{text-align:center;font-size:1rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;border-top:1px solid #ccc;border-bottom:1px solid #ccc;padding:7px 0;margin:16px 0}
.cert-body{line-height:2.3;font-size:0.9rem;text-align:justify;margin-bottom:16px}
.cert-name{font-size:1.05rem;font-weight:700;border-bottom:1px dashed #444;display:inline-block;padding:0 8px;margin:0 3px}
.contrib-box{background:#f9f9f9;border:1px solid #ddd;border-radius:4px;padding:12px 16px;margin:14px 0;display:flex;gap:24px;justify-content:center;flex-wrap:wrap}
.contrib-item{text-align:center}
.contrib-item strong{display:block;font-size:1.1rem;font-weight:700}
.contrib-item span{font-size:0.75rem;color:#555}
.cert-footer{margin-top:32px;display:flex;justify-content:space-between}
.sign{text-align:center}.sign-line{border-top:1px solid #333;width:140px;padding-top:5px;font-size:0.75rem;color:#555;margin:0 auto}
@media print{.no-print{display:none!important}body{background:#fff!important;padding:0!important}
.cert{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head><body>
<div class="no-print">
  <button onclick="window.print()" class="btn btn-primary">🖨️ সনদ প্রিন্ট / ডাউনলোড</button>
  <a href="donor_statement.php" class="btn btn-outline">📄 বিবরণী</a>
  <a href="dashboard.php" class="btn btn-outline">← ড্যাশবোর্ড</a>
</div>
<div class="cert">
  <div class="wm"><?php if($lib_logo):?><img src="<?=htmlspecialchars($lib_logo)?>"><?php else:?><div class="wm-text">JP</div><?php endif;?></div>
  <div class="cert-top">
    <?php if($lib_logo):?><img src="<?=htmlspecialchars($lib_logo)?>" alt="logo"><br><?php endif;?>
    <h1><?=htmlspecialchars($lib_name)?></h1>
    <div class="tl"><?=htmlspecialchars($lib_tagline)?></div>
    <div class="ad"><?=htmlspecialchars($lib_address)?></div>
  </div>
  <div class="cert-title">দাতা সম্মাননা সনদ</div>
  <div class="cert-body">
    <p>এই মর্মে সনদ প্রদান করা যাচ্ছে যে,</p>
    <p style="text-align:center;margin:14px 0"><span class="cert-name"><?=htmlspecialchars($d['name'])?></span></p>
    <?php if($d['father_name']):?><p>পিতা: <strong><?=htmlspecialchars($d['father_name'])?></strong></p><?php endif;?>
    <?php if($d['address']):?><p>ঠিকানা: <?=htmlspecialchars($d['address'])?></p><?php endif;?>
    <p style="margin-top:14px">সুমহান মহানুভবতায় <strong><?=htmlspecialchars($lib_name)?></strong>-এ নিম্নরূপ দান করে পাঠাগারের উন্নয়নে অনন্য ভূমিকা রেখেছেন:</p>
    <div class="contrib-box">
      <?php if($d['money_total']>0):?>
      <div class="contrib-item"><strong>৳<?=number_format($d['money_total'])?></strong><span>নগদ অর্থ দান</span></div>
      <?php endif;?>
      <?php if($d['book_total']>0):?>
      <div class="contrib-item"><strong><?=$d['book_total']?>টি</strong><span>বই দান</span></div>
      <?php endif;?>
    </div>
    <p>পাঠাগার কর্তৃপক্ষ তাঁর প্রতি আন্তরিক কৃতজ্ঞতা ও ধন্যবাদ জ্ঞাপন করছে।</p>
    <p style="margin-top:12px;font-size:0.82rem;color:#555">তারিখ: <?=date('d/m/Y')?></p>
  </div>
  <div class="cert-footer">
    <div class="sign"><div class="sign-line">পাঠাগার সভাপতি</div></div>
    <div class="sign"><div class="sign-line">সম্পাদক</div></div>
    <div class="sign"><div class="sign-line">কোষাধ্যক্ষ</div></div>
  </div>
</div>
</body></html>
