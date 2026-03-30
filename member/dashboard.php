<?php
require_once '../includes/config.php';
requireMember();
$mid = (int)$_SESSION['member_id'];
$m   = db()->query("SELECT * FROM members WHERE id=$mid")->fetch_assoc();
$borrows = db()->query("SELECT br.*,bk.title FROM borrows br JOIN books bk ON br.book_id=bk.id WHERE br.member_id=$mid ORDER BY br.created_at DESC LIMIT 20");
$active_borrow = db()->query("SELECT br.*,bk.title FROM borrows br JOIN books bk ON br.book_id=bk.id WHERE br.member_id=$mid AND br.status='borrowed' LIMIT 1")->fetch_assoc();
$msg = '';
// Reissue
if (isset($_POST['reissue']) && $active_borrow && !$active_borrow['reissued']) {
    $bid = (int)$active_borrow['id'];
    $new_due = date('Y-m-d', strtotime($active_borrow['due_date'].' +5 days'));
    db()->query("UPDATE borrows SET due_date='$new_due', reissued=1 WHERE id=$bid");
    $msg = 'বই পুনরায় ইস্যু হয়েছে! নতুন ফেরতের তারিখ: '.$new_due;
    $active_borrow = db()->query("SELECT br.*,bk.title FROM borrows br JOIN books bk ON br.book_id=bk.id WHERE br.member_id=$mid AND br.status='borrowed' LIMIT 1")->fetch_assoc();
}
// Donor data
$donor_data = null;
if ($m['is_donor']) {
    $donor_data = db()->query("SELECT
        COALESCE(SUM(CASE WHEN d.type='money' THEN d.amount ELSE 0 END),0) as money_total,
        COALESCE(SUM(CASE WHEN d.type='book' THEN d.book_count ELSE 0 END),0) as book_total
        FROM donations d WHERE d.donor_id=$mid")->fetch_assoc();
}
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>সদস্য ড্যাশবোর্ড</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style>
.member-portal{max-width:800px;margin:0 auto;padding:16px}
.portal-header{background:var(--primary);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.portal-header a{color:var(--accent);text-decoration:none;font-weight:700;font-size:0.9rem;display:flex;align-items:center;gap:6px}
.portal-header .logout{color:rgba(255,255,255,0.7);font-size:0.82rem}
</style>
</head><body style="background:var(--bg)">
<div class="portal-header">
  <a href="/"><span style="font-size:1.1rem">📚</span> <?=htmlspecialchars(libName())?></a>
  <div style="display:flex;align-items:center;gap:12px">
    <span style="color:rgba(255,255,255,0.8);font-size:0.82rem">👤 <?=htmlspecialchars($m['name']??'')?></span>
    <a href="logout.php" class="logout">লগআউট</a>
  </div>
</div>

<div class="member-portal">
  <?php if ($msg): ?><div class="alert alert-success" style="margin-top:12px"><?=$msg?></div><?php endif; ?>

  <!-- Profile card -->
  <div class="card" style="margin-top:16px">
    <div class="card-header">
      <h3><span class="material-icons">badge</span> আমার প্রোফাইল</h3>
      <div style="display:flex;gap:6px">
        <a href="member_card.php" class="btn btn-outline btn-sm"><span class="material-icons">badge</span> আইডি কার্ড</a>
        <?php if ($m['is_donor']): ?>
        <a href="donor_certificate.php" class="btn btn-outline btn-sm"><span class="material-icons">workspace_premium</span> সনদ</a>
        <a href="donor_statement.php" class="btn btn-outline btn-sm"><span class="material-icons">receipt_long</span> বিবরণী</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div>
          <p><strong>নাম:</strong> <?=htmlspecialchars($m['name'])?></p>
          <p><strong>সদস্য আইডি:</strong> <?=htmlspecialchars($m['member_id']??'—')?></p>
          <p><strong>যোগদান:</strong> <?=$m['join_date']??'—'?></p>
        </div>
        <div>
          <p><strong>ফোন:</strong> <?=htmlspecialchars($m['phone']??'—')?></p>
          <p><strong>ঠিকানা:</strong> <?=htmlspecialchars($m['address']??'—')?></p>
          <p><strong>অবস্থা:</strong>
            <?=$m['status']==='active'?"<span class='badge badge-success'>সক্রিয়</span>":"<span class='badge badge-warning'>অনুমোদন বাকি</span>"?>
            <?php if ($m['is_donor']): ?><span class="badge badge-warning" style="margin-left:4px">★ দাতা সদস্য</span><?php endif; ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Donor section -->
  <?php if ($m['is_donor'] && $donor_data): ?>
  <div class="card">
    <div class="card-header">
      <h3><span class="material-icons">volunteer_activism</span> আমার দানের সারসংক্ষেপ</h3>
      <div style="display:flex;gap:6px">
        <a href="donor_certificate.php" class="btn btn-primary btn-sm"><span class="material-icons">workspace_premium</span> সনদ ডাউনলোড</a>
        <a href="donor_statement.php" class="btn btn-outline btn-sm"><span class="material-icons">receipt_long</span> বিবরণী</a>
      </div>
    </div>
    <div class="card-body" style="display:flex;gap:20px;flex-wrap:wrap">
      <div style="text-align:center;background:#f0f9f4;border:1px solid #c8e6c9;border-radius:8px;padding:16px 24px;flex:1">
        <div style="font-size:1.3rem;font-weight:700;color:var(--success)">৳<?=number_format($donor_data['money_total'])?></div>
        <div style="font-size:0.78rem;color:var(--muted)">মোট অর্থ দান</div>
      </div>
      <?php if ($donor_data['book_total'] > 0): ?>
      <div style="text-align:center;background:#e3f2fd;border:1px solid #bbdefb;border-radius:8px;padding:16px 24px;flex:1">
        <div style="font-size:1.3rem;font-weight:700;color:#1565c0"><?=$donor_data['book_total']?>টি</div>
        <div style="font-size:0.78rem;color:var(--muted)">মোট বই দান</div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Active borrow -->
  <?php if ($active_borrow): ?>
  <div class="card">
    <div class="card-header"><h3><span class="material-icons">outbox</span> বর্তমান ইস্যুকৃত বই</h3></div>
    <div class="card-body">
      <?php
      $fpd = finePerDay();
      $due = new DateTime($active_borrow['due_date']);
      $today = new DateTime();
      $late = $today > $due;
      $days = $late ? $today->diff($due)->days : 0;
      $fine = $days * $fpd;
      ?>
      <p><strong>বই:</strong> <?=htmlspecialchars($active_borrow['title'])?></p>
      <p><strong>ইস্যুর তারিখ:</strong> <?=$active_borrow['borrow_date']?></p>
      <p><strong>ফেরতের তারিখ:</strong> <span class="<?=$late?'text-danger':'text-success'?>"><?=$active_borrow['due_date']?></span></p>
      <?php if ($late): ?>
      <p><strong>জরিমানা:</strong> <span class="text-danger">৳<?=$fine?> (<?=$days?> দিন × ৳<?=$fpd?>)</span></p>
      <?php endif; ?>
      <?php if (!$active_borrow['reissued']): ?>
      <form method="POST" style="margin-top:10px">
        <button type="submit" name="reissue" class="btn btn-accent" onclick="return confirm('৫ দিন মেয়াদ বাড়াবেন?')">
          <span class="material-icons">refresh</span> পুনরায় ইস্যু (+৫ দিন)
        </button>
      </form>
      <?php else: ?>
      <p style="color:var(--muted);font-size:0.82rem;margin-top:8px">⚠️ এই বইটি ইতিমধ্যে একবার পুনরায় ইস্যু করা হয়েছে।</p>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Borrow history -->
  <div class="card">
    <div class="card-header"><h3><span class="material-icons">history</span> বই ইস্যুর ইতিহাস</h3></div>
    <div class="table-responsive"><table style="min-width:500px">
      <thead><tr><th>বই</th><th>ইস্যু</th><th>ফেরতের তারিখ</th><th>ফেরত</th><th>জরিমানা</th></tr></thead>
      <tbody>
      <?php while ($b = $borrows->fetch_assoc()): ?>
        <tr>
          <td><?=htmlspecialchars($b['title'])?></td>
          <td><?=$b['borrow_date']?></td>
          <td><?=$b['due_date']?></td>
          <td><?=$b['return_date']??'—'?></td>
          <td><?=$b['fine']>0?"৳{$b['fine']}".($b['fine_waived']?' (মওকুফ)':''):'—'?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table></div>
  </div>
</div>
</body></html>
