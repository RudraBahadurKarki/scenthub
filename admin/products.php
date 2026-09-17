<?php
require_once 'includes/auth.php';
$page_title = 'Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete'])) {
        verify_csrf();

        $id = (int) $_POST['delete'];

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE products SET status='inactive' WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 1) {
            flash('success', 'Product removed from public catalog successfully.');
        } else {
            flash('warning', 'Product not found.');
        }

mysqli_stmt_close($stmt);

redirect('products.php');
    }

    if (isset($_POST['restore'])) {
        verify_csrf();

        $id = (int) $_POST['restore'];

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE products SET status='active' WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 1) {
            flash('success', 'Product restored to public catalog.');
        } else {
            flash('warning', 'Product not found.');
        }

        mysqli_stmt_close($stmt);

        redirect('products.php');
    }
}

include 'includes/header.php';

$products = fetch_all(
    $conn,
    "SELECT p.*, b.name brand, c.name category, f.name family
     FROM products p
     JOIN brands b ON p.brand_id=b.id
     JOIN categories c ON p.category_id=c.id
     JOIN fragrance_families f ON p.family_id=f.id
     ORDER BY p.id DESC"
);
?>

<div class="admin-title">
    <h1>Products</h1>
    <a class="btn btn-gold" href="add_product.php">Add Product</a>
</div>

<div class="lux-panel">
    <div class="table-responsive">
        <table class="table luxury-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Family</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th class="text-end align-middle">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($products as $p): ?>

                    <tr <?php echo $p['status'] === 'inactive' ? 'style="opacity: 0.5;"' : ''; ?>>

                        <td>
                            <img class="admin-thumb"
                                src="../<?php echo product_image($p['image']); ?>">
                        </td>

                        <td>
                            <?php echo clean($p['name']); ?>

                            <?php if ($p['status'] === 'inactive'): ?>
                                <small class="text-danger d-block" style="font-size: 11px;">
                                    [ Inactive ]
                                </small>
                            <?php else: ?>
                                <small class="text-success d-block" style="font-size: 11px;">
                                    [ Active ]
                                </small>
                            <?php endif; ?>
                        </td>

                        <td><?php echo clean($p['brand']); ?></td>
                        <td><?php echo clean($p['category']); ?></td>
                        <td><?php echo clean($p['family']); ?></td>
                        <td><?php echo money($p['price']); ?></td>
                        <td><?php echo $p['stock']; ?></td>

                        <td class="text-end">

                            <a class="btn btn-sm btn-outline-gold"
                                href="edit_product.php?id=<?php echo $p['id']; ?>">
                                Edit
                            </a>

                            <?php if ($p['status'] === 'inactive'): ?>

                                <form method="POST"
                                    action="products.php"
                                    style="display:inline;"
                                    onsubmit="return confirm('Restore product?');">

                                    <input type="hidden"
                                        name="csrf_token"
                                        value="<?php echo clean(csrf_token()); ?>">

                                    <input type="hidden"
                                        name="restore"
                                        value="<?php echo (int) $p['id']; ?>">

                                    <button type="submit"
                                        class="btn btn-sm btn-outline-success">
                                        Restore
                                    </button>

                                </form>

                            <?php else: ?>

                                <form method="POST"
                                    action="products.php"
                                    style="display:inline;"
                                    onsubmit="return confirm('Delete product?');">

                                    <input type="hidden"
                                        name="csrf_token"
                                        value="<?php echo clean(csrf_token()); ?>">

                                    <input type="hidden"
                                        name="delete"
                                        value="<?php echo (int) $p['id']; ?>">

                                    <button type="submit"
                                        class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>

                                </form>

                            <?php endif; ?>

                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>