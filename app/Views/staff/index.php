<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><span class="eyebrow">THE TEAM</span><h1>Staff</h1><p class="muted">The people who keep your store moving.</p></div><a class="button" href="<?= site_url('staff/new') ?>">+ Add staff member</a></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Staff member</th><th>Username</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
<?php if (!$staff): ?><tr><td colspan="4" class="empty">No staff members found.</td></tr><?php endif ?>
<?php foreach ($staff as $person): ?><tr><td><div class="person-cell"><img class="avatar" src="<?= media_url('avatars', $person['avatar']) ?>" alt=""><strong><?= esc($person['full_name']) ?></strong><?php if ((int) $person['id'] === (int) $currentUser['id']): ?><span class="badge">You</span><?php endif ?></div></td><td>@<?= esc($person['username']) ?></td><td><?= esc(date('M j, Y', strtotime($person['created_at']))) ?></td><td><div class="row-actions"><a class="text-link" href="<?= site_url('staff/' . $person['id'] . '/edit') ?>">Edit</a><?php if ((int) $person['id'] !== (int) $currentUser['id']): ?><form method="post" action="<?= site_url('staff/' . $person['id'] . '/delete') ?>" data-confirm="Delete this staff account and revoke its access? Past sales will be preserved."><?= csrf_field() ?><button class="text-button danger-text" type="submit">Delete</button></form><?php endif ?></div></td></tr><?php endforeach ?>
</tbody></table></div></section><div class="pagination"><?= $pager->links() ?></div>
<?= $this->endSection() ?>
