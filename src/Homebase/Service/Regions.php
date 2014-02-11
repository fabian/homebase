<?php

namespace Homebase\Service;

class Regions
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addRegion($beacon)
    {
        $sql = 'INSERT INTO `regions` (`uuid`, `major`, `minor`, `state`, `recorded`) VALUES (?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array(
            $beacon->get('uuid', ''),
            $beacon->get('major', ''),
            $beacon->get('minor', ''),
            $beacon->get('state', ''),
        ));
    }

    public function getRegions($from, $to, $limit = 10)
    {
        $sql = 'SELECT * FROM `regions` WHERE `recorded` >= ? AND `recorded` < ? ORDER BY `recorded` DESC LIMIT ?';

        $result = $this->database->fetchAll($sql, array($from, $to, $limit));

        return $result;
    }
}
