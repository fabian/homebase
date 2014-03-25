<?php

namespace Homebase\Service;

class Beacons
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addProximity($beacon, $accuracy, $proximity, $rssi, $occurred, $occurredMicro)
    {
        $sql = 'INSERT INTO `beacons_proximities` (`beacon`, `accuracy`, `proximity`, `rssi`, `occurred`, `occurred_micro`, `recorded`) VALUES (?, ?, ?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($beacon, $accuracy, $proximity, $rssi, $occurred, $occurredMicro));
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

    public function deleteMappings($user)
    {
        // clear old values
        $sql = 'DELETE FROM `beacons_mappings` WHERE `user` = ? OR `user` IS NULL';

        $this->database->executeUpdate($sql, array($user));
    }

    public function saveMapping($beacon, $light, $user)
    {
        $sql = 'INSERT INTO `beacons_mappings` (`beacon`, `light`, `user`) VALUES (?, ?, ?)';

        $result = $this->database->executeUpdate($sql, array($beacon, $light, $user));
    }

    public function getUserMappings($user)
    {
        $sql = 'SELECT * FROM `beacons_mappings` WHERE `user` = ? OR `user` IS NULL';

        $result = $this->database->executeQuery($sql, array($user));

        return $result->fetchAll();
    }

    public function getMappings()
    {
        $sql = 'SELECT * FROM `beacons_mappings`';

        $result = $this->database->executeQuery($sql);

        return $result->fetchAll();
    }

    public function addState($beacon, $state, $occurred, $occurredMicro)
    {
        $sql = 'INSERT INTO `beacons_states` (`beacon`, `state`, `occurred`, `occurred_micro`, `recorded`) VALUES (?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($beacon, $state, $occurred, $occurredMicro));
    }

    public function getStates($from, $to, $limit = 10)
    {
        $sql = 'SELECT bs.id, bs.beacon, bs.state, bs.recorded, b.name AS `beacon_name` 
            FROM `beacons_states` bs
            INNER JOIN `beacons` b ON b.id = bs.beacon 
            WHERE `recorded` >= ? AND `recorded` < ? 
            ORDER BY `occurred` DESC, `occurred_micro` DESC 
            LIMIT ?';

        $result = $this->database->executeQuery($sql, array($from, $to, $limit), array(\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT));

        return $result->fetchAll();
    }

    public function getLatestStates()
    {
        $sql = 'SELECT `id`,
            (SELECT `state`
                FROM `beacons_states` bs
                WHERE bs.beacon = b.id
                ORDER BY `occurred` DESC,  `occurred_micro` DESC
                LIMIT 1) AS `state`
            FROM `beacons` b
            GROUP BY b.id';

        $result = $this->database->fetchAll($sql);

        return $result;
    }
}
