<?php
require_once '../includes/config.php';
requireAdmin();
$success=$error='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $book_id=(int)$_POST['book_id'];
    $member_id=(int)$_POST['member_id'];
    $borrow_date=escape($_POST['borrow_date']);
    $due_date=escape($_POST['due_date']);

    // Check member already has a book
    $has_book = db()->query("SELECT id FROM borrows WHERE member_id=$member_id AND status='borrowed' LIMIT 1")->num_rows;
    if ($has_book) { $error='এই সদস্য ইতিমধ্যে একটি বই ইস্যু করেছেন। আগে ফেরত দিতে হবে।'; }
    else {
        $book = db()->query("SELECT * FROM books WHERE id=$book_id")->fetch_assoc();
        $member = db()->query("SELECT * FROM members WHERE id=$member_id AND status='active'")->fetch_assoc();
        if (!$book) $error='বই পাওয়া যায়নি।';
        elseif (!$member) $error='সদস্য সক্রিয় নন।';
        elseif ($book['available']<1) $error='কোনো কপি পাওয়া যাচ্ছে না।';
        else {
            db()->query("INSERT INTO borrows (book_id,member_id,borrow_date,due_date) VALUES ($book_id,$member_id,'$borrow_date','$due_date')");
            db()->query("UPDATE books SET available=available-1 WHERE id=$book_id");
            $success='বই সফলভাবে ইস্যু করা হয়েছে!';
        }
    }
}

$issue_days = issueDays();
$issued = db()->query("SELECT br.*,bk.title,m.name,m.member_id as mid FROM borrows br JOIN books bk ON br.book_id=bk.id JOIN members m ON br.member_id=m.id WHERE br.status='borrowed' ORDER BY br.due_date ASC");
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>বই ইস্যু</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>বই ইস্যু করুন</h1>
    <a href="returns.php" class="btn btn-outline btn-sm"><span class="material-icons">move_to_inbox</span> বই ফেরত</a>
  </div>
  <div class="content">
    <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
    <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
    <div class="card">
      <div class="card-header"><h3><span class="material-icons">outbox</span> নতুন ইস্যু</h3></div>
      <div class="card-body">
        <form method="POST" id="borrowForm">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">সদস্য (নাম বা আইডি লিখুন) *</label>
              <div class="autocomplete-wrap">
                <input type="text" id="memberInput" class="form-control" placeholder="সদস্যের নাম বা আইডি লিখুন..." autocomplete="off">
                <input type="hidden" name="member_id" id="member_id" required>
                <div class="autocomplete-list" id="memberList" style="display:none"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">বই (নাম বা আইডি লিখুন) *</label>
              <div class="autocomplete-wrap">
                <input type="text" id="bookInput" class="form-control" placeholder="বইয়ের নাম বা আইডি লিখুন..." autocomplete="off">
                <input type="hidden" name="book_id" id="book_id" required>
                <div class="autocomplete-list" id="bookList" style="display:none"></div>
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">ইস্যুর তারিখ *</label>
              <input type="date" name="borrow_date" class="form-control" value="<?=date('Y-m-d')?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">ফেরতের তারিখ *</label>
              <input type="date" name="due_date" id="due_date" class="form-control" value="<?=date('Y-m-d',strtotime("+$issue_days days"))?>" required>
            </div>
          </div>
          <button type="submit" class="btn btn-accent"><span class="material-icons">outbox</span> ইস্যু করুন</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3><span class="material-icons">list</span> বর্তমানে ইস্যুকৃত বই</h3></div>
      <div class="table-responsive"><table style="min-width:600px">
        <thead><tr><th>বই</th><th>সদস্য</th><th>ইস্যু তারিখ</th><th>ফেরতের তারিখ</th><th>অবস্থা</th></tr></thead>
        <tbody>
        <?php while($r=$issued->fetch_assoc()):
          $od=strtotime($r['due_date'])<time();?>
          <tr>
            <td><?=htmlspecialchars($r['title'])?></td>
            <td><?=htmlspecialchars($r['name'])?><br><small class="badge badge-muted"><?=$r['mid']?></small></td>
            <td><?=$r['borrow_date']?></td>
            <td><?=$r['due_date']?></td>
            <td><?=$od?'<span class="badge badge-danger">মেয়াদোত্তীর্ণ</span>':'<span class="badge badge-success">সময়মতো</span>'?></td>
          </tr>
        <?php endwhile;?>
        </tbody>
      </table></div>
    </div>
  </div>
</div>
<script>
function autocomplete(inputId, listId, hiddenId, url) {
    const inp = document.getElementById(inputId);
    const lst = document.getElementById(listId);
    const hid = document.getElementById(hiddenId);
    inp.addEventListener('input', async function() {
        const q = this.value.trim();
        if (q.length < 1) { lst.style.display='none'; return; }
        const r = await fetch('../includes/search.php?type='+inputId.replace('Input','')+'&q='+encodeURIComponent(q));
        const data = await r.json();
        if (!data.length) { lst.style.display='none'; return; }
        lst.innerHTML = data.map(d=>`<div class="autocomplete-item" data-id="${d.id}" data-label="${d.label}">${d.label}</div>`).join('');
        lst.style.display='block';
        lst.querySelectorAll('.autocomplete-item').forEach(el=>{
            el.onclick=()=>{ inp.value=el.dataset.label; hid.value=el.dataset.id; lst.style.display='none'; }
        });
    });
    document.addEventListener('click', e=>{ if(!inp.contains(e.target)&&!lst.contains(e.target)) lst.style.display='none'; });
}
autocomplete('memberInput','memberList','member_id','');
autocomplete('bookInput','bookList','book_id','');
</script>
</body></html>
