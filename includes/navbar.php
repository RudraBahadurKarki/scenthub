<div class="top-announcement">
    <div class="container d-flex justify-content-between align-items-center gap-3">
        <span>Complimentary luxury wrapping on selected gift sets</span>
        <span class="d-none d-md-inline">COD, eSewa and Khalti available</span>
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top" id="luxNavbar">
    <div class="container">
        <a class="navbar-brand luxury-logo" href="index.php"><span>Scent</span>Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li>
            </ul>
            <form class="d-flex nav-search me-lg-3" action="shop.php" method="get">
                <input class="form-control" type="search" name="search" placeholder="Search perfumes">
                <button class="btn btn-gold" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex align-items-center gap-2">
                <a href="cart.php" class="btn btn-icon position-relative"><i class="bi bi-bag"></i>
                    <span class="cart-badge"><?php echo get_cart_count($conn); ?></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <a href="account.php" class="btn btn-outline-gold nav-account"><i
                            class="bi bi-person-circle"></i><span>Account</span></a>
                    <a href="logout.php" class="btn btn-gold">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-gold">Login</a>
                    <a href="register.php" class="btn btn-gold">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>