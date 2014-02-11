<?php

namespace Homebase\Service;

class Beacons
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addBeacon($beacon)
    {
        $sql = 'INSERT INtO `beacons` (`uuid`, `major`, `minor`, `accuracy`, `proximity`, `rssi`, `recorded`) VALUES (?, ?, ?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array(
            $beacon->get('uuid', ''),
            $beacon->get('major', ''),
            $beacon->get('minor', ''),
            $beacon->get('accuracy', 0),
            $beacon->get('proximity', ''),
            $beacon->get('rssi', 0),
        ));
    }

    public function getBeacon($uuid, $major, $minor) {
    
        $sql = 'SELECT * FROM `beacons` WHERE `uuid` = ? AND `major` = ? AND `minor` = ? ORDER BY `recorded` DESC LIMIT 1';

        $beacon = $this->database->fetchAssoc($sql, array($uuid, $major, $minor));

        return $beacon;
    }
}
