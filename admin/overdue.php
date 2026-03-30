<?php
require_once '../includes/config.php';
requireAdmin();
$fpd=finePerDay();
$overdue=db()->query("SELECT br.*,bk.title,m.name,m.phone,m.member_id as mid FROM borrows br JOIN books bk ON br.book_id=bk.id JOIN members m ON br.member_id=m.id WHERE br.status='borrowed' AND br.due_date<CURDATE() ORDER BY br.due_date ASC");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>মেয়াদোত্তীর্ণ</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button><h1>মেয়াদোত্তীর্ণ বই</h1></div>
  <div class="content">
    <div class="alert alert-warning"><span class="material-icons" style="font-size:1rem">warning</span> জরিমানার হার: ৳<?=$fpd?> প্রতিদিন।</div>
    <div class="card"><div class="table-responsive"><table style="min-width:600px">
      <thead><tr><th>বই</th><th>সদস্য</th><th>ফোন</th><th>ফেরতের তারিখ</th><th>বিলম্ব</th><th>জরিমানা</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
      <?php while($r=$overdue->fetch_assoc()):
        $due=new DateTime($r['due_date']);$today=new DateTime();$days=$today->diff($due)->days;$fine=$days*$fpd;?>
        <tr>
          <td><?=htmlspecialchars($r['title'])?></td>
          <td><?=htmlspecialchars($r['name'])?><br><small class="badge badge-muted"><?=$r['mid']?></small></td>
          <td><?=htmlspecialchars($r['phone']??'—')?></td>
          <td><span class="badge badge-danger"><?=$r['due_date']?></span></td>
          <td><?=$days?> দিন</td>
          <td><strong class="text-danger">৳<?=$fine?></strong></td>
          <td><a href="returns.php" class="btn btn-primary btn-sm"><span class="material-icons">move_to_inbox</span> ফেরত</a></td>
        </tr>
      <?php endwhile;?>
      </tbody>
    </table></div></div>
  </div>
</div></body></html>
