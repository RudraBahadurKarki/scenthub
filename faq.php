<?php $page_title = 'FAQ';
include 'includes/header.php'; ?>
<section class="page-hero">
    <div class="container">
        <h1>FAQ</h1>
        <p>Quick answers for ScentHub customers.</p>
    </div>
</section>
<section class="section-pad">
    <div class="container">
        <div class="lux-panel accordion luxury-accordion" id="faq">
            <?php $faqs = ['Are products genuine?' => 'This academic demo uses sample products, but the flow is designed for genuine perfume inventory management.', 'Which payment methods are available?' => 'Cash on Delivery, eSewa dummy/manual, and Khalti dummy/manual are available for checkout.', 'Can I track orders?' => 'Yes, logged-in users can view order status and details from My Account.', 'Can admins manage stock?' => 'Yes, admins can add/edit products, stock, categories, brands, and order status.'];
            $i = 0;
            foreach ($faqs as $q => $a):
                $i++; ?>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button <?php echo $i > 1 ? 'collapsed' : ''; ?>"
                            data-bs-toggle="collapse" data-bs-target="#f<?php echo $i; ?>"><?php echo $q; ?></button></h2>
                    <div id="f<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i == 1 ? 'show' : ''; ?>"
                        data-bs-parent="#faq">
                        <div class="accordion-body"><?php echo $a; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section><?php include 'includes/footer.php'; ?>