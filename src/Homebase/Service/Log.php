<?php

namespace Homebase\Service;

class Log
{

    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getLogs($since)
    {
        $sql = 'SELECT * FROM `log_states` WHERE `created` >= ? ORDER BY `created`';

        $result = $this->database->fetchAll($sql, array($since));

        return $result;
    }
}
