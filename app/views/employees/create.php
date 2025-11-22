<!-- FILE: /app/views/employees/create.php -->
<div class="page-header">
    <h1>Add New Employee</h1>
</div>

<div class="card">
    <form method="POST" action="/employees/create" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRF(); ?>">

        <h3>Basic Information</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="employee_code">Employee Code *</label>
                <input type="text" id="employee_code" name="employee_code" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
            </div>

            <div class="form-group">
                <label for="hire_date">Hire Date *</label>
                <input type="date" id="hire_date" name="hire_date" class="form-control" required>
            </div>
        </div>

        <h3>Employment Details</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" class="form-control">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo e($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="job_title_id">Job Title</label>
                <select id="job_title_id" name="job_title_id" class="form-control">
                    <option value="">Select Job Title</option>
                    <?php foreach ($job_titles as $jt): ?>
                        <option value="<?php echo $jt['id']; ?>"><?php echo e($jt['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="employment_type">Employment Type</label>
                <select id="employment_type" name="employment_type" class="form-control">
                    <option value="full-time">Full-Time</option>
                    <option value="part-time">Part-Time</option>
                    <option value="contract">Contract</option>
                    <option value="intern">Intern</option>
                </select>
            </div>
        </div>

        <h3>Salary Information</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="basic_salary">Basic Salary</label>
                <input type="number" id="basic_salary" name="basic_salary" class="form-control" step="0.01" value="0">
            </div>

            <div class="form-group">
                <label for="housing_allowance">Housing Allowance</label>
                <input type="number" id="housing_allowance" name="housing_allowance" class="form-control" step="0.01" value="0">
            </div>

            <div class="form-group">
                <label for="transport_allowance">Transport Allowance</label>
                <input type="number" id="transport_allowance" name="transport_allowance" class="form-control" step="0.01" value="0">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="social_security_deduction">Social Security Deduction</label>
                <input type="number" id="social_security_deduction" name="social_security_deduction" class="form-control" step="0.01" value="0">
            </div>

            <div class="form-group">
                <label for="bank_name">Bank Name</label>
                <input type="text" id="bank_name" name="bank_name" class="form-control">
            </div>

            <div class="form-group">
                <label for="bank_account_number">Bank Account Number</label>
                <input type="text" id="bank_account_number" name="bank_account_number" class="form-control">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Employee</button>
            <a href="/employees" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
