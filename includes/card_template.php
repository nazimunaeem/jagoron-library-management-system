<?php
// Required: $m, $lib_name, $lib_tagline, $lib_address, $lib_logo, $rules_text
$rules_clean = $rules_text; // Already HTML
?>
<div class="card-set">

  <!-- FRONT -->
  <div class="card-wrap">
    <div class="card-label">সামনের পিঠ — Front</div>
    <div class="id-card">
      <div class="top-strip"></div>
      <div class="wm">
        <?php if($lib_logo):?><img src="<?=htmlspecialchars($lib_logo)?>" alt=""><?php else:?><div class="wm-text">JP</div><?php endif;?>
      </div>
      <div class="card-hdr">
        <?php if($lib_logo):?><img src="<?=htmlspecialchars($lib_logo)?>" alt="logo">&nbsp;<?php endif;?>
        <h3><?=htmlspecialchars($lib_name)?></h3>
        <div class="tl"><?=htmlspecialchars($lib_tagline)?></div>
        <div class="ad"><?=htmlspecialchars($lib_address)?></div>
      </div>
      <div style="padding-bottom:20px">
        <div class="card-row"><span class="card-lbl">নাম:</span><span class="card-val"><?=htmlspecialchars($m['name'])?><?php if(!empty($m['is_donor'])):?> <span class="donor-tag">★ দাতা</span><?php endif;?></span></div>
        <?php if(!empty($m['father_name'])):?><div class="card-row"><span class="card-lbl">পিতা:</span><span class="card-val"><?=htmlspecialchars($m['father_name'])?></span></div><?php endif;?>
        <?php if(!empty($m['address'])):?><div class="card-row"><span class="card-lbl">ঠিকানা:</span><span class="card-val" style="font-size:0.58rem"><?=htmlspecialchars(mb_substr($m['address'],0,48))?></span></div><?php endif;?>
        <?php if(!empty($m['join_date'])):?><div class="card-row"><span class="card-lbl">তারিখ:</span><span class="card-val"><?=date('d/m/Y',strtotime($m['join_date']))?></span></div><?php endif;?>
      </div>
      <div class="card-id-strip"><?=htmlspecialchars($m['member_id']??'—')?></div>
    </div>
  </div>

  <!-- BACK -->
  <div class="card-wrap">
    <div class="card-label">পেছনের পিঠ — Back</div>
    <div class="id-card card-back">
      <div class="top-strip"></div>
      <div class="wm">
        <?php if($lib_logo):?><img src="<?=htmlspecialchars($lib_logo)?>" alt=""><?php else:?><div class="wm-text">JP</div><?php endif;?>
      </div>
      <div class="back-hdr">
        <h4>পাঠাগার সদস্যপদের নিয়মাবলী</h4>
        <small><?=htmlspecialchars($lib_name)?></small>
      </div>
      <div class="back-rules"><?=$rules_clean?></div>
      <div class="back-footer">
        <span>আইডি: <?=htmlspecialchars($m['member_id']??'—')?></span>
        <span><?=htmlspecialchars($lib_name)?></span>
        <span><?=!empty($m['is_donor'])?'★ দাতা সদস্য':'সাধারণ সদস্য'?></span>
      </div>
    </div>
  </div>

</div>
<p class="no-print" style="margin-top:12px;font-size:0.75rem;color:#777;text-align:center">
  কার্ডের মাপ: ৩ ইঞ্চি × ২ ইঞ্চি | প্রিন্টে Scale 100% রাখুন | দুই পিঠ আলাদা কাটুন
</p>
