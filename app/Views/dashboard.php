<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><span class="eyebrow">A GOOD DAY FOR BUSINESS</span><h1>Your store, in focus.</h1><p class="muted">Everything you need to keep your counter moving.</p></div><a class="button" href="<?= site_url('sales/new') ?>">+ Record sale</a></div>
<div class="stats">
    <article class="stat accent"><span>ALL-TIME SALES</span><strong><?= money($revenue) ?></strong><small>Recorded transaction total</small></article>
    <article class="stat"><span>PRODUCTS</span><strong><?= (int) $productCount ?></strong><small>In your active catalog</small></article>
    <article class="stat"><span>CUSTOMERS</span><strong><?= (int) $customerCount ?></strong><small>People behind the purchases</small></article>
    <article class="stat"><span>STAFF</span><strong><?= (int) $staffCount ?></strong><small>Keeping things running</small></article>
</div>
<div class="dashboard-grid">
    <section class="panel"><div class="panel-heading"><div><h2>Recent sales</h2><p class="muted">The latest from your counter.</p></div><a class="text-link" href="<?= site_url('sales') ?>">View all →</a></div>
        <?= view('sales/table', ['sales' => $recentSales]) ?>
    </section>
    <section class="panel"><div class="panel-heading"><div><h2>Stock watch</h2><p class="muted">Products with 5 units or fewer.</p></div><span class="dot"></span></div>
        <?php if (!$lowStock): ?><div class="empty"><strong>Looking good.</strong><p>Your products have healthy stock levels.</p></div><?php endif ?>
        <?php foreach ($lowStock as $product): ?><a class="stock-row" href="<?= site_url('products/' . $product['id'] . '/edit') ?>"><img class="thumb" src="<?= media_url('products', $product['image']) ?>" alt=""><span><?= esc($product['name']) ?><small><?= money($product['price']) ?></small></span><span class="badge <?= (int) $product['stock_quantity'] === 0 ? 'danger' : 'warning' ?>"><?= (int) $product['stock_quantity'] ?> left</span></a><?php endforeach ?>
    </section>
</div>
<?= $this->endSection() ?>
