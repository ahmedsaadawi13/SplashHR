<!-- FILE: /app/views/layouts/auth.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? e($title) . ' - ' : ''; ?>SplashHR</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <?php require __DIR__ . '/../partials/flash.php'; ?>

        <?php echo $content; ?>
    </div>

    <script src="<?php echo asset('js/app.js'); ?>"></script>
</body>
</html>
