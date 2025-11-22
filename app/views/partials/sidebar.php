<!-- FILE: /app/views/partials/sidebar.php -->
<?php $user = Auth::user(); ?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="/dashboard" class="nav-item">Dashboard</a>

        <?php if ($user['role'] === 'platform_admin'): ?>
            <a href="/tenants" class="nav-item">Tenants</a>
        <?php endif; ?>

        <?php if (in_array($user['role'], ['tenant_admin', 'manager'])): ?>
            <div class="nav-section">Organization</div>
            <a href="/employees" class="nav-item">Employees</a>
            <a href="/departments" class="nav-item">Departments</a>
            <a href="/job-titles" class="nav-item">Job Titles</a>

            <div class="nav-section">HR Management</div>
            <a href="/attendance" class="nav-item">Attendance</a>
            <a href="/leaves" class="nav-item">Leave Requests</a>
            <a href="/payroll" class="nav-item">Payroll</a>
            <a href="/loans" class="nav-item">Loans</a>
        <?php endif; ?>

        <?php if ($user['role'] === 'employee'): ?>
            <a href="/leaves" class="nav-item">My Leave Requests</a>
        <?php endif; ?>

        <?php if ($user['role'] === 'tenant_admin'): ?>
            <div class="nav-section">Settings</div>
            <a href="/settings" class="nav-item">Settings</a>
        <?php endif; ?>
    </nav>
</aside>
