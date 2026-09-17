<?php
require_once 'includes/auth.php';

$page_title = 'Contact Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    verify_csrf();

    $id = (int) $_POST['delete'];

    $stmt = mysqli_prepare($conn, "DELETE FROM contact_messages WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {
        flash('success', 'Message deleted.');
    } else {
        flash('warning', 'Message not found.');
    }

    mysqli_stmt_close($stmt);

    redirect('contacts.php');
}

include 'includes/header.php';

$messages = fetch_all($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<div class="admin-title">
    <h1>Contact Messages</h1>
</div>

<div class="row g-4">

    <?php foreach ($messages as $m): ?>

        <div class="col-lg-6">
            <div class="lux-panel">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h3 class="mb-0 fw-bold">
                        <?php echo clean($m['subject']); ?>
                    </h3>

                    <form method="POST" action="contacts.php"
                        onsubmit="return confirm('Delete this message?');">

                        <input type="hidden"
                            name="csrf_token"
                            value="<?php echo clean(csrf_token()); ?>">

                        <input type="hidden"
                            name="delete"
                            value="<?php echo (int) $m['id']; ?>">

                        <button type="submit"
                            class="btn btn-sm rounded-pill px-3 py-2 shadow-sm text-white"
                            style="background:linear-gradient(135deg,#dc3545,#8b0000);border:none;">

                            <i class="bi bi-trash3-fill me-1"></i> Delete

                        </button>

                    </form>

                </div>

                <p class="mb-2">
                    <i class="bi bi-person-fill text-warning me-1"></i>

                    <strong>
                        <?php echo clean($m['name']); ?>
                    </strong>

                    <span class="mx-2 text-muted">•</span>

                    <i class="bi bi-envelope-fill text-warning me-1"></i>

                    <?php echo clean($m['email']); ?>
                </p>

                <p class="mb-3 text-light">
                    <?php echo clean($m['message']); ?>
                </p>

                <small class="d-inline-flex align-items-center text-secondary"
                    style="color:#c9a227 !important; font-weight:500;">

                    <i class="bi bi-calendar-event-fill me-2"></i>

                    <?php echo date('M d, Y • h:i A', strtotime($m['created_at'])); ?>

                </small>

            </div>
        </div>

    <?php endforeach; ?>

    <?php
    if (!$messages) {
        echo '<p>No messages.</p>';
    }
    ?>

</div>

<?php include 'includes/footer.php'; ?>