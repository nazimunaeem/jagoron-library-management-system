<?php
require_once '../includes/config.php';
requireAdmin();
if(isset($_GET['delete'])&&allowDelete()){$id=(int)$_GET['delete'];db()->query("DELETE FROM pages WHERE id=$id");header('Location: pages.php');exit;}
$pages=db()->query("SELECT * FROM pages ORDER BY sort_order,id");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>পেজ ম্যানেজার</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php';?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>পেজ ম্যানেজার</h1>
    <a href="edit_page.php?new=1" class="btn btn-primary btn-sm"><span class="material-icons">add</span> নতুন পেজ</a>
  </div>
  <div class="content">
    <div class="card"><table>
      <thead><tr><th>#</th><th>শিরোনাম</th><th>স্লাগ</th><th>অবস্থা</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
      <?php while($p=$pages->fetch_assoc()):?>
        <tr>
          <td><?=$p['id']?></td>
          <td><?=htmlspecialchars($p['title'])?></td>
          <td><code>/page/<?=htmlspecialchars($p['slug'])?></code></td>
          <td><?=$p['is_published']?"<span class='badge badge-success'>প্রকাশিত</span>":"<span class='badge badge-muted'>খসড়া</span>"?></td>
          <td style="white-space:nowrap">
            <a href="edit_page.php?id=<?=$p['id']?>" class="btn btn-outline btn-sm"><span class="material-icons">edit</span></a>
            <a href="../page.php?slug=<?=$p['slug']?>" class="btn btn-outline btn-sm" target="_blank"><span class="material-icons">visibility</span></a>
            <?php if(allowDelete()):?><a href="?delete=<?=$p['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('মুছবেন?')"><span class="material-icons">delete</span></a><?php endif;?>
          </td>
        </tr>
      <?php endwhile;?>
      </tbody>
    </table></div>
  </div>
</div></body></html>
