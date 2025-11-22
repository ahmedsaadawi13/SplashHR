-- FILE: /database.sql
-- SplashHR - Multi-tenant HR & Payroll SaaS Platform
-- Database Schema and Seed Data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database: splashhr
CREATE DATABASE IF NOT EXISTS `splashhr` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `splashhr`;

-- --------------------------------------------------------
-- Table structure for table `tenants`
-- --------------------------------------------------------

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `default_currency` varchar(10) DEFAULT 'USD',
  `timezone` varchar(50) DEFAULT 'UTC',
  `date_format` varchar(20) DEFAULT 'Y-m-d',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('platform_admin','tenant_admin','manager','employee') NOT NULL DEFAULT 'employee',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `departments`
-- --------------------------------------------------------

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `manager_id` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `job_titles`
-- --------------------------------------------------------

CREATE TABLE `job_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `employees`
-- --------------------------------------------------------

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `employee_code` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `probation_end_date` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_title_id` int(11) DEFAULT NULL,
  `employment_type` enum('full-time','part-time','contract','intern') DEFAULT 'full-time',
  `basic_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `housing_allowance` decimal(12,2) DEFAULT '0.00',
  `transport_allowance` decimal(12,2) DEFAULT '0.00',
  `other_allowances` decimal(12,2) DEFAULT '0.00',
  `social_security_deduction` decimal(12,2) DEFAULT '0.00',
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_iban` varchar(50) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `status` enum('active','probation','terminated','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_employee_code` (`tenant_id`, `employee_code`),
  KEY `tenant_id` (`tenant_id`),
  KEY `department_id` (`department_id`),
  KEY `job_title_id` (`job_title_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `attendance`
-- --------------------------------------------------------

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT '0.00',
  `overtime_hours` decimal(5,2) DEFAULT '0.00',
  `is_late` tinyint(1) DEFAULT '0',
  `is_early_leave` tinyint(1) DEFAULT '0',
  `is_absent` tinyint(1) DEFAULT '0',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_employee_date` (`tenant_id`, `employee_id`, `date`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `leave_types`
-- --------------------------------------------------------

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `default_days_per_year` int(11) DEFAULT '0',
  `is_paid` tinyint(1) DEFAULT '1',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `leave_requests`
-- --------------------------------------------------------

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payroll_runs`
-- --------------------------------------------------------

CREATE TABLE `payroll_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `pay_date` date NOT NULL,
  `total_gross` decimal(15,2) DEFAULT '0.00',
  `total_deductions` decimal(15,2) DEFAULT '0.00',
  `total_net` decimal(15,2) DEFAULT '0.00',
  `status` enum('draft','finalized','paid') DEFAULT 'draft',
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_month_year` (`tenant_id`, `month`, `year`),
  KEY `tenant_id` (`tenant_id`),
  KEY `processed_by` (`processed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payroll_items`
-- --------------------------------------------------------

CREATE TABLE `payroll_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(12,2) DEFAULT '0.00',
  `housing_allowance` decimal(12,2) DEFAULT '0.00',
  `transport_allowance` decimal(12,2) DEFAULT '0.00',
  `other_allowances` decimal(12,2) DEFAULT '0.00',
  `overtime_amount` decimal(12,2) DEFAULT '0.00',
  `gross_salary` decimal(12,2) DEFAULT '0.00',
  `social_security_deduction` decimal(12,2) DEFAULT '0.00',
  `loan_deduction` decimal(12,2) DEFAULT '0.00',
  `unpaid_leave_deduction` decimal(12,2) DEFAULT '0.00',
  `other_deductions` decimal(12,2) DEFAULT '0.00',
  `total_deductions` decimal(12,2) DEFAULT '0.00',
  `net_salary` decimal(12,2) DEFAULT '0.00',
  `working_days` int(11) DEFAULT '0',
  `present_days` int(11) DEFAULT '0',
  `absent_days` int(11) DEFAULT '0',
  `overtime_hours` decimal(5,2) DEFAULT '0.00',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_employee` (`payroll_run_id`, `employee_id`),
  KEY `payroll_run_id` (`payroll_run_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `loans`
-- --------------------------------------------------------

CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `loan_amount` decimal(12,2) NOT NULL,
  `installment_amount` decimal(12,2) NOT NULL,
  `total_installments` int(11) NOT NULL,
  `paid_installments` int(11) DEFAULT '0',
  `remaining_amount` decimal(12,2) NOT NULL,
  `start_month` int(11) NOT NULL,
  `start_year` int(11) NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `loan_installments`
-- --------------------------------------------------------

CREATE TABLE `loan_installments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `payroll_item_id` int(11) DEFAULT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `loan_id` (`loan_id`),
  KEY `payroll_item_id` (`payroll_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `subscription_plans`
-- --------------------------------------------------------

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) NOT NULL,
  `max_employees` int(11) DEFAULT NULL,
  `max_departments` int(11) DEFAULT NULL,
  `max_monthly_payroll_runs` int(11) DEFAULT '1',
  `max_storage_mb` int(11) DEFAULT '1000',
  `features` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tenant_subscriptions`
-- --------------------------------------------------------

CREATE TABLE `tenant_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `billing_cycle` enum('monthly','yearly') DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('trialing','active','past_due','cancelled','expired') DEFAULT 'active',
  `trial_ends_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `invoices`
-- --------------------------------------------------------

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `paid_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `tenant_id` (`tenant_id`),
  KEY `subscription_id` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(50) DEFAULT 'string',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_setting` (`tenant_id`, `setting_key`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `documents`
-- --------------------------------------------------------

CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `holidays`
-- --------------------------------------------------------

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `data` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `api_keys`
-- --------------------------------------------------------

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `permissions` text,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Add foreign key constraints
-- --------------------------------------------------------

ALTER TABLE `users`
  ADD CONSTRAINT `users_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

ALTER TABLE `departments`
  ADD CONSTRAINT `departments_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

ALTER TABLE `job_titles`
  ADD CONSTRAINT `job_titles_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

ALTER TABLE `employees`
  ADD CONSTRAINT `employees_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_department_fk` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_job_title_fk` FOREIGN KEY (`job_title_id`) REFERENCES `job_titles` (`id`) ON DELETE SET NULL;

ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

ALTER TABLE `leave_types`
  ADD CONSTRAINT `leave_types_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_leave_type_fk` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

ALTER TABLE `payroll_runs`
  ADD CONSTRAINT `payroll_runs_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

ALTER TABLE `payroll_items`
  ADD CONSTRAINT `payroll_items_payroll_run_fk` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_items_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

ALTER TABLE `loans`
  ADD CONSTRAINT `loans_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

ALTER TABLE `loan_installments`
  ADD CONSTRAINT `loan_installments_loan_fk` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_installments_payroll_item_fk` FOREIGN KEY (`payroll_item_id`) REFERENCES `payroll_items` (`id`) ON DELETE SET NULL;

ALTER TABLE `tenant_subscriptions`
  ADD CONSTRAINT `tenant_subscriptions_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_subscriptions_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_subscription_fk` FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions` (`id`) ON DELETE SET NULL;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_invoice_fk` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

ALTER TABLE `documents`
  ADD CONSTRAINT `documents_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

ALTER TABLE `holidays`
  ADD CONSTRAINT `holidays_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_tenant_fk` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- Seed Data
-- --------------------------------------------------------

-- Subscription Plans
INSERT INTO `subscription_plans` (`id`, `name`, `code`, `description`, `price_monthly`, `price_yearly`, `max_employees`, `max_departments`, `max_monthly_payroll_runs`, `max_storage_mb`, `features`, `status`) VALUES
(1, 'Starter', 'starter', 'Perfect for small businesses', 49.00, 490.00, 25, 5, 1, 1000, 'Basic HR features, Attendance tracking, Leave management', 'active'),
(2, 'Professional', 'professional', 'For growing companies', 99.00, 990.00, 100, 20, 1, 5000, 'All Starter features, Payroll processing, Loan management, Advanced reports', 'active'),
(3, 'Enterprise', 'enterprise', 'Unlimited scale for large organizations', 299.00, 2990.00, NULL, NULL, 12, 50000, 'All Professional features, Unlimited employees, API access, Priority support', 'active');

-- Tenants
INSERT INTO `tenants` (`id`, `company_name`, `slug`, `email`, `phone`, `address`, `city`, `country`, `default_currency`, `status`) VALUES
(1, 'TechCorp Solutions', 'techcorp', 'admin@techcorp.com', '+1-555-0100', '123 Tech Street', 'San Francisco', 'USA', 'USD', 'active'),
(2, 'Global Innovations Ltd', 'globalinnovations', 'hr@globalinnovations.com', '+44-20-7123-4567', '45 Innovation Road', 'London', 'UK', 'GBP', 'active');

-- Platform Admin User
INSERT INTO `users` (`id`, `tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES
(1, NULL, 'admin@splashhr.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform', 'Admin', 'platform_admin', 'active');
-- Password: password

-- TechCorp Departments
INSERT INTO `departments` (`id`, `tenant_id`, `name`, `code`, `description`, `status`) VALUES
(1, 1, 'Engineering', 'ENG', 'Software development and engineering', 'active'),
(2, 1, 'Human Resources', 'HR', 'Human resources and talent management', 'active'),
(3, 1, 'Sales', 'SALES', 'Sales and business development', 'active'),
(4, 1, 'Marketing', 'MKT', 'Marketing and communications', 'active');

-- Global Innovations Departments
INSERT INTO `departments` (`id`, `tenant_id`, `name`, `code`, `description`, `status`) VALUES
(5, 2, 'Research & Development', 'RND', 'Research and product development', 'active'),
(6, 2, 'Operations', 'OPS', 'Operations and logistics', 'active'),
(7, 2, 'Finance', 'FIN', 'Finance and accounting', 'active');

-- Job Titles for TechCorp
INSERT INTO `job_titles` (`id`, `tenant_id`, `title`, `description`, `status`) VALUES
(1, 1, 'Software Engineer', 'Develops and maintains software applications', 'active'),
(2, 1, 'Senior Software Engineer', 'Senior-level software engineer', 'active'),
(3, 1, 'HR Manager', 'Manages human resources operations', 'active'),
(4, 1, 'Sales Representative', 'Handles sales and client relations', 'active'),
(5, 1, 'Marketing Specialist', 'Marketing campaigns and content creation', 'active');

-- Job Titles for Global Innovations
INSERT INTO `job_titles` (`id`, `tenant_id`, `title`, `description`, `status`) VALUES
(6, 2, 'Research Scientist', 'Conducts research and development', 'active'),
(7, 2, 'Operations Manager', 'Manages daily operations', 'active'),
(8, 2, 'Accountant', 'Financial accounting and reporting', 'active');

-- Employees for TechCorp
INSERT INTO `employees` (`id`, `tenant_id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `gender`, `date_of_birth`, `hire_date`, `department_id`, `job_title_id`, `employment_type`, `basic_salary`, `housing_allowance`, `transport_allowance`, `status`) VALUES
(1, 1, 'EMP001', 'John', 'Smith', 'john.smith@techcorp.com', '+1-555-0101', 'male', '1985-03-15', '2020-01-15', 1, 2, 'full-time', 8000.00, 2000.00, 500.00, 'active'),
(2, 1, 'EMP002', 'Sarah', 'Johnson', 'sarah.johnson@techcorp.com', '+1-555-0102', 'female', '1990-07-22', '2021-03-01', 1, 1, 'full-time', 6000.00, 1500.00, 500.00, 'active'),
(3, 1, 'EMP003', 'Michael', 'Brown', 'michael.brown@techcorp.com', '+1-555-0103', 'male', '1988-11-30', '2019-06-10', 2, 3, 'full-time', 7000.00, 1800.00, 500.00, 'active'),
(4, 1, 'EMP004', 'Emily', 'Davis', 'emily.davis@techcorp.com', '+1-555-0104', 'female', '1992-05-18', '2022-02-14', 3, 4, 'full-time', 5500.00, 1200.00, 400.00, 'active'),
(5, 1, 'EMP005', 'David', 'Wilson', 'david.wilson@techcorp.com', '+1-555-0105', 'male', '1987-09-25', '2020-08-20', 1, 1, 'full-time', 6500.00, 1600.00, 500.00, 'active'),
(6, 1, 'EMP006', 'Jennifer', 'Martinez', 'jennifer.martinez@techcorp.com', '+1-555-0106', 'female', '1991-12-08', '2023-01-10', 4, 5, 'full-time', 5000.00, 1000.00, 400.00, 'probation');

-- Employees for Global Innovations
INSERT INTO `employees` (`id`, `tenant_id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `gender`, `date_of_birth`, `hire_date`, `department_id`, `job_title_id`, `employment_type`, `basic_salary`, `housing_allowance`, `transport_allowance`, `status`) VALUES
(7, 2, 'GI001', 'James', 'Anderson', 'james.anderson@globalinnovations.com', '+44-20-7000-0001', 'male', '1983-04-12', '2018-05-15', 5, 6, 'full-time', 7500.00, 2000.00, 600.00, 'active'),
(8, 2, 'GI002', 'Emma', 'Taylor', 'emma.taylor@globalinnovations.com', '+44-20-7000-0002', 'female', '1989-08-20', '2019-09-01', 5, 6, 'full-time', 7000.00, 1800.00, 600.00, 'active'),
(9, 2, 'GI003', 'Oliver', 'White', 'oliver.white@globalinnovations.com', '+44-20-7000-0003', 'male', '1986-02-28', '2020-11-10', 6, 7, 'full-time', 6500.00, 1500.00, 500.00, 'active'),
(10, 2, 'GI004', 'Sophia', 'Harris', 'sophia.harris@globalinnovations.com', '+44-20-7000-0004', 'female', '1993-06-14', '2021-04-01', 7, 8, 'full-time', 5500.00, 1200.00, 400.00, 'active');

-- Users for TechCorp employees
INSERT INTO `users` (`id`, `tenant_id`, `employee_id`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES
(2, 1, 3, 'michael.brown@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', 'tenant_admin', 'active'),
(3, 1, 1, 'john.smith@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'manager', 'active'),
(4, 1, 2, 'sarah.johnson@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', 'employee', 'active'),
(5, 1, 4, 'emily.davis@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emily', 'Davis', 'employee', 'active');

-- Users for Global Innovations employees
INSERT INTO `users` (`id`, `tenant_id`, `employee_id`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES
(6, 2, 9, 'oliver.white@globalinnovations.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Oliver', 'White', 'tenant_admin', 'active'),
(7, 2, 7, 'james.anderson@globalinnovations.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Anderson', 'manager', 'active'),
(8, 2, 8, 'emma.taylor@globalinnovations.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Taylor', 'employee', 'active');

-- Leave Types for TechCorp
INSERT INTO `leave_types` (`id`, `tenant_id`, `name`, `code`, `default_days_per_year`, `is_paid`, `status`) VALUES
(1, 1, 'Annual Leave', 'ANNUAL', 21, 1, 'active'),
(2, 1, 'Sick Leave', 'SICK', 10, 1, 'active'),
(3, 1, 'Unpaid Leave', 'UNPAID', 0, 0, 'active');

-- Leave Types for Global Innovations
INSERT INTO `leave_types` (`id`, `tenant_id`, `name`, `code`, `default_days_per_year`, `is_paid`, `status`) VALUES
(4, 2, 'Annual Leave', 'ANNUAL', 25, 1, 'active'),
(5, 2, 'Sick Leave', 'SICK', 15, 1, 'active'),
(6, 2, 'Personal Leave', 'PERSONAL', 5, 1, 'active');

-- Leave Requests (sample data)
INSERT INTO `leave_requests` (`id`, `tenant_id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`, `approved_at`) VALUES
(1, 1, 2, 1, '2025-12-01', '2025-12-05', 5, 'Family vacation', 'approved', 3, '2025-11-20 10:30:00'),
(2, 1, 4, 2, '2025-11-18', '2025-11-19', 2, 'Medical appointment', 'approved', 2, '2025-11-17 14:00:00'),
(3, 1, 5, 1, '2025-12-20', '2025-12-23', 4, 'Holiday travel', 'pending', NULL, NULL),
(4, 2, 8, 4, '2025-11-25', '2025-11-29', 5, 'Annual leave', 'approved', 7, '2025-11-15 09:00:00');

-- Attendance Records for November 2025 (TechCorp - last 20 days)
INSERT INTO `attendance` (`tenant_id`, `employee_id`, `date`, `check_in`, `check_out`, `total_hours`, `overtime_hours`, `is_late`, `is_early_leave`, `is_absent`) VALUES
-- Employee 1 (John Smith)
(1, 1, '2025-11-01', '09:00:00', '17:30:00', 8.50, 0.50, 0, 0, 0),
(1, 1, '2025-11-04', '08:55:00', '17:00:00', 8.08, 0.00, 0, 0, 0),
(1, 1, '2025-11-05', '09:10:00', '17:00:00', 7.83, 0.00, 1, 0, 0),
(1, 1, '2025-11-06', '09:00:00', '18:00:00', 9.00, 1.00, 0, 0, 0),
(1, 1, '2025-11-07', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-08', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-11', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-12', '09:05:00', '17:00:00', 7.92, 0.00, 1, 0, 0),
(1, 1, '2025-11-13', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-14', '09:00:00', '17:30:00', 8.50, 0.50, 0, 0, 0),
(1, 1, '2025-11-15', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-18', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-19', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 1, '2025-11-20', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
-- Employee 2 (Sarah Johnson)
(1, 2, '2025-11-01', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-04', '09:15:00', '17:00:00', 7.75, 0.00, 1, 0, 0),
(1, 2, '2025-11-05', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-06', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-07', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-08', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-11', NULL, NULL, 0.00, 0.00, 0, 0, 1),
(1, 2, '2025-11-12', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-13', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-14', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-15', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-18', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-19', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 2, '2025-11-20', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
-- Employee 4 (Emily Davis)
(1, 4, '2025-11-01', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 4, '2025-11-04', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 4, '2025-11-05', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0),
(1, 4, '2025-11-06', '09:20:00', '17:00:00', 7.67, 0.00, 1, 0, 0),
(1, 4, '2025-11-07', '09:00:00', '17:00:00', 8.00, 0.00, 0, 0, 0);

-- Payroll Run for TechCorp (October 2025)
INSERT INTO `payroll_runs` (`id`, `tenant_id`, `month`, `year`, `pay_date`, `total_gross`, `total_deductions`, `total_net`, `status`, `processed_by`, `processed_at`) VALUES
(1, 1, 10, 2025, '2025-11-05', 65500.00, 8500.00, 57000.00, 'finalized', 2, '2025-11-01 15:30:00');

-- Payroll Items for October 2025 (TechCorp)
INSERT INTO `payroll_items` (`payroll_run_id`, `employee_id`, `basic_salary`, `housing_allowance`, `transport_allowance`, `other_allowances`, `overtime_amount`, `gross_salary`, `social_security_deduction`, `loan_deduction`, `unpaid_leave_deduction`, `other_deductions`, `total_deductions`, `net_salary`, `working_days`, `present_days`, `absent_days`, `overtime_hours`) VALUES
(1, 1, 8000.00, 2000.00, 500.00, 0.00, 100.00, 10600.00, 800.00, 500.00, 0.00, 0.00, 1300.00, 9300.00, 22, 22, 0, 2.00),
(1, 2, 6000.00, 1500.00, 500.00, 0.00, 0.00, 8000.00, 600.00, 0.00, 0.00, 0.00, 600.00, 7400.00, 22, 21, 1, 0.00),
(1, 3, 7000.00, 1800.00, 500.00, 0.00, 50.00, 9350.00, 700.00, 0.00, 0.00, 0.00, 700.00, 8650.00, 22, 22, 0, 1.00),
(1, 4, 5500.00, 1200.00, 400.00, 0.00, 0.00, 7100.00, 550.00, 0.00, 0.00, 0.00, 550.00, 6550.00, 22, 22, 0, 0.00),
(1, 5, 6500.00, 1600.00, 500.00, 0.00, 75.00, 8675.00, 650.00, 300.00, 0.00, 0.00, 950.00, 7725.00, 22, 22, 0, 1.50);

-- Loans for TechCorp employees
INSERT INTO `loans` (`id`, `tenant_id`, `employee_id`, `loan_amount`, `installment_amount`, `total_installments`, `paid_installments`, `remaining_amount`, `start_month`, `start_year`, `status`, `notes`) VALUES
(1, 1, 1, 6000.00, 500.00, 12, 1, 5500.00, 10, 2025, 'active', 'Personal loan'),
(2, 1, 5, 3600.00, 300.00, 12, 1, 3300.00, 10, 2025, 'active', 'Emergency loan');

-- Loan Installments
INSERT INTO `loan_installments` (`loan_id`, `payroll_item_id`, `installment_number`, `amount`, `month`, `year`, `status`, `paid_at`) VALUES
(1, 1, 1, 500.00, 10, 2025, 'paid', '2025-11-01 15:30:00'),
(2, 5, 1, 300.00, 10, 2025, 'paid', '2025-11-01 15:30:00');

-- Tenant Subscriptions
INSERT INTO `tenant_subscriptions` (`id`, `tenant_id`, `plan_id`, `billing_cycle`, `start_date`, `end_date`, `status`) VALUES
(1, 1, 2, 'monthly', '2025-01-01', '2025-12-31', 'active'),
(2, 2, 3, 'yearly', '2025-01-01', '2025-12-31', 'active');

-- Invoices
INSERT INTO `invoices` (`id`, `tenant_id`, `subscription_id`, `invoice_number`, `invoice_date`, `due_date`, `subtotal`, `tax`, `total`, `status`, `paid_at`) VALUES
(1, 1, 1, 'INV-2025-001', '2025-01-01', '2025-01-15', 99.00, 9.90, 108.90, 'paid', '2025-01-10 10:00:00'),
(2, 1, 1, 'INV-2025-002', '2025-02-01', '2025-02-15', 99.00, 9.90, 108.90, 'paid', '2025-02-08 10:00:00'),
(3, 1, 1, 'INV-2025-003', '2025-03-01', '2025-03-15', 99.00, 9.90, 108.90, 'paid', '2025-03-07 10:00:00'),
(4, 2, 2, 'INV-2025-004', '2025-01-01', '2025-01-15', 299.00, 29.90, 328.90, 'paid', '2025-01-12 10:00:00');

-- Payments
INSERT INTO `payments` (`tenant_id`, `invoice_id`, `payment_method`, `transaction_id`, `amount`, `payment_date`, `status`) VALUES
(1, 1, 'Credit Card', 'TXN-20250110-001', 108.90, '2025-01-10 10:00:00', 'completed'),
(1, 2, 'Credit Card', 'TXN-20250208-001', 108.90, '2025-02-08 10:00:00', 'completed'),
(1, 3, 'Credit Card', 'TXN-20250307-001', 108.90, '2025-03-07 10:00:00', 'completed'),
(2, 4, 'Bank Transfer', 'TXN-20250112-001', 328.90, '2025-01-12 10:00:00', 'completed');

-- Settings for TechCorp
INSERT INTO `settings` (`tenant_id`, `setting_key`, `setting_value`, `setting_type`) VALUES
(1, 'work_week_start', 'Monday', 'string'),
(1, 'work_week_end', 'Friday', 'string'),
(1, 'work_hours_per_day', '8', 'integer'),
(1, 'work_start_time', '09:00', 'string'),
(1, 'work_end_time', '17:00', 'string'),
(1, 'overtime_rate_per_hour', '25', 'decimal'),
(1, 'late_arrival_threshold_minutes', '15', 'integer');

-- Settings for Global Innovations
INSERT INTO `settings` (`tenant_id`, `setting_key`, `setting_value`, `setting_type`) VALUES
(2, 'work_week_start', 'Monday', 'string'),
(2, 'work_week_end', 'Friday', 'string'),
(2, 'work_hours_per_day', '8', 'integer'),
(2, 'work_start_time', '09:00', 'string'),
(2, 'work_end_time', '17:00', 'string'),
(2, 'overtime_rate_per_hour', '30', 'decimal'),
(2, 'late_arrival_threshold_minutes', '10', 'integer');

-- Holidays for TechCorp (2025)
INSERT INTO `holidays` (`tenant_id`, `name`, `date`, `is_recurring`) VALUES
(1, 'New Year Day', '2025-01-01', 1),
(1, 'Independence Day', '2025-07-04', 1),
(1, 'Thanksgiving', '2025-11-27', 1),
(1, 'Christmas', '2025-12-25', 1);

-- Holidays for Global Innovations (2025)
INSERT INTO `holidays` (`tenant_id`, `name`, `date`, `is_recurring`) VALUES
(2, 'New Year Day', '2025-01-01', 1),
(2, 'Good Friday', '2025-04-18', 0),
(2, 'Easter Monday', '2025-04-21', 0),
(2, 'Early May Bank Holiday', '2025-05-05', 0),
(2, 'Spring Bank Holiday', '2025-05-26', 0),
(2, 'Summer Bank Holiday', '2025-08-25', 0),
(2, 'Christmas Day', '2025-12-25', 1),
(2, 'Boxing Day', '2025-12-26', 1);

-- API Keys
INSERT INTO `api_keys` (`tenant_id`, `api_key`, `name`, `permissions`, `status`) VALUES
(1, 'demo_key_techcorp_abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567', 'TechCorp Production API', 'employees.read,employees.write,attendance.read,attendance.write', 'active'),
(2, 'demo_key_global_innovations_zyx987wvu654tsr321qpo098nml765kji432hgf109edc876ba', 'Global Innovations API', 'employees.read,attendance.write', 'active');

-- Notifications (sample)
INSERT INTO `notifications` (`tenant_id`, `user_id`, `type`, `title`, `message`, `is_read`) VALUES
(1, 4, 'leave_approved', 'Leave Request Approved', 'Your leave request for Nov 18-19, 2025 has been approved.', 0),
(1, 2, 'payroll_completed', 'Payroll Processed', 'October 2025 payroll has been processed successfully.', 1),
(2, 8, 'leave_approved', 'Leave Request Approved', 'Your leave request for Nov 25-29, 2025 has been approved.', 0);

COMMIT;
