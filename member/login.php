<?php
require_once '../includes/config.php';
if (isMember()) { header('Location: dashboard.php'); exit; }
header('Location: /login.php?role=member');
exit;
