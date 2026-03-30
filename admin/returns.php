<?php
require_once '../includes/config.php';
requireAdmin();
$success=$error='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['return_id'])) {
        $id=(int)$_POST['return_id'];
        $waive=(int)($_POST['waive_fine']??0);
        $borrow=db()->query("SELECT * FROM borrows WHERE id=$id AND status='borrowed'")->fetch_assoc();
        if ($borrow) {
            $today=date('Y-m-d');
            $due=new DateTime($borrow['due_date']);
            $now=new DateTime($today);
            $fine=0;
            if ($now>$due) $fine=$now->diff($due)->days * finePerDay();
            $actual_fine=$waive?0:$fine;
            db()->query("UPDATE borrows SET status='returned',return_date='$today',fine=$fine,fine_waived=$waive,fine_paid=".($actual_fine==0?1:0)." WHERE id=$id");
            db()->query("UPDATE books SET available=available+1 WHERE id=".(int)$borrow['book_id']);
            if ($actual_fine>0) {
                $desc=escape("বই ফেরত জরিমানা (ইস্যু #{$id})");
                db()->query("INSERT INTO finance (date,type,category,description,amount,member_id) VALUES ('$today','income','Fine','$desc',$actual_fine,".(int)$borrow['member_id'].")");
            }
            $success='বই সফলভাবে ফেরত হয়েছে!'.($actual_fine>0?' জরিমানা: ৳'.$actual_fine:'').($waive?' (মওকুফ)':'');
        } else { $error='রেকর্ড পাওয়া যায়নি।'; }
    }
}

$fpd=finePerDay();
$borrowed=db()->query("SELECT br.*,bk.title,m.name,m.member_id as mid FROM borrows br JOIN books bk ON br.book_id=bk.id JOIN members m ON br.member_id=m.id WHERE br.status='borrowed' ORDER BY br.due_date ASC");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>বই ফেরত</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>বই ফেরত</h1>
    <a href="borrow.php" class="btn btn-outline btn-sm"><span class="material-icons">outbox</span> বই ইস্যু</a>
  </div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <div class="alert alert-info"><span class="material-icons" style="font-size:1rem">info</span> জরিমানার হার: ৳<?=$fpd?> প্রতিদিন। অ্যাডমিন জরিমানা মওকুফ করতে পারবেন।</div>
    <div class="card">
      <div class="card-header"><h3><span class="material-icons">list</span> বর্তমানে ইস্যুকৃত বই</h3></div>
      <div class="table-responsive"><table style="min-width:600px">
        <thead><tr><th>বই</th><th>সদস্য</th><th>ইস্যু</th><th>ফেরতের তারিখ</th><th>বিলম্ব</th><th>জরিমানা</th><th>অ্যাকশন</th></tr></thead>
        <tbody>
        <?php while($r=$borrowed->fetch_assoc()):
          $due=new DateTime($r['due_date']);$today=new DateTime();
          $late=$today>$due;$days=$late?$today->diff($due)->days:0;$fine=$days*$fpd;?>
          <tr>
            <td><?=htmlspecialchars($r['title'])?></td>
            <td><?=htmlspecialchars($r['name'])?><br><small class="badge badge-muted"><?=$r['mid']?></small></td>
            <td><?=$r['borrow_date']?></td>
            <td><?=$r['due_date']?></td>
            <td><?=$late?"<span class='badge badge-danger'>$days দিন</span>":'<span class="badge badge-success">ঠিকমতো</span>'?></td>
            <td><?=$fine>0?"<strong class='text-danger'>৳$fine</strong>":'—'?></td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="return_id" value="<?=$r['id']?>">
                <?php if($fine>0):?>
                <label style="font-size:0.75rem;display:block;margin-bottom:4px">
                  <input type="checkbox" name="waive_fine" value="1"> জরিমানা মওকুফ
                </label>
                <?php endif;?>
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('বই ফেরত নিশ্চিত করুন?')">
                  <span class="material-icons">move_to_inbox</span> ফেরত
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile;?>
        </tbody>
      </table></div>
    </div>
  </div>
</div></body></html>
