<?php
$page_title = 'Register';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $errors = [];

    if (!preg_match("/^[a-zA-Z ]{3,50}$/", $name)) {
        $errors[] = "Enter a valid full name.";
    }

    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = "Please enter a valid Gmail address (example@gmail.com).";
    }

    if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
        $errors[] = "Enter a valid Nepal mobile number.";
    }

    if (strlen($address) < 5) {
        $errors[] = "Please enter a valid address.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }


    if (!empty($errors)) {

        flash('danger', implode("<br>", $errors));

    } else {

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email=?"
        );

        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);


        if (mysqli_stmt_num_rows($check) > 0) {

            flash('danger', 'Email already registered.');

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (name,email,phone,address,password,role,status)
                VALUES (?,?,?,?,?,'user','active')"
            );


            mysqli_stmt_bind_param(
                $stmt,
                'sssss',
                $name,
                $email,
                $phone,
                $address,
                $hash
            );


            if (mysqli_stmt_execute($stmt)) {

                flash(
                    'success',
                    'Account created successfully. Please login.'
                );

                redirect('login.php');

            } else {

                flash('danger', 'Registration failed.');

            }
        }
    }
}
?>
<section class="auth-page">
    <div class="auth-card reveal-on-scroll"><span class="eyebrow">Join ScentHub</span>
        <h1>Create Account</h1>
        <p>Build your personal scent wardrobe and track every order.</p>
        <form method="post" class="lux-form">
            <input type="hidden"
                name="csrf_token"
                value="<?php echo clean(csrf_token()); ?>">
            <input class="form-control" name="name" required placeholder="Full name">
            <input class="form-control" name="email" type="email" pattern="[a-zA-Z0-9._%+-]+@gmail\.com" required
                placeholder="example@gmail.com"> <input class="form-control" name="phone" pattern="(98|97)[0-9]{8}"
                maxlength="10" required placeholder="98XXXXXXXX"> <textarea class="form-control" name="address"
                placeholder="Address"></textarea>
            <input class="form-control" name="password" type="password" minlength="8" required
                placeholder="Minimum 8 characters"> <input class="form-control" name="confirm" type="password" required
                placeholder="Confirm password">
            <button class="btn btn-gold w-100">Register</button>
            <p>Already registered? <a href="login.php">Login</a></p>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>