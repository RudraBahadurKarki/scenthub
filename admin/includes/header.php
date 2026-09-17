<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? clean($page_title) . ' | ' : ''; ?>ScentHub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar"><a class="luxury-logo" href="index.php">ScentHub</a>
            <nav>
                <a href="dashboard.php"><i class="bi bi-grid"></i> Dashboard</a><a href="products.php"><i
                        class="bi bi-box-seam"></i> Products</a><a href="categories.php"><i class="bi bi-tags"></i>
                    Categories</a><a href="brands.php"><i class="bi bi-award"></i> Brands</a><a
                    href="fragrance_families.php"><i class="bi bi-flower1"></i> Fragrance</a><a href="orders.php"><i
                        class="bi bi-receipt"></i> Orders</a><a href="users.php"><i class="bi bi-people"></i>
                    Users</a><a href="reviews.php"><i class="bi bi-star"></i> Reviews</a><a href="contacts.php"><i
                        class="bi bi-envelope"></i> Contacts</a><a href="logout.php"><i
                        class="bi bi-box-arrow-right"></i> Logout</a>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="container-fluid"><?php show_flash(); ?>