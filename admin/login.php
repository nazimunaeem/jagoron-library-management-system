<?php
require_once '../includes/config.php';
if (isAdmin()) { header('Location: index.php'); exit; }
header('Location: /login.php?role=admin');
exit;
