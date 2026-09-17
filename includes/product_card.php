<div class="col-sm-6 col-lg-3">

    <a href="product.php?id=<?php echo $p['id']; ?>">

        <div class="product-card tilt-card reveal-on-scroll">

            <div class="product-img">
                <div class="product-spotlight"></div>

                <?php if (!empty($p['is_new'])): ?>
                    <span class="badge badge-new">New</span>
                <?php endif; ?>

                <?php if (!empty($p['is_bestseller'])): ?>
                    <span class="badge badge-best">Best Seller</span>
                <?php endif; ?>

                <img src="<?php echo product_image($p['image']); ?>" alt="<?php echo clean($p['name']); ?>">
            </div>

            <div class="product-body">
                <p class="product-brand">
                    <?php echo clean($p['brand']); ?>
                    ·
                    <?php echo clean($p['category']); ?>
                </p>

                <h3><?php echo clean($p['name']); ?></h3>

                <div class="product-divider"></div>

                <div class="product-bottom">
                    <strong><?php echo money($p['price']); ?></strong>

                    <span class="btn btn-sm btn-gold">
                        View Details
                    </span>

                </div>
            </div>

        </div>

    </a>

</div>