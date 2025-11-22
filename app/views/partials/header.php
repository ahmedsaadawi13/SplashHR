<!-- FILE: /app/views/partials/header.php -->
<?php $user = Auth::user(); ?>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <h1>SplashHR</h1>
        </div>

        <div class="header-right">
            <span class="user-name"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></span>
            <span class="user-role">(<?php echo e(ucfirst(str_replace('_', ' ', $user['role']))); ?>)</span>
            <a href="/logout" class="btn btn-sm btn-outline">Logout</a>
        </div>
    </div>
</header>
