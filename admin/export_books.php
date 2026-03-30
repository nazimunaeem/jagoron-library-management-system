<?php
require_once '../includes/config.php';
requireAdmin();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=books_'.date('Y-m-d').'.csv');
$out=fopen('php://output','w');
fprintf($out,chr(0xEF).chr(0xBB).chr(0xBF));// BOM for Excel
fputcsv($out,['আইডি','শিরোনাম','লেখক','প্রকাশক','ISBN','বিভাগ','সাল','কপি','পাওয়া যাচ্ছে','তাক']);
$r=db()->query("SELECT * FROM books ORDER BY title");
while($b=$r->fetch_assoc()) fputcsv($out,[$b['id'],$b['title'],$b['author'],$b['publisher']??'',$b['isbn']??'',$b['category']??'',$b['year']??'',$b['copies'],$b['available'],$b['shelf']??'']);
fclose($out);
