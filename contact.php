<?php
$page_title = 'Contact Us';
include 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $errors = [];

    if (!preg_match('/^[a-zA-Z ]{3,50}$/', $name)) {
        $errors[] = 'Enter a valid name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($subject === '' || strlen($subject) > 150) {
        $errors[] = 'Subject must be between 1 and 150 characters.';
    }

    if ($message === '' || strlen($message) > 3000) {
        $errors[] = 'Message must be between 1 and 3000 characters.';
    }

    if (!empty($errors)) {
        flash('danger', implode('<br>', $errors));
        redirect('contact.php');
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $subject, $message);
        mysqli_stmt_execute($stmt);
        flash('success', 'Message sent. Our team will contact you soon.');
        redirect('contact.php');
}
?>
<section class="page-hero">
    <div class="container"><span class="eyebrow">Concierge</span>
        <h1>Contact ScentHub</h1>
        <p>Questions about fragrance, orders, or gifting?</p>
    </div>
</section>
<section class="section-pad">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="lux-panel contact-card reveal-on-scroll"><span class="eyebrow">Visit Us</span>
                    <h3>Luxury desk</h3>
                    <p>Kathmandu, Nepal</p>
                    <p>Email: perfume@scenthub.com</p>
                    <p>Phone: +977-98754648498</p>
                    <div class="contact-mini-grid"><span>Gift edits</span><span>Order support</span><span>Fragrance
                            advice</span></div>
                </div>
            </div>
            <div class="col-lg-7">
                <form method="post" class="lux-panel lux-form reveal-on-scroll">
                    <input type="hidden"
                    name="csrf_token"
                    value="<?php echo clean(csrf_token()); ?>">
                    <h3>Send a Message</h3><input class="form-control" name="name" required placeholder="Name"><input
                        class="form-control" name="email" type="email" required placeholder="Email"><input
                        class="form-control" name="subject" required placeholder="Subject"><textarea
                        class="form-control" name="message" required placeholder="Message"></textarea><button
                        class="btn btn-gold">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>