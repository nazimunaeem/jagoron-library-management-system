<?php
require_once '../includes/config.php';
requireAdmin();

$lib_name    = libName();
$lib_tagline = libTagline();
$lib_address = libAddress();
$lib_logo    = libLogo();
$rules_page  = db()->query("SELECT content FROM pages WHERE slug='rules' LIMIT 1")->fetch_assoc();
$rules_text  = $rules_page['content'] ?? '<ul><li>একজন সদস্য একসাথে একটি বই নিতে পারবেন।</li><li>বই ইস্যুর মেয়াদ ১৫ দিন।</li><li>প্রতিদিন ২ টাকা জরিমানা।</li><li>মাসিক ফি নির্ধারিত তারিখে দিতে হবে।</li></ul>';

$members = null;
$from_id = $to_id = $count = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['from']) && isset($_GET['to']))) {
    $from_id = (int)($_POST['from_id'] ?? $_GET['from'] ?? 0);
    $to_id   = (int)($_POST['to_id'] ?? $_GET['to'] ?? 0);
    $mode    = escape($_POST['mode'] ?? $_GET['mode'] ?? 'range');

    if ($mode === 'range') {
        if ($from_id > 0 && $to_id >= $from_id) {
            // Range by numeric part of member_id
            $members = db()->query("SELECT * FROM members
                WHERE status='active'
                AND CAST(SUBSTRING(member_id, 3) AS UNSIGNED) >= $from_id
                AND CAST(SUBSTRING(member_id, 3) AS UNSIGNED) <= $to_id
                ORDER BY CAST(SUBSTRING(member_id, 3) AS UNSIGNED) ASC");
            $count = $members ? $members->num_rows : 0;
        } else {
            $error = 'শুরু ও শেষ নম্বর সঠিকভাবে দিন।';
        }
    } else {
        // All active members
        $members = db()->query("SELECT * FROM members WHERE status='active' AND member_id IS NOT NULL ORDER BY CAST(SUBSTRING(member_id,3) AS UNSIGNED) ASC");
        $count = $members ? $members->num_rows : 0;
    }
}

$print_mode = isset($_GET['print']) && $_GET['print'] === '1';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>বাল্ক কার্ড প্রিন্ট</title>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<?php if (!$print_mode): ?>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<?php endif; ?>
<style>
<?php if (!$print_mode): ?>
/* Admin view styles */
body{font-family:'Hind Siliguri',sans-serif}
.preview-grid{display:flex;flex-wrap:wrap;gap:12px;margin-top:16px}
.card-mini{border:1px solid #ddd;border-radius:6px;padding:8px 12px;font-size:0.75rem;background:#fff;width:200px}
.card-mini strong{display:block;font-size:0.82rem;margin-bottom:3px}
<?php endif; ?>

/* Card print styles - always included */
<?php include '../includes/card_styles.php'; ?>

/* Bulk print specific */
.bulk-page{page-break-after:always}
.bulk-page:last-child{page-break-after:avoid}

@media print {
  <?php if (!$print_mode): ?>
  .no-print,.main,.topbar,.sidebar,.sidebar-overlay{display:none!important}
  <?php endif; ?>
  .bulk-page{page-break-after:always;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8mm}
  .bulk-page:last-child{page-break-after:avoid}
  .id-card{width:288px!important;height:192px!important;box-shadow:none!important;border:1.5px solid #222!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .card-label{display:none}
  @page{margin:8mm;size:A4}
}
</style>
</head>
<body>

<?php if (!$print_mode): ?>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>বাল্ক আইডি কার্ড প্রিন্ট</h1>
    <?php if ($members && $count > 0): ?>
    <a href="bulk_cards.php?from=<?= $from_id ?>&to=<?= $to_id ?>&mode=<?= ($_GET['mode']??'range') ?>&print=1"
       target="_blank" class="btn btn-primary">
      <span class="material-icons">print</span> <?= $count ?>টি কার্ড প্রিন্ট করুন
    </a>
    <?php endif; ?>
  </div>
  <div class="content">
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><h3><span class="material-icons">badge</span> কার্ড প্রিন্টের পরিসর নির্বাচন করুন</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-row" style="margin-bottom:14px">
            <div class="form-group">
              <label class="form-label">প্রিন্টের ধরন</label>
              <select name="mode" id="modeSelect" class="form-control" onchange="toggleMode()">
                <option value="range">নির্দিষ্ট পরিসর (যেমন: JP0001 থেকে JP0050)</option>
                <option value="all">সব সক্রিয় সদস্য</option>
              </select>
            </div>
          </div>
          <div id="rangeFields" class="form-row">
            <div class="form-group">
              <label class="form-label">শুরুর নম্বর (যেমন: 1 = JP0001)</label>
              <input type="number" name="from_id" class="form-control" min="1" value="<?= $from_id ?: 1 ?>" placeholder="1">
            </div>
            <div class="form-group">
              <label class="form-label">শেষের নম্বর (যেমন: 100 = JP0100)</label>
              <input type="number" name="to_id" class="form-control" min="1" value="<?= $to_id ?: 100 ?>" placeholder="100">
            </div>
          </div>
          <div class="alert alert-info" style="font-size:0.82rem">
            <span class="material-icons" style="font-size:1rem">info</span>
            প্রতিটি কার্ড দুই পাতায় প্রিন্ট হবে — সামনে (তথ্য) ও পিছনে (নিয়মকানুন)।
            সর্বোচ্চ একসাথে ১০০টি কার্ড প্রিন্ট করা যাবে।
          </div>
          <button type="submit" class="btn btn-primary">
            <span class="material-icons">preview</span> প্রিভিউ দেখুন
          </button>
        </form>
      </div>
    </div>

    <?php if ($members && $count > 0): ?>
    <div class="card">
      <div class="card-header">
        <h3><span class="material-icons">list</span> নির্বাচিত সদস্য — মোট: <?= $count ?>জন</h3>
        <a href="bulk_cards.php?from=<?= $from_id ?>&to=<?= $to_id ?>&mode=<?= ($_GET['mode']??($_POST['mode']??'range')) ?>&print=1"
           target="_blank" class="btn btn-primary btn-sm">
          <span class="material-icons">print</span> প্রিন্ট করুন
        </a>
      </div>
      <div class="card-body">
        <div class="preview-grid">
        <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
          <div class="card-mini">
            <strong><?= htmlspecialchars($m['name']) ?></strong>
            <span><?= htmlspecialchars($m['member_id'] ?? '—') ?></span>
            <?php if ($m['is_donor']): ?><span style="color:#e65100">★ দাতা</span><?php endif; ?>
          </div>
        <?php endwhile; ?>
        </div>
      </div>
    </div>
    <?php elseif ($members !== null): ?>
    <div class="alert alert-warning">এই পরিসরে কোনো সক্রিয় সদস্য পাওয়া যায়নি।</div>
    <?php endif; ?>
  </div>
</div>
<script>
function toggleMode() {
  const m = document.getElementById('modeSelect').value;
  document.getElementById('rangeFields').style.display = m === 'range' ? 'grid' : 'none';
}
</script>

<?php else: ?>
<!-- PRINT MODE - Only cards -->
<?php endif; ?>

<?php
// Render cards for print (both modes)
if ($members && $count > 0):
    if ($print_mode): ?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<?php endif; ?>

<?php
    $members->data_seek(0);
    $card_num = 0;
    while ($m = $members->fetch_assoc()):
        $card_num++;
        if ($card_num > 100) break; // Max 100 cards
?>
<div class="bulk-page">
  <!-- Front Card -->
  <div class="card-wrap">
    <?php if (!$print_mode): ?><div class="card-label">কার্ড #<?= $card_num ?> — সামনের পিঠ</div><?php endif; ?>
    <div class="id-card">
      <div class="top-strip"></div>
      <div class="wm">
        <?php if ($lib_logo): ?><img src="<?= htmlspecialchars($lib_logo) ?>" alt=""><?php else: ?><div class="wm-text">JP</div><?php endif; ?>
      </div>
      <div class="card-hdr">
        <?php if ($lib_logo): ?><img src="<?= htmlspecialchars($lib_logo) ?>" alt="logo">&nbsp;<?php endif; ?>
        <h3><?= htmlspecialchars($lib_name) ?></h3>
        <div class="tl"><?= htmlspecialchars($lib_tagline) ?></div>
        <div class="ad"><?= htmlspecialchars($lib_address) ?></div>
      </div>
      <div style="padding-bottom:20px">
        <div class="card-row"><span class="card-lbl">নাম:</span><span class="card-val"><?= htmlspecialchars($m['name']) ?><?php if (!empty($m['is_donor'])): ?> <span class="donor-tag">★ দাতা</span><?php endif; ?></span></div>
        <?php if (!empty($m['father_name'])): ?><div class="card-row"><span class="card-lbl">পিতা:</span><span class="card-val"><?= htmlspecialchars($m['father_name']) ?></span></div><?php endif; ?>
        <?php if (!empty($m['address'])): ?><div class="card-row"><span class="card-lbl">ঠিকানা:</span><span class="card-val" style="font-size:0.58rem"><?= htmlspecialchars(mb_substr($m['address'],0,48)) ?></span></div><?php endif; ?>
        <?php if (!empty($m['join_date'])): ?><div class="card-row"><span class="card-lbl">তারিখ:</span><span class="card-val"><?= date('d/m/Y',strtotime($m['join_date'])) ?></span></div><?php endif; ?>
      </div>
      <div class="card-id-strip"><?= htmlspecialchars($m['member_id'] ?? '—') ?></div>
    </div>
  </div>

  <!-- Back Card -->
  <div class="card-wrap" style="margin-top:16px">
    <?php if (!$print_mode): ?><div class="card-label">পেছনের পিঠ</div><?php endif; ?>
    <div class="id-card card-back">
      <div class="top-strip"></div>
      <div class="wm">
        <?php if ($lib_logo): ?><img src="<?= htmlspecialchars($lib_logo) ?>" alt=""><?php else: ?><div class="wm-text">JP</div><?php endif; ?>
      </div>
      <div class="back-hdr">
        <h4>পাঠাগার সদস্যপদের নিয়মাবলী</h4>
        <small><?= htmlspecialchars($lib_name) ?></small>
      </div>
      <div class="back-rules"><?= $rules_text ?></div>
      <div class="back-footer">
        <span>আইডি: <?= htmlspecialchars($m['member_id'] ?? '—') ?></span>
        <span><?= htmlspecialchars($lib_name) ?></span>
        <span><?= !empty($m['is_donor']) ? '★ দাতা' : 'সদস্য' ?></span>
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>

<?php if ($print_mode): ?>
<script>window.onload=function(){window.print();}</script>
<?php endif; ?>

<?php endif; // members ?>

<?php if (!$print_mode): ?>
</div><!-- .main -->
<?php endif; ?>
</body></html>
