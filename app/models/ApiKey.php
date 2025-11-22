<?php
// FILE: /app/models/ApiKey.php

/**
 * API Key Model
 * Handles API key data operations for API authentication
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class ApiKey extends Model {

    protected $table = 'api_keys';

    /**
     * Validate API key and get tenant
     * @param string $apiKey
     * @return array|false
     */
    public function validateApiKey($apiKey) {
        $sql = "SELECT ak.*, t.company_name, t.status as tenant_status
                FROM api_keys ak
                INNER JOIN tenants t ON ak.tenant_id = t.id
                WHERE ak.api_key = :api_key
                AND ak.status = 'active'
                AND t.status = 'active'
                AND (ak.expires_at IS NULL OR ak.expires_at > NOW())
                LIMIT 1";

        $stmt = $this->query($sql, [':api_key' => $apiKey]);
        $result = $stmt->fetch();

        if ($result) {
            // Update last used timestamp
            $this->update($result['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
        }

        return $result;
    }

    /**
     * Generate new API key
     * @return string
     */
    public function generateKey() {
        return 'sk_live_' . bin2hex(random_bytes(32));
    }

    /**
     * Get API keys for a tenant
     * @param int $tenantId
     * @return array
     */
    public function getByTenant($tenantId) {
        return $this->findAll(['tenant_id' => $tenantId], 'created_at DESC');
    }
}
