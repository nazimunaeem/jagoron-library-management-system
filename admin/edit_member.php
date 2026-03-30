<?php
require_once '../includes/config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$m = db()->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();
if (!$m) { header('Location: members.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = escape($_POST['name']);
    $new_username = escape(trim($_POST['username'] ?? ''));
    
    // ইউজারনেম চেক লজিক: যদি আগে খালি থাকে এবং এখন নতুন কিছু ইনপুট দেওয়া হয়
    if (empty($m['username']) && !empty($new_username)) {
        $check = db()->query("SELECT id FROM members WHERE username='$new_username' AND id != $id");
        if ($check && $check->num_rows > 0) {
            $error = 'Username already exists, try new!';
        }
    } elseif (!empty($m['username'])) {
        // যদি আগে থেকেই ইউজারনেম থাকে, তবে সেটিই বজায় থাকবে (পরিবর্তন করা যাবে না)
        $new_username = $m['username'];
    } else {
        // যদি ইনপুট খালি থাকে এবং আগে থেকেও না থাকে, তবে খালিই থাকবে
        $new_username = '';
    }

    // যদি কোনো এরর না থাকে তবেই আপডেট হবে
    if (empty($error)) {
        $father = escape($_POST['father_name'] ?? '');
        $phone = escape($_POST['phone'] ?? '');
        $address = escape($_POST['address'] ?? '');
        $type = escape($_POST['membership_type'] ?? 'regular');
        $status = escape($_POST['status'] ?? 'active');
        $join = escape($_POST['join_date'] ?? '');

        db()->query("UPDATE members SET name='$name', username='$new_username', father_name='$father', phone='$phone', address='$address', membership_type='$type', status='$status', join_date='$join' WHERE id=$id");
        
        if (!empty($_POST['password'])) {
            $h = password_hash($_POST['password'], PASSWORD_DEFAULT);
            db()->query("UPDATE members SET password='$h' WHERE id=$id");
        }
        
        $success = 'আপডেট হয়েছে!';
        $m = db()->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>সদস্য সম্পাদনা</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>সদস্য সম্পাদনা</h1>
    <div style="display:flex;gap:8px">
      <a href="member_card.php?id=<?=$id?>" class="btn btn-outline btn-sm"><span class="material-icons">badge</span> কার্ড</a>
      <a href="members.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
    </div>
  </div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">নাম *</label>
            <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($m['name'] ?? '')?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">পিতার নাম</label>
            <input type="text" name="father_name" class="form-control" value="<?=htmlspecialchars($m['father_name']??'')?>">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ফোন</label>
            <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($m['phone']??'')?>">
          </div>
          <div class="form-group">
            <label class="form-label">যোগদান</label>
            <input type="date" name="join_date" class="form-control" value="<?=$m['join_date']??''?>">
          </div>
        </div>

        <div class="form-group">
            <label class="form-label">ঠিকানা</label>
            <textarea name="address" class="form-control" rows="2"><?=htmlspecialchars($m['address']??'')?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ধরন</label>
            <select name="membership_type" class="form-control">
              <option value="regular" <?=($m['membership_type']=='regular'?'selected':'')?>>সাধারণ</option>
              <option value="student" <?=($m['membership_type']=='student'?'selected':'')?>>শিক্ষার্থী</option>
              <option value="senior" <?=($m['membership_type']=='senior'?'selected':'')?>>প্রবীণ</option>
              <option value="donor" <?=($m['membership_type']=='donor'?'selected':'')?>>দাতা</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">অবস্থা</label>
            <select name="status" class="form-control">
              <option value="active" <?=($m['status']=='active'?'selected':'')?>>সক্রিয়</option>
              <option value="pending" <?=($m['status']=='pending'?'selected':'')?>>অনুমোদন বাকি</option>
              <option value="suspended" <?=($m['status']=='suspended'?'selected':'')?>>স্থগিত</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">ইউজারনেম (Login ID)</label>
          <?php $has_username = !empty($m['username']); ?>
          <input type="text" name="username" class="form-control" 
                 value="<?=htmlspecialchars($m['username'] ?? '')?>" 
                 <?= $has_username ? 'readonly' : '' ?> 
                 style="<?= $has_username ? 'background-color: #f5f7f5; cursor: not-allowed; color: #666;' : '' ?>" 
                 placeholder="ইউজারনেম দিন (ঐচ্ছিক)">
          <small class="form-hint"><?= $has_username ? 'ইউজারনেম পরিবর্তনযোগ্য নয়।' : 'খালি রাখতে পারেন, তবে একবার সেভ করলে আর পরিবর্তন করা যাবে না।' ?></small>
        </div>

        <div class="form-group">
          <label class="form-label">নতুন পাসওয়ার্ড (ঐচ্ছিক)</label>
          <input type="password" name="password" class="form-control" placeholder="পরিবর্তন করতে চাইলে লিখুন">
        </div>

        <button type="submit" class="btn btn-primary"><span class="material-icons">save</span> সংরক্ষণ করুন</button>
      </form>
    </div></div>
  </div>
</div>
</body>
</html>