<?php
require_once 'config.php';
header('Content-Type: application/json');
$type = $_GET['type'] ?? '';
$q = escape($_GET['q'] ?? '');
$results = [];
if ($type === 'member') {
    $res = db()->query("SELECT id, name, member_id FROM members WHERE status='active' AND (name LIKE '%$q%' OR member_id LIKE '%$q%') LIMIT 10");
    while ($r = $res->fetch_assoc()) $results[] = ['id'=>$r['id'], 'label'=>$r['name'].' ('.$r['member_id'].')'];
} elseif ($type === 'book') {
    $res = db()->query("SELECT id, title, author FROM books WHERE available>0 AND (title LIKE '%$q%' OR id LIKE '%$q%') LIMIT 10");
    while ($r = $res->fetch_assoc()) $results[] = ['id'=>$r['id'], 'label'=>$r['title'].' — '.$r['author']];
}
echo json_encode($results);
