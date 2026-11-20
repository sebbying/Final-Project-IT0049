<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="auth-brand"><span class="brand-mark">c.</span> counterpoint</div>
<div class="auth-card">
    <span class="eyebrow">LET'S GET TO WORK</span><h1>Welcome back.</h1><p class="muted">Sign in to manage your store and keep things moving.</p>
    <form method="post" action="<?= site_url('login') ?>" class="form-stack">
        <?= csrf_field() ?>
        <label>Username<input name="username" required maxlength="50" autocomplete="username" autofocus></label>
        <label>Password<input name="password" type="password" required maxlength="255" autocomplete="current-password"></label>
        <button class="button full" type="submit">Sign in <span>→</span></button>
    </form>
</div>
<p class="auth-foot">A little more order. A better day at the counter.</p>
<?= $this->endSection() ?>
