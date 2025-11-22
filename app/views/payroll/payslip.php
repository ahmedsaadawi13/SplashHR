<!-- FILE: /app/views/payroll/payslip.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?php echo date('F Y', mktime(0, 0, 0, $payslip['month'], 1, $payslip['year'])); ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <style>
        body { background: white; padding: 20px; }
        .payslip { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 30px; }
        .payslip-header { text-align: center; margin-bottom: 30px; }
        .payslip-table { width: 100%; margin: 20px 0; }
        .payslip-table th { background: #f5f5f5; padding: 10px; text-align: left; }
        .payslip-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .total-row { font-weight: bold; background: #f5f5f5; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="payslip">
        <div class="payslip-header">
            <h1>SplashHR</h1>
            <h2>Payslip</h2>
            <p><?php echo date('F Y', mktime(0, 0, 0, $payslip['month'], 1, $payslip['year'])); ?></p>
        </div>

        <table class="payslip-table">
            <tr>
                <th>Employee Name:</th>
                <td><?php echo e($payslip['first_name'] . ' ' . $payslip['last_name']); ?></td>
                <th>Employee Code:</th>
                <td><?php echo e($payslip['employee_code']); ?></td>
            </tr>
            <tr>
                <th>Department:</th>
                <td><?php echo e($payslip['department_name']); ?></td>
                <th>Job Title:</th>
                <td><?php echo e($payslip['job_title_name']); ?></td>
            </tr>
            <tr>
                <th>Pay Date:</th>
                <td><?php echo e(formatDate($payslip['pay_date'])); ?></td>
                <th>Days Worked:</th>
                <td><?php echo e($payslip['present_days']); ?> / <?php echo e($payslip['working_days']); ?></td>
            </tr>
        </table>

        <h3>Earnings</h3>
        <table class="payslip-table">
            <tr>
                <th>Component</th>
                <th style="text-align: right;">Amount</th>
            </tr>
            <tr>
                <td>Basic Salary</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['basic_salary']); ?></td>
            </tr>
            <tr>
                <td>Housing Allowance</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['housing_allowance']); ?></td>
            </tr>
            <tr>
                <td>Transport Allowance</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['transport_allowance']); ?></td>
            </tr>
            <tr>
                <td>Other Allowances</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['other_allowances']); ?></td>
            </tr>
            <tr>
                <td>Overtime (<?php echo e($payslip['overtime_hours']); ?> hrs)</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['overtime_amount']); ?></td>
            </tr>
            <tr class="total-row">
                <td>Gross Salary</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['gross_salary']); ?></td>
            </tr>
        </table>

        <h3>Deductions</h3>
        <table class="payslip-table">
            <tr>
                <th>Component</th>
                <th style="text-align: right;">Amount</th>
            </tr>
            <tr>
                <td>Social Security</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['social_security_deduction']); ?></td>
            </tr>
            <tr>
                <td>Loan Installment</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['loan_deduction']); ?></td>
            </tr>
            <tr>
                <td>Unpaid Leave</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['unpaid_leave_deduction']); ?></td>
            </tr>
            <tr>
                <td>Other Deductions</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['other_deductions']); ?></td>
            </tr>
            <tr class="total-row">
                <td>Total Deductions</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['total_deductions']); ?></td>
            </tr>
        </table>

        <h3>Net Salary</h3>
        <table class="payslip-table">
            <tr class="total-row" style="font-size: 1.2em;">
                <td>Net Pay</td>
                <td style="text-align: right;"><?php echo formatCurrency($payslip['net_salary']); ?></td>
            </tr>
        </table>

        <?php if ($payslip['bank_name']): ?>
            <p><strong>Payment Method:</strong> Bank Transfer to <?php echo e($payslip['bank_name']); ?> - <?php echo e($payslip['bank_account_number']); ?></p>
        <?php endif; ?>

        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print();" class="btn btn-primary">Print Payslip</button>
            <button onclick="window.close();" class="btn btn-outline">Close</button>
        </div>
    </div>
</body>
</html>
