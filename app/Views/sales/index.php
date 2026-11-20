<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><span class="eyebrow">EVERY TRANSACTION</span><h1>Sales history</h1><p class="muted">A clear record of what sold, when, and who was at the counter.</p></div><a class="button" href="<?= site_url('sales/new') ?>">+ Record sale</a></div>
<section class="panel"><?= view('sales/table', ['sales' => $sales]) ?></section><div class="pagination"><?= $pager->links() ?></div>
<p class="muted footnote">Amounts are in Philippine pesos. Dates and times use Asia/Manila. Product, customer, and staff names are kept as recorded at the time of sale.</p>
<?= $this->endSection() ?>
