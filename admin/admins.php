<?php
require_once '../includes/config.php';
requireAdmin();
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $uname=escape($_POST['username']);$name=escape($_POST['name']);$pass=$_POST['password']??'';
    if($uname&&$pass){
        $h=password_hash($pass,PASSWORD_DEFAULT);
        $chk=db()->query("SELECT id FROM admins WHERE username='$uname'")->num_rows;
        if($chk){$error='এই username ইতিমধ্যে আছে।';}
        else{db()->query("INSERT INTO admins (username,password,name) VALUES ('$uname','$h','$name')");$success='নতুন অ্যাডমিন যোগ হয়েছে!';}
    } else $error='username ও পাসওয়ার্ড আবশ্যক।';
}
if(isset($_GET['delete'])&&allowDelete()){
    $id=(int)$_GET['delete'];
    if($id!=(int)$_SESSION['admin_id']) db()->query("DELETE FROM admins WHERE id=$id");
    header('Location: admins.php');exit;
}
$admins=db()->query("SELECT id,username,name,created_at FROM admins ORDER BY id");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>অ্যাডমিন</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button><h1>অ্যাডমিন ব্যবস্থাপনা</h1></div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <div class="card">
      <div class="card-header"><h3>নতুন অ্যাডমিন যোগ করুন</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-row-3">
            <div class="form-group"><label class="form-label">নাম</label><input type="text" name="name" class="form-control"></div>
            <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
            <div class="form-group"><label class="form-label">পাসওয়ার্ড *</label><input type="password" name="password" class="form-control" required></div>
          </div>
          <button type="submit" class="btn btn-primary"><span class="material-icons">person_add</span> অ্যাডমিন যোগ</button>
        </form>
      </div>
    </div>
    <div class="card"><table>
      <thead><tr><th>নাম</th><th>Username</th><th>যোগদান</th><th></th></tr></thead>
      <tbody>
      <?php while($a=$admins->fetch_assoc()):?>
        <tr>
          <td><?=htmlspecialchars($a['name']??'—')?></td>
          <td><?=htmlspecialchars($a['username'])?> <?=$a['id']==$_SESSION['admin_id']?"<span class='badge badge-info'>আপনি</span>":''?></td>
          <td><?=date('d/m/Y',strtotime($a['created_at']))?></td>
          <td><?php if($a['id']!=(int)$_SESSION['admin_id']&&allowDelete()):?><a href="?delete=<?=$a['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('মুছবেন?')"><span class="material-icons">delete</span></a><?php endif;?></td>
        </tr>
      <?php endwhile;?>
      </tbody>
    </table></div>
  </div>
</div></body></html>
