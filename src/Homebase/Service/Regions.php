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
        $limit = (int) $limit;
        $sql = 'SELECT * FROM `regions` WHERE `recorded` >= ? AND `recorded` < ? ORDER BY `recorded` DESC LIMIT ' . $limit;

        $result = $this->database->fetchAll($sql, array($from, $to));

        return $result;
    }

    public function getRegionStates()
    {
        $sql = 'SELECT `uuid`, `major`, `minor`, 
            (SELECT `state` 
                FROM regions rr 
                WHERE rr.uuid = r.uuid AND rr.major = r.major AND rr.minor = r.minor 
                ORDER BY `recorded` DESC 
                LIMIT 1) AS `state` 
            FROM `regions` r
            GROUP BY uuid, major, minor';

        $result = $this->database->fetchAll($sql);

        return $result;
    }
}
