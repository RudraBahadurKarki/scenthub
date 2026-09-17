<?php
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    flash('warning', 'Please login to continue.');
    redirect('login.php');
}
?>