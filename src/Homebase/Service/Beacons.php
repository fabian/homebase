<?php

namespace Homebase\Service;

class Beacons
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addProximity($beacon, $accuracy, $proximity, $rssi)
    {
        $sql = 'INSERT INTO `beacons_proximities` (`beacon`, `accuracy`, `proximity`, `rssi`, `recorded`) VALUES (?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($beacon, $accuracy, $proximity, $rssi));
    }

    public function addBeacon($uuid, $major, $minor, $name = '', $active = false)
    {
        $sql = 'INSERT IGNORE INTO `beacons` (`uuid`, `major`, `minor`, `name`, `active`, `added`) VALUES (?, ?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array(
            $uuid,
            $major,
            $minor,
            $name,
            $active,
        ));
    }

    public function getProximities($from, $to)
    {
        $sql = 'SELECT * FROM `beacons_proximities` WHERE `recorded` >= ? AND `recorded` < ? ORDER BY `recorded` DESC';

        $result = $this->database->fetchAll($sql, array($from, $to));

        return $result;
    }

    public function getBeacon($uuid, $major, $minor) {

        $sql = 'SELECT * FROM `beacons` WHERE `uuid` = ? AND `major` = ? AND `minor` = ?';

        $beacon = $this->database->fetchAssoc($sql, array($uuid, $major, $minor));

        return $beacon;
    }

    public function addState($beacon, $state)
    {
        $sql = 'INSERT INTO `beacons_states` (`beacon`, `state`, `recorded`) VALUES (?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($beacon, $state));
    }

    public function getStates($from, $to, $limit = 10)
    {
        $sql = 'SELECT * FROM `beacons_states` WHERE `recorded` >= ? AND `recorded` < ? ORDER BY `recorded` DESC LIMIT ?';

        $result = $this->database->executeQuery($sql, array($from, $to, $limit), array(\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT));

        return $result->fetchAll();
    }

    public function getLatestStates()
    {
        $sql = 'SELECT `id`,
            (SELECT `state`
                FROM `beacons_states` bs
                WHERE bs.beacon = b.id
                ORDER BY `recorded` DESC
                LIMIT 1) AS `state`
            FROM `beacons` b
            GROUP BY b.id';

        $result = $this->database->fetchAll($sql);

        return $result;
    }
}
