<?php
require_once 'includes/auth.php';

$page_title = 'Dashboard';
include 'includes/header.php';

/* Dashboard Statistics */
$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='user'"))['c'];
$products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM products"))['c'];
$orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders"))['c'];
$sales = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COALESCE(SUM(total),0) AS c FROM orders WHERE status='Delivered'")
)['c'];
$processing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='Processing'"))['c'];

$cards = [
    ['Users', $users, 'people', 'users.php'],
    ['Products', $products, 'box-seam', 'products.php'],
    ['Orders', $orders, 'receipt', 'orders.php'],
    ['Total Sales', money($sales), 'cash', 'orders.php'],
    ['Processing', $processing, 'hourglass', 'orders.php?status=Processing']
];
$recentOrders = fetch_all($conn, "SELECT * FROM orders ORDER BY id DESC");
?>

<div class="admin-title">
    <div>
        <span class="eyebrow">Control Room</span>
        <h1>Dashboard</h1>
        <p>Welcome, <?php echo clean($_SESSION['user_name']); ?>.</p>
    </div>
</div>

<div class="row g-4">

    <?php foreach ($cards as $card): ?>

        <div class="col-md-6 col-xl">

            <a href="<?php echo $card[3]; ?>" class="text-decoration-none text-reset">

                <div class="stat-card reveal-on-scroll">

                    <i class="bi bi-<?php echo $card[2]; ?>"></i>

                    <span><?php echo $card[0]; ?></span>

                    <strong><?php echo $card[1]; ?></strong>

                </div>

            </a>

        </div>

    <?php endforeach; ?>

</div>

<div class="lux-panel mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3 class="mb-0">Recent Orders</h3>

        <a href="orders.php" class="btn btn-outline-gold btn-sm">
            View All
        </a>

    </div>

    <table class="table luxury-table">

        <thead>

            <tr>

                <th>ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>

            </tr>

        </thead>

        <tbody>

            <?php if ($recentOrders): ?>

                <?php foreach ($recentOrders as $o): ?>

                    <tr style="cursor:pointer;" onclick="window.location='orders.php?id=<?php echo $o['id']; ?>';">

                        <td>#<?php echo $o['id']; ?></td>

                        <td><?php echo clean($o['customer_name']); ?></td>

                        <td><?php echo money($o['total']); ?></td>

                        <td class="align-middle">

                            <?php

                            $status = strtolower(trim($o['status']));

                            $color = '#6c757d';
                            $text = '#fff';
                            $icon = 'bi-circle-fill';

                            switch ($status) {

                                case 'pending':
                                    $color = '#ffc107';
                                    $text = '#111';
                                    $icon = 'bi-clock-fill';
                                    break;

                                case 'processing':
                                    $color = '#0dcaf0';
                                    $text = '#111';
                                    $icon = 'bi-gear-fill';
                                    break;

                                case 'shipped':
                                    $color = '#0d6efd';
                                    $icon = 'bi-truck';
                                    break;

                                case 'delivered':
                                    $color = '#198754';
                                    $icon = 'bi-check-circle-fill';
                                    break;

                                case 'cancelled':
                                    $color = '#dc3545';
                                    $icon = 'bi-x-circle-fill';
                                    break;
                            }

                            ?>

                            <span style="
display:inline-flex;
align-items:center;
gap:6px;
padding:6px 14px;
border-radius:999px;
background:<?php echo $color; ?>;
color:<?php echo $text; ?>;
font-size:.82rem;
font-weight:600;
">

                                <i class="bi <?php echo $icon; ?>"></i>

                                <?php echo clean($o['status']); ?>

                            </span>

                        </td>
                    <?php endforeach; ?>

                <?php else: ?>

                <tr>

                    <td colspan="4" class="text-center py-4">

                        No recent orders found.

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php include 'includes/footer.php'; ?>