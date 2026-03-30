<?php
require_once '../includes/config.php';
requireAdmin();
$success=$error='';
$sel_month=escape($_GET['month']??date('m'));
$sel_year=escape($_GET['year']??date('Y'));

if($_SERVER['REQUEST_METHOD']==='POST'){
    $mid=(int)$_POST['member_id'];$month=escape($_POST['month']);
    $year=escape($_POST['year']);$amount=(int)$_POST['amount'];$date=escape($_POST['paid_date']);
    $notes=escape($_POST['notes']??'');
    if($mid&&$month&&$year&&$amount>0){
        $check=db()->query("SELECT id FROM monthly_fees WHERE member_id=$mid AND month='$month' AND year='$year'")->num_rows;
        if($check){$error='এই মাসের ফি ইতিমধ্যে নেওয়া হয়েছে।';}
        else {
            db()->query("INSERT INTO monthly_fees (member_id,month,year,amount,paid_date,notes) VALUES ($mid,'$month','$year',$amount,'$date','$notes')");
            $m=db()->query("SELECT name,member_id FROM members WHERE id=$mid")->fetch_assoc();
            $mname=escape($m['name']??'');$mid_str=$m['member_id']??'';
            db()->query("INSERT INTO finance (date,type,category,description,amount,member_id) VALUES ('$date','income','Monthly Fee','মাসিক ফি — $mname ($mid_str) — $month/$year',$amount,$mid)");
            $success='ফি গ্রহণ সম্পন্ন!';
        }
    } else $error='সব তথ্য পূরণ করুন।';
}

$monthly_fee=monthlyFee();
// Active members with fee status for selected month
$members=db()->query("SELECT m.*, (SELECT id FROM monthly_fees WHERE member_id=m.id AND month='$sel_month' AND year='$sel_year' LIMIT 1) as fee_paid_id FROM members m WHERE m.status='active' AND m.is_donor=0 ORDER BY m.name");
$months=['01'=>'জানুয়ারি','02'=>'ফেব্রুয়ারি','03'=>'মার্চ','04'=>'এপ্রিল','05'=>'মে','06'=>'জুন','07'=>'জুলাই','08'=>'আগস্ট','09'=>'সেপ্টেম্বর','10'=>'অক্টোবর','11'=>'নভেম্বর','12'=>'ডিসেম্বর'];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>মাসিক ফি</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button><h1>মাসিক ফি ব্যবস্থাপনা</h1></div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>

    <!-- Collect Fee Form -->
    <div class="card">
      <div class="card-header"><h3><span class="material-icons">payments</span> ফি গ্রহণ করুন</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-row-3">
            <div class="form-group"><label class="form-label">সদস্য *</label>
              <select name="member_id" class="form-control" required>
                <option value="">— সদস্য বেছে নিন —</option>
                <?php $m2=db()->query("SELECT id,name,member_id FROM members WHERE status='active' AND is_donor=0 ORDER BY name");
                while($m=$m2->fetch_assoc()):?><option value="<?=$m['id']?>"><?=htmlspecialchars($m['name'])?> (<?=$m['member_id']?>)</option><?php endwhile;?>
              </select>
            </div>
            <div class="form-group"><label class="form-label">মাস *</label>
              <select name="month" class="form-control" required>
                <?php foreach($months as $k=>$v):?><option value="<?=$k?>" <?=date('m')==$k?'selected':''?>><?=$v?></option><?php endforeach;?>
              </select>
            </div>
            <div class="form-group"><label class="form-label">বছর *</label>
              <input type="number" name="year" class="form-control" value="<?=date('Y')?>" min="2020" max="<?=date('Y')+1?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">পরিমাণ (টাকা) *</label><input type="number" name="amount" class="form-control" value="<?=$monthly_fee?>" min="1" required><div class="form-hint">নির্ধারিত মাসিক ফি: ৳<?=$monthly_fee?></div></div>
            <div class="form-group"><label class="form-label">তারিখ *</label><input type="date" name="paid_date" class="form-control" value="<?=date('Y-m-d')?>" required></div>
          </div>
          <div class="form-group"><label class="form-label">মন্তব্য</label><input type="text" name="notes" class="form-control"></div>
          <button type="submit" class="btn btn-primary"><span class="material-icons">check_circle</span> ফি গ্রহণ করুন</button>
        </form>
      </div>
    </div>

    <!-- Month filter -->
    <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;align-items:center">
      <select name="month" class="form-control" style="width:160px">
        <?php foreach($months as $k=>$v):?><option value="<?=$k?>" <?=$sel_month==$k?'selected':''?>><?=$v?></option><?php endforeach;?>
      </select>
      <input type="number" name="year" class="form-control" style="width:100px" value="<?=$sel_year?>">
      <button type="submit" class="btn btn-primary btn-sm">দেখুন</button>
    </form>

    <div class="card">
      <div class="card-header"><h3><span class="material-icons">list</span> <?=$months[$sel_month]?? $sel_month?> <?=$sel_year?> — ফির অবস্থা</h3></div>
      <div class="table-responsive"><table style="min-width:600px">
        <thead><tr><th>সদস্য আইডি</th><th>নাম</th><th>ফোন</th><th>অবস্থা</th></tr></thead>
        <tbody>
        <?php while($m=$members->fetch_assoc()):?>
          <tr>
            <td><?=htmlspecialchars($m['member_id']??'—')?></td>
            <td><?=htmlspecialchars($m['name'])?></td>
            <td><?=htmlspecialchars($m['phone']??'—')?></td>
            <td><?=$m['fee_paid_id']?"<span class='badge badge-success'>পরিশোধিত</span>":"<span class='badge badge-danger'>বাকি</span>"?></td>
          </tr>
        <?php endwhile;?>
        </tbody>
      </table></div>
    </div>
  </div>
</div></body></html>
