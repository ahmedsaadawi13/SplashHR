<!-- FILE: /app/views/payroll/create.php -->
<div class="page-header">
    <h1>Process Payroll</h1>
</div>

<div class="card">
    <p>Process monthly payroll for all active employees. The system will automatically calculate salaries based on attendance, overtime, deductions, and loans.</p>

    <form method="POST" action="/payroll/process" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRF(); ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="month">Month *</label>
                <select id="month" name="month" class="form-control" required>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m == date('m') ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="year">Year *</label>
                <select id="year" name="year" class="form-control" required>
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="pay_date">Pay Date *</label>
                <input type="date" id="pay_date" name="pay_date" class="form-control" value="<?php echo date('Y-m-25'); ?>" required>
            </div>
        </div>

        <div class="alert alert-info">
            <strong>Processing will:</strong>
            <ul>
                <li>Calculate salary for all active employees</li>
                <li>Include attendance and overtime data</li>
                <li>Apply loan installments and other deductions</li>
                <li>Consider unpaid leave days</li>
            </ul>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Process payroll for selected period?');">Process Payroll</button>
            <a href="/payroll" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
