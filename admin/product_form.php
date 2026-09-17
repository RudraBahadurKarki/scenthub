<?php
$categories = fetch_all($conn, "SELECT * FROM categories ORDER BY name");
$brands = fetch_all($conn, "SELECT * FROM brands ORDER BY name");
$families = fetch_all($conn, "SELECT * FROM fragrance_families ORDER BY name");
?>
<form method="post" enctype="multipart/form-data" class="lux-panel lux-form product-admin-form">
    <input type="hidden"
      name="csrf_token"
      value="<?php echo clean(csrf_token()); ?>">
    <div class="row g-3">
        <div class="col-md-8"><label>Name</label><input class="form-control" name="name" required
                value="<?php echo clean($product['name'] ?? ''); ?>"></div>
        <div class="col-md-4"><label>Price</label><input class="form-control" type="number" step="1000" name="price"
                required value="<?php echo clean($product['price'] ?? ''); ?>"></div>
        <div class="col-md-4"><label>Brand</label><select class="form-select"
                name="brand_id"><?php foreach ($brands as $x): ?>
                    <option value="<?php echo $x['id']; ?>" <?php echo (($product['brand_id'] ?? '') == $x['id']) ? 'selected' : ''; ?>><?php echo clean($x['name']); ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-4"><label>Category</label><select class="form-select"
                name="category_id"><?php foreach ($categories as $x): ?>
                    <option value="<?php echo $x['id']; ?>" <?php echo (($product['category_id'] ?? '') == $x['id']) ? 'selected' : ''; ?>><?php echo clean($x['name']); ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-4"><label>Fragrance Family</label><select class="form-select"
                name="family_id"><?php foreach ($families as $x): ?>
                    <option value="<?php echo $x['id']; ?>" <?php echo (($product['family_id'] ?? '') == $x['id']) ? 'selected' : ''; ?>><?php echo clean($x['name']); ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-4"><label>Stock</label><input class="form-control" type="number" name="stock" required
                value="<?php echo clean($product['stock'] ?? 0); ?>"></div>
        <div class="col-md-8"><label>Image</label><input class="form-control" type="file" name="image"
                accept="image/*"><?php if (!empty($product['image'])): ?><small>Current:
                    <?php echo clean($product['image']); ?></small><?php endif; ?></div>
        <div class="col-12"><label>Description</label><textarea class="form-control" name="description"
                rows="5"><?php echo clean($product['description'] ?? ''); ?></textarea></div>
        <div class="col-12 d-flex gap-4 flex-wrap"><label><input type="checkbox" name="is_featured" <?php echo !empty($product['is_featured']) ? 'checked' : ''; ?>> Featured</label><label><input type="checkbox"
                    name="is_bestseller" <?php echo !empty($product['is_bestseller']) ? 'checked' : ''; ?>> Best
                Seller</label><label><input type="checkbox" name="is_new" <?php echo !empty($product['is_new']) ? 'checked' : ''; ?>> New Arrival</label></div>
        <div class="col-12"><button class="btn btn-gold"><?php echo $button; ?></button><a href="products.php"
                class="btn btn-outline-gold">Cancel</a></div>
    </div>
</form>