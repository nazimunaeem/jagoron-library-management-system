<?php
require_once 'includes/config.php';
$s   = escape($_GET['s']??'');
$cat = escape($_GET['cat']??'');
$where='WHERE 1=1';
if($s)   $where.=" AND (b.title LIKE '%$s%' OR b.author LIKE '%$s%')";
if($cat) $where.=" AND b.category='$cat'";
// Latest 10 books (or search results)
$books_q=db()->query("SELECT b.* FROM books b $where ORDER BY b.id DESC LIMIT 10");
$cats_q=db()->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category!='' ORDER BY category");
$total_books=(int)(db()->query("SELECT COALESCE(SUM(copies),0) FROM books")->fetch_row()[0]??0);
$total_bk   =(int)(db()->query("SELECT COUNT(*) FROM books")->fetch_row()[0]??0);
$total_mbr  =(int)(db()->query("SELECT COUNT(*) FROM members WHERE status='active'")->fetch_row()[0]??0);
$top_books  =db()->query("SELECT bk.title, COUNT(*) as cnt FROM borrows br JOIN books bk ON br.book_id=bk.id GROUP BY br.book_id ORDER BY cnt DESC LIMIT 6");
$top_members=db()->query("SELECT m.name, m.member_id as mid, COUNT(*) as cnt FROM borrows br JOIN members m ON br.member_id=m.id GROUP BY br.member_id ORDER BY cnt DESC LIMIT 6");
$top_donors =db()->query("SELECT m.name, COALESCE(SUM(CASE WHEN d.type='money' THEN d.amount ELSE 0 END),0) as money_total, COALESCE(SUM(CASE WHEN d.type='book' THEN d.book_count ELSE 0 END),0) as book_total FROM members m LEFT JOIN donations d ON d.donor_id=m.id WHERE m.is_donor=1 GROUP BY m.id ORDER BY money_total DESC LIMIT 5");
$issued_map=[];
$iq=db()->query("SELECT br.book_id, m.name FROM borrows br JOIN members m ON br.member_id=m.id WHERE br.status='borrowed'");
if($iq) while($r=$iq->fetch_assoc()) $issued_map[$r['book_id']][]=$r['name'];
$lib_name=libName();$lib_tagline=libTagline();$lib_address=libAddress();$lib_logo=libLogo();
?>
<!DOCTYPE html><html lang="bn"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($lib_name)?> — <?=htmlspecialchars($lib_tagline)?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
<style>
.main-wrap{max-width:1160px;margin:0 auto;padding:20px 14px}
.two-col{display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start}
@media(max-width:900px){.two-col{grid-template-columns:1fr}.sidebar-col{order:2}}
.bl-row{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border);transition:background 0.15s}
.bl-row:hover{background:#f7fbf7}.bl-row:last-child{border-bottom:none}
.bl-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;flex-shrink:0}
.bl-info{flex:1;min-width:0}
.bl-info h4{font-size:0.845rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:2px}
.bl-info p{font-size:0.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.copy-status{flex-shrink:0;text-align:right;font-size:0.7rem;line-height:1.6;max-width:130px}
.cat-chips{display:flex;gap:5px;flex-wrap:wrap;margin:8px 0 12px}
.cat-chip{padding:3px 9px;border-radius:20px;font-size:0.72rem;text-decoration:none;border:1px solid var(--border);background:#fff;color:var(--text);white-space:nowrap}
.cat-chip.active{background:var(--primary);color:#fff;border-color:var(--primary)}
</style>
</head><body>
<?php include 'includes/pub_nav.php';?>

<div class="hero">
  <div style="max-width:720px;margin:0 auto;padding:0 12px">
    <?php if($lib_logo):?><img src="<?=htmlspecialchars($lib_logo)?>" style="height:52px;margin-bottom:8px"><br><?php endif;?>
    <h1><?=htmlspecialchars($lib_name)?></h1>
    <p><?=htmlspecialchars($lib_tagline)?></p>
    <p style="color:rgba(255,255,255,0.5);font-size:0.75rem;margin-top:2px"><?=htmlspecialchars($lib_address)?></p>
    <div class="opac-search-box" style="margin-top:14px">
      <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
        <input type="text" name="s" class="form-control" placeholder="বই বা লেখকের নাম লিখুন..." value="<?=htmlspecialchars($s)?>" style="flex:1;min-width:150px">
        <button type="submit" class="btn btn-primary"><span class="material-icons">search</span></button>
        <?php if($s||$cat):?><a href="/" class="btn btn-outline">✕</a><?php endif;?>
      </form>
      <?php if($cats_q&&$cats_q->num_rows>0):?>
      <div class="cat-chips" style="margin-top:8px">
        <a href="/" class="cat-chip <?=!$cat?'active':''?>">সব</a>
        <?php while($c=$cats_q->fetch_row()):?>
          <a href="?cat=<?=urlencode($c[0])?>" class="cat-chip <?=$cat==$c[0]?'active':''?>"><?=htmlspecialchars($c[0])?></a>
        <?php endwhile;?>
      </div>
      <?php endif;?>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><strong><?=$total_bk?></strong><span>বইয়ের শিরোনাম</span></div>
      <div class="hero-stat"><strong><?=$total_books?></strong><span>মোট কপি</span></div>
      <div class="hero-stat"><strong><?=$total_mbr?></strong><span>সদস্য</span></div>
    </div>
  </div>
</div>

<div class="main-wrap">
  <div class="two-col">
    <!-- Left: Latest Books -->
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <h2 style="font-size:0.95rem;font-weight:600;color:var(--primary)">
          <?=($s||$cat)?'🔍 অনুসন্ধানের ফলাফল':'🆕 সর্বশেষ সংযোজিত বই'?>
        </h2>
        <a href="/books.php<?=($s||$cat)?'?s='.urlencode($s).($cat?'&cat='.urlencode($cat):''):''?>" class="btn btn-outline btn-sm">
          সব বই →
        </a>
      </div>
      <div class="card" style="margin-bottom:10px">
        <?php $has=false; if($books_q): while($b=$books_q->fetch_assoc()): $has=true;
          $issued=$issued_map[$b['id']]??[];$avail=$b['available'];$total=$b['copies'];?>
          <div class="bl-row">
            <div class="bl-icon">📖</div>
            <div class="bl-info">
              <h4><?=htmlspecialchars($b['title'])?></h4>
              <p>✍️ <?=htmlspecialchars($b['author'])?>
                <?php if($b['category']):?>&nbsp;·&nbsp;<?=htmlspecialchars($b['category'])?><?php endif;?>
                <?php if($b['shelf']):?>&nbsp;·&nbsp;📍<?=htmlspecialchars($b['shelf'])?><?php endif;?>
              </p>
            </div>
            <div class="copy-status">
              <?php if($avail>0):?>
                <span class="badge badge-success">(<?=$avail?>) পাওয়া যাচ্ছে</span><br>
              <?php endif;?>
              <?php foreach($issued as $un):?>
                <span class="badge badge-danger" style="font-size:0.65rem"><?=htmlspecialchars($un)?> (1)</span><br>
              <?php endforeach;?>
            </div>
          </div>
        <?php endwhile; endif;?>
        <?php if(!$has):?>
          <div class="empty-state" style="padding:28px"><span class="material-icons">search_off</span><p>কোনো বই পাওয়া যায়নি</p></div>
        <?php endif;?>
      </div>
      <a href="/books.php" class="btn btn-outline" style="width:100%;justify-content:center">📚 সকল বই দেখুন (<?=$total_bk?>টি)</a>
    </div>

    <!-- Right Sidebar -->
    <div class="sidebar-col">
      <div class="card">
        <div class="card-header"><h3><span class="material-icons">trending_up</span> সর্বাধিক পঠিত</h3></div>
        <table><?php if($top_books): while($b=$top_books->fetch_assoc()):?>
          <tr><td style="font-size:0.8rem"><?=htmlspecialchars($b['title'])?></td><td><span class="badge badge-info" style="white-space:nowrap"><?=$b['cnt']?>বার</span></td></tr>
        <?php endwhile; else:?><tr><td colspan="2" style="text-align:center;color:var(--muted);padding:12px;font-size:0.8rem">তথ্য নেই</td></tr><?php endif;?></table>
      </div>
      <div class="card">
        <div class="card-header"><h3><span class="material-icons">star</span> সক্রিয় পাঠক</h3></div>
        <table><?php if($top_members): while($m=$top_members->fetch_assoc()):?>
          <tr><td style="font-size:0.8rem"><?=htmlspecialchars($m['name'])?><br><small class="badge badge-muted"><?=$m['mid']?></small></td><td><span class="badge badge-success"><?=$m['cnt']?>বই</span></td></tr>
        <?php endwhile; else:?><tr><td colspan="2" style="text-align:center;color:var(--muted);padding:12px;font-size:0.8rem">তথ্য নেই</td></tr><?php endif;?></table>
      </div>
      <div class="card">
        <div class="card-header"><h3><span class="material-icons">volunteer_activism</span> শীর্ষ দাতা</h3></div>
        <table><?php $i=1; if($top_donors): while($d=$top_donors->fetch_assoc()):?>
          <tr><td style="font-size:0.8rem"><?=$i++?>. <?=htmlspecialchars($d['name'])?></td><td style="white-space:nowrap"><span class="badge badge-warning">৳<?=number_format($d['money_total'])?></span><?php if($d['book_total']>0):?> <span class="badge badge-info" style="font-size:0.65rem"><?=$d['book_total']?>বই</span><?php endif;?></td></tr>
        <?php endwhile; else:?><tr><td colspan="2" style="text-align:center;color:var(--muted);padding:12px;font-size:0.8rem">তথ্য নেই</td></tr><?php endif;?></table>
      </div>
    </div>
  </div>
</div>

<footer style="background:#0f2d1e;color:rgba(255,255,255,0.5);text-align:center;padding:12px;font-size:0.78rem;margin-top:16px">
  <?=htmlspecialchars($lib_name)?> — <?=htmlspecialchars($lib_tagline)?> | <?=htmlspecialchars($lib_address)?>
</footer>
</body></html>
