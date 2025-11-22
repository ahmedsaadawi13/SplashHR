<?php
// FILE: /app/controllers/SettingsController.php

/**
 * Settings Controller
 * Handles tenant settings management
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class SettingsController extends Controller {

    /**
     * Show settings page
     */
    public function index() {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->tenantId();

        $settingModel = $this->model('Setting');
        $settings = $settingModel->getAllSettings($tenantId);

        // Get tenant details
        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->findById($tenantId);

        $data = [
            'settings' => $settings,
            'tenant' => $tenant
        ];

        $this->view->render('settings/index', $data);
    }

    /**
     * Update settings
     */
    public function update() {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/settings');
        }

        $tenantId = $this->tenantId();
        $settingModel = $this->model('Setting');

        // Work settings
        $settingModel->setValue('work_week_start', $this->input('work_week_start'), $tenantId, 'string');
        $settingModel->setValue('work_week_end', $this->input('work_week_end'), $tenantId, 'string');
        $settingModel->setValue('work_hours_per_day', $this->input('work_hours_per_day'), $tenantId, 'integer');
        $settingModel->setValue('work_start_time', $this->input('work_start_time'), $tenantId, 'string');
        $settingModel->setValue('work_end_time', $this->input('work_end_time'), $tenantId, 'string');
        $settingModel->setValue('overtime_rate_per_hour', $this->input('overtime_rate_per_hour'), $tenantId, 'decimal');

        // Update tenant info
        $tenantModel = $this->model('Tenant');
        $tenantData = [
            'company_name' => $this->sanitize($this->input('company_name')),
            'email' => $this->sanitize($this->input('email')),
            'phone' => $this->sanitize($this->input('phone')),
            'address' => $this->sanitize($this->input('address')),
            'city' => $this->sanitize($this->input('city')),
            'country' => $this->sanitize($this->input('country')),
            'default_currency' => $this->input('default_currency')
        ];

        $tenantModel->update($tenantId, $tenantData);

        $this->flash('success', 'Settings updated successfully.');
        $this->redirect('/settings');
    }
}
