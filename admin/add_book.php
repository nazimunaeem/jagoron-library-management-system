<?php
require_once '../includes/config.php';
requireAdmin();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id   = escape($_POST['book_id'] ?? '');
    $title     = escape($_POST['title'] ?? '');
    $author    = escape($_POST['author'] ?? '');
    $publisher = escape($_POST['publisher'] ?? '');
    $isbn      = escape($_POST['isbn'] ?? '');
    $category  = escape($_POST['category'] ?? '');
    $year      = (int)($_POST['year'] ?? 0);
    $copies    = max(1, (int)($_POST['copies'] ?? 1));
    $shelf     = escape($_POST['shelf'] ?? '');
    $desc      = escape($_POST['description'] ?? '');
    if ($title && $author && $book_id) {
        $yr = $year ?: 'NULL';
        $chk = db()->query("SELECT id FROM books WHERE book_id='$book_id'")->num_rows;
        if ($chk) { $error = "এই বই আইডি ($book_id) ইতিমধ্যে আছে।"; }
        else {
            db()->query("INSERT INTO books (book_id,title,author,publisher,isbn,category,year,copies,available,shelf,description) VALUES ('$book_id','$title','$author','$publisher','$isbn','$category',$yr,$copies,$copies,'$shelf','$desc')");
            $success = "বই যোগ হয়েছে! বই আইডি: <strong>$book_id</strong>";
        }
    } else { $error = 'শিরোনাম, লেখক ও বই আইডি আবশ্যক।'; }
}
$next_bid = nextBookId();
$cats = ['সাহিত্য','বিজ্ঞান','ইতিহাস','ধর্ম','শিশু','জীবনী','রাজনীতি','দর্শন','শিল্পকলা','গল্প','উপন্যাস','কবিতা','প্রবন্ধ','অন্যান্য'];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>বই যোগ</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>নতুন বই যোগ</h1><a href="books.php" class="btn btn-outline btn-sm">← ফিরে যান</a></div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <div class="card"><div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">বই আইডি *</label>
            <input type="text" name="book_id" class="form-control" value="<?= $next_bid ?>" required>
            <div class="form-hint">স্বয়ংক্রিয়: <?= $next_bid ?> (পরিবর্তন করা যাবে)</div>
          </div>
          <div class="form-group"><label class="form-label">বইয়ের নাম *</label><input type="text" name="title" class="form-control" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">লেখক *</label><input type="text" name="author" class="form-control" required></div>
          <div class="form-group"><label class="form-label">প্রকাশক</label><input type="text" name="publisher" class="form-control"></div>
        </div>
        <div class="form-row-3">
          <div class="form-group"><label class="form-label">বিভাগ</label>
            <select name="category" class="form-control"><option value="">— নির্বাচন —</option>
              <?php foreach($cats as $c):?><option value="<?=$c?>"><?=$c?></option><?php endforeach;?>
            </select></div>
          <div class="form-group"><label class="form-label">সাল</label><input type="number" name="year" class="form-control" min="1800" max="<?=date('Y')?>"></div>
          <div class="form-group"><label class="form-label">কপির সংখ্যা</label><input type="number" name="copies" class="form-control" value="1" min="1"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">তাক নম্বর</label><input type="text" name="shelf" class="form-control" placeholder="যেমন: A-01"></div>
          <div class="form-group"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">বিবরণ</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        <button type="submit" class="btn btn-primary"><span class="material-icons">add</span> বই যোগ করুন</button>
      </form>
    </div></div>
  </div>
</div></body></html>
