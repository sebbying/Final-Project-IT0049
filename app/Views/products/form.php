<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<a class="back" href="<?= site_url('products') ?>">← Back to products</a>
<div class="page-heading"><div><span class="eyebrow">YOUR CATALOG</span><h1><?= esc($title) ?></h1><p class="muted">Give your product the details it needs.</p></div></div>
<form method="post" action="<?= site_url($product ? 'products/' . $product['id'] : 'products') ?>" enctype="multipart/form-data" class="panel form-panel">
    <?= csrf_field() ?>
    <?php if ($product): ?><input type="hidden" name="original_stock" value="<?= field_value('original_stock', $product['stock_quantity']) ?>"><?php endif ?>
    <div class="form-grid"><div class="form-stack">
        <label>Product name<input name="name" required maxlength="100" value="<?= field_value('name', $product['name'] ?? '') ?>"></label>
        <div class="two-fields"><label>Price (₱)<input name="price" type="number" min="0.01" max="99999999.99" step="0.01" required value="<?= field_value('price', $product['price'] ?? '') ?>"></label><label>Stock quantity<input name="stock_quantity" type="number" min="0" max="1000000" step="1" required value="<?= field_value('stock_quantity', $product['stock_quantity'] ?? 0) ?>"></label></div>
        <div class="hint">Stock updates are checked against recent sales. If stock changes while you edit, reload this page before saving.</div>
    </div><div class="upload-box"><img class="image-preview" id="product-preview" src="<?= media_url('products', $product['image'] ?? null) ?>" alt="Product preview"><label>Product image<input name="image" type="file" accept="image/jpeg,image/png,image/webp" data-preview="product-preview"><small>JPEG, PNG, or WebP · up to 2 MB. Prepared to fit 900 × 900 px. Leave empty to keep the current image.</small></label></div></div>
    <div class="form-actions"><a class="button secondary" href="<?= site_url('products') ?>">Cancel</a><button class="button" type="submit"><?= $product ? 'Save changes' : 'Add product' ?></button></div>
</form>
<?= $this->endSection() ?>
