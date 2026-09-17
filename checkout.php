<?php
require_once 'includes/auth.php';
$page_title = 'Checkout';
include 'includes/header.php';
$selectedItems = $_SESSION['checkout_selected_items'] ?? [];

$selectedItems = array_values(
    array_unique(
        array_filter(
            array_map('intval', $selectedItems),
            fn($id) => $id > 0
        )
    )
);

if (!$selectedItems) {
    flash('warning', 'Please select at least one product to checkout.');
    redirect('cart.php');
}

$placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

$sql = "
    SELECT cart.id AS cart_id, cart.quantity, p.*
    FROM cart
    JOIN products p ON cart.product_id = p.id
    WHERE cart.user_id = ?
    AND cart.id IN ($placeholders)
";

$stmt = mysqli_prepare($conn, $sql);

$userId = (int) $_SESSION['user_id'];

$types = 'i' . str_repeat('i', count($selectedItems));
$params = array_merge([$userId], $selectedItems);

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

$items = mysqli_fetch_all(
    mysqli_stmt_get_result($stmt),
    MYSQLI_ASSOC
);

mysqli_stmt_close($stmt);

if (!$items) {
    unset($_SESSION['checkout_selected_items']);

    flash('warning', 'The selected cart items are no longer available.');
    redirect('cart.php');
}

$total = array_reduce(
    $items,
    fn($sum, $i) => $sum + ($i['price'] * $i['quantity']),
    0
);
if (
    empty($_SESSION['demo_payment_token']) ||
    !isset($_SESSION['demo_payment_amount']) ||
    (float) $_SESSION['demo_payment_amount'] !== (float) $total
) {
    $_SESSION['demo_payment_token'] = bin2hex(random_bytes(32));
    $_SESSION['demo_payment_amount'] = (float) $total;
    $_SESSION['demo_payment_completed'] = false;
    unset($_SESSION['demo_payment_method']);
}
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=" . (int) $_SESSION['user_id']));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    $city = clean($_POST['city']);
    $payment = trim($_POST['payment'] ?? '');

        $allowed_payments = [
            'Cash on Delivery',
            'eSewa',
            'Khalti'
        ];

        if (!$name || !$email || !$phone || !$address || !$city) {
        flash('danger', 'Please fill all checkout details.');
        } elseif (!in_array($payment, $allowed_payments, true)) {
        flash('danger', 'Please select a valid payment method.');
        } elseif (
        $payment !== 'Cash on Delivery'
        && (
        empty($_SESSION['demo_payment_completed'])
        || empty($_SESSION['demo_payment_method'])
        || $_SESSION['demo_payment_method'] !== $payment
        || !isset($_SESSION['demo_payment_amount'])
        || (float) $_SESSION['demo_payment_amount'] !== (float) $total
    )
) {
    flash('danger', 'Please complete the payment before placing your order.');
} else {
        try {
            mysqli_begin_transaction($conn);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO orders
                (user_id,customer_name,email,phone,address,city,payment_method,total)
                VALUES (?,?,?,?,?,?,?,?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'issssssd',
                $_SESSION['user_id'],
                $name,
                $email,
                $phone,
                $address,
                $city,
                $payment,
                $total
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $orderId = mysqli_insert_id($conn);

            foreach ($items as $i) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO order_items
                    (order_id,product_id,product_name,price,quantity)
                    VALUES (?,?,?,?,?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    'iisdi',
                    $orderId,
                    $i['id'],
                    $i['name'],
                    $i['price'],
                    $i['quantity']
                );

                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $quantity = (int) $i['quantity'];
                $productId = (int) $i['id'];

                $stockStmt = mysqli_prepare(
                    $conn,
                    "UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                    AND stock >= ?"
                );

                mysqli_stmt_bind_param(
                    $stockStmt,
                    'iii',
                    $quantity,
                    $productId,
                    $quantity
                );

                mysqli_stmt_execute($stockStmt);

                if (mysqli_stmt_affected_rows($stockStmt) !== 1) {
                    mysqli_stmt_close($stockStmt);
                    throw new Exception('One or more products are out of stock.');
                }

                mysqli_stmt_close($stockStmt);
            }

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO payments (order_id,method,amount)
                VALUES (?,?,?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'isd',
                $orderId,
                $payment,
                $total
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

            $cartStmt = mysqli_prepare(
                $conn,
                "DELETE FROM cart
                WHERE user_id=?
                AND id IN ($placeholders)"
            );

            $cartParams = array_merge(
                [(int) $_SESSION['user_id']],
                $selectedItems
            );

            $cartTypes = 'i' . str_repeat('i', count($selectedItems));

            mysqli_stmt_bind_param(
                $cartStmt,
                $cartTypes,
                ...$cartParams
            );

            mysqli_stmt_execute($cartStmt);
            mysqli_stmt_close($cartStmt);

            mysqli_commit($conn);

            unset(
                $_SESSION['checkout_selected_items'],
                $_SESSION['demo_payment_token'],
                $_SESSION['demo_payment_amount'],
                $_SESSION['demo_payment_completed'],
                $_SESSION['demo_payment_method']
            );

            flash('success', 'Order placed successfully.');
            redirect("orders.php?id=" . $orderId);

        } catch (Throwable $e) {

            mysqli_rollback($conn);

            flash('danger', 'Order failed. Please try again.');
            redirect('cart.php');
        }
                }
            }
?>

<section class="page-hero">
    <div class="container"><span class="eyebrow">Secure Demo Checkout</span>
        <h1>Checkout</h1>
        <p>Complete your luxury perfume order.</p>
    </div>
</section>
<section class="section-pad">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <form method="post" id="checkoutForm" class="lux-panel lux-form checkout-form reveal-on-scroll">
                    <input type="hidden"
                        name="csrf_token"
                         value="<?php echo clean(csrf_token()); ?>">
                    <h3>Shipping Details</h3>
                    <input class="form-control" name="name" required value="<?php echo clean($user['name']); ?>"
                        placeholder="Full name">
                    <input class="form-control" name="email" type="email" required
                        value="<?php echo clean($user['email']); ?>" placeholder="Email">
                    <input class="form-control" name="phone" required value="<?php echo clean($user['phone']); ?>"
                        placeholder="Phone">
                    <input class="form-control" name="city" required placeholder="City">
                    <input class="form-control" name="address" required placeholder="Full Address">
                    <select id="paymentMethod" name="payment" class="form-select custom-select">
                        <option selected disabled>Select Payment Method</option>
                        <option>Cash on Delivery</option>
                        <option>eSewa</option>
                        <option>Khalti</option>
                    </select>
                    <button type="submit" id="placeOrderBtn" class="btn btn-gold w-100">

                        Place Order

                    </button>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="lux-panel order-summary-card reveal-on-scroll"><span class="eyebrow">Order Summary</span>
                    <h3>Your Scent</h3><?php foreach ($items as $i): ?>
                        <div class="summary-line">
                            <div class="d-flex align-items-center gap-2">

                                <img src="<?php echo product_image($i['image']); ?>"
                                    style="width:45px;height:45px;border-radius:8px;object-fit:cover;">

                                <span>
                                    <?php echo clean($i['name']); ?>
                                    <br>
                                    <small>x <?php echo $i['quantity']; ?></small>
                                </span>
                            </div>
                            <strong>
                                <?php echo money($i['price'] * $i['quantity']); ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="summary-line">
                        <span>Delivery</span>
                        <strong>FREE</strong>
                    </div>
                    <div class="summary-line total"><span>Total</span><strong><?php echo money($total); ?></strong>
                    </div>
                    <div class="checkout-trust"><span><i class="bi bi-lock"></i> Session protected</span><span><i
                                class="bi bi-receipt"></i> Order saved</span></div>
                </div>
            </div>
        </div>
    </div>

</section>


<?php include 'includes/footer.php'; ?>
<script>

    const form = document.getElementById("checkoutForm");
    const payment = document.getElementById("paymentMethod");
    const btn = document.getElementById("placeOrderBtn");

    let paymentSuccess = false;
    let popupOpened = false;
    let paymentPopup = null;

    form.addEventListener("submit", function (e) {

        if (paymentSuccess) {
            return;
        }

        if (payment.value === "Cash on Delivery") {

            btn.disabled = true;
            btn.innerHTML = "Processing...";

            return;
        }

        e.preventDefault();

        if (popupOpened) {
            return;
        }

            let url = "";

            if (payment.value === "eSewa") {

            url = "esewa_demo.php?token=<?php echo clean($_SESSION['demo_payment_token']); ?>";

            } else if (payment.value === "Khalti") {

            url = "khalti_demo.php?token=<?php echo clean($_SESSION['demo_payment_token']); ?>";

            }

        paymentPopup = window.open(
            url,
            "PaymentWindow",
            "width=450,height=700,left=500,top=80"
        );

        if (!paymentPopup) {

            alert("Please allow popups for payment.");

            return;
        }

        popupOpened = true;

        btn.disabled = true;
        btn.innerHTML = "Waiting for Payment...";

        const popupChecker = setInterval(function () {

            if (paymentPopup.closed) {

                clearInterval(popupChecker);

                if (!paymentSuccess) {

                    popupOpened = false;

                    btn.disabled = false;

                    btn.innerHTML = "Place Order";

                }

            }

        }, 500);

    });


    function paymentCancelled() {

        popupOpened = false;

        paymentSuccess = false;

        btn.disabled = false;

        btn.innerHTML = "Place Order";

    }

    window.paymentCancelled = paymentCancelled;


    function paymentCompleted() {

        paymentSuccess = true;

        popupOpened = false;

        btn.disabled = true;

        btn.innerHTML = "Processing Order";

        form.submit();

    }

    window.paymentCompleted = paymentCompleted;

</script>