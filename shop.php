
<?php
$page_title = 'Shop Perfumes';
include 'includes/header.php';
$where = [
    "p.status = 'active'",
    "b.status = 'active'",
    "c.status = 'active'",
    "f.status = 'active'"
];
$params = [];
$types = '';

if (!empty($_GET['search'])) {
    $where[] = "(p.name LIKE ? OR b.name LIKE ? OR c.name LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}

foreach (['category' => 'p.category_id', 'brand' => 'p.brand_id', 'family' => 'p.family_id'] as $key => $col) {
    if (!empty($_GET[$key])) {
        $where[] = "$col=?";
        $params[] = (int) $_GET[$key];
        $types .= 'i';
    }
}

$sortSql = 'p.created_at DESC';
if (($_GET['sort'] ?? '') === 'price_low')
    $sortSql = 'p.price ASC';
if (($_GET['sort'] ?? '') === 'price_high')
    $sortSql = 'p.price DESC';

$sql = "SELECT p.*, b.name brand, c.name category, f.name family
        FROM products p
        JOIN brands b ON p.brand_id=b.id
        JOIN categories c ON p.category_id=c.id
        JOIN fragrance_families f ON p.family_id=f.id";

if ($where)
    $sql .= ' WHERE ' . implode(' AND ', $where);

$sql .= " ORDER BY $sortSql";

$stmt = mysqli_prepare($conn, $sql);

if ($params)
    mysqli_stmt_bind_param($stmt, $types, ...$params);

mysqli_stmt_execute($stmt);
$products = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$categories = fetch_all($conn, "SELECT * FROM categories ORDER BY name");
$brands = fetch_all($conn, "SELECT * FROM brands ORDER BY name");
$families = fetch_all($conn, "SELECT * FROM fragrance_families ORDER BY name");
?>
<section class="page-hero shop-hero">
    <div class="container"><span class="eyebrow">Fragrance Wardrobe</span>
        <h1>Perfume Collection</h1>
        <p>Filter by brands, category, fragrance and price.</p>
    </div>
</section>
<section class="section-pad shop-section">
    <div class="container">
        <form class="filter-panel reveal-on-scroll" method="get">
            <div class="filter-label"><i class="bi bi-sliders"></i><span>Refine</span></div>
            <input class="form-control" name="search" value="<?php echo clean($_GET['search'] ?? ''); ?>"
                placeholder="Search perfumes">
            <select class="form-select" name="category">
                <option value="">All Categories</option><?php foreach ($categories as $x): ?>
                    <option value="<?php echo $x['id']; ?>" <?php echo (($_GET['category'] ?? '') == $x['id']) ? 'selected' : ''; ?>><?php echo clean($x['name']); ?></option><?php endforeach; ?>
            </select>
            <select class="form-select" name="brand">
                <option value="">All Brands</option><?php foreach ($brands as $x): ?>
                    <option value="<?php echo $x['id']; ?>" <?php echo (($_GET['brand'] ?? '') == $x['id']) ? 'selected' : ''; ?>>
                        <?php echo clean($x['name']); ?></option><?php endforeach; ?>
            </select>
            <select class="form-select" name="family">
                <option value="">All Fragrance</option><?php foreach ($families as $x): ?>
                    <option value="<?php echo $x['id']; ?>" <?php echo (($_GET['family'] ?? '') == $x['id']) ? 'selected' : ''; ?>><?php echo clean($x['name']); ?></option><?php endforeach; ?>
            </select>
            <select class="form-select" name="sort">
                <option value="">Newest</option>
                <option value="price_low" <?php echo (($_GET['sort'] ?? '') === 'price_low') ? 'selected' : ''; ?>>Price low
                    to high</option>
                <option value="price_high" <?php echo (($_GET['sort'] ?? '') === 'price_high') ? 'selected' : ''; ?>>Price
                    high to low</option>
            </select>
            <button class="btn btn-gold">Filter</button>
        </form>
        <div class="shop-count"><?php echo count($products); ?> scent<?php echo count($products) === 1 ? '' : 's'; ?>
            found</div>
        <div class="row g-4 mt-3 product-grid">
            <?php if (!$products): ?>
                <div class="empty-state">
                    <h3>No perfumes found.</h3>
                    <p>Try a different brand or collection.</p>
                </div><?php endif; ?>
            <?php foreach ($products as $p)
                include 'includes/product_card.php'; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>