<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    exit;
}

verify_csrf();


$cartId = (int) $_POST['cart_id'];
$qty = max(1, (int) $_POST['quantity']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT p.stock
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.id=? AND c.user_id=?"
);

mysqli_stmt_bind_param($stmt, "ii", $cartId, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    exit("Invalid");
}

if ((int) $row['stock'] <= 0) {
    http_response_code(400);
    exit("Product is out of stock.");
}

if ($qty > (int) $row['stock']) {
    $qty = (int) $row['stock'];
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE cart
     SET quantity=?
     WHERE id=? AND user_id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $qty,
    $cartId,
    $_SESSION['user_id']
);

mysqli_stmt_execute($stmt);

echo "success";