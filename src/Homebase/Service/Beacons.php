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
        $sql = 'INSERT INtO `beacons` (`uuid`, `major`, `minor`, `accuracy`, `proximity`, `recorded`) VALUES (?, ?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array(
            $beacon->get('uuid', ''),
            $beacon->get('major', ''),
            $beacon->get('minor', ''),
            $beacon->get('accuracy', 0),
            $beacon->get('proximity', ''),
        ));
    }
}
