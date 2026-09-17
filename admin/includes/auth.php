<?php
require_once __DIR__ . '/../../includes/functions.php';

/* Prevent browser caching */
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* Check login */
if (!is_logged_in()) {
    flash('warning', 'Please log in first.');
    redirect('../login.php');
}

/* Check admin role */
if (!is_admin()) {
    flash('danger', 'Access denied. Administrator privileges required.');
    redirect('../index.php');
}
?>