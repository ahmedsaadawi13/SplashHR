<?php
// FILE: /app/models/Setting.php

/**
 * Setting Model
 * Handles tenant settings data operations
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Setting extends Model {

    protected $table = 'settings';

    /**
     * Get setting value for tenant
     * @param string $key
     * @param int $tenantId
     * @param mixed $default
     * @return mixed
     */
    public function getValue($key, $tenantId, $default = null) {
        $setting = $this->findOne([
            'tenant_id' => $tenantId,
            'setting_key' => $key
        ]);

        if ($setting) {
            return $this->castValue($setting['setting_value'], $setting['setting_type']);
        }

        return $default;
    }

    /**
     * Set setting value for tenant
     * @param string $key
     * @param mixed $value
     * @param int $tenantId
     * @param string $type
     * @return bool
     */
    public function setValue($key, $value, $tenantId, $type = 'string') {
        $existing = $this->findOne([
            'tenant_id' => $tenantId,
            'setting_key' => $key
        ]);

        $data = [
            'setting_value' => (string)$value,
            'setting_type' => $type
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['tenant_id'] = $tenantId;
            $data['setting_key'] = $key;
            return $this->insert($data) > 0;
        }
    }

    /**
     * Get all settings for tenant
     * @param int $tenantId
     * @return array Key-value pairs
     */
    public function getAllSettings($tenantId) {
        $settings = $this->findAll(['tenant_id' => $tenantId]);
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->castValue(
                $setting['setting_value'],
                $setting['setting_type']
            );
        }

        return $result;
    }

    /**
     * Cast value to appropriate type
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'decimal':
            case 'float':
                return (float)$value;
            case 'boolean':
                return (bool)$value;
            default:
                return $value;
        }
    }
}
