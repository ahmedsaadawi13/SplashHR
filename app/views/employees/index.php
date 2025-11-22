<!-- FILE: /app/views/employees/index.php -->
<div class="page-header">
    <h1>Employees</h1>
    <a href="/employees/create" class="btn btn-primary">Add Employee</a>
</div>

<div class="filter-box">
    <form method="GET" action="/employees" class="filter-form">
        <div class="form-group">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Name, code, or email">
        </div>

        <div class="form-group">
            <label for="department_id">Department</label>
            <select id="department_id" name="department_id" class="form-control">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>" <?php echo ($filters['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                        <?php echo e($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active" <?php echo ($filters['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="probation" <?php echo ($filters['status'] ?? '') == 'probation' ? 'selected' : ''; ?>>Probation</option>
                <option value="terminated" <?php echo ($filters['status'] ?? '') == 'terminated' ? 'selected' : ''; ?>>Terminated</option>
            </select>
        </div>

        <button type="submit" class="btn">Filter</button>
        <a href="/employees" class="btn btn-outline">Clear</a>
    </form>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Job Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?php echo e($emp['employee_code']); ?></td>
                        <td><?php echo e($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                        <td><?php echo e($emp['email']); ?></td>
                        <td><?php echo e($emp['department_name']); ?></td>
                        <td><?php echo e($emp['job_title_name']); ?></td>
                        <td><span class="badge badge-<?php echo $emp['status']; ?>"><?php echo e(ucfirst($emp['status'])); ?></span></td>
                        <td class="actions">
                            <a href="/employees/<?php echo $emp['id']; ?>" class="btn btn-sm">View</a>
                            <a href="/employees/<?php echo $emp['id']; ?>/edit" class="btn btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No employees found.</td>
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
