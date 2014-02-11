<?php

namespace Homebase\Service;

class Log
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getLogs($from, $to)
    {
        $sql = 'SELECT * FROM `log_states` WHERE `created` >= ? AND `created` < ? ORDER BY `created`';

        $result = $this->database->fetchAll($sql, array($from, $to));

        return $result;
    }
}
