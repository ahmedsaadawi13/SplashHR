<!-- FILE: /app/views/partials/flash.php -->
<?php if (Session::hasFlash('success')): ?>
    <div class="alert alert-success">
        <?php echo e(Session::getFlash('success')); ?>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('error')): ?>
    <div class="alert alert-error">
        <?php echo e(Session::getFlash('error')); ?>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('warning')): ?>
    <div class="alert alert-warning">
        <?php echo e(Session::getFlash('warning')); ?>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('info')): ?>
    <div class="alert alert-info">
        <?php echo e(Session::getFlash('info')); ?>
    </div>
<?php endif; ?>
