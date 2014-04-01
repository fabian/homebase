<?php

namespace Homebase\Service;

class Config
{
    const ENGINE_MODE = 'engine_mode';
    
    const ENGINE_MODE_MANUAL = 'manual';

    const ENGINE_MODE_AUTOMATIC = 'automatic';

    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function get($key)
    {
        $sql = 'SELECT value FROM `config` WHERE `key` = ?';

        $result = $this->database->executeQuery($sql, array($key));

        return $result->fetchColumn(0);
    }

    public function set($key, $value)
    {
        $sql = 'INSERT INTO `config` (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?';

        $result = $this->database->executeUpdate($sql, array($key, $value, $value));
    }
}
