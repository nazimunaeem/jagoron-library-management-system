<?php
require_once '../includes/config.php';
requireAdmin();
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $date=escape($_POST['date']);$type=escape($_POST['type']);
    $cat=escape($_POST['category']);$desc=escape($_POST['description']);
    $amount=(int)$_POST['amount'];
    if($date&&$type&&$desc&&$amount>0){
        db()->query("INSERT INTO finance (date,type,category,description,amount) VALUES ('$date','$type','$cat','$desc',$amount)");
        $success='এন্ট্রি যোগ হয়েছে!';
    } else $error='সব তথ্য পূরণ করুন এবং পরিমাণ > ০।';
}
$inc_cats=['নিবন্ধন ফি','মাসিক ফি','দান','অনুদান','জরিমানা','বই বিক্রয়','অনুষ্ঠান','অন্যান্য'];
$exp_cats=['বই ক্রয়','ভাড়া','বিদ্যুৎ','বেতন','রক্ষণাবেক্ষণ','ইন্টারনেট','স্টেশনারি','অন্যান্য'];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>এন্ট্রি যোগ</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>আর্থিক এন্ট্রি যোগ</h1><a href="finance.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
  </div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label class="form-label">তারিখ *</label><input type="date" name="date" class="form-control" value="<?=date('Y-m-d')?>" required></div>
          <div class="form-group"><label class="form-label">ধরন *</label>
            <select name="type" class="form-control" id="typesel" required onchange="updateCats()">
              <option value="">— নির্বাচন —</option>
              <option value="income">আয়</option><option value="expense">ব্যয়</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">বিভাগ</label>
            <select name="category" class="form-control" id="catsel"><option value="">— ধরন আগে নির্বাচন করুন —</option></select>
          </div>
          <div class="form-group"><label class="form-label">পরিমাণ (টাকা) *</label><input type="number" name="amount" class="form-control" min="1" required><div class="form-hint">শুধু পূর্ণ সংখ্যা (কোনো দশমিক নয়)</div></div>
        </div>
        <div class="form-group"><label class="form-label">বিবরণ *</label><input type="text" name="description" class="form-control" required></div>
        <button type="submit" class="btn btn-primary"><span class="material-icons">add_card</span> এন্ট্রি যোগ করুন</button>
      </form>
    </div></div>
  </div>
</div>
<script>
const inc=<?=json_encode($inc_cats)?>,exp=<?=json_encode($exp_cats)?>;
function updateCats(){
  const t=document.getElementById('typesel').value,sel=document.getElementById('catsel');
  const cats=t==='income'?inc:t==='expense'?exp:[];
  sel.innerHTML='<option value="">— নির্বাচন —</option>'+cats.map(c=>`<option>${c}</option>`).join('');
}
</script>
</body></html>
