<?php
require_once 'includes/auth.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$page_title = 'Orders';

include 'includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$updated = isset($_GET['updated']);

?>

<div class="admin-title">
    <h1>Orders</h1>
</div>

<?php if ($id):
$orderStmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM orders
     WHERE id=? AND user_id=?
     LIMIT 1"
);

$userId = (int) $_SESSION['user_id'];

mysqli_stmt_bind_param(
    $orderStmt,
    "ii",
    $id,
    $userId
);

mysqli_stmt_execute($orderStmt);

$order = mysqli_fetch_assoc(
    mysqli_stmt_get_result($orderStmt)
);

mysqli_stmt_close($orderStmt);

    if (!$order) {
        flash('danger', 'Order not found.');
        redirect('orders.php');
    }

    $payment = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT method, status, amount
            FROM payments
            WHERE order_id=$id
            LIMIT 1"
        )
    );

if (!$payment) {
    $payment = [
        'method' => $order['payment_method'],
        'status' => 'Pending',
        'amount' => $order['total']
    ];
}

    $items = fetch_all(
        $conn,
        "SELECT * FROM order_items WHERE order_id=$id"
    );

    ?>
    <?php
    $grandTotal = 0;
    $totalQty = 0;

    foreach ($items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $grandTotal += $subtotal;
        $totalQty += $item['quantity'];
    }
    ?>
    <div class="lux-panel">

        <div class="d-flex justify-content-between flex-wrap gap-4">

            <div>

                <h3>
                    Order #<?php echo $order['id']; ?>
                </h3>

                <p>
                    <?php echo clean($order['customer_name']); ?>
                    ·
                    <?php echo clean($order['phone']); ?>
                    ·
                    <?php echo clean($order['email']); ?>
                </p>

                <p>
                    <?php echo clean($order['address']); ?>,
                    <?php echo clean($order['city']); ?>
                </p>

            </div>

            <div>

                <form method="post" class="d-flex gap-2 align-items-center">
                    <input type="hidden"
                         name="csrf_token"
                         value="<?php echo clean(csrf_token()); ?>">

                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">

                    <select name="status" class="form-select" style="width:190px">
                        <?php

                        if ($order['status'] == 'Delivered') {

                            $statuses = [
                                'Delivered'
                            ];

                        } elseif ($order['status'] == 'Cancelled') {

                            $statuses = [
                                'Cancelled'
                            ];

                        } else {

                            $statuses = [
                                'Pending',
                                'Processing',
                                'Shipped',
                                'Delivered',
                                'Cancelled'
                            ];

                        }

                        foreach ($statuses as $status):

                            ?>

                            <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>

                                <?php echo $status; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>
                    <button class="btn btn-gold" <?php echo ($order['status'] == 'Delivered' || $order['status'] == 'Cancelled') ? 'disabled' : ''; ?>>

                        <?php echo $updated ? '✓ Updated' : 'Update'; ?>

                    </button>

                </form>

            </div>

        </div>


        <table class="table luxury-table mt-4">

            <thead>

                <tr>

                    <th>Product</th>

                    <th>Quantity</th>


                    <th class="text-end">Subtotal</th>


                </tr>

            </thead>

            <tbody>

                <?php foreach ($items as $item): ?>

                    <tr>

                        <td>

                            <?php echo clean($item['product_name']); ?>

                        </td>

                        <td>

                            <?php echo $item['quantity']; ?>

                        <td class="text-end">
                            <div class="fw-bold">
                                <?php echo money($item['price'] * $item['quantity']); ?>
                            </div>

                            <small class="text-gold">
                                <?php echo money($item['price']); ?> ×
                                <?php echo $item['quantity']; ?>
                            </small>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

        <h4 class="text-end fw-bold mb-0">

            <span class="text-light">Total:</span>

            <span class="text-gold">
                <?php echo money($grandTotal); ?>
            </span>

            <small class="d-block text-warning mt-1">
                via <?php echo clean($payment['method']); ?>
            </small>

        </h4>
    </div>

<?php else:

    $userId = (int) $_SESSION['user_id'];

$orders = fetch_all(
    $conn,
    "SELECT *
     FROM orders
     WHERE user_id=$userId
     ORDER BY created_at DESC"
);
    ?>


    <div class="lux-panel">

        <table class="table luxury-table align-middle">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($orders as $o):

                    $status = strtolower(trim($o['status']));

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
                            $text = '#fff';
                            $icon = 'bi-truck';
                            break;

                        case 'delivered':
                            $color = '#198754';
                            $text = '#fff';
                            $icon = 'bi-check-circle-fill';
                            break;

                        case 'cancelled':
                            $color = '#dc3545';
                            $text = '#fff';
                            $icon = 'bi-x-circle-fill';
                            break;

                        default:
                            $color = '#6c757d';
                            $text = '#fff';
                            $icon = 'bi-circle-fill';
                    }

                    ?>

                    <tr>

                        <td>
                            <strong>#<?php echo $o['id']; ?></strong>
                        </td>

                        <td>

                            <strong>
                                <?php echo clean($o['customer_name']); ?>
                            </strong>

                        </td>

                        <td>

                            <?php echo clean($o['payment_method']); ?>

                        </td>

                        <td>

                            <strong>

                                <?php echo money($o['total']); ?>

                            </strong>

                        </td>

                        <td>

                            <span style="
                    display:inline-flex;
                    align-items:center;
                    gap:8px;
                    padding:7px 16px;
                    border-radius:999px;
                    background:<?php echo $color; ?>;
                    color:<?php echo $text; ?>;
                    font-size:.85rem;
                    font-weight:600;
                    ">

                                <i class="bi <?php echo $icon; ?>"></i>

                                <?php echo clean($o['status']); ?>

                            </span>

                        </td>

                        <td>

                            <?php echo date('M d, Y', strtotime($o['created_at'])); ?>

                        </td>

                        <td class="text-end">

                            <?php

                            $status = strtolower(trim($o['status']));

                            if ($status == 'cancelled'):

                                ?>

                                <button class="btn btn-sm btn-secondary" disabled>

                                    <i class="bi bi-lock-fill"></i>

                                    Closed

                                </button>

                            <?php else: ?>

                                <a href="orders.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-gold">

                                    <i class="bi bi-eye-fill"></i>

                                    View Details

                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>