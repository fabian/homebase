<?php

namespace Homebase\Service;

class Beacons
{
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addProximity($beacon, $accuracy, $proximity, $rssi, $occurred, $occurredMicro, $positionX, $positionY)
    {
        $sql = 'INSERT INTO `beacons_proximities` (`beacon`, `accuracy`, `proximity`, `rssi`, `occurred`, `occurred_micro`, `position_x`, `position_y`, `recorded`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($beacon, $accuracy, $proximity, $rssi, $occurred, $occurredMicro, $positionX, $positionY));
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

    public function saveBeacon($id, $name, $uuid, $major, $minor, $active)
    {
        $sql = 'UPDATE `beacons` SET `name` = ?, `uuid` = ?, `major` = ?, `minor` = ?, `active` = ? WHERE `id` = ?';

        $result = $this->database->executeUpdate($sql, array(
            $name,
            $uuid,
            $major,
            $minor,
            $active,
            $id,
        ));
    }

    public function isDuplicate($uuid, $major, $minor, $id = '0')
    {
        $beacons = $this->getBeacons();
        $duplicate = false;
        foreach ($beacons as $beacon) {
            if ($beacon['uuid'] == $uuid && $beacon['major'] == $major && $beacon['minor'] == $minor) {
                if ($beacon['id'] != $id) {
                    $duplicate = true;
                    break;
                }
            }
        }

        return $duplicate;
    }

    public function getBeacons()
    {
        $sql = 'SELECT * FROM `beacons` ORDER BY `added` DESC';

        $result = $this->database->executeQuery($sql);

        return $result->fetchAll();
    }

    public function getNumberBeacons($active)
    {
        $sql = 'SELECT COUNT(*) FROM `beacons` WHERE `active` = ?';

        $result = $this->database->executeQuery($sql, array($active));

        return $result->fetchColumn(0);
    }

    public function getProximities($from, $to, $limit = 100)
    {
        $sql = 'SELECT * FROM `beacons_proximities` WHERE `recorded` >= ? AND `recorded` < ? ORDER BY `recorded` DESC LIMIT ?';

        $result = $this->database->executeQuery($sql, array($from, $to, $limit), array(\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT));

        return $result->fetchAll();
    }

    public function getMeasurements($beacon, $from, $to)
    {
        $sql = 'SELECT `position_x`, `position_y`, `rssi` FROM `beacons_proximities` WHERE `beacon` = ? AND `recorded` >= ? AND `recorded` < ? AND `position_x` IS NOT NULL AND `position_y` IS NOT NULL AND `rssi` < 0 ORDER BY `rssi`';

        $result = $this->database->executeQuery($sql, array($beacon, $from, $to));

        return $result->fetchAll();
    }

    public function getBeaconById($id)
    {
        $sql = 'SELECT * FROM `beacons` WHERE `id` = ?';

        $beacon = $this->database->fetchAssoc($sql, array($id));

        return $beacon;
    }

    public function getBeacon($uuid, $major, $minor)
    {
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
        $sql = 'SELECT l.id AS `light`, bm.beacon
            FROM `lights` l
            LEFT JOIN `beacons_mappings` bm ON bm.light = l.id';

        $result = $this->database->executeQuery($sql);

        return $result->fetchAll();
    }

    public function addState($beacon, $state, $occurred, $occurredMicro)
    {
        $sql = 'INSERT INTO `beacons_states` (`beacon`, `state`, `occurred`, `occurred_micro`, `recorded`) VALUES (?, ?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($beacon, $state, $occurred, $occurredMicro));
    }

    public function getStates($limit = 10)
    {
        $sql = 'SELECT bs.id, bs.beacon, bs.state, bs.recorded, bs.occurred, bs.occurred_micro, b.name AS `beacon_name`, \'state\' AS `type`
            FROM `beacons_states` bs
            INNER JOIN `beacons` b ON b.id = bs.beacon 
            ORDER BY `occurred` DESC, `occurred_micro` DESC 
            LIMIT ?';

        $result = $this->database->executeQuery($sql, array($limit), array(\PDO::PARAM_INT));

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
