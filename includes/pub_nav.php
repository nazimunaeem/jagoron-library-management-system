<?php
$lib_name = libName(); $lib_logo = libLogo();
$current_slug = $_GET['slug'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
$nav_pages = db()->query("SELECT slug, title FROM pages WHERE is_published=1 ORDER BY sort_order, id");
$logged_in_member = isMember();
$logged_in_admin  = isAdmin();
?>
<header class="site-header">
  <div class="header-inner">
    <a href="/" class="header-brand">
      <?php if ($lib_logo): ?>
        <img src="<?= htmlspecialchars($lib_logo) ?>" alt="logo" class="header-logo">
      <?php else: ?>
        <span class="header-logo-emoji">📚</span>
      <?php endif; ?>
      <div class="header-brand-text">
        <span class="header-lib-name"><?= htmlspecialchars($lib_name) ?></span>
        <span class="header-lib-tag"><?= htmlspecialchars(libTagline()) ?></span>
      </div>
    </a>

    <nav class="header-nav" id="headerNav">
      <a href="/" class="nav-item <?= $current_page==='index.php'?'nav-active':'' ?>">হোম</a>
      <a href="/books.php" class="nav-item <?= $current_page==='books.php'?'nav-active':'' ?>">বইয়ের তালিকা</a>
      <a href="/members-public.php" class="nav-item <?= $current_page==='members-public.php'?'nav-active':'' ?>">সদস্য</a>
      <a href="/donors-public.php" class="nav-item <?= $current_page==='donors-public.php'?'nav-active':'' ?>">দাতা</a>
      <?php if ($nav_pages): while ($pg = $nav_pages->fetch_assoc()): ?>
        <a href="/page.php?slug=<?= $pg['slug'] ?>" class="nav-item <?= $pg['slug']==$current_slug?'nav-active':'' ?>"><?= htmlspecialchars($pg['title']) ?></a>
      <?php endwhile; endif; ?>
    </nav>

    <div class="header-actions">
      <?php if ($logged_in_admin): ?>
        <a href="/admin/index.php" class="header-btn header-btn-outline">অ্যাডমিন</a>
        <a href="/admin/logout.php" class="header-btn header-btn-ghost">লগআউট</a>
      <?php elseif ($logged_in_member): ?>
        <a href="/member/dashboard.php" class="header-btn header-btn-outline">
          👤 <?= htmlspecialchars(mb_substr($_SESSION['member_name']??'আমার একাউন্ট',0,10)) ?>
        </a>
        <a href="/member/logout.php" class="header-btn header-btn-ghost">লগআউট</a>
      <?php else: ?>
        <a href="/login.php" class="header-btn header-btn-primary">লগইন</a>
      <?php endif; ?>
      <button class="header-hamburger" id="headerToggle" onclick="toggleNav()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  <div class="header-overlay" id="headerOverlay" onclick="closeNav()"></div>
</header>
<style>
:root{--hdr:#1a4731;--hdr-dark:#0f2d1e;--hdr-accent:#f4a261}
.site-header{background:var(--hdr);position:sticky;top:0;z-index:500;box-shadow:0 2px 12px rgba(0,0,0,0.18)}
.header-inner{max-width:1200px;margin:0 auto;padding:0 18px;height:58px;display:flex;align-items:center;gap:16px}
.header-brand{display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0}
.header-logo{height:36px;width:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.3)}
.header-logo-emoji{font-size:1.5rem;line-height:1}
.header-brand-text{display:flex;flex-direction:column}
.header-lib-name{font-family:'Merriweather',serif;font-size:0.9rem;color:var(--hdr-accent);font-weight:700;line-height:1.2}
.header-lib-tag{font-size:0.58rem;color:rgba(255,255,255,0.5);line-height:1}
.header-nav{display:flex;align-items:center;gap:2px;flex:1;justify-content:center;flex-wrap:wrap}
.nav-item{color:rgba(255,255,255,0.78);text-decoration:none;font-size:0.83rem;padding:6px 10px;border-radius:6px;transition:all 0.2s;white-space:nowrap;font-family:'Hind Siliguri',sans-serif}
.nav-item:hover{color:#fff;background:rgba(255,255,255,0.1)}
.nav-item.nav-active{color:var(--hdr-accent);background:rgba(244,162,97,0.12);font-weight:600}
.header-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
.header-btn{padding:6px 13px;border-radius:20px;font-size:0.8rem;font-family:'Hind Siliguri',sans-serif;text-decoration:none;font-weight:500;white-space:nowrap;transition:all 0.2s}
.header-btn-primary{background:var(--hdr-accent);color:#fff}
.header-btn-primary:hover{background:#e76f51}
.header-btn-outline{border:1px solid rgba(255,255,255,0.3);color:rgba(255,255,255,0.85)}
.header-btn-outline:hover{background:rgba(255,255,255,0.1);color:#fff}
.header-btn-ghost{color:rgba(255,255,255,0.55);font-size:0.78rem}
.header-btn-ghost:hover{color:#fff}
.header-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;flex-direction:column;gap:5px}
.header-hamburger span{display:block;width:22px;height:2px;background:rgba(255,255,255,0.8);border-radius:2px;transition:all 0.3s}
.header-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:498;top:58px}
@media(max-width:900px){
  .header-nav{display:none;position:fixed;top:58px;left:0;right:0;background:var(--hdr-dark);flex-direction:column;align-items:stretch;padding:12px;gap:4px;z-index:499;box-shadow:0 8px 24px rgba(0,0,0,0.3);max-height:calc(100vh - 58px);overflow-y:auto}
  .header-nav.open{display:flex}
  .header-nav.open ~ .header-overlay,.header-overlay.open{display:block}
  .nav-item{padding:10px 14px;border-radius:8px;font-size:0.9rem}
  .header-hamburger{display:flex}
  .header-lib-tag{display:none}
  .header-btn-ghost{display:none}
}
@media(max-width:480px){
  .header-inner{padding:0 12px;height:52px}
  .header-lib-name{font-size:0.82rem}
  .header-btn-outline{font-size:0.75rem;padding:5px 10px}
}
</style>
<script>
function toggleNav(){
  const nav=document.getElementById('headerNav');
  const ov=document.getElementById('headerOverlay');
  nav.classList.toggle('open');
  ov.classList.toggle('open');
}
function closeNav(){
  document.getElementById('headerNav').classList.remove('open');
  document.getElementById('headerOverlay').classList.remove('open');
}
</script>
