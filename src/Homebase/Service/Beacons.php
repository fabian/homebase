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

    public function saveBeaconActive($beacon, $active = false)
    {
        $sql = 'UPDATE `beacons` SET `active` = ? WHERE `id` = ?';

        $result = $this->database->executeUpdate($sql, array($active, $beacon));
    }

    public function getBeacons()
    {
        $sql = 'SELECT * FROM `beacons` ORDER BY `added` DESC';

        $result = $this->database->executeQuery($sql);

        return $result->fetchAll();
    }

    public function getProximities($from, $to, $limit = 100)
    {
        $sql = 'SELECT * FROM `beacons_proximities` WHERE `recorded` >= ? AND `recorded` < ? ORDER BY `recorded` DESC LIMIT ?';

        $result = $this->database->executeQuery($sql, array($from, $to, $limit), array(\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT));

        return $result->fetchAll();
    }

    public function getBeacon($uuid, $major, $minor) {

        $sql = 'SELECT * FROM `beacons` WHERE `uuid` = ? AND `major` = ? AND `minor` = ?';

        $beacon = $this->database->fetchAssoc($sql, array($uuid, $major, $minor));

        return $beacon;
    }

    public function saveMapping($mapping, $user)
    {
        // clear old values
        $sql = 'DELETE FROM `beacons_mapping` WHERE `user` = ? OR `user` IS NULL';

        $this->database->executeUpdate($sql, array($user));

        // store new values
        $sql = 'INSERT INTO `beacons_mapping` (`beacon`, `light`, `user`) VALUES (?, ?, ?)';

        foreach ($mapping as $beacon => $light) {

            foreach ($light as $lightId => $true) {

                $result = $this->database->executeUpdate($sql, array($beacon, $lightId, $user));
            }
        }
    }

    public function getMapping($user)
    {
        $sql = 'SELECT * FROM `beacons_mapping` WHERE `user` = ? OR `user` IS NULL';

        $result = $this->database->executeQuery($sql, array($user));

        return $result->fetchAll();
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
