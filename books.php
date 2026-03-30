<?php
require_once 'includes/config.php';
$per_page = 15;
$page_num = max(1, (int)($_GET['page'] ?? 1));
$s   = escape($_GET['s'] ?? '');
$cat = escape($_GET['cat'] ?? '');
$offset = ($page_num - 1) * $per_page;

$where = 'WHERE 1=1';
if ($s)   $where .= " AND (title LIKE '%$s%' OR author LIKE '%$s%')";
if ($cat) $where .= " AND category='$cat'";

$total_count = (int)(db()->query("SELECT COUNT(*) FROM books $where")->fetch_row()[0] ?? 0);
$total_pages = max(1, ceil($total_count / $per_page));
$page_num    = min($page_num, $total_pages);
$offset      = ($page_num - 1) * $per_page;

$books_q = db()->query("SELECT * FROM books $where ORDER BY created_at DESC, id DESC LIMIT $per_page OFFSET $offset");
$cats_q  = db()->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category");

// issued map
$issued_map = [];
$iq = db()->query("SELECT br.book_id, m.name FROM borrows br JOIN members m ON br.member_id=m.id WHERE br.status='borrowed'");
if ($iq) while ($r = $iq->fetch_assoc()) $issued_map[$r['book_id']] = $r['name'];

$lib_name = libName();
function buildUrl($p, $s, $cat) {
    $q = ['page' => $p];
    if ($s)   $q['s']   = $s;
    if ($cat) $q['cat'] = $cat;
    return '/books.php?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>সকল বই — <?= htmlspecialchars($lib_name) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
.book-list{display:flex;flex-direction:column;gap:0}
.book-row{display:flex;align-items:center;gap:12px;padding:11px 14px;border-bottom:1px solid var(--border);background:#fff;transition:background 0.15s}
.book-row:hover{background:#f7fbf7}
.book-num{min-width:30px;color:var(--muted);font-size:0.78rem;text-align:right;flex-shrink:0}
.book-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.book-details{flex:1;min-width:0}
.book-details h4{font-size:0.875rem;font-weight:600;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.book-details p{font-size:0.75rem;color:var(--muted)}
.book-meta{text-align:right;flex-shrink:0}
.cat-chips{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:12px}
.cat-chip{padding:4px 10px;border-radius:20px;font-size:0.75rem;cursor:pointer;text-decoration:none;border:1px solid var(--border);background:#fff;color:var(--text);white-space:nowrap}
.cat-chip.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.pagination{display:flex;gap:4px;flex-wrap:wrap;margin-top:14px;justify-content:center}
.page-btn{padding:6px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:#fff;font-size:0.82rem;text-decoration:none;color:var(--text);font-family:inherit}
.page-btn.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.page-btn:hover:not(.active){border-color:var(--primary);color:var(--primary)}
</style>
</head>
<body>
<?php include 'includes/pub_nav.php'; ?>

<div class="opac-container">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
    <h2 style="font-size:1rem;font-weight:600;color:var(--primary)">📚 সকল বই</h2>
    <span style="font-size:0.8rem;color:var(--muted)">মোট: <?= $total_count ?>টি বই</span>
    <a href="/" style="margin-left:auto;font-size:0.82rem;color:var(--primary)">← হোমপেজ</a>
  </div>

  <form method="GET" style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap">
    <input type="text" name="s" class="form-control" placeholder="বই বা লেখকের নাম..." value="<?= htmlspecialchars($s) ?>" style="flex:1;min-width:160px">
    <button type="submit" class="btn btn-primary"><span class="material-icons">search</span></button>
    <?php if ($s || $cat): ?><a href="/books.php" class="btn btn-outline">✕ মুছুন</a><?php endif; ?>
  </form>

  <?php if ($cats_q && $cats_q->num_rows > 0): ?>
  <div class="cat-chips">
    <a href="<?= buildUrl(1,$s,'') ?>" class="cat-chip <?= !$cat ? 'active' : '' ?>">সব</a>
    <?php while ($c = $cats_q->fetch_row()): ?>
      <a href="<?= buildUrl(1,$s,$c[0]) ?>" class="cat-chip <?= $cat==$c[0]?'active':'' ?>"><?= htmlspecialchars($c[0]) ?></a>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="book-list">
    <?php
    $has = false; $num = $offset + 1;
    if ($books_q): while ($b = $books_q->fetch_assoc()):
      $has = true;
      $it  = $issued_map[$b['id']] ?? null;
    ?>
      <div class="book-row">
        <div class="book-num"><?= $num++ ?></div>
        <div class="book-icon">📖</div>
        <div class="book-details">
          <h4><?= htmlspecialchars($b['title']) ?></h4>
          <p>✍️ <?= htmlspecialchars($b['author']) ?>
            <?php if ($b['category']): ?> &nbsp;·&nbsp; 📂 <?= htmlspecialchars($b['category']) ?><?php endif; ?>
            <?php if ($b['shelf']): ?> &nbsp;·&nbsp; 📍 <?= htmlspecialchars($b['shelf']) ?><?php endif; ?>
          </p>
        </div>
        <div class="book-meta">
          <?php if ($b['available'] > 0): ?>
            <span class="badge badge-success">পাওয়া যাচ্ছে (<?= $b['available'] ?>)</span>
          <?php else: ?>
            <span class="badge badge-danger" style="font-size:0.68rem">নেই<?= $it ? '<br>'.htmlspecialchars($it) : '' ?></span>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; endif; ?>
    <?php if (!$has): ?>
      <div class="empty-state"><span class="material-icons">search_off</span><p>কোনো বই পাওয়া যায়নি</p></div>
    <?php endif; ?>
    </div>
  </div>

  <?php if ($total_pages > 1): ?>
  <div class="pagination">
    <?php if ($page_num > 1): ?><a href="<?= buildUrl(1,$s,$cat) ?>" class="page-btn">«</a><a href="<?= buildUrl($page_num-1,$s,$cat) ?>" class="page-btn">‹</a><?php endif; ?>
    <?php
    $start = max(1, $page_num - 2);
    $end   = min($total_pages, $page_num + 2);
    for ($p = $start; $p <= $end; $p++):
    ?>
      <a href="<?= buildUrl($p,$s,$cat) ?>" class="page-btn <?= $p==$page_num?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($page_num < $total_pages): ?><a href="<?= buildUrl($page_num+1,$s,$cat) ?>" class="page-btn">›</a><a href="<?= buildUrl($total_pages,$s,$cat) ?>" class="page-btn">»</a><?php endif; ?>
  </div>
  <p style="text-align:center;font-size:0.78rem;color:var(--muted);margin-top:8px">
    পেজ <?= $page_num ?> / <?= $total_pages ?> &nbsp;·&nbsp; মোট <?= $total_count ?>টি বই
  </p>
  <?php endif; ?>
</div>

<footer style="background:#0f2d1e;color:rgba(255,255,255,0.5);text-align:center;padding:12px;font-size:0.78rem;margin-top:28px">
  <?= htmlspecialchars($lib_name) ?> — <?= htmlspecialchars(libAddress()) ?>
</footer>
</body>
</html>
