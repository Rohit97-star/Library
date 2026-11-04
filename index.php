<?php
require_once __DIR__ . '/includes/functions.php';
redirect(!empty($_SESSION['admin_id']) ? 'admin/dashboard.php' : 'admin/login.php');
