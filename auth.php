<?php
// Protect admin-only pages by requiring an authenticated session.
require_once __DIR__ . '/functions.php';
if (empty($_SESSION['admin_id'])) {
    set_flash('warning', 'Please sign in to continue.');
    redirect('login.php');
}
