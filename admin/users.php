<?php
require_once 'includes/auth.php';

$page_title = 'Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
    verify_csrf();

    $id = (int) $_POST['toggle'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET status=IF(status='active','blocked','active') WHERE id=? AND role='user'"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {
        flash('success', 'User status changed.');
    } else {
        flash('warning', 'User not found or cannot be modified.');
    }

    mysqli_stmt_close($stmt);

    redirect('users.php');
}

include 'includes/header.php';

$users = fetch_all($conn, "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
?>

<div class="admin-title">
    <h1>Users</h1>
</div>

<div class="lux-panel">
    <table class="table luxury-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Joined</th>
                <th style="width:120px;" class="text-center">Action</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo clean($u['name']); ?></td>

                    <td><?php echo clean($u['email']); ?></td>

                    <td><?php echo clean($u['phone']); ?></td>

                    <td>
                        <?php if ($u['status'] == 'active'): ?>
                            <span class="status-badge status-active">
                                <?php echo clean($u['status']); ?>
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-blocked">
                                <?php echo clean($u['status']); ?>
                            </span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                    </td>

                    <td class="text-end">
                        <form method="POST" action="users.php" style="display:inline;">

                            <input type="hidden"
                                name="csrf_token"
                                value="<?php echo clean(csrf_token()); ?>">

                            <input type="hidden"
                                name="toggle"
                                value="<?php echo (int) $u['id']; ?>">

                            <button type="submit"
                                class="btn btn-sm btn-outline-gold">
                                Block/Unblock
                            </button>

                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>