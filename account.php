<?php
require_once 'includes/auth.php';
$page_title = 'My Account';
include 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $errors = [];

        if (!preg_match('/^[a-zA-Z ]{3,50}$/', $name)) {
            $errors[] = 'Enter a valid full name.';
        }

        if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
            $errors[] = 'Enter a valid Nepal mobile number.';
        }

        if (strlen($address) < 5 || strlen($address) > 255) {
            $errors[] = 'Address must be between 5 and 255 characters.';
        }

        if (!empty($errors)) {
            flash('danger', implode('<br>', $errors));
            redirect('account.php');
        }
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    session_unset();
    session_destroy();

    flash('danger', 'Your account could not be found. Please login again.');
    redirect('login.php');
}

$orders = fetch_all($conn, "SELECT * FROM orders WHERE user_id=" . (int) $_SESSION['user_id'] . " ORDER BY created_at DESC LIMIT 5");
?>
<section class="page-hero">
    <div class="container"><span class="eyebrow">Client Lounge</span>
        <h1>My Account</h1>
        <p>Manage profile and fragrance orders.</p>
    </div>
</section>
<section class="section-pad">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <form method="post" class="lux-panel lux-form account-card reveal-on-scroll">
                    <input type="hidden"
                       name="csrf_token"
                         value="<?php echo clean(csrf_token()); ?>">
                    <span class="eyebrow">Profile</span>
                    <h3>Your Details</h3>
                    <input class="form-control" name="name" value="<?php echo clean($user['name']); ?>" required>
                    <input class="form-control" value="<?php echo clean($user['email']); ?>" disabled>
                    <input class="form-control" name="phone" value="<?php echo clean($user['phone']); ?>">
                    <textarea class="form-control" name="address"><?php echo clean($user['address']); ?></textarea>
                    <button class="btn btn-gold">Save Profile</button>
                </form>
            </div>
            <div class="col-lg-7">
                <div class="lux-panel account-card reveal-on-scroll"><span class="eyebrow">Orders</span>
                    <h3>Recent Purchases</h3><?php if (!$orders): ?>
                        <p>No orders yet.</p><?php endif; ?>
                    <?php

                    foreach ($orders as $o):
                        ?>
                        <div class="order-row">
                            <div>
                                <strong>Order #<?php echo $o['id']; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <span>
                                    <?php echo date(' M d, Y', strtotime($o['created_at'])); ?>
                                    - <strong class="order-status">
                                        <?php echo strtoupper(clean($o['status'])); ?>
                                    </strong>
                                </span>
                            </div>

                            <div>
                                <b><?php echo money($o['total']); ?></b>
                                <a href="orders.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-gold">
                                    View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>