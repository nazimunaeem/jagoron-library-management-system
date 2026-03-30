<?php
require_once '../includes/config.php';
requireAdmin();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = escape($_POST['name'] ?? '');
    $father   = escape($_POST['father_name'] ?? '');
    $phone    = escape($_POST['phone'] ?? '');
    $address  = escape($_POST['address'] ?? '');
    $join     = escape($_POST['join_date'] ?? date('Y-m-d'));
    $don_type = escape($_POST['don_type'] ?? 'money');
    $amount   = (int)($_POST['initial_amount'] ?? 0);
    $book_count = (int)($_POST['initial_books'] ?? 0);
    $desc     = escape($_POST['description'] ?? 'প্রাথমিক দান');

    if ($name) {
        $mid = nextMemberId();
        db()->query("INSERT INTO members (member_id,name,father_name,phone,address,membership_type,join_date,status,is_donor,reg_fee_paid)
            VALUES ('$mid','$name','$father','$phone','$address','donor','$join','active',1,1)");
        $new_id = db()->insert_id;

        // Record donation
        if ($amount > 0 || $book_count > 0) {
            db()->query("INSERT INTO donations (donor_id,date,type,amount,book_count,description)
                VALUES ($new_id,'$join','$don_type',$amount,$book_count,'$desc')");
            $don_id = db()->insert_id;
            if ($don_type === 'money' && $amount > 0) {
                db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,donation_id)
                    VALUES ('$join','income','Donation','অর্থ দান — $name ($mid)',$amount,$new_id,$don_id)");
            }
            if ($don_type === 'book' && $amount > 0) {
                // Balanced: income + expense (net 0)
                db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,donation_id)
                    VALUES ('$join','income','Book Donation','বই দান (আনু. মূল্য) — $name ($mid) — {$book_count}টি বই',$amount,$new_id,$don_id)");
                db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,donation_id)
                    VALUES ('$join','expense','Book Donation','বই দান — ব্যয় সমন্বয় — $name ($mid)',$amount,$new_id,$don_id)");
            }
        }
        $success = "দাতা সদস্য যোগ হয়েছে! আইডি: <strong>$mid</strong>";
    } else {
        $error = 'নাম আবশ্যক।';
    }
}
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দাতা যোগ</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>নতুন দাতা সদস্য</h1><a href="donors.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
  </div>
  <div class="content">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <div class="alert alert-info"><span class="material-icons" style="font-size:1rem">info</span>
      দাতা সদস্যরা স্বয়ংক্রিয়ভাবে সদস্যপদ পান, কোনো মাসিক ফি নেই।</div>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label class="form-label">নাম *</label><input type="text" name="name" class="form-control" required></div>
          <div class="form-group"><label class="form-label">পিতার নাম</label><input type="text" name="father_name" class="form-control"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">ফোন</label><input type="text" name="phone" class="form-control"></div>
          <div class="form-group"><label class="form-label">যোগদানের তারিখ</label><input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">ঠিকানা</label><textarea name="address" class="form-control" rows="2"></textarea></div>

        <hr style="margin:16px 0;border-color:var(--border)">
        <p style="font-weight:600;margin-bottom:10px">প্রারম্ভিক দান (ঐচ্ছিক)</p>
        <div class="form-row">
          <div class="form-group"><label class="form-label">দানের ধরন</label>
            <select name="don_type" id="donType" class="form-control" onchange="toggleDonFields()">
              <option value="money">💵 অর্থ</option>
              <option value="book">📚 বই</option>
              <option value="other">🎁 অন্যান্য</option>
            </select>
          </div>
          <div class="form-group" id="amtField">
            <label class="form-label" id="amtLabel">পরিমাণ (টাকা)</label>
            <input type="number" name="initial_amount" class="form-control" min="0" value="0">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" id="bookField" style="display:none">
            <label class="form-label">বইয়ের সংখ্যা</label>
            <input type="number" name="initial_books" class="form-control" min="0" value="0">
          </div>
          <div class="form-group"><label class="form-label">বিবরণ</label>
            <input type="text" name="description" class="form-control" value="প্রাথমিক দান"></div>
        </div>
        <button type="submit" class="btn btn-primary"><span class="material-icons">favorite</span> দাতা যোগ করুন</button>
      </form>
    </div></div>
  </div>
</div>
<script>
function toggleDonFields() {
    const t = document.getElementById('donType').value;
    const bf = document.getElementById('bookField');
    const al = document.getElementById('amtLabel');
    bf.style.display = t === 'book' ? 'block' : 'none';
    al.textContent = t === 'book' ? 'আনুমানিক মূল্য (ঐচ্ছিক)' : 'পরিমাণ (টাকা)';
}
</script>
</body></html>
