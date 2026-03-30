<?php
require_once '../includes/config.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$m  = db()->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();
if (!$m) { header('Location: members.php'); exit; }
$lib_name    = libName();
$lib_tagline = libTagline();
$lib_address = libAddress();
$lib_logo    = libLogo();
$rules_page  = db()->query("SELECT content FROM pages WHERE slug='rules' LIMIT 1")->fetch_assoc();
$rules_text  = $rules_page['content'] ?? '<ul><li>একজন সদস্য একসাথে একটি বই নিতে পারবেন।</li><li>বই ইস্যুর মেয়াদ ১৫ দিন।</li><li>প্রতিদিন ২ টাকা জরিমানা (দেরিতে ফেরত দিলে)।</li><li>মাসিক ফি নির্ধারিত তারিখে দিতে হবে।</li></ul>';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>আইডি কার্ড — <?= htmlspecialchars($m['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style><?php include '../includes/card_styles.php'; ?></style>
</head>
<body>
<div class="no-print" style="text-align:center;padding:20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
  <button onclick="window.print()" class="btn btn-primary">🖨️ প্রিন্ট করুন (৩×২ ইঞ্চি)</button>
  <a href="members.php" class="btn btn-outline">← তালিকায় ফিরুন</a>
</div>
<?php include '../includes/card_template.php'; ?>
</body>
</html>
