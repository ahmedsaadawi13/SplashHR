<!-- FILE: /app/views/payroll/index.php -->
<div class="page-header">
    <h1>Payroll Runs</h1>
    <a href="/payroll/create" class="btn btn-primary">Process New Payroll</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Period</th>
                <th>Pay Date</th>
                <th>Employees</th>
                <th>Total Gross</th>
                <th>Total Deductions</th>
                <th>Total Net</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payroll_runs)): ?>
                <?php foreach ($payroll_runs as $pr): ?>
                    <tr>
                        <td><?php echo date('F Y', mktime(0, 0, 0, $pr['month'], 1, $pr['year'])); ?></td>
                        <td><?php echo e(formatDate($pr['pay_date'])); ?></td>
                        <td><?php echo e($pr['employee_count']); ?></td>
                        <td><?php echo formatCurrency($pr['total_gross']); ?></td>
                        <td><?php echo formatCurrency($pr['total_deductions']); ?></td>
                        <td><?php echo formatCurrency($pr['total_net']); ?></td>
                        <td><span class="badge badge-<?php echo $pr['status']; ?>"><?php echo e(ucfirst($pr['status'])); ?></span></td>
                        <td class="actions">
                            <a href="/payroll/<?php echo $pr['id']; ?>" class="btn btn-sm">View</a>
                            <?php if ($pr['status'] === 'draft'): ?>
                                <form method="POST" action="/payroll/<?php echo $pr['id']; ?>/finalize" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRF(); ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Finalize this payroll run?');">Finalize</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No payroll runs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
                <a href="?page=<?php echo $pagination['prev_page']; ?>" class="btn btn-sm">Previous</a>
            <?php endif; ?>

            <span>Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?></span>

            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?php echo $pagination['next_page']; ?>" class="btn btn-sm">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
