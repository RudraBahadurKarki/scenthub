<?php
include 'includes/header.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare(
    $conn,
    "SELECT p.*, b.name brand, c.name category, f.name family
     FROM products p
     JOIN brands b ON p.brand_id=b.id
     JOIN categories c ON p.category_id=c.id
     JOIN fragrance_families f ON p.family_id=f.id
     WHERE p.id=?
     AND p.status='active'"
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$product) {
    flash('danger', 'Product not found.');
    redirect('shop.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {

    verify_csrf();


    $qty = max(1, (int) ($_POST['quantity'] ?? 1));

        if ((int) $product['stock'] <= 0) {
            flash('danger', 'This product is currently out of stock.');
            redirect('product.php?id=' . $id);
        }

        if ($qty > (int) $product['stock']) {
            flash('danger', 'Only ' . (int) $product['stock'] . ' item(s) are available.');
            redirect('product.php?id=' . $id);
        }

        if (!is_logged_in()) {

        $_SESSION['pending_cart'] = [
            'product_id' => $id,
            'quantity' => $qty
        ];

        $_SESSION['redirect_after_login'] = "product.php?id=" . $id;

        flash('warning', 'Please login to continue.');

        redirect('login.php');
    }

    $cartStmt = mysqli_prepare(
    $conn,
    "SELECT quantity
     FROM cart
     WHERE user_id=? AND product_id=?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $cartStmt,
    "ii",
    $_SESSION['user_id'],
    $id
);

mysqli_stmt_execute($cartStmt);

$existingCart = mysqli_fetch_assoc(
    mysqli_stmt_get_result($cartStmt)
);

mysqli_stmt_close($cartStmt);

$existingQty = $existingCart ? (int) $existingCart['quantity'] : 0;
$newQty = $existingQty + $qty;

if ($newQty > (int) $product['stock']) {
    flash(
        'danger',
        'You already have ' . $existingQty . ' item(s) in your cart. Only ' .
        (int) $product['stock'] . ' item(s) are available.'
    );

    redirect('product.php?id=' . $id);
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO cart(user_id,product_id,quantity)
     VALUES(?,?,?)
     ON DUPLICATE KEY UPDATE
     quantity = quantity + VALUES(quantity)"
);

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $_SESSION['user_id'],
    $id,
    $qty
);

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

flash('success', 'Perfume added to cart.');

redirect('cart.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    verify_csrf();

    if (!is_logged_in()) {
        flash('warning', 'Login required to review.');
        redirect('login.php');
    }

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5 || $comment === '') {
    flash('danger', 'Please provide a valid rating and comment.');
    redirect('product.php?id=' . $id);
    }

    if (strlen($comment) > 2000) {
        flash('danger', 'Review comment must be 2000 characters or less.');
        redirect('product.php?id=' . $id);
    }

    $purchaseStmt = mysqli_prepare(
        $conn,
        "SELECT oi.id
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE o.user_id=?
         AND oi.product_id=?
         AND o.status='Delivered'
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $purchaseStmt,
        'ii',
        $_SESSION['user_id'],
        $id
    );

    mysqli_stmt_execute($purchaseStmt);
    $purchase = mysqli_fetch_assoc(mysqli_stmt_get_result($purchaseStmt));
    mysqli_stmt_close($purchaseStmt);

    if (!$purchase) {
        flash('danger', 'You can review this product only after a delivered purchase.');
        redirect('product.php?id=' . $id);
    }

    $reviewStmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM reviews
         WHERE user_id=? AND product_id=?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $reviewStmt,
        'ii',
        $_SESSION['user_id'],
        $id
    );

    mysqli_stmt_execute($reviewStmt);
    $existingReview = mysqli_fetch_assoc(mysqli_stmt_get_result($reviewStmt));
    mysqli_stmt_close($reviewStmt);

    if ($existingReview) {
        flash('warning', 'You have already reviewed this product.');
        redirect('product.php?id=' . $id);
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO reviews
        (user_id, product_id, rating, comment)
        VALUES (?,?,?,?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'iiis',
        $_SESSION['user_id'],
        $id,
        $rating,
        $comment
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    flash('success', 'Review submitted.');
    redirect('product.php?id=' . $id);
}
$can_review = false;

if (is_logged_in()) {
    $reviewCheck = mysqli_prepare(
        $conn,
        "SELECT oi.id
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE o.user_id=?
         AND oi.product_id=?
         AND o.status='Delivered'
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $reviewCheck,
        'ii',
        $_SESSION['user_id'],
        $id
    );

    mysqli_stmt_execute($reviewCheck);
    $purchase = mysqli_fetch_assoc(
        mysqli_stmt_get_result($reviewCheck)
    );
    mysqli_stmt_close($reviewCheck);

    if ($purchase) {
        $reviewCheck = mysqli_prepare(
            $conn,
            "SELECT id
             FROM reviews
             WHERE user_id=? AND product_id=?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $reviewCheck,
            'ii',
            $_SESSION['user_id'],
            $id
        );

        mysqli_stmt_execute($reviewCheck);
        $existingReview = mysqli_fetch_assoc(
            mysqli_stmt_get_result($reviewCheck)
        );
        mysqli_stmt_close($reviewCheck);

        $can_review = !$existingReview;
    }
}


$reviews = fetch_all($conn, "
    SELECT r.*, u.name AS user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = $id
    ORDER BY r.created_at DESC
");
$related = fetch_all($conn, "SELECT p.*, b.name brand, c.name category FROM products p JOIN brands b ON p.brand_id=b.id JOIN categories c ON p.category_id=c.id WHERE p.id<>$id AND (p.family_id={$product['family_id']} OR p.brand_id={$product['brand_id']}) LIMIT 4");
?>
<section class="section-pad product-detail">
    <div class="container">
        <div class="row g-5 align-items-center product-hero-panel">
            <div class="col-lg-6">
                <div class="detail-image tilt-card reveal-on-scroll">
                    <div class="product-spotlight"></div><img src="<?php echo product_image($product['image']); ?>"
                        alt="<?php echo clean($product['name']); ?>">
                </div>
            </div>
            <div class="col-lg-6 reveal-on-scroll">
                <span class="eyebrow">Signature Perfume</span>
                <p class="product-brand"><?php echo clean($product['brand']); ?> /
                    <?php echo clean($product['category']); ?> / <?php echo clean($product['family']); ?>
                </p>
                <h1><?php echo clean($product['name']); ?></h1>
                <p><?php echo clean($product['description']); ?></p>
                <h2 class="price"><?php echo money($product['price']); ?></h2>
                <div class="product-trust">
                    <span><i class="bi bi-shield-check"></i> Authentic edit</span>
                    <span><i class="bi bi-gift"></i> Gift-ready</span>
                </div>
                <p class="stock">


                    <?php echo $product['stock'] > 0 ? 'In stock: ' . (int) $product['stock'] : 'Out of stock'; ?>
                </p>
                <form method="post" class="d-flex gap-2 detail-actions">
                    <input type="hidden"
                        name="csrf_token"
                        value="<?php echo clean(csrf_token()); ?>">
                    <input type="number" class="form-control" name="quantity" min="1"
                        max="<?php echo (int) $product['stock']; ?>" value="1">

                    <button name="add_cart" class="btn btn-gold btn-lg" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>Add to Cart</button>
                </form>
            </div>
        </div>
        <div class="row g-4 mt-5">

            <div class="col-lg-6">
                <div class="lux-panel">
                    <h3>Customer Reviews</h3>

                    <?php foreach ($reviews as $r): ?>
                        <div class="review-box" style="padding:10px; margin-bottom:15px;">
                            <strong style="background: linear-gradient(to right, #FFD700, #FFA500);
                   -webkit-background-clip: text;
                   -webkit-text-fill-color: transparent;
                   font-size:18px;">
                                <?php echo clean($r['user_name']); ?>
                            </strong>

                            <div style="margin:5px 0; color:#FFD700; font-size:16px;">
                                <?php echo str_repeat('&#9733;', (int) $r['rating']); ?>
                            </div>

                            <p style="margin:0; font-size:14px; color:#66;">
                                <?php echo clean($r['comment']); ?>
                            </p>
                        </div>


                    <?php endforeach; ?>

                    <?php if (!$reviews): ?>
                        <p>No reviews yet. Be the first to describe the trail after use.</p>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-lg-6">

                <?php if (!is_logged_in()): ?>

                    <div class="lux-panel">
                        <h3>Write a Review</h3>
                        <div class="alert alert-warning mb-0">
                            Please login to write a review.
                        </div>
                    </div>

                <?php elseif ($can_review): ?>

                    <form method="post" class="lux-panel lux-form">
                        <input type="hidden"
                            name="csrf_token"
                            value="<?php echo clean(csrf_token()); ?>">
                        <h3>Write a Review</h3>

                        <select name="rating" class="form-select mb-3" required>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>

                        <textarea name="comment" class="form-control mb-3" required
                            placeholder="Your experience"></textarea>

                        <button type="submit" name="review" class="btn btn-gold">
                            Submit Review
                        </button>
                    </form>

                <?php else: ?>

                    <div class="lux-panel">
                        <h3>Write a Review</h3>
                        <div class="alert alert-info mb-0">
                            <strong>Verified Purchase Only</strong><br>
                            You can review this perfume only after purchasing it and your order status is
                            <strong>Delivered</strong>.
                        </div>
                    </div>

                <?php endif; ?>

            </div>

        </div>
        <div class="section-heading mt-5"><span>Similar</span>
            <h2>Related Perfumes</h2>
        </div>
        <div class="row g-4"><?php foreach ($related as $p)
            include 'includes/product_card.php'; ?></div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>