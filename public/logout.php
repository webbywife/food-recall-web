<?php
require_once __DIR__ . '/../includes/auth.php';
start_session();
session_destroy();
header('Location: index.php');
exit;
