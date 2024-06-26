<?php

namespace PHPvian\Models;

use PHPvian\Libs\Connection;

class Config extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSettings()
    {
        $settings = $this->conn->select()->from('config')->first();
        if (empty($settings)) {
            return [];
        }
        return $settings ?? null;
    }

    public function updateSettings(array $newSettings)
    {
        $currentSettings = $this->getSettings();
        $updatedSettings = array_merge($currentSettings, $newSettings);

        $this->conn->update('config', ['config' => json_encode($updatedSettings)]);
    }

    public function getSettingValue($key)
    {
        $settings = $this->getSettings();
        return isset($settings[$key]) ? $settings[$key] : null;
    }
}