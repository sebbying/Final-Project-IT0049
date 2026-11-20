<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><span class="eyebrow">THE PEOPLE</span><h1>Customers</h1><p class="muted">A familiar face behind every transaction.</p></div><a class="button" href="<?= site_url('customers/new') ?>">+ Add customer</a></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
<?php if (!$customers): ?><tr><td colspan="5" class="empty">No customers yet. Add your first customer above.</td></tr><?php endif ?>
<?php foreach ($customers as $customer): ?><tr><td><strong><?= esc($customer['full_name']) ?></strong></td><td><?= esc($customer['email']) ?></td><td><?= esc($customer['phone'] ?: '—') ?></td><td><?= esc(date('M j, Y', strtotime($customer['created_at']))) ?></td><td><div class="row-actions"><a class="text-link" href="<?= site_url('customers/' . $customer['id'] . '/edit') ?>">Edit</a><form method="post" action="<?= site_url('customers/' . $customer['id'] . '/delete') ?>" data-confirm="Delete this customer from the active list? Past sales will be preserved."><?= csrf_field() ?><button class="text-button danger-text" type="submit">Delete</button></form></div></td></tr><?php endforeach ?>
</tbody></table></div></section><div class="pagination"><?= $pager->links() ?></div>
<?= $this->endSection() ?>
