<!-- FILE: /app/views/dashboard/employee.php -->
<div class="page-header">
    <h1>Employee Dashboard</h1>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h2>My Profile</h2>
        <table class="table">
            <tr>
                <th>Employee Code</th>
                <td><?php echo e($employee['employee_code']); ?></td>
            </tr>
            <tr>
                <th>Department</th>
                <td><?php echo e($employee['department_name']); ?></td>
            </tr>
            <tr>
                <th>Job Title</th>
                <td><?php echo e($employee['job_title_name']); ?></td>
            </tr>
            <tr>
                <th>Employment Type</th>
                <td><?php echo e(ucfirst($employee['employment_type'])); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="badge badge-<?php echo $employee['status']; ?>"><?php echo e(ucfirst($employee['status'])); ?></span></td>
            </tr>
        </table>
        <a href="/employees/<?php echo $employee['id']; ?>" class="btn btn-sm">View Full Profile</a>
    </div>

    <div class="dashboard-card">
        <h2>Recent Attendance (This Month)</h2>
        <?php if (!empty($recent_attendance)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($recent_attendance, -10) as $att): ?>
                        <tr>
                            <td><?php echo e(formatDate($att['date'])); ?></td>
                            <td><?php echo e($att['check_in'] ?: '-'); ?></td>
                            <td><?php echo e($att['check_out'] ?: '-'); ?></td>
                            <td><?php echo e($att['total_hours']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No attendance records for this month.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <h2>My Leave Requests</h2>
        <?php if (!empty($leave_requests)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $leave): ?>
                        <tr>
                            <td><?php echo e($leave['leave_type_name']); ?></td>
                            <td><?php echo e(formatDate($leave['start_date'])); ?> - <?php echo e(formatDate($leave['end_date'])); ?></td>
                            <td><span class="badge badge-<?php echo $leave['status']; ?>"><?php echo e(ucfirst($leave['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No leave requests.</p>
        <?php endif; ?>
        <a href="/leaves/create" class="btn btn-sm btn-primary">Request Leave</a>
    </div>

    <div class="dashboard-card">
        <h2>Recent Payslips</h2>
        <?php if (!empty($payslips)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Net Salary</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payslips as $payslip): ?>
                        <tr>
                            <td><?php echo date('F Y', mktime(0, 0, 0, $payslip['month'], 1, $payslip['year'])); ?></td>
                            <td><?php echo formatCurrency($payslip['net_salary']); ?></td>
                            <td><a href="/payslip/<?php echo $payslip['id']; ?>" class="btn btn-sm" target="_blank">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No payslips available.</p>
        <?php endif; ?>
    </div>
</div>
