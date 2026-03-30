<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <?php $logo = libLogo(); ?>
    <div class="logo-img"><?= $logo ? '<img src="'.htmlspecialchars($logo).'" style="width:36px;height:36px;border-radius:50%;object-fit:cover">' : '📚' ?></div>
    <div class="sidebar-logo-text">
      <h2><?= htmlspecialchars(libName()) ?></h2>
      <p><?= htmlspecialchars(libTagline()) ?></p>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">প্রধান</div>
    <a href="index.php" class="nav-link <?= $cur=='index.php'?'active':'' ?>"><span class="material-icons">dashboard</span> ড্যাশবোর্ড</a>

    <div class="nav-section">বই</div>
    <a href="books.php" class="nav-link <?= $cur=='books.php'?'active':'' ?>"><span class="material-icons">menu_book</span> বইয়ের তালিকা</a>
    <a href="add_book.php" class="nav-link <?= $cur=='add_book.php'?'active':'' ?>"><span class="material-icons">add_circle</span> বই যোগ করুন</a>
    <a href="borrow.php" class="nav-link <?= $cur=='borrow.php'?'active':'' ?>"><span class="material-icons">outbox</span> বই ইস্যু</a>
    <a href="returns.php" class="nav-link <?= $cur=='returns.php'?'active':'' ?>"><span class="material-icons">move_to_inbox</span> বই ফেরত</a>
    <a href="overdue.php" class="nav-link <?= $cur=='overdue.php'?'active':'' ?>"><span class="material-icons">warning</span> মেয়াদোত্তীর্ণ</a>

    <div class="nav-section">সদস্য</div>
    <a href="members.php" class="nav-link <?= $cur=='members.php'?'active':'' ?>"><span class="material-icons">groups</span> সদস্যতালিকা</a>
    <a href="add_member.php" class="nav-link <?= $cur=='add_member.php'?'active':'' ?>"><span class="material-icons">person_add</span> সদস্য যোগ</a>
    <a href="approve_member.php" class="nav-link <?= $cur=='approve_member.php'?'active':'' ?>"><span class="material-icons">how_to_reg</span> অনুমোদন</a>
    <a href="monthly_fees.php" class="nav-link <?= $cur=='monthly_fees.php'?'active':'' ?>"><span class="material-icons">payments</span> মাসিক ফি</a>
    <a href="bulk_cards.php" class="nav-link <?= $cur=='bulk_cards.php'?'active':'' ?>"><span class="material-icons">badge</span> বাল্ক কার্ড প্রিন্ট</a>

    <div class="nav-section">দাতা</div>
    <a href="donors.php" class="nav-link <?= $cur=='donors.php'?'active':'' ?>"><span class="material-icons">volunteer_activism</span> দাতা তালিকা</a>
    <a href="add_donor.php" class="nav-link <?= $cur=='add_donor.php'?'active':'' ?>"><span class="material-icons">favorite</span> দাতা যোগ</a>

    <div class="nav-section">অর্থায়ন</div>
    <a href="finance.php" class="nav-link <?= $cur=='finance.php'?'active':'' ?>"><span class="material-icons">account_balance_wallet</span> আর্থিক হিসাব</a>
    <a href="add_finance.php" class="nav-link <?= $cur=='add_finance.php'?'active':'' ?>"><span class="material-icons">add_card</span> এন্ট্রি যোগ</a>

    <div class="nav-section">কন্টেন্ট</div>
    <a href="about_page.php" class="nav-link <?= $cur=='about_page.php'?'active':'' ?>"><span class="material-icons">info</span> সম্পর্কে ও নিয়মকানুন</a>
    <a href="about_page.php" class="nav-link <?= $cur=='about_page.php'?'active':'' ?>"><span class="material-icons">info</span> পাঠাগার সম্পর্কে</a>
    <a href="pages.php" class="nav-link <?= $cur=='pages.php'?'active':'' ?>"><span class="material-icons">article</span> পেজ ম্যানেজার</a>

    <div class="nav-section">সিস্টেম</div>
    <a href="admins.php" class="nav-link <?= $cur=='admins.php'?'active':'' ?>"><span class="material-icons">admin_panel_settings</span> অ্যাডমিন</a>
    <a href="settings.php" class="nav-link <?= $cur=='settings.php'?'active':'' ?>"><span class="material-icons">settings</span> সেটিংস</a>
    <a href="../index.php" class="nav-link" target="_blank"><span class="material-icons">public</span> পাবলিক সাইট</a>
    <a href="logout.php" class="nav-link"><span class="material-icons">logout</span> বের হন</a>
  </nav>
</div>
<script>
function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('overlay').classList.add('active')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('active')}
</script>
