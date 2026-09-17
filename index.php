<?php
$page_title = 'Find Your Signature Scent';
include 'includes/header.php';

$featured = fetch_all($conn, "
SELECT p.*, b.name AS brand, c.name AS category
FROM products p
JOIN brands b ON p.brand_id = b.id
JOIN categories c ON p.category_id = c.id
WHERE p.is_featured = 1
  AND p.status = 'active'
  AND b.status = 'active'
  AND c.status = 'active'
ORDER BY p.id DESC
LIMIT 6
");

$best = fetch_all($conn, "
SELECT p.*, b.name AS brand, c.name AS category
FROM products p
JOIN brands b ON p.brand_id = b.id
JOIN categories c ON p.category_id = c.id
WHERE p.is_bestseller = 1
  AND p.status = 'active'
  AND b.status = 'active'
  AND c.status = 'active'
ORDER BY p.id DESC
LIMIT 4
");

$new = fetch_all($conn, "
SELECT p.*, b.name AS brand, c.name AS category
FROM products p
JOIN brands b ON p.brand_id = b.id
JOIN categories c ON p.category_id = c.id
WHERE p.is_new = 1
  AND p.status = 'active'
  AND b.status = 'active'
  AND c.status = 'active'
ORDER BY p.id DESC
LIMIT 4
");

$categories = fetch_all($conn, "
SELECT *
FROM categories
WHERE status = 'active'
ORDER BY name
");

$brands = fetch_all($conn, "
SELECT *
FROM brands
WHERE status = 'active'
ORDER BY name
");

$families = fetch_all($conn, "
SELECT *
FROM fragrance_families
WHERE status = 'active'
ORDER BY name
");
?>
<section class="hero-section">
    <div class="hero-grain"></div>
    <div class="hero-light hero-light-one"></div>
    <div class="hero-light hero-light-two"></div>
    <div class="container">
        <div class="row align-items-center min-vh-hero">
            <div class="col-lg-6 reveal-on-scroll">
                <span class="eyebrow">Online Perfume Store</span>
                <h1>ScentHub</h1>
                <p class="hero-tagline">Find Your Signature Scent</p>
                <p class="hero-copy">Discover luxury perfumes selected for elegance, longevity, and unforgettable
                    presence.</p>
                <div class="hero-actions">
                    <a href="shop.php" class="btn btn-gold btn-lg">Shop Collection</a>
                    <a href="#best-sellers" class="btn btn-outline-light btn-lg">Discover Luxury</a>
                </div>
                <div class="hero-stats">
                    <div><strong>18+</strong><span>Perfumes</span></div>
                    <div><strong>8</strong><span>Luxury houses</span></div>
                    <div><strong>7</strong><span>Fragrance moods</span></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-showcase tilt-card reveal-on-scroll">
                    <div class="showcase-ring"></div>
                    <div class="showcase-card back-card"></div>
                    <div class="hero-bottle">
                        <img src="assets/images/homepage.png" alt="ScentHub luxury perfume">
                    </div>
                    <div class="scent-note scent-note-top">Woody Amber</div>
                    <div class="scent-note scent-note-bottom">Luxury EDP</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad collection-section reveal-on-scroll">
    <div class="container">
        <div class="section-heading"><span>Curated</span>
            <h2>Featured Collections</h2>
            <p>Choose by occasion, personality, and lasting impression.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-6 col-lg-2">
                    <a class="category-tile tilt-card" href="shop.php?category=<?php echo $cat['id']; ?>">
                        <i class="bi bi-stars"></i>
                        <span><?php echo clean($cat['name']); ?></span>
                        <small>Explore Collection</small>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-pad dark-band product-showcase-band" id="best-sellers">
    <div class="container">
        <div class="section-heading"><span>Iconic</span>
            <h2>Best Sellers</h2>
            <p>The most memorable perfumes in the ScentHub edit.</p>
        </div>
        <div class="row g-4"><?php foreach ($best as $p)
            include 'includes/product_card.php'; ?></div>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="section-heading"><span>Fresh Drops</span>
            <h2>New Arrivals</h2>
            <p>Modern profiles for daily luxury and evening presence.</p>
        </div>
        <div class="row g-4"><?php foreach ($new as $p)
            include 'includes/product_card.php'; ?></div>
    </div>
</section>

<section class="section-pad fragrance-strip reveal-on-scroll">
    <div class="container">
        <div class="section-heading"><span>Mood</span>
            <h2>Shop by Fragrance Family</h2>
            <p>From soft florals to deep woods and spicy amber trails.</p>
        </div>
        <div class="family-grid">
            <?php foreach ($families as $fam): ?>
                <a href="shop.php?family=<?php echo $fam['id']; ?>"><?php echo clean($fam['name']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="offer-banner reveal-on-scroll">
            <div>
                <span class="eyebrow">Limited Edition</span>
                <h2>The Signature Gift Ritual</h2>
                <p>Luxury gift sets with polished presentation, warm trails, and an unmistakable finishing touch.</p>
            </div>
            <div class="offer-visual"><img src="assets/images/giftset.jpg" alt="Luxury gift perfume"></div>
            <a href="shop.php?category=5" class="btn btn-gold">Explore Gifts</a>
        </div>
    </div>
</section>

<section class="section-pad dark-band">
    <div class="container">
        <div class="section-heading"><span>Houses</span>
            <h2>Luxury Brands</h2>
            <p>Designer icons and crowd-favourite fragrance houses.</p>
        </div>
        <div class="brand-row"><?php foreach ($brands as $brand): ?><a
                    href="shop.php?brand=<?php echo $brand['id']; ?>"><?php echo clean($brand['name']); ?></a><?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-pad promise-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="promise-card reveal-on-scroll"><i class="bi bi-gem"></i>
                    <h3>Curated Luxury</h3>
                    <p>Every product is framed like a personal signature, not just another catalog item.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="promise-card reveal-on-scroll"><i class="bi bi-stars"></i>
                    <h3>Fragrance Discovery</h3>
                    <p>Shop by family, category, house, mood, and occasion with elegant filtering.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="promise-card reveal-on-scroll"><i class="bi bi-bag-heart"></i>
                    <h3>Gift-Ready Flow</h3>
                    <p>Cart, checkout, order tracking, and admin handling built for a full demo experience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="section-heading"><span>Reviews</span>
            <h2>What Customers Say</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial reveal-on-scroll">"Premium packaging and genuine perfume quality."<strong>Asmita
                        K.</strong></div>
            </div>
            <div class="col-md-4">
                <div class="testimonial reveal-on-scroll">"The shop feels luxurious and checkout is
                    simple."<strong>Rohan S.</strong></div>
            </div>
            <div class="col-md-4">
                <div class="testimonial reveal-on-scroll">"Best gift set collection for perfume lovers highly
                    recommended."<strong>Neha
                        P.</strong></div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>