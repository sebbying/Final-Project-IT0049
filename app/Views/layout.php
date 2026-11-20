<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'POS') ?> · Counterpoint POS</title>
    <link rel="icon" href="<?= base_url('assets/mark.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <script src="<?= base_url('assets/app.js') ?>" defer></script>
</head>
<body class="<?= $currentUser ? 'app-body' : 'auth-body' ?>">
<?php if ($currentUser): ?>
<aside class="sidebar">
    <a class="brand" href="<?= site_url('/') ?>"><span class="brand-mark">c.</span><span>counterpoint<small>POINT OF SALE</small></span></a>
    <div class="nav-label">WORKSPACE</div>
    <nav aria-label="Main navigation">
    <?php $segment = service('uri')->getSegment(1); ?>
    <?php foreach (['' => ['Overview', '◫'], 'sales/new' => ['Record sale', '+'], 'products' => ['Products', '▦'], 'customers' => ['Customers', '◎'], 'staff' => ['Staff', '◇'], 'sales' => ['Sales history', '↗']] as $path => [$label, $icon]): ?>
        <?php $active = $path === '' ? $segment === '' : ($path === 'sales/new' ? uri_string() === 'sales/new' : ($segment === $path && !($path === 'sales' && uri_string() === 'sales/new'))); ?>
        <a class="nav-link <?= $active ? 'active' : '' ?>" href="<?= site_url($path) ?>" <?= $active ? 'aria-current="page"' : '' ?>><span class="nav-icon" aria-hidden="true"><?= $icon ?></span><?= esc($label) ?></a>
    <?php endforeach ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="profile"><img class="avatar" src="<?= media_url('avatars', $currentUser['avatar']) ?>" alt=""><div><strong><?= esc($currentUser['full_name']) ?></strong><small>@<?= esc($currentUser['username']) ?></small></div></div>
        <form method="post" action="<?= site_url('logout') ?>"><?= csrf_field() ?><button class="logout" type="submit">Sign out <span>↗</span></button></form>
    </div>
</aside>
<?php endif ?>
<main class="<?= $currentUser ? 'main' : 'auth-main' ?>">
<?php if ($currentUser): ?>
    <header class="topbar"><span>YOUR STORE, AT A GLANCE</span><span><?= esc(date('D, M j, Y')) ?></span></header>
<?php endif ?>
<div class="<?= $currentUser ? 'content' : 'auth-content' ?>">
<?php foreach (['success', 'error'] as $kind): ?>
    <?php if ($message = session()->getFlashdata($kind)): ?><div class="alert <?= $kind ?>" role="alert"><?= esc($message) ?></div><?php endif ?>
<?php endforeach ?>
<?php if ($errors = session()->getFlashdata('errors')): ?>
    <div class="alert error" role="alert"><strong>Please check your entries.</strong><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div>
<?php endif ?>
<?= $this->renderSection('content') ?>
</div>
</main>
</body>
</html>
