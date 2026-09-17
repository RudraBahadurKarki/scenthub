<?php
$page_title = 'Login';
include 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = clean($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND status='active'");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);


    if (!$user) {

        flash('danger', 'This email is not registered.');

    }

    elseif (!password_ok($password, $user['password'])) {

        flash('danger', 'Incorrect password. Please try again.');

    }

    else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        flash('success', 'Welcome back, ' . $user['name'] . '.');


        if (isset($_SESSION['pending_cart'])) {

    $pending = $_SESSION['pending_cart'];

    $product_id = (int) $pending['product_id'];
    $quantity = max(1, (int) $pending['quantity']);
    $user_id = (int) $_SESSION['user_id'];

    $stockStmt = mysqli_prepare(
        $conn,
        "SELECT stock
         FROM products
         WHERE id=? AND status='active'
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stockStmt,
        "i",
        $product_id
    );

    mysqli_stmt_execute($stockStmt);

    $productRow = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stockStmt)
    );

    mysqli_stmt_close($stockStmt);

    if (!$productRow || (int) $productRow['stock'] <= 0) {

        flash('danger', 'The selected product is currently out of stock.');

    } else {

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
            $user_id,
            $product_id
        );

        mysqli_stmt_execute($cartStmt);

        $existingCart = mysqli_fetch_assoc(
            mysqli_stmt_get_result($cartStmt)
        );

        mysqli_stmt_close($cartStmt);

        $existingQty = $existingCart
            ? (int) $existingCart['quantity']
            : 0;

        $newQty = $existingQty + $quantity;
        $stock = (int) $productRow['stock'];

        if ($newQty > $stock) {

            flash(
                'danger',
                'Only ' . $stock . ' item(s) are currently available.'
            );

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO cart (user_id, product_id, quantity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 quantity = quantity + VALUES(quantity)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iii",
                $user_id,
                $product_id,
                $quantity
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            flash('success', 'Perfume added to your cart.');
        }
    }

    unset($_SESSION['pending_cart']);
}


        if ($user['role'] === 'admin') {
            redirect('admin/index.php');
        }


        if (isset($_SESSION['redirect_after_login'])) {

            $redirect = $_SESSION['redirect_after_login'];

            unset($_SESSION['redirect_after_login']);

            redirect($redirect);
        }


        redirect('index.php');
    }
}
?>

<section class="auth-page">
    <div class="auth-card reveal-on-scroll">
        <span class="eyebrow">Member Access</span>

        <h1>Welcome Back</h1>

        <p>Continue your fragrance journey with ScentHub.</p>

        <?php show_flash(); ?>

        <form method="post" class="lux-form">
            <input type="hidden"
                name="csrf_token"
                value="<?php echo clean(csrf_token()); ?>">
            <input class="form-control" name="email" type="email" required placeholder="Email">

            <input class="form-control" name="password" type="password" required placeholder="Password">

            <button class="btn btn-gold w-100">Login</button>

            <p>New to ScentHub? <a href="register.php">Create account</a></p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>