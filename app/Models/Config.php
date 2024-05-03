<?php

namespace PHPvian\Models;

use PHPvian\Libs\Database;

class Config extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getSettings()
    {
        $settings = $this->db->select('config');
        if (empty($settings)) {
            return [];
        }
        return $settings[0];
    }

    public function updateSettings(array $newSettings)
    {
        $currentSettings = $this->getSettings();
        $updatedSettings = array_merge($currentSettings, $newSettings);

        $this->db->update('config', ['config' => json_encode($updatedSettings)], '1');
    }

    public function getSettingValue($key)
    {
        $settings = $this->getSettings();
        return isset($settings[$key]) ? $settings[$key] : null;
    }
}