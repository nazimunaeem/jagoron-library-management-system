<?php
require_once '../includes/config.php';
requireMember();
$mid  = (int)$_SESSION['member_id'];
$m    = db()->query("SELECT * FROM members WHERE id=$mid")->fetch_assoc();
if (!$m) { header('Location: dashboard.php'); exit; }
$lib_name    = libName();
$lib_tagline = libTagline();
$lib_address = libAddress();
$lib_logo    = libLogo();
$rules_page  = db()->query("SELECT content FROM pages WHERE slug='rules' LIMIT 1")->fetch_assoc();
$rules_text  = $rules_page['content'] ?? '<ul><li>একজন সদস্য একসাথে একটি বই নিতে পারবেন।</li><li>বই ইস্যুর মেয়াদ ১৫ দিন।</li><li>প্রতিদিন ২ টাকা জরিমানা।</li><li>মাসিক ফি নির্ধারিত তারিখে দিতে হবে।</li></ul>';
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>আমার আইডি কার্ড — <?=htmlspecialchars($m['name'])?></title>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style>
<?php include '../includes/card_styles.php'; ?>
</style>
</head><body>
<div class="no-print" style="text-align:center;padding:20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
  <button onclick="window.print()" class="btn btn-primary">🖨️ প্রিন্ট / PDF ডাউনলোড</button>
  <a href="dashboard.php" class="btn btn-outline">← ড্যাশবোর্ডে ফিরুন</a>
</div>
<p class="no-print" style="text-align:center;font-size:0.8rem;color:#666;margin-bottom:16px">
  PDF সেভ করতে: প্রিন্ট → Destination: <strong>Save as PDF</strong> → Save
</p>
<?php include '../includes/card_template.php'; ?>
</body></html>
