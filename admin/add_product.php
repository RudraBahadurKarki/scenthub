<?php
require_once 'includes/auth.php';
$page_title = 'Add Product';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $brand_id = (int) ($_POST['brand_id'] ?? 0);
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $family_id = (int) ($_POST['family_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);

    if ($name === '' || strlen($name) > 160) {
        flash('danger', 'Product name is required and must be 160 characters or less.');
        redirect('add_product.php');
    }

    if ($brand_id <= 0 || $category_id <= 0 || $family_id <= 0) {
        flash('danger', 'Please select a valid brand, category, and fragrance family.');
        redirect('add_product.php');
    }

    if ($price <= 0) {
        flash('danger', 'Product price must be greater than 0.');
        redirect('add_product.php');
    }

    if ($stock < 0) {
        flash('danger', 'Stock cannot be negative.');
        redirect('add_product.php');
    }

    $image = null;
    if (!empty($_FILES['image']['name'])) {
    $allowed = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp'
    ];

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        flash('danger', 'Image upload failed.');
        redirect('add_product.php');
    }

    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        flash('danger', 'Image size must be 2MB or less.');
        redirect('add_product.php');
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['image']['tmp_name']);

    if (!isset($allowed[$ext]) || $mime !== $allowed[$ext]) {
        flash('danger', 'Only JPG, PNG, or WEBP images are allowed.');
        redirect('add_product.php');
    }

    $image = bin2hex(random_bytes(8)) . '.' . $ext;
    $upload_path = __DIR__ . '/../assets/uploads/' . $image;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        flash('danger', 'Unable to save the uploaded image.');
        redirect('add_product.php');
    }
}
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $best = isset($_POST['is_bestseller']) ? 1 : 0;
    $new = isset($_POST['is_new']) ? 1 : 0;
    $stmt = mysqli_prepare($conn, "INSERT INTO products (name,brand_id,category_id,family_id,description,price,stock,image,is_featured,is_bestseller,is_new) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param(
    $stmt,
    'siiisdisiii',
    $name,
    $brand_id,
    $category_id,
    $family_id,
    $description,
    $price,
    $stock,
    $image,
    $featured,
    $best,
    $new
);
    try {
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    flash('success', 'Product added.');
    redirect('products.php');

} catch (Throwable $e) {
    mysqli_stmt_close($stmt);

    if ($image !== null) {
        $uploaded_file = __DIR__ . '/../assets/uploads/' . $image;

        if (is_file($uploaded_file)) {
            unlink($uploaded_file);
        }
    }

    flash('danger', 'Unable to add product.');
    redirect('add_product.php');
}
}
include 'includes/header.php';
$product = [];
$button = 'Add Product'; ?>
<div class="admin-title">
    <h1>Add Product</h1>
</div><?php include 'product_form.php';
include 'includes/footer.php'; ?>