<?php
require_once '../includes/config.php';
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=escape($_POST['name']);$father=escape($_POST['father_name']??'');
    $phone=escape($_POST['phone']??'');$address=escape($_POST['address']??'');
    $username=escape($_POST['username']);$password=$_POST['password']??'';
    if(!$name||!$username||!$password){$error='নাম, username ও পাসওয়ার্ড আবশ্যক।';}
    elseif(mb_strlen($password)<6){$error='পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।';}
    elseif(db()->query("SELECT id FROM members WHERE username='$username'")->num_rows){$error='এই username ইতিমধ্যে নেওয়া হয়েছে।';}
    else{
        $h=password_hash($password,PASSWORD_DEFAULT);
        db()->query("INSERT INTO members (name,father_name,phone,address,status,username,password) VALUES ('$name','$father','$phone','$address','pending','$username','$h')");
        $success='নিবন্ধন সফল! অ্যাডমিন অনুমোদনের পর আপনার সদস্যপদ সক্রিয় হবে।';
    }
}
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>সদস্য নিবন্ধন — <?=htmlspecialchars(libName())?></title><link rel="stylesheet" href="../assets/css/style.css">
</head><body>
<div class="login-page" style="align-items:flex-start;padding:30px 16px">
  <div class="login-card" style="max-width:460px">
    <h2>📚 সদস্য নিবন্ধন</h2>
    <p class="subtitle"><?=htmlspecialchars(libName())?></p>
    <div class="alert alert-info" style="font-size:0.82rem">নিবন্ধন ফি: ৳<?=regFee()?>। প্রথম মাস বিনামূল্যে। জরিমানা: ৳<?=finePerDay()?>/দিন।</div>
    <?php if($success):?><div class="alert alert-success"><?=$success?></div>
    <?php else:?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <form method="POST">
      <div class="form-group"><label class="form-label">নাম *</label><input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label class="form-label">পিতার নাম</label><input type="text" name="father_name" class="form-control"></div>
      <div class="form-group"><label class="form-label">ফোন</label><input type="text" name="phone" class="form-control"></div>
      <div class="form-group"><label class="form-label">ঠিকানা</label><textarea name="address" class="form-control" rows="2"></textarea></div>
      <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
      <div class="form-group"><label class="form-label">পাসওয়ার্ড *</label><input type="password" name="password" class="form-control" required minlength="6"></div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">নিবন্ধন করুন</button>
    </form>
    <?php endif;?>
    <p style="text-align:center;margin-top:12px;font-size:0.82rem"><a href="login.php" style="color:var(--primary)">← লগইন পেজে ফিরে যান</a></p>
  </div>
</div></body></html>
