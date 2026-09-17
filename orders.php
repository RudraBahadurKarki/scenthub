<?php
require_once 'includes/auth.php';
$page_title = 'My Orders';
include 'includes/header.php';
$orderId = (int) ($_GET['id'] ?? 0);
?>
<section class="page-hero">
    <div class="container">
        <h1>Order History</h1>
        <p>Track your ScentHub purchases.</p>
    </div>
</section>
<section class="section-pad">
    <div class="container">
        <?php if ($orderId): ?>
            <?php
            $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id=? AND user_id=?");
            mysqli_stmt_bind_param($stmt, 'ii', $orderId, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            ?>
            <?php if (!$order): ?>
                <div class="empty-state">Order not found.</div>
            <?php else: ?>
                <?php
                $items = fetch_all($conn, "
                    SELECT
                        oi.*,
                        p.image
                    FROM order_items oi
                    LEFT JOIN products p
                    ON oi.product_id = p.id
                    WHERE oi.order_id=$orderId
                ");
                ?>
                <div class="lux-panel">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <h3>Order</h3>
                        <span class="status-pill"><?php echo clean($order['status']); ?></span>
                    </div>
                    <p><?php echo clean($order['address']); ?>, <?php echo clean($order['city']); ?> -
                        <?php echo clean($order['payment_method']); ?>
                    </p>
                    <table class="table luxury-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $i): ?>

                                <tr>

                                    <td class="order-product">

                                        <?php if (!empty($i['image'])): ?>

                                            <img src="assets/uploads/<?php echo clean($i['image']); ?>" class="order-product-img"
                                                alt="<?php echo clean($i['product_name']); ?>">

                                        <?php else: ?>

                                            <img src="assets/images/perfume-placeholder.svg" class="order-product-img">

                                        <?php endif; ?>


                                        <div>
                                            <strong>
                                                <?php echo clean($i['product_name']); ?>
                                            </strong>
                                        </div>

                                    </td>


                                    <td>
                                        <?php echo $i['quantity']; ?>
                                    </td>


                                    <td class="text-end">
                                        <div class="fw-bold">
                                            <?php echo money($i['price'] * $i['quantity']); ?>
                                        </div>

                                        <small class="text-gold">
                                            <?php echo money($i['price']); ?> ×
                                            <?php echo $i['quantity']; ?>
                                        </small>
                                    </td>


                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <h4 class="text-end my-3">Total: <?php echo money($order['total']); ?></h4>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php $orders = fetch_all($conn, "SELECT * FROM orders WHERE user_id=" . (int) $_SESSION['user_id'] . " ORDER BY created_at DESC"); ?>
            <div class="lux-panel">
                <?php foreach ($orders as $o): ?>
                    <div class="order-row">
                        <div><strong>Order
                                #<?php echo $o['id']; ?></strong><span><?php echo date('M d, Y', strtotime($o['created_at'])); ?>
                                - <?php echo clean($o['status']); ?></span></div>
                        <div><b><?php echo money($o['total']); ?></b><a href="orders.php?id=<?php echo $o['id']; ?>"
                                class="btn btn-sm btn-gold">Details</a></div>
                    </div>
                <?php endforeach;
                if (!$orders)
                    echo '<p>No orders found.</p>'; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>