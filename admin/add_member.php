<?php
require_once '../includes/config.php';
requireAdmin();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = escape($_POST['name'] ?? '');
    $father   = escape($_POST['father_name'] ?? '');
    $phone    = escape($_POST['phone'] ?? '');
    $address  = escape($_POST['address'] ?? '');
    $type     = escape($_POST['membership_type'] ?? 'regular');
    $join     = escape($_POST['join_date'] ?? date('Y-m-d'));
    $username = escape($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $reg_fee  = (int)($_POST['reg_fee'] ?? regFee());

    if (!$name) {
        $error = 'নাম আবশ্যক।';
    } else {
        // Auto-generate member ID
        $last = db()->query("SELECT member_id FROM members WHERE member_id IS NOT NULL ORDER BY id DESC LIMIT 1")->fetch_row();
        $num  = $last ? ((int)preg_replace('/[^0-9]/', '', ($last[0] ?? '0')) + 1) : 1;
        $mid  = 'JP' . str_pad($num, 4, '0', STR_PAD_LEFT);
        $hpass = $password ? password_hash($password, PASSWORD_DEFAULT) : '';

        $sql = "INSERT INTO members (member_id, name, father_name, phone, address, membership_type, join_date, status, username, password, reg_fee_paid)
                VALUES ('$mid','$name','$father','$phone','$address','$type','$join','active','$username','$hpass',1)";
        if (db()->query($sql)) {
            $new_id = db()->insert_id;
            if ($reg_fee > 0) {
                db()->query("INSERT INTO finance (date, type, category, description, amount, member_id)
                             VALUES ('$join','income','Registration','নিবন্ধন ফি — $name ($mid)',$reg_fee,$new_id)");
            }
            $success = "সদস্য যোগ হয়েছে! সদস্য আইডি: <strong>$mid</strong>" . ($reg_fee > 0 ? " | নিবন্ধন ফি: ৳$reg_fee" : '');
        } else {
            $error = 'ত্রুটি: ' . db()->error;
        }
    }
}

// Suggest next member ID
$last = db()->query("SELECT member_id FROM members WHERE member_id IS NOT NULL ORDER BY id DESC LIMIT 1")->fetch_row();
$num  = $last ? ((int)preg_replace('/[^0-9]/', '', ($last[0] ?? '0')) + 1) : 1;
$suggested = 'JP' . str_pad($num, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>সদস্য যোগ — <?= htmlspecialchars(libName()) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>নতুন সদস্য যোগ</h1>
    <a href="members.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
  </div>
  <div class="content">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="alert alert-info">
      <span class="material-icons" style="font-size:1rem">info</span>
      পরবর্তী সদস্য আইডি: <strong><?= $suggested ?></strong> | ডিফল্ট নিবন্ধন ফি: ৳<?= regFee() ?> (পরিবর্তন করা যাবে)
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">নাম *</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">পিতার নাম</label>
              <input type="text" name="father_name" class="form-control">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">ফোন</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-label">সদস্যের ধরন</label>
              <select name="membership_type" class="form-control">
                <option value="regular">সাধারণ</option>
                <option value="student">শিক্ষার্থী</option>
                <option value="senior">প্রবীণ</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">ঠিকানা</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">যোগদানের তারিখ</label>
              <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">নিবন্ধন ফি (টাকা)</label>
              <input type="number" name="reg_fee" class="form-control" value="<?= regFee() ?>" min="0">
              <div class="form-hint">পরিবর্তন করলে সেই পরিমাণ গ্রহণ করা হবে</div>
            </div>
          </div>

          <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
            <p style="font-size:0.82rem;color:var(--muted);margin-bottom:10px">অনলাইন অ্যাক্সেস (ঐচ্ছিক):</p>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control">
              </div>
              <div class="form-group">
                <label class="form-label">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <span class="material-icons">person_add</span> সদস্য যোগ করুন
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
