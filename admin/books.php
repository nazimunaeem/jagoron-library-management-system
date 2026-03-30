<?php
require_once '../includes/config.php';
requireAdmin();
if(isset($_GET['delete'])&&allowDelete()){
    $bid=(int)$_GET['delete'];
    db()->query("DELETE FROM borrows WHERE book_id=$bid");
    db()->query("DELETE FROM books WHERE id=$bid");
    header('Location: books.php');exit;
}
$s=escape($_GET['s']??'');$cat=escape($_GET['cat']??'');
$w='WHERE 1=1';
if($s) $w.=" AND (title LIKE '%$s%' OR author LIKE '%$s%' OR book_id LIKE '%$s%' OR isbn LIKE '%$s%')";
if($cat) $w.=" AND category='$cat'";
$books=db()->query("SELECT * FROM books $w ORDER BY id DESC");
$cats=db()->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category!='' ORDER BY category");
// Build issued map: book_id => array of member names
$issued_map=[];
$iq=db()->query("SELECT br.book_id, m.name FROM borrows br JOIN members m ON br.member_id=m.id WHERE br.status='borrowed'");
if($iq) while($r=$iq->fetch_assoc()) $issued_map[$r['book_id']][]=$r['name'];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>বইয়ের তালিকা</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>বইয়ের তালিকা</h1>
    <a href="add_book.php" class="btn btn-primary btn-sm"><span class="material-icons">add</span> <span class="hide-xs">বই যোগ</span></a>
  </div>
  <div class="content">
    <form method="GET" class="search-bar">
      <input type="text" name="s" class="form-control" placeholder="শিরোনাম, লেখক, বই আইডি..." value="<?=htmlspecialchars($s)?>">
      <select name="cat" class="form-control" style="width:160px" onchange="this.form.submit()">
        <option value="">সব বিভাগ</option>
        <?php while($c=$cats->fetch_row()):?><option value="<?=htmlspecialchars($c[0])?>" <?=$cat==$c[0]?'selected':''?>><?=htmlspecialchars($c[0])?></option><?php endwhile;?>
      </select>
      <button type="submit" class="btn btn-primary"><span class="material-icons">search</span></button>
      <?php if($s||$cat):?><a href="books.php" class="btn btn-outline">✕</a><?php endif;?>
      <a href="export_books.php" class="btn btn-outline btn-sm"><span class="material-icons">download</span> CSV</a>
    </form>
    <div class="card">
      <div class="table-responsive" style="-webkit-overflow-scrolling:touch;overflow-x:auto">
        <table style="min-width:700px">
          <thead><tr><th>বই আইডি</th><th>শিরোনাম</th><th>লেখক</th><th>বিভাগ</th><th>তাক</th><th>কপি / অবস্থা</th><th>অ্যাকশন</th></tr></thead>
          <tbody>
          <?php if($books): while($b=$books->fetch_assoc()):
            $issued = $issued_map[$b['id']] ?? [];
            $avail  = $b['available'];
            $total  = $b['copies'];
            $issued_count = count($issued);
          ?>
            <tr>
              <td><code style="font-size:0.72rem"><?=htmlspecialchars($b['book_id']??'—')?></code></td>
              <td><strong><?=htmlspecialchars($b['title'])?></strong></td>
              <td><?=htmlspecialchars($b['author'])?></td>
              <td><?=htmlspecialchars($b['category']??'—')?></td>
              <td><?=htmlspecialchars($b['shelf']??'—')?></td>
              <td>
                <div style="line-height:1.6">
                  <?php if($avail>0):?>
                    <span class="badge badge-success">(<?=$avail?>) পাওয়া যাচ্ছে</span><br>
                  <?php endif;?>
                  <?php foreach($issued as $uname):?>
                    <span class="badge badge-danger" style="font-size:0.68rem"><?=htmlspecialchars($uname)?> (1)</span><br>
                  <?php endforeach;?>
                  <small style="color:var(--muted)">মোট: <?=$total?>টি</small>
                </div>
              </td>
              <td>
                <div class="action-btns">
                  <a href="edit_book.php?id=<?=$b['id']?>" class="btn btn-outline btn-sm"><span class="material-icons">edit</span></a>
                  <?php if(allowDelete()):?><a href="?delete=<?=$b['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('মুছবেন?')"><span class="material-icons">delete</span></a><?php endif;?>
                </div>
              </td>
            </tr>
          <?php endwhile; else:?>
            <tr><td colspan="7"><div class="empty-state"><span class="material-icons">search_off</span><p>কোনো বই নেই</p></div></td></tr>
          <?php endif;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div></body></html>
