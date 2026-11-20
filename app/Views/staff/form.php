<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<a class="back" href="<?= site_url('staff') ?>">← Back to staff</a><div class="page-heading"><div><span class="eyebrow">YOUR TEAM</span><h1><?= esc($title) ?></h1></div></div>
<form method="post" action="<?= site_url($staff ? 'staff/' . $staff['id'] : 'staff') ?>" enctype="multipart/form-data" class="panel form-panel"><?= csrf_field() ?>
    <div class="form-grid"><div class="form-stack">
        <label>Full name<input name="full_name" required maxlength="100" value="<?= field_value('full_name', $staff['full_name'] ?? '') ?>" autocomplete="name"></label>
        <label>Username<input name="username" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_-]+" value="<?= field_value('username', $staff['username'] ?? '') ?>" autocomplete="username"><small>Letters, numbers, underscores, and hyphens. Deleted usernames remain reserved.</small></label>
        <label><?= $staff ? 'New password' : 'Password' ?><input name="password" type="password" minlength="12" maxlength="72" <?= $staff ? '' : 'required' ?> autocomplete="new-password"><small>12–72 characters. <?= $staff ? 'Leave blank to keep the current password.' : '' ?></small></label>
        <label>Confirm password<input name="password_confirm" type="password" minlength="12" maxlength="72" <?= $staff ? '' : 'required' ?> autocomplete="new-password"></label>
    </div><div class="upload-box"><img class="image-preview avatar-preview" id="avatar-preview" src="<?= media_url('avatars', $staff['avatar'] ?? null) ?>" alt="Avatar preview"><label>Avatar<input name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-preview="avatar-preview"><small>JPEG, PNG, or WebP · up to 2 MB. Center-cropped to 240 × 240 px. Leave empty to keep the current avatar.</small></label></div></div>
    <div class="form-actions"><a class="button secondary" href="<?= site_url('staff') ?>">Cancel</a><button class="button" type="submit"><?= $staff ? 'Save changes' : 'Add staff member' ?></button></div>
</form>
<?= $this->endSection() ?>
