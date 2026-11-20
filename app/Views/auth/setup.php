<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="auth-brand"><span class="brand-mark">c.</span> counterpoint</div>
<div class="auth-card">
    <span class="eyebrow">YOUR FIRST STEP</span><h1>Set up your store.</h1><p class="muted">Create the first staff account. Use the setup token generated in your <code>.env</code> file.</p>
    <form method="post" action="<?= site_url('setup') ?>" class="form-stack">
        <?= csrf_field() ?>
        <label>Setup token<input name="setup_token" type="password" required minlength="32" autocomplete="off"></label>
        <label>Full name<input name="full_name" required maxlength="100" value="<?= field_value('full_name') ?>" autocomplete="name"></label>
        <label>Username<input name="username" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_-]+" value="<?= field_value('username') ?>" autocomplete="username"><small>Letters, numbers, underscores, and hyphens.</small></label>
        <label>Password<input name="password" type="password" required minlength="12" maxlength="72" autocomplete="new-password"><small>Use 12–72 characters.</small></label>
        <label>Confirm password<input name="password_confirm" type="password" required minlength="12" maxlength="72" autocomplete="new-password"></label>
        <button class="button full" type="submit">Create staff account <span>→</span></button>
    </form>
</div>
<?= $this->endSection() ?>
