<?php
require_once 'includes/auth.php';

$page_title = $title;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    if (isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');

    if ($name === '' || strlen($name) > 100) {
        flash('danger', 'Name is required and must be 100 characters or less.');
        redirect($self);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO $table (name) VALUES (?)");
        mysqli_stmt_bind_param($stmt, 's', $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        flash('success', "$title added.");
    }

    if (isset($_POST['edit'])) {
    $name = trim($_POST['name'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($name === '' || strlen($name) > 100 || $id <= 0) {
        flash('danger', 'Please provide valid information.');
        redirect($self);
    }

    $stmt = mysqli_prepare($conn, "UPDATE $table SET name=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'si', $name, $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 1) {
            flash('success', "$title updated.");
        } else {
            flash('warning', "$title item not found or unchanged.");
        }

        mysqli_stmt_close($stmt);
    }

    if (isset($_POST['delete'])) {
        $id = (int) $_POST['delete'];

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE $table SET status='inactive' WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        flash('success', "$title removed from public selection.");
    }

    if (isset($_POST['restore'])) {
        $id = (int) $_POST['restore'];

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE $table SET status='active' WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        flash('success', "$title restored successfully.");
    }

    redirect($self);
}

include 'includes/header.php';

$rows = fetch_all($conn, "SELECT * FROM $table ORDER BY name");

$edit = null;

if (isset($_GET['edit'])) {
    $edit = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT * FROM $table WHERE id=" . (int) $_GET['edit']
        )
    );
}
?>

<div class="admin-title">
    <h1><?php echo $title; ?></h1>
</div>

<div class="row g-4">

    <div class="col-lg-4">

        <form method="post" class="lux-panel lux-form">

            <h3><?php echo $edit ? 'Edit' : 'Add'; ?></h3>

            <input type="hidden"
                name="csrf_token"
                value="<?php echo clean(csrf_token()); ?>">

            <input type="hidden"
                name="id"
                value="<?php echo $edit['id'] ?? ''; ?>">

            <input class="form-control"
                name="name"
                required
                value="<?php echo clean($edit['name'] ?? ''); ?>">

            <button class="btn btn-gold"
                name="<?php echo $edit ? 'edit' : 'add'; ?>">
                <?php echo $edit ? 'Update' : 'Add'; ?>
            </button>

        </form>

    </div>

    <div class="col-lg-8">

        <div class="lux-panel">

            <table class="table luxury-table">

                <thead>
                    <tr>
                        <th class="align-middle">Name</th>
                        <th class="text-end align-middle">Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $r): ?>

                        <tr <?php echo $r['status'] === 'inactive' ? 'style="opacity: 0.5;"' : ''; ?>>

                            <td class="align-middle">

                                <?php echo clean($r['name']); ?>

                                <?php if ($r['status'] === 'inactive'): ?>

                                    <small class="text-danger d-block"
                                        style="font-size: 11px;">
                                        [ InActive ]
                                    </small>

                                <?php else: ?>

                                    <small class="text-success d-block"
                                        style="font-size: 11px;">
                                        [ Active ]
                                    </small>

                                <?php endif; ?>

                            </td>

                            <td class="text-end align-middle">

                                <a class="btn btn-sm btn-outline-gold"
                                    href="<?php echo $self; ?>?edit=<?php echo $r['id']; ?>">
                                    Edit
                                </a>

                                <?php if ($r['status'] === 'inactive'): ?>

                                    <form method="POST"
                                        action="<?php echo $self; ?>"
                                        style="display:inline;"
                                        onsubmit="return confirm('Restore this item?');">

                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?php echo clean(csrf_token()); ?>">

                                        <input type="hidden"
                                            name="restore"
                                            value="<?php echo (int) $r['id']; ?>">

                                        <button type="submit"
                                            class="btn btn-sm btn-outline-success">
                                            Restore
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <form method="POST"
                                        action="<?php echo $self; ?>"
                                        style="display:inline;"
                                        onsubmit="return confirm('Delete this item?');">

                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?php echo clean(csrf_token()); ?>">

                                        <input type="hidden"
                                            name="delete"
                                            value="<?php echo (int) $r['id']; ?>">

                                        <button type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>