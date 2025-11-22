<?php
// FILE: /app/controllers/AuthController.php

/**
 * Authentication Controller
 * Handles user authentication (login, logout, registration)
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class AuthController extends Controller {

    /**
     * Show login page
     */
    public function login() {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view->render('auth/login', [], 'auth');
    }

    /**
     * Process login
     */
    public function loginPost() {
        if (!$this->validateCSRF()) {
            $this->flash('error', 'Invalid request. Please try again.');
            $this->redirect('/login');
        }

        $email = $this->sanitize($this->input('email'));
        $password = $this->input('password');

        $validator = new Validator();
        $validator->required('email', $email, 'Email');
        $validator->email('email', $email);
        $validator->required('password', $password, 'Password');

        if ($validator->fails()) {
            $this->flash('error', $validator->firstError());
            $this->redirect('/login');
        }

        if (Auth::login($email, $password)) {
            $this->redirect('/dashboard');
        } else {
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }
    }

    /**
     * Logout
     */
    public function logout() {
        Auth::logout();
        $this->redirect('/login');
    }
}
