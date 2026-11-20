<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><span class="eyebrow">THE GOODS</span><h1>Products</h1><p class="muted">Keep your catalog current and your shelves ready.</p></div><a class="button" href="<?= site_url('products/new') ?>">+ Add product</a></div>
<form class="search-bar" method="get" action="<?= site_url('products') ?>"><label class="sr-only" for="search">Search products</label><input id="search" name="q" maxlength="100" value="<?= esc($q) ?>" placeholder="Search your products…"><button class="button secondary" type="submit">Search</button><?php if ($q !== ''): ?><a href="<?= site_url('products') ?>">Clear</a><?php endif ?></form>
<?php if (!$products): ?><div class="panel empty"><h2>No products yet.</h2><p>Add a product to start building your catalog.</p></div><?php endif ?>
<div class="product-grid">
<?php foreach ($products as $product): ?>
<article class="product-card"><div class="product-picture"><img src="<?= media_url('products', $product['image']) ?>" alt="<?= esc($product['name']) ?>" loading="lazy"><span class="badge <?= (int) $product['stock_quantity'] === 0 ? 'danger' : ((int) $product['stock_quantity'] <= 5 ? 'warning' : '') ?>"><?= (int) $product['stock_quantity'] ?> in stock</span></div><div class="product-info"><h2><?= esc($product['name']) ?></h2><strong class="price"><?= money($product['price']) ?></strong><div class="card-actions"><a class="button secondary small" href="<?= site_url('products/' . $product['id'] . '/edit') ?>">Edit product</a><form method="post" action="<?= site_url('products/' . $product['id'] . '/delete') ?>" data-confirm="Archive this product? Past sales will be preserved."><?= csrf_field() ?><button class="text-button danger-text" type="submit">Archive</button></form></div></div></article>
<?php endforeach ?>
</div>
<div class="pagination"><?= $pager->links() ?></div>
<?= $this->endSection() ?>
