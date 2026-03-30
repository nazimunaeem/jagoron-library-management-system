<?php
require_once '../includes/config.php';
requireAdmin();
$id=(int)($_GET['id']??0);
$b=db()->query("SELECT * FROM books WHERE id=$id")->fetch_assoc();
if(!$b){header('Location: books.php');exit;}
$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=escape($_POST['title']);$author=escape($_POST['author']);
    $publisher=escape($_POST['publisher']??'');$isbn=escape($_POST['isbn']??'');
    $category=escape($_POST['category']??'');$year=(int)($_POST['year']??0);
    $copies=max(1,(int)$_POST['copies']);$shelf=escape($_POST['shelf']??'');
    $desc=escape($_POST['description']??'');
    $diff=$copies-$b['copies'];$new_avail=max(0,$b['available']+$diff);
    db()->query("UPDATE books SET title='$title',author='$author',publisher='$publisher',isbn='$isbn',category='$category',year=".($year?$year:'NULL').",copies=$copies,available=$new_avail,shelf='$shelf',description='$desc' WHERE id=$id");
    $success='আপডেট হয়েছে!';
    $b=db()->query("SELECT * FROM books WHERE id=$id")->fetch_assoc();
}
$cats=['সাহিত্য','বিজ্ঞান','ইতিহাস','ধর্ম','শিশু','জীবনী','রাজনীতি','দর্শন','শিল্পকলা','গল্প','উপন্যাস','কবিতা','প্রবন্ধ','অন্যান্য'];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>বই সম্পাদনা</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>বই সম্পাদনা</h1><a href="books.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
  </div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label class="form-label">বইয়ের নাম *</label><input type="text" name="title" class="form-control" value="<?=htmlspecialchars($b['title'])?>" required></div>
          <div class="form-group"><label class="form-label">লেখক *</label><input type="text" name="author" class="form-control" value="<?=htmlspecialchars($b['author'])?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">প্রকাশক</label><input type="text" name="publisher" class="form-control" value="<?=htmlspecialchars($b['publisher']??'')?>"></div>
          <div class="form-group"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" value="<?=htmlspecialchars($b['isbn']??'')?>"></div>
        </div>
        <div class="form-row-3">
          <div class="form-group"><label class="form-label">বিভাগ</label>
            <select name="category" class="form-control"><option value="">— নির্বাচন —</option>
              <?php foreach($cats as $c):?><option value="<?=$c?>" <?=$b['category']==$c?'selected':''?>><?=$c?></option><?php endforeach;?>
            </select>
          </div>
          <div class="form-group"><label class="form-label">সাল</label><input type="number" name="year" class="form-control" value="<?=$b['year']?>"></div>
          <div class="form-group"><label class="form-label">কপি</label><input type="number" name="copies" class="form-control" value="<?=$b['copies']?>" min="1"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">তাক</label><input type="text" name="shelf" class="form-control" value="<?=htmlspecialchars($b['shelf']??'')?>"></div>
          <div class="form-group"><label class="form-label">বিবরণ</label><textarea name="description" class="form-control" rows="2"><?=htmlspecialchars($b['description']??'')?></textarea></div>
        </div>
        <button type="submit" class="btn btn-primary"><span class="material-icons">save</span> সংরক্ষণ করুন</button>
      </form>
    </div></div>
  </div>
</div></body></html>
