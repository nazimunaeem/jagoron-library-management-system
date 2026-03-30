<?php
require_once '../includes/config.php';
requireAdmin();
$donor_id = (int)($_GET['donor_id'] ?? 0);
$donor = db()->query("SELECT * FROM members WHERE id=$donor_id AND is_donor=1")->fetch_assoc();
if (!$donor) { header('Location: donors.php'); exit; }
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date       = escape($_POST['date']);
    $type       = escape($_POST['type']);
    $amount     = (int)($_POST['amount'] ?? 0);
    $book_count = (int)($_POST['book_count'] ?? 0);
    $desc       = escape($_POST['description']);

    if (!$desc) { $error = 'বিবরণ আবশ্যক।'; }
    elseif ($type === 'money' && $amount <= 0) { $error = 'অর্থের পরিমাণ দিন।'; }
    elseif ($type === 'book' && $book_count <= 0) { $error = 'বইয়ের সংখ্যা দিন।'; }
    else {
        $dname = escape($donor['name']);

        // 1. Insert donation record
        db()->query("INSERT INTO donations (donor_id,date,type,amount,book_count,description)
            VALUES ($donor_id,'$date','$type',$amount,$book_count,'$desc')");
        $don_id = db()->insert_id;

        if ($type === 'money' && $amount > 0) {
            // Money: single income entry
            db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,donation_id)
                VALUES ('$date','income','Donation','অর্থ দান — $dname — $desc',$amount,$donor_id,$don_id)");
        }

        if ($type === 'book' && $amount > 0) {
            // Book with estimated value: income + matching expense = net 0 on balance
            // Income: book received (asset)
            db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,donation_id)
                VALUES ('$date','income','Book Donation','বই দান (আনু. মূল্য) — $dname — {$book_count}টি বই — $desc',$amount,$donor_id,$don_id)");
            // Expense: book cost (purchase equivalent)
            db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,donation_id)
                VALUES ('$date','expense','Book Donation','বই দান — ব্যয় সমন্বয় — $dname — {$book_count}টি বই',$amount,$donor_id,$don_id)");
        }

        $success = 'দান সফলভাবে যোগ হয়েছে!';
    }
}

$hist = db()->query("SELECT * FROM donations WHERE donor_id=$donor_id ORDER BY date DESC");
$money_total = db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donor_id=$donor_id AND type='money'")->fetch_row()[0];
$book_total  = db()->query("SELECT COALESCE(SUM(book_count),0) FROM donations WHERE donor_id=$donor_id AND type='book'")->fetch_row()[0];
$other_total = db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE donor_id=$donor_id AND type='other'")->fetch_row()[0];
?>
<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>দান যোগ</title><link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head><body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
  <div class="topbar"><button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
    <h1>দান যোগ — <?= htmlspecialchars($donor['name']) ?></h1>
    <a href="donors.php" class="btn btn-outline btn-sm">← ফিরে যান</a>
  </div>
  <div class="content">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
      <div class="stat-card"><div class="stat-icon green"><span class="material-icons">payments</span></div>
        <div class="stat-info"><h3>৳<?= number_format($money_total) ?></h3><p>মোট অর্থ দান</p></div></div>
      <div class="stat-card"><div class="stat-icon blue"><span class="material-icons">menu_book</span></div>
        <div class="stat-info"><h3><?= $book_total ?>টি</h3><p>মোট বই দান</p></div></div>
      <div class="stat-card"><div class="stat-icon orange"><span class="material-icons">card_giftcard</span></div>
        <div class="stat-info"><h3>৳<?= number_format($other_total) ?></h3><p>অন্যান্য</p></div></div>
    </div>

    <div class="alert alert-info">
      <span class="material-icons" style="font-size:1rem">info</span>
      <div>বই দানের আনুমানিক মূল্য দিলে <strong>আয় ও ব্যয় উভয়ে সমান পরিমাণ</strong> যোগ হবে — ফলে ব্যালেন্সে কোনো পরিবর্তন হবে না কিন্তু বই ক্রয়ের হিসাব সামঞ্জস্যপূর্ণ থাকবে।</div>
    </div>

    <div class="card"><div class="card-header"><h3><span class="material-icons">add_circle</span> নতুন দান যোগ</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-row">
            <div class="form-group"><label class="form-label">তারিখ *</label>
              <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            <div class="form-group"><label class="form-label">দানের ধরন *</label>
              <select name="type" id="donType" class="form-control" onchange="toggleFields()" required>
                <option value="money">💵 অর্থ</option>
                <option value="book">📚 বই</option>
                <option value="other">🎁 অন্যান্য</option>
              </select></div>
          </div>
          <div class="form-row">
            <div class="form-group" id="amountField">
              <label class="form-label" id="amountLabel">পরিমাণ (টাকা) *</label>
              <input type="number" name="amount" id="amountInput" class="form-control" min="0">
            </div>
            <div class="form-group" id="bookCountField" style="display:none">
              <label class="form-label">বইয়ের সংখ্যা *</label>
              <input type="number" name="book_count" id="bookCountInput" class="form-control" min="0">
            </div>
          </div>
          <div class="form-group"><label class="form-label">বিবরণ *</label>
            <input type="text" name="description" class="form-control" required
              placeholder="যেমন: বার্ষিক অনুদান / ১০টি উপন্যাস দান"></div>
          <button type="submit" class="btn btn-primary"><span class="material-icons">add</span> দান যোগ করুন</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3><span class="material-icons">history</span> দানের ইতিহাস</h3></div>
      <div class="table-responsive"><table style="min-width:500px">
        <thead><tr><th>তারিখ</th><th>ধরন</th><th>পরিমাণ / বই</th><th>বিবরণ</th><th></th></tr></thead>
        <tbody>
        <?php while ($h = $hist->fetch_assoc()): ?>
          <tr>
            <td><?= $h['date'] ?></td>
            <td><?php if ($h['type']==='money'): ?><span class="badge badge-success">অর্থ</span>
              <?php elseif ($h['type']==='book'): ?><span class="badge badge-info">বই</span>
              <?php else: ?><span class="badge badge-muted">অন্যান্য</span><?php endif; ?></td>
            <td>
              <?php if ($h['type']==='money'): ?>৳<?= number_format($h['amount']) ?>
              <?php elseif ($h['type']==='book'): ?>
                <?= ($h['book_count']??0) ?>টি বই<?= $h['amount']>0?' (আনু. ৳'.number_format($h['amount']).')':'' ?>
              <?php else: ?>৳<?= number_format($h['amount']) ?><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($h['description'] ?? '—') ?></td>
            <td><?php if (allowDelete()): ?>
              <a href="?delete_donation=<?= $h['id'] ?>&donor_id=<?= $donor_id ?>" class="btn btn-danger btn-sm"
                onclick="return confirm('এই দান মুছবেন? আর্থিক হিসাব থেকেও মুছে যাবে।')">
                <span class="material-icons">delete</span></a>
            <?php endif; ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</div>
<?php
// Delete donation + linked finance entries
if (isset($_GET['delete_donation']) && allowDelete()) {
    $did = (int)$_GET['delete_donation'];
    db()->query("DELETE FROM finance WHERE donation_id=$did");
    db()->query("DELETE FROM donations WHERE id=$did AND donor_id=$donor_id");
    header("Location: add_donation.php?donor_id=$donor_id"); exit;
}
?>
<script>
function toggleFields() {
    const t = document.getElementById('donType').value;
    const af = document.getElementById('amountField');
    const bf = document.getElementById('bookCountField');
    const al = document.getElementById('amountLabel');
    const ai = document.getElementById('amountInput');
    const bi = document.getElementById('bookCountInput');
    if (t === 'book') {
        af.style.display='block'; al.textContent='আনুমানিক মূল্য (ঐচ্ছিক — ব্যালেন্সে প্রভাব নেই)';
        ai.required=false; ai.min=0;
        bf.style.display='block'; bi.required=true;
    } else if (t === 'other') {
        af.style.display='block'; al.textContent='পরিমাণ (টাকা)';
        ai.required=false; bf.style.display='none'; bi.required=false;
    } else {
        af.style.display='block'; al.textContent='পরিমাণ (টাকা) *';
        ai.required=true; ai.min=1; bf.style.display='none'; bi.required=false;
    }
}
</script>
</body></html>
