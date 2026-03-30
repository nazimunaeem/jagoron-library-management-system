<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isAdmin()) { header('Location: /admin/index.php'); exit; }
if (isMember()) { header('Location: /member/dashboard.php'); exit; }

$error = '';
$role = $_POST['role'] ?? $_GET['role'] ?? 'member';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = escape($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'member';

    if ($role === 'admin') {
        $r = db()->query("SELECT * FROM admins WHERE username='$username' LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_id'] = $row['id'];
                header('Location: /admin/index.php'); exit;
            }
        }
        $error = 'ভুল অ্যাডমিন তথ্য।';
    } else {
        // Member or Donor login
        $r = db()->query("SELECT * FROM members WHERE username='$username' AND status='active' LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['member_logged_in'] = true;
                $_SESSION['member_id'] = $row['id'];
                $_SESSION['member_name'] = $row['name'];
                $_SESSION['is_donor'] = (bool)$row['is_donor'];
                header('Location: /member/dashboard.php'); exit;
            }
        }
        $error = 'ভুল তথ্য অথবা সদস্যপদ এখনও সক্রিয় নয়।';
    }
}

$lib_name = libName();
$lib_tagline = libTagline();
$lib_logo = libLogo();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>লগইন — <?= htmlspecialchars($lib_name) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:16px">
      <?php if ($lib_logo): ?>
        <img src="<?= htmlspecialchars($lib_logo) ?>" style="height:50px;margin-bottom:8px"><br>
      <?php endif; ?>
      <h2>📚 <?= htmlspecialchars($lib_name) ?></h2>
      <p class="subtitle"><?= htmlspecialchars($lib_tagline) ?></p>
    </div>

    <!-- Role tabs -->
    <div class="role-tabs">
      <button type="button" class="role-tab <?= $role !== 'admin' ? 'active' : '' ?>" onclick="setRole('member')">
        সদস্য / দাতা
      </button>
      <button type="button" class="role-tab <?= $role === 'admin' ? 'active' : '' ?>" onclick="setRole('admin')">
        অ্যাডমিন
      </button>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login.php">
      <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($role) ?>">
      <div class="form-group">
        <label class="form-label">ব্যবহারকারীর নাম</label>
        <input type="text" name="username" class="form-control" required autofocus autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label">পাসওয়ার্ড</label>
        <input type="password" name="password" class="form-control" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:10px;font-size:0.95rem">
        <span class="material-icons">login</span> প্রবেশ করুন
      </button>
    </form>

    <div id="memberLinks" style="<?= $role === 'admin' ? 'display:none' : '' ?>">
      <p style="text-align:center;margin-top:14px;font-size:0.82rem">
        নতুন সদস্য? <a href="/member/register.php" style="color:var(--primary);font-weight:600">নিবন্ধন করুন →</a>
      </p>
    </div>
    <p style="text-align:center;margin-top:10px;font-size:0.8rem">
      <a href="/" style="color:var(--muted)">← পাঠাগারে ফিরে যান</a>
    </p>
  </div>
</div>
<script>
function setRole(r) {
  document.getElementById('roleInput').value = r;
  document.querySelectorAll('.role-tab').forEach((t,i) => {
    t.classList.toggle('active', (r==='admin') ? i===1 : i===0);
  });
  document.getElementById('memberLinks').style.display = r==='admin' ? 'none' : '';
}
</script>
</body>
</html>
