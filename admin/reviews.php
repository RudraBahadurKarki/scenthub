<?php
require_once 'includes/auth.php';

$page_title = 'Reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    verify_csrf();

    $id = (int) $_POST['delete'];

    $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {
        flash('success', 'Review deleted.');
    } else {
        flash('warning', 'Review not found.');
    }

    mysqli_stmt_close($stmt);

    redirect('reviews.php');
}

include 'includes/header.php';

$reviews = fetch_all(
    $conn,
    "SELECT r.*, u.name user_name, p.name product_name
     FROM reviews r
     JOIN users u ON r.user_id=u.id
     JOIN products p ON r.product_id=p.id
     ORDER BY r.created_at DESC"
);
?>

<div class="admin-title">
    <h1>Reviews</h1>
</div>

<div class="lux-panel">
    <table class="table luxury-table">

        <thead>
            <tr>
                <th>User</th>
                <th>Product</th>
                <th>Rating</th>
                <th>Comment</th>
                <th></th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($reviews as $r): ?>

                <tr>

                    <td>
                        <?php echo clean($r['user_name']); ?>
                    </td>

                    <td>
                        <?php echo clean($r['product_name']); ?>
                    </td>

                    <td>
                        <?php echo str_repeat('&#9733;', (int) $r['rating']); ?>
                    </td>

                    <td>
                        <?php echo clean($r['comment']); ?>
                    </td>

                    <td>

                        <form method="POST"
                            action="reviews.php"
                            style="display:inline;"
                            onsubmit="return confirm('Delete review?');">

                            <input type="hidden"
                                name="csrf_token"
                                value="<?php echo clean(csrf_token()); ?>">

                            <input type="hidden"
                                name="delete"
                                value="<?php echo (int) $r['id']; ?>">

                            <button type="submit"
                                class="btn btn-sm btn-outline-danger">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>
</div>

<?php include 'includes/footer.php'; ?>