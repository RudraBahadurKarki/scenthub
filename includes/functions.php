<?php
require_once __DIR__ . '/../config/db.php';

function clean($value)
{
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money($amount)
{
    return 'Rs. ' . number_format((float) $amount, 2);
}

function flash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function show_flash()
{
    if (empty($_SESSION['flash']))
        return;
    foreach ($_SESSION['flash'] as $item) {
        echo '<div class="alert alert-' . clean($item['type']) . ' alert-dismissible fade show luxury-alert" role="alert">'
            . clean($item['message']) .
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_user()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function password_ok($plain, $stored)
{
    return password_verify($plain, $stored);
}
function get_cart_count($conn)
{
    if (!is_logged_in())
        return 0;
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(quantity),0) total FROM cart WHERE user_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return (int) $row['total'];
}

function product_image($image)
{
    if (!$image)
        return 'assets/images/perfume-placeholder.svg';
    if (str_starts_with($image, 'http'))
        return $image;
    return 'assets/uploads/' . $image;
}

function fetch_all($conn, $sql)
{
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}


function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf()
{
    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}
?>