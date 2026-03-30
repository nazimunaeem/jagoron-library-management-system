<?php
require_once '../includes/config.php';
requireAdmin();
$id=(int)($_GET['id']??0);$is_new=isset($_GET['new']);
$page=['title'=>'','slug'=>'','content'=>'','is_published'=>1,'sort_order'=>0];
if(!$is_new&&$id) $page=db()->query("SELECT * FROM pages WHERE id=$id")->fetch_assoc()?:$page;
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=escape($_POST['title']);$slug=escape(preg_replace('/[^a-z0-9-]/','',strtolower($_POST['slug'])));
    $content=db()->real_escape_string($_POST['content']??'');
    $pub=(int)($_POST['is_published']??0);$sort=(int)($_POST['sort_order']??0);
    if($title&&$slug){
        if($is_new||$id==0) db()->query("INSERT INTO pages (title,slug,content,is_published,sort_order) VALUES ('$title','$slug','$content',$pub,$sort)");
        else db()->query("UPDATE pages SET title='$title',slug='$slug',content='$content',is_published=$pub,sort_order=$sort WHERE id=$id");
        $success='পেজ সংরক্ষণ হয়েছে!';
        if($is_new){$id=db()->insert_id;$is_new=false;$page=db()->query("SELECT * FROM pages WHERE id=$id")->fetch_assoc();}
    } else $error='শিরোনাম ও স্লাগ আবশ্যক।';
}
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>পেজ সম্পাদনা</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1><?=$is_new?'নতুন পেজ':'পেজ সম্পাদনা'?></h1><a href="pages.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
  </div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label class="form-label">শিরোনাম *</label><input type="text" name="title" class="form-control" value="<?=htmlspecialchars($page['title'])?>" required></div>
          <div class="form-group"><label class="form-label">স্লাগ (URL) *</label><input type="text" name="slug" class="form-control" value="<?=htmlspecialchars($page['slug']??'')?>" required><div class="form-hint">শুধু ইংরেজি হরফ ও হাইফেন, যেমন: about-us</div></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">ক্রম</label><input type="number" name="sort_order" class="form-control" value="<?=$page['sort_order']??0?>"></div>
          <div class="form-group"><label class="form-label">অবস্থা</label>
            <select name="is_published" class="form-control">
              <option value="1" <?=($page['is_published']??1)?'selected':''?>>প্রকাশিত</option>
              <option value="0" <?=!($page['is_published']??1)?'selected':''?>>খসড়া</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">বিষয়বস্তু</label>
          <textarea name="content" id="pageContent" class="form-control" rows="12"><?=htmlspecialchars($page['content']??'')?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><span class="material-icons">save</span> সংরক্ষণ করুন</button>
      </form>
    </div></div>
  </div>
</div>
<script>
tinymce.init({selector:'#pageContent',plugins:'lists link',toolbar:'bold italic | bullist numlist | link',menubar:false,height:300,language:'bn_BD'});
</script>
</body></html>
