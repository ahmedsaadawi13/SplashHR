<!-- FILE: /app/views/auth/login.php -->
<div class="auth-box">
    <h2 class="auth-title">Login to SplashHR</h2>
    <p class="auth-subtitle">Multi-tenant HR & Payroll SaaS Platform</p>

    <form method="POST" action="/login" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRF(); ?>">

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <div class="auth-footer">
        <p><strong>Demo Credentials:</strong></p>
        <p>Platform Admin: admin@splashhr.com / password</p>
        <p>Tenant Admin (TechCorp): michael.brown@techcorp.com / password</p>
        <p>Employee (TechCorp): sarah.johnson@techcorp.com / password</p>
    </div>
</div>
