<?php
// FILE: /app/controllers/JobTitleController.php

/**
 * Job Title Controller
 * Handles job title CRUD operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class JobTitleController extends Controller {

    /**
     * List all job titles
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'manager']);
        $tenantId = $this->tenantId();

        $jobTitleModel = $this->model('JobTitle');
        $jobTitles = $jobTitleModel->getByTenant($tenantId, null);

        $data = ['job_titles' => $jobTitles];
        $this->view->render('job_titles/index', $data);
    }

    /**
     * Show create job title form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);
        $this->view->render('job_titles/create', []);
    }

    /**
     * Process create job title
     */
    public function store() {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/job-titles');
        }

        $tenantId = $this->tenantId();
        $jobTitleModel = $this->model('JobTitle');

        $data = [
            'tenant_id' => $tenantId,
            'title' => $this->sanitize($this->input('title')),
            'description' => $this->sanitize($this->input('description')),
            'status' => 'active'
        ];

        // Validation
        $validator = new Validator();
        $validator->required('title', $data['title'], 'Job Title');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/job-titles/create');
        }

        if ($jobTitleModel->insert($data)) {
            $this->flash('success', 'Job title created successfully.');
            $this->redirect('/job-titles');
        } else {
            $this->flash('error', 'Failed to create job title.');
            $this->redirect('/job-titles/create');
        }
    }

    /**
     * Show edit job title form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->tenantId();

        $jobTitleModel = $this->model('JobTitle');
        $jobTitle = $jobTitleModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);

        if (!$jobTitle) {
            $this->flash('error', 'Job title not found.');
            $this->redirect('/job-titles');
        }

        $data = ['job_title' => $jobTitle];
        $this->view->render('job_titles/edit', $data);
    }

    /**
     * Process update job title
     */
    public function update($id) {
        $this->requireRole(['tenant_admin']);

        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/job-titles');
        }

        $tenantId = $this->tenantId();
        $jobTitleModel = $this->model('JobTitle');

        $jobTitle = $jobTitleModel->findOne(['id' => $id, 'tenant_id' => $tenantId]);
        if (!$jobTitle) {
            $this->flash('error', 'Job title not found.');
            $this->redirect('/job-titles');
        }

        $data = [
            'title' => $this->sanitize($this->input('title')),
            'description' => $this->sanitize($this->input('description')),
            'status' => $this->input('status')
        ];

        if ($jobTitleModel->update($id, $data)) {
            $this->flash('success', 'Job title updated successfully.');
            $this->redirect('/job-titles');
        } else {
            $this->flash('error', 'Failed to update job title.');
            $this->redirect('/job-titles/' . $id . '/edit');
        }
    }
}
