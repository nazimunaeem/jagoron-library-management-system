<?php
require_once 'includes/config.php';
$slug = escape($_GET['slug'] ?? '');
$page = db()->query("SELECT * FROM pages WHERE slug='$slug' AND is_published=1")->fetch_assoc();
if (!$page) { header('Location: /'); exit; }
$lib_name = libName();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page['title']) ?> — <?= htmlspecialchars($lib_name) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
.page-wrap{max-width:800px;margin:28px auto;background:#fff;border-radius:12px;padding:28px;box-shadow:var(--shadow)}
@media(max-width:768px){.page-wrap{margin:14px;padding:18px}}
.page-wrap h1{color:var(--primary);margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid var(--border);font-size:1.2rem}
.page-body{line-height:2.2;font-size:0.93rem}
.page-body p{margin-bottom:10px}
.page-body ul{margin:8px 0 10px 20px}
.page-body li{margin-bottom:5px}
.page-body strong{color:var(--primary)}
</style>
</head>
<body>
<?php include 'includes/pub_nav.php'; ?>
<div class="page-wrap">
  <h1><?= htmlspecialchars($page['title']) ?></h1>
  <div class="page-body"><?= $page['content'] ?></div>
</div>
<footer style="background:#0f2d1e;color:rgba(255,255,255,0.5);text-align:center;padding:12px;font-size:0.78rem;margin-top:20px">
  <?= htmlspecialchars($lib_name) ?> — <?= htmlspecialchars(libAddress()) ?>
</footer>
</body>
</html>
