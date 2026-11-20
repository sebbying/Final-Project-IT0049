<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><span class="eyebrow">AT THE COUNTER</span><h1>Record a sale</h1><p class="muted">Choose a product, set the quantity, and you're ready.</p></div><a class="text-link" href="<?= site_url('sales') ?>">Sales history →</a></div>
<?php if (!$products): ?><div class="panel empty"><h2>Your shelves need a refill.</h2><p>Add a product or restock an existing product to record a sale.</p><a class="button" href="<?= site_url('products') ?>">Manage products</a></div>
<?php else: ?>
<form method="post" action="<?= site_url('sales') ?>" class="sale-layout" id="sale-form"><?= csrf_field() ?>
    <section class="panel form-panel form-stack"><h2>Sale details</h2>
        <label>Product<select name="product_id" required id="sale-product"><option value="">Choose a product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" data-price="<?= esc($product['price']) ?>" data-stock="<?= (int) $product['stock_quantity'] ?>" <?= (string) old('product_id', '', false) === (string) $product['id'] ? 'selected' : '' ?>><?= esc($product['name']) ?> · <?= money($product['price']) ?> · <?= (int) $product['stock_quantity'] ?> available</option><?php endforeach ?></select></label>
        <label>Customer <small>Optional</small><select name="customer_id"><option value="">Walk-in customer</option><?php foreach ($customers as $customer): ?><option value="<?= (int) $customer['id'] ?>" <?= (string) old('customer_id', '', false) === (string) $customer['id'] ? 'selected' : '' ?>><?= esc($customer['full_name']) ?></option><?php endforeach ?></select></label>
        <label>Quantity<input name="quantity" id="sale-quantity" type="number" min="1" max="1000000" step="1" required value="<?= field_value('quantity', 1) ?>"><small id="stock-note">Select a product to see available stock.</small></label>
        <p class="hint">Stock is checked again when you save. A successful sale updates inventory immediately.</p>
    </section>
    <aside class="panel sale-summary"><span class="eyebrow">ORDER SUMMARY</span><h2>Ready when you are.</h2><div class="summary-line"><span>Sold by</span><strong><?= esc($currentUser['full_name']) ?></strong></div><div class="summary-line"><span>Unit price</span><span id="sale-price">—</span></div><div class="summary-total"><span>Total amount</span><strong id="sale-total" aria-live="polite">₱0.00</strong></div><button class="button full" type="submit">Record sale <span>→</span></button><small class="muted">The final total uses the product's current saved price.</small></aside>
</form>
<?php endif ?>
<?= $this->endSection() ?>
