<?php
require_once '../includes/config.php';
requireAdmin();
$success = '';

// Get or create about & rules pages
$about = db()->query("SELECT * FROM pages WHERE slug='about' LIMIT 1")->fetch_assoc();
$rules = db()->query("SELECT * FROM pages WHERE slug='rules' LIMIT 1")->fetch_assoc();

if (!$about) {
    db()->query("INSERT INTO pages (slug,title,content,sort_order) VALUES ('about','পাঠাগার সম্পর্কে','<p>জাগরণ পাঠাগার সম্পর্কে তথ্য এখানে লিখুন।</p>',1)");
    $about = db()->query("SELECT * FROM pages WHERE slug='about' LIMIT 1")->fetch_assoc();
}
if (!$rules) {
    db()->query("INSERT INTO pages (slug,title,content,sort_order) VALUES ('rules','নিয়মকানুন','<p>নিয়মকানুন এখানে লিখুন।</p>',2)");
    $rules = db()->query("SELECT * FROM pages WHERE slug='rules' LIMIT 1")->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $about_content = db()->real_escape_string($_POST['about_content'] ?? '');
    $about_title   = escape($_POST['about_title'] ?? 'পাঠাগার সম্পর্কে');
    $rules_content = db()->real_escape_string($_POST['rules_content'] ?? '');
    $rules_title   = escape($_POST['rules_title'] ?? 'নিয়মকানুন');

    db()->query("UPDATE pages SET title='$about_title', content='$about_content' WHERE slug='about'");
    db()->query("UPDATE pages SET title='$rules_title', content='$rules_content' WHERE slug='rules'");
    $success = 'পেজ সংরক্ষণ হয়েছে!';

    $about = db()->query("SELECT * FROM pages WHERE slug='about' LIMIT 1")->fetch_assoc();
    $rules = db()->query("SELECT * FROM pages WHERE slug='rules' LIMIT 1")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>পাঠাগার সম্পর্কে — <?= htmlspecialchars(libName()) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
.tab-btns { display:flex; gap:0; margin-bottom:-1px; }
.tab-btn { padding:10px 20px; border:1px solid var(--border); background:#f5f7f5; cursor:pointer; font-family:inherit; font-size:0.875rem; color:var(--muted); border-bottom:none; border-radius:8px 8px 0 0; }
.tab-btn.active { background:#fff; color:var(--primary); font-weight:600; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
textarea.rich { width:100%; min-height:300px; font-family:inherit; font-size:0.9rem; padding:12px; border:1px solid var(--border); border-radius:var(--radius-sm); resize:vertical; line-height:1.7; }
textarea.rich:focus { outline:none; border-color:var(--primary); }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>পাঠাগার সম্পর্কে ও নিয়মকানুন</h1>
    <div style="display:flex;gap:8px">
      <a href="/page.php?slug=about" class="btn btn-outline btn-sm" target="_blank"><span class="material-icons">visibility</span> সম্পর্কে দেখুন</a>
      <a href="/page.php?slug=rules" class="btn btn-outline btn-sm" target="_blank"><span class="material-icons">visibility</span> নিয়ম দেখুন</a>
    </div>
  </div>
  <div class="content">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <div class="tab-btns">
      <button class="tab-btn active" onclick="showTab('about',this)">পাঠাগার সম্পর্কে</button>
      <button class="tab-btn" onclick="showTab('rules',this)">নিয়মকানুন</button>
    </div>

    <form method="POST">
      <!-- About Tab -->
      <div class="tab-pane active card" id="tab-about" style="border-radius:0 var(--radius) var(--radius) var(--radius)">
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">পেজের শিরোনাম</label>
            <input type="text" name="about_title" class="form-control" value="<?= htmlspecialchars($about['title'] ?? 'পাঠাগার সম্পর্কে') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">বিষয়বস্তু (HTML সমর্থিত)</label>
            <div style="font-size:0.78rem;color:var(--muted);margin-bottom:6px">
              টিপস: &lt;h2&gt;শিরোনাম&lt;/h2&gt;, &lt;p&gt;অনুচ্ছেদ&lt;/p&gt;, &lt;ul&gt;&lt;li&gt;তালিকা&lt;/li&gt;&lt;/ul&gt;, &lt;strong&gt;굵은&lt;/strong&gt;
            </div>
            <textarea name="about_content" class="rich"><?= htmlspecialchars($about['content'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Rules Tab -->
      <div class="tab-pane card" id="tab-rules" style="border-radius:0 var(--radius) var(--radius) var(--radius)">
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">পেজের শিরোনাম</label>
            <input type="text" name="rules_title" class="form-control" value="<?= htmlspecialchars($rules['title'] ?? 'নিয়মকানুন') ?>">
          </div>
          <div class="alert alert-info" style="font-size:0.82rem">
            <span class="material-icons" style="font-size:1rem">info</span>
            নিবন্ধন পেজ ও নিয়মকানুন পেজে জরিমানার তথ্য স্বয়ংক্রিয়ভাবে দেখাবে: ৳<?= finePerDay() ?>/দিন
          </div>
          <div class="form-group">
            <label class="form-label">বিষয়বস্তু (HTML সমর্থিত)</label>
            <textarea name="rules_content" class="rich"><?= htmlspecialchars($rules['content'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:16px">
        <span class="material-icons">save</span> সংরক্ষণ করুন
      </button>
    </form>
  </div>
</div>
<script>
function showTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>
