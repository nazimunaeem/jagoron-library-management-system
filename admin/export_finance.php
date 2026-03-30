<?php
require_once '../includes/config.php';
requireAdmin();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=finance_'.date('Y-m-d').'.csv');
$out=fopen('php://output','w');
fprintf($out,chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out,['তারিখ','ধরন','বিভাগ','বিবরণ','পরিমাণ']);
$r=db()->query("SELECT * FROM finance ORDER BY date DESC");
while($f=$r->fetch_assoc()) fputcsv($out,[$f['date'],$f['type'],$f['category']??'',$f['description'],$f['amount']]);
fclose($out);
