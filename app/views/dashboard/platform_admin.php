<!-- FILE: /app/views/dashboard/platform_admin.php -->
<div class="page-header">
    <h1>Platform Dashboard</h1>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Tenants</h3>
        <p class="stat-number"><?php echo e($total_tenants); ?></p>
    </div>

    <div class="stat-card">
        <h3>Active Tenants</h3>
        <p class="stat-number"><?php echo e($active_tenants); ?></p>
    </div>

    <div class="stat-card">
        <h3>Total Employees</h3>
        <p class="stat-number"><?php echo e($total_employees); ?></p>
    </div>
</div>

<div class="dashboard-card">
    <h2>Recent Tenants</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Email</th>
                <th>Plan</th>
                <th>Subscription Status</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_tenants as $tenant): ?>
                <tr>
                    <td><a href="/tenants/<?php echo $tenant['id']; ?>"><?php echo e($tenant['company_name']); ?></a></td>
                    <td><?php echo e($tenant['email']); ?></td>
                    <td><?php echo e($tenant['plan_name'] ?: 'None'); ?></td>
                    <td><span class="badge badge-<?php echo $tenant['subscription_status']; ?>"><?php echo e(ucfirst($tenant['subscription_status'] ?: 'none')); ?></span></td>
                    <td><span class="badge badge-<?php echo $tenant['status']; ?>"><?php echo e(ucfirst($tenant['status'])); ?></span></td>
                    <td><?php echo e(formatDate($tenant['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/tenants" class="btn">View All Tenants</a>
</div>
