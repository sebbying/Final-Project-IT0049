<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<a class="back" href="<?= site_url('customers') ?>">← Back to customers</a><div class="page-heading"><div><span class="eyebrow">YOUR COMMUNITY</span><h1><?= esc($title) ?></h1></div></div>
<form method="post" action="<?= site_url($customer ? 'customers/' . $customer['id'] : 'customers') ?>" class="panel form-panel narrow"><?= csrf_field() ?>
    <div class="form-stack"><label>Full name<input name="full_name" required maxlength="100" autocomplete="name" value="<?= field_value('full_name', $customer['full_name'] ?? '') ?>"></label><label>Email address<input name="email" type="email" required maxlength="100" autocomplete="email" value="<?= field_value('email', $customer['email'] ?? '') ?>"></label><label>Phone <small>Optional</small><input name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?= field_value('phone', $customer['phone'] ?? '') ?>"></label></div>
    <div class="form-actions"><a class="button secondary" href="<?= site_url('customers') ?>">Cancel</a><button class="button" type="submit"><?= $customer ? 'Save changes' : 'Add customer' ?></button></div>
</form>
<?= $this->endSection() ?>
