<?php
// FILE: /app/models/SubscriptionPlan.php

/**
 * Subscription Plan Model
 * Handles subscription plan data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class SubscriptionPlan extends Model {

    protected $table = 'subscription_plans';

    /**
     * Get all active subscription plans
     * @return array
     */
    public function getActivePlans() {
        return $this->findAll(['status' => 'active'], 'price_monthly ASC');
    }
}
