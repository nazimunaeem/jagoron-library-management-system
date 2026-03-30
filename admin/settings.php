<?php
require_once '../includes/config.php';
requireAdmin();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $fields = [
        'library_name_bn', 'library_name_en', 'tagline', 'address',
        'reg_fee', 'monthly_fee', 'fine_per_day', 'issue_days', 'allow_delete'
    ];
    foreach ($fields as $f) {
        $v = escape($_POST[$f] ?? '');
        db()->query("INSERT INTO settings (skey, svalue) VALUES ('$f','$v') ON DUPLICATE KEY UPDATE svalue='$v'");
    }
    // Logo upload
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp','svg'])) {
            $dir = __DIR__ . '/../uploads/logo/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dir . $fname)) {
                $url = '/uploads/logo/' . $fname;
                db()->query("INSERT INTO settings (skey,svalue) VALUES ('logo','$url') ON DUPLICATE KEY UPDATE svalue='$url'");
            }
        }
    }
    // Reset settings cache
    $success = 'সেটিংস সংরক্ষণ হয়েছে!';
}

// Password change
if (isset($_POST['change_pass'])) {
    $old = $_POST['old_pass'] ?? '';
    $new = $_POST['new_pass'] ?? '';
    $aid = (int)($_SESSION['admin_id'] ?? 0);
    $adm = db()->query("SELECT password FROM admins WHERE id=$aid")->fetch_assoc();
    if ($adm && password_verify($old, $adm['password'])) {
        $h = password_hash($new, PASSWORD_DEFAULT);
        db()->query("UPDATE admins SET password='$h' WHERE id=$aid");
        $success = 'পাসওয়ার্ড পরিবর্তন হয়েছে!';
    } else {
        $error = 'পুরনো পাসওয়ার্ড ভুল।';
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>সেটিংস — <?= htmlspecialchars(libName()) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>সিস্টেম সেটিংস</h1>
  </div>
  <div class="content">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

      <!-- Library Identity -->
      <div class="card">
        <div class="card-header"><h3><span class="material-icons">business</span> পাঠাগারের পরিচয়</h3></div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">পাঠাগারের নাম (বাংলা)</label>
              <input type="text" name="library_name_bn" class="form-control" value="<?= htmlspecialchars(libName()) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Library Name (English)</label>
              <input type="text" name="library_name_en" class="form-control" value="<?= htmlspecialchars(libNameEn()) ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">স্লোগান / Tagline</label>
            <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars(libTagline()) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">ঠিকানা</label>
            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars(libAddress()) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">লোগো আপলোড (JPG/PNG/SVG)</label>
            <?php $logo = libLogo(); if ($logo): ?>
              <div style="margin-bottom:8px">
                <img src="<?= htmlspecialchars($logo) ?>" style="height:55px;border-radius:6px;border:1px solid var(--border)">
                <span style="font-size:0.78rem;color:var(--muted);margin-left:8px">বর্তমান লোগো</span>
              </div>
            <?php endif; ?>
            <input type="file" name="logo" class="form-control" accept="image/*">
            <div class="form-hint">সর্বোচ্চ ৫ MB। নতুন লোগো আপলোড করলে পুরনোটি প্রতিস্থাপিত হবে।</div>
          </div>
        </div>
      </div>

      <!-- Fee Settings -->
      <div class="card">
        <div class="card-header"><h3><span class="material-icons">payments</span> ফি ও জরিমানা</h3></div>
        <div class="card-body">
          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">ডিফল্ট নিবন্ধন ফি (৳)</label>
              <input type="number" name="reg_fee" class="form-control" value="<?= regFee() ?>" min="0">
              <div class="form-hint">সদস্য যোগ ও অনুমোদনের সময় পরিবর্তন করা যাবে</div>
            </div>
            <div class="form-group">
              <label class="form-label">মাসিক ফি (৳)</label>
              <input type="number" name="monthly_fee" class="form-control" value="<?= monthlyFee() ?>" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">জরিমানা (৳/দিন)</label>
              <input type="number" name="fine_per_day" class="form-control" value="<?= finePerDay() ?>" min="0">
            </div>
          </div>
          <div class="form-group" style="max-width:260px">
            <label class="form-label">ডিফল্ট ইস্যু মেয়াদ (দিন)</label>
            <input type="number" name="issue_days" class="form-control" value="<?= issueDays() ?>" min="1" max="90">
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="card" style="border:1px solid var(--danger)">
        <div class="card-header" style="background:#fff5f5">
          <h3><span class="material-icons" style="color:var(--danger)">warning</span> ডিলিট অপশন</h3>
        </div>
        <div class="card-body">
          <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer">
            <input type="checkbox" name="allow_delete" value="1" <?= allowDelete() ? 'checked' : '' ?> style="width:18px;height:18px;margin-top:2px;flex-shrink:0">
            <div>
              <strong>ডিলিট অপশন চালু করুন</strong>
              <p style="font-size:0.8rem;color:var(--muted);margin:3px 0 0">
                চালু থাকলে বই, সদস্য, আর্থিক রেকর্ড ও দাতা মুছে ফেলার বোতাম দেখাবে।<br>
                <em>ডিফল্ট: বন্ধ (নিরাপদ মোড)</em>
              </p>
            </div>
          </label>
        </div>
      </div>

      <button type="submit" name="save_settings" class="btn btn-primary">
        <span class="material-icons">save</span> সেটিংস সংরক্ষণ করুন
      </button>
    </form>

    <!-- Password Change -->
    <div class="card" style="margin-top:24px">
      <div class="card-header"><h3><span class="material-icons">lock</span> পাসওয়ার্ড পরিবর্তন</h3></div>
      <div class="card-body">
        <form method="POST" style="max-width:500px">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">পুরনো পাসওয়ার্ড</label>
              <input type="password" name="old_pass" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">নতুন পাসওয়ার্ড</label>
              <input type="password" name="new_pass" class="form-control" required minlength="6">
            </div>
          </div>
          <button type="submit" name="change_pass" class="btn btn-warning">
            <span class="material-icons">lock_reset</span> পাসওয়ার্ড পরিবর্তন করুন
          </button>
        </form>
      </div>
    </div>

  </div>
</div>
</body>
</html>
