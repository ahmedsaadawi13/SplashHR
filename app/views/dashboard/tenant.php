<!-- FILE: /app/views/dashboard/tenant.php -->
<div class="page-header">
    <h1>Dashboard</h1>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Employees</h3>
        <p class="stat-number"><?php echo e($total_employees); ?></p>
    </div>

    <div class="stat-card">
        <h3>Active Employees</h3>
        <p class="stat-number"><?php echo e($active_employees); ?></p>
    </div>

    <div class="stat-card">
        <h3>Probation</h3>
        <p class="stat-number"><?php echo e($probation_employees); ?></p>
    </div>

    <div class="stat-card">
        <h3>Departments</h3>
        <p class="stat-number"><?php echo e(count($departments)); ?></p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h2>Attendance Summary (This Month)</h2>
        <?php if ($attendance_summary): ?>
            <table class="table">
                <tr>
                    <th>Late Arrivals</th>
                    <td><?php echo e($attendance_summary['late_arrivals']); ?></td>
                </tr>
                <tr>
                    <th>Absences</th>
                    <td><?php echo e($attendance_summary['absences']); ?></td>
                </tr>
                <tr>
                    <th>Overtime Hours</th>
                    <td><?php echo e(number_format($attendance_summary['total_overtime_hours'], 2)); ?> hrs</td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <h2>Pending Leave Requests</h2>
        <?php if (!empty($pending_leaves)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Dates</th>
                        <th>Days</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_leaves as $leave): ?>
                        <tr>
                            <td><?php echo e($leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                            <td><?php echo e(formatDate($leave['start_date'])); ?> - <?php echo e(formatDate($leave['end_date'])); ?></td>
                            <td><?php echo e($leave['total_days']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="/leaves" class="btn btn-sm">View All</a>
        <?php else: ?>
            <p>No pending leave requests.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <h2>Upcoming Birthdays</h2>
        <?php if (!empty($upcoming_birthdays)): ?>
            <ul class="list">
                <?php foreach ($upcoming_birthdays as $emp): ?>
                    <li><?php echo e($emp['first_name'] . ' ' . $emp['last_name']); ?> - <?php echo e(formatDate($emp['date_of_birth'], 'M d')); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No upcoming birthdays.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <h2>Latest Payroll Run</h2>
        <?php if ($latest_payroll): ?>
            <table class="table">
                <tr>
                    <th>Period</th>
                    <td><?php echo date('F Y', mktime(0, 0, 0, $latest_payroll['month'], 1, $latest_payroll['year'])); ?></td>
                </tr>
                <tr>
                    <th>Total Net Salary</th>
                    <td><?php echo formatCurrency($latest_payroll['total_net']); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge badge-<?php echo $latest_payroll['status']; ?>"><?php echo e(ucfirst($latest_payroll['status'])); ?></span></td>
                </tr>
            </table>
            <a href="/payroll/<?php echo $latest_payroll['id']; ?>" class="btn btn-sm">View Details</a>
        <?php else: ?>
            <p>No payroll runs yet.</p>
            <a href="/payroll/create" class="btn btn-sm btn-primary">Create Payroll Run</a>
        <?php endif; ?>
    </div>
</div>
