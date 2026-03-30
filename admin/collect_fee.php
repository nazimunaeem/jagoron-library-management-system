<?php
require_once '../includes/config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$member = db()->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();
if (!$member) { header('Location: members.php'); exit; }

$monthly_fee_default = getSetting('monthly_fee') ?? 30;
$success = $error = '';

// Get all paid months
$fees_paid = [];
$res = db()->query("SELECT month_year, amount, paid_date FROM monthly_fees WHERE member_id=$id ORDER BY month_year DESC");
$fee_history = [];
while ($r = $res->fetch_assoc()) {
    $fees_paid[] = $r['month_year'];
    $fee_history[] = $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month_year = escape($_POST['month_year']);
    $amount = (float)$_POST['amount'];
    $paid_date = escape($_POST['paid_date'] ?? date('Y-m-d'));
    $notes = escape($_POST['notes'] ?? '');
    $collected_by = escape($_SESSION['admin_name'] ?? 'Admin');

    if (!$month_year || $amount <= 0) {
        $error = 'মাস ও পরিমাণ আবশ্যক।';
    } elseif (in_array($month_year, $fees_paid)) {
        $error = "এই মাসের ফি ইতিমধ্যে সংগ্রহ হয়েছে।";
    } else {
        db()->query("INSERT INTO monthly_fees (member_id,month_year,amount,paid_date,collected_by,notes)
            VALUES ($id,'$month_year',$amount,'$paid_date','$collected_by','$notes')");
        // Add to finance
        $desc = db()->real_escape_string("মাসিক ফি ($month_year) — {$member['name']} ({$member['member_id']})");
        db()->query("INSERT INTO finance (date,type,category,description,amount,member_id,ref_type)
            VALUES ('$paid_date','income','মাসিক ফি','$desc',$amount,$id,'monthly_fee')");
        // Update member reg_fee if not set
        $success = "✅ ৳$amount মাসিক ফি ($month_year) সংগ্রহ হয়েছে এবং আয়ে যোগ হয়েছে।";
        // Refresh
        $fees_paid = [];
        $fee_history = [];
        $res2 = db()->query("SELECT month_year, amount, paid_date FROM monthly_fees WHERE member_id=$id ORDER BY month_year DESC");
        while ($r = $res2->fetch_assoc()) { $fees_paid[] = $r['month_year']; $fee_history[] = $r; }
    }
}

// Generate months grid (current year + prev year)
$months_list = [];
for ($i = 11; $i >= -1; $i--) {
    $ts = strtotime("-$i months");
    $months_list[] = ['ym' => date('Y-m', $ts), 'label' => date('M Y', $ts)];
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>মাসিক ফি সংগ্রহ — <?= LIBRARY_NAME_EN ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <button class="hamburger" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <h1>💰 মাসিক ফি সংগ্রহ</h1>
        <a href="members.php" class="btn btn-outline btn-sm">← ফিরুন</a>
    </div>
    <div class="content">
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <!-- Member Info -->
        <div class="card" style="margin-bottom:16px;">
            <div class="card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <div class="member-mini-avatar"><?= mb_strtoupper(mb_substr($member['name'],0,1)) ?></div>
                <div>
                    <strong><?= htmlspecialchars($member['name']) ?></strong><br>
                    <span style="font-size:0.83rem;color:var(--text-muted);">
                        <?= htmlspecialchars($member['member_id']) ?> &nbsp;|&nbsp;
                        <?php if ($member['father_name']): ?>পিতা: <?= htmlspecialchars($member['father_name']) ?> &nbsp;|&nbsp;<?php endif; ?>
                        <?= htmlspecialchars($member['phone'] ?? '') ?>
                    </span>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
            <!-- Collect Form -->
            <div class="card">
                <div class="card-header"><h3>নতুন ফি সংগ্রহ</h3></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">মাস * </label>
                            <select name="month_year" class="form-control" required>
                                <option value="">— মাস বেছে নিন —</option>
                                <?php foreach ($months_list as $ml):
                                    $paid = in_array($ml['ym'], $fees_paid);
                                ?>
                                <option value="<?= $ml['ym'] ?>" <?= $paid ? 'disabled' : '' ?>>
                                    <?= $ml['label'] ?> <?= $paid ? '✓ পরিশোধিত' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">পরিমাণ (৳) *</label>
                            <input type="number" name="amount" class="form-control" value="<?= $monthly_fee_default ?>" min="1" step="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">পরিশোধের তারিখ</label>
                            <input type="date" name="paid_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">নোট</label>
                            <input type="text" name="notes" class="form-control" placeholder="ঐচ্ছিক নোট">
                        </div>
                        <button type="submit" class="btn btn-accent">💰 ফি সংগ্রহ করুন</button>
                    </form>
                </div>
            </div>

            <!-- Fee History -->
            <div class="card">
                <div class="card-header"><h3>ফি স্ট্যাটাস (গত ১২ মাস)</h3></div>
                <div class="card-body">
                    <div class="fee-month-grid">
                    <?php foreach (array_reverse($months_list) as $ml):
                        $isPaid = in_array($ml['ym'], $fees_paid);
                        $isPast = $ml['ym'] <= date('Y-m');
                        $cls = $isPaid ? 'paid' : ($isPast ? 'due' : 'future');
                    ?>
                        <div class="fee-month-chip <?= $cls ?>">
                            <?= $isPaid ? '✓' : ($isPast ? '✗' : '·') ?>
                            <br><small><?= $ml['ym'] ?></small>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <?php if ($fee_history): ?>
                    <table style="margin-top:14px;font-size:0.82rem;">
                        <thead><tr><th>মাস</th><th>পরিমাণ</th><th>তারিখ</th></tr></thead>
                        <tbody>
                        <?php foreach ($fee_history as $fh): ?>
                            <tr><td><?= $fh['month_year'] ?></td><td>৳<?= $fh['amount'] ?></td><td><?= $fh['paid_date'] ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('overlay').classList.add('active');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('active');}
</script>
</body>
</html>
