<?php

namespace Homebase\Service;

class Queue
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addLight($light)
    {
        $sql = 'INSERT INTO `queue` (`light`, `created`) VALUES (?, NOW())';

        $result = $this->database->executeUpdate($sql, array($light));
    }

    public function getLights($to)
    {
        $sql = 'SELECT * FROM `queue` WHERE `created` < ? GROUP BY `light`';

        $result = $this->database->fetchAll($sql, array($to));

        return $result;
    }

    public function deleteLight($light)
    {
        $sql = 'DELETE FROM `queue` WHERE `light` = ?';

        $this->database->executeUpdate($sql, array($light));
    }
}
