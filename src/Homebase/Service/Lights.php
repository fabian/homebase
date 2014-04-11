<?php

namespace Homebase\Service;

class Lights
{
    const STATE_EXECUTED = 'executed';

    const STATE_QUEUED = 'queued';

    const STATE_CANCELLED = 'cancelled';
    
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function addLight($number, $name)
    {
        $sql = 'INSERT INTO `lights` (`number`, `name`, `added`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `name` = ?';
    
        $result = $this->database->executeUpdate($sql, array(
            $number,
            $name,
            $name,
        ));
    }

    public function getLights()
    {
        $sql = 'SELECT * FROM `lights`';

        $result = $this->database->fetchAll($sql);

        return $result;
    }

    public function getLight($number)
    {
        $sql = 'SELECT * FROM `lights` WHERE `number` = ?';

        $light = $this->database->fetchAssoc($sql, array($number));

        return $light;
    }

    public function addLog($light, $on)
    {
        $sql = 'INSERT INTO `lights_log` (`light`, `on`, `created`) VALUES (?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array(
            $light,
            $on,
        ));
    }

    public function addAction($light, $on, $delay)
    {
        $sql = 'INSERT INTO `lights_actions` (`light`, `on`, `scheduled`, `state`) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?)';

        $result = $this->database->executeUpdate($sql, array($light, $on, $delay, self::STATE_QUEUED));
    }

    public function updateActions($light, $on, $state, $newState)
    {
        $sql = 'UPDATE `lights_actions` SET `state` = ?, `executed` = NOW() WHERE `light` = ? AND `on` = ? AND `state` = ?';

        $result = $this->database->executeUpdate($sql, array($newState, $light, $on, $state));
    }

    public function updateAction($action, $state)
    {
        $sql = 'UPDATE `lights_actions` SET `state` = ?, `executed` = NOW() WHERE `id` = ?';

        $result = $this->database->executeUpdate($sql, array($state, $action));
    }

    public function getActions()
    {
        $sql = 'SELECT a.id, a.on, a.state, a.scheduled, a.executed, l.number, l.name 
            FROM `lights_actions` a 
            INNER JOIN `lights` l ON l.id = a.light 
            ORDER BY `scheduled` DESC';

        $result = $this->database->fetchAll($sql);

        return $result;
    }

    public function getQueuedActions()
    {
        $sql = 'SELECT a.id, a.on, l.number FROM `lights_actions` a INNER JOIN `lights` l ON l.id = a.light WHERE a.state = ? AND `scheduled` <= NOW()';

        $result = $this->database->fetchAll($sql, array(self::STATE_QUEUED));

        return $result;
    }

    public function getLatestActions()
    {
        $sql = 'SELECT a1.*
            FROM lights_actions a1
            LEFT JOIN lights_actions a2
            ON a1.light = a2.light AND a1.scheduled < a2.scheduled
            WHERE a1.state IN (?, ?) AND a2.id IS NULL';

        $result = $this->database->fetchAll($sql, array(self::STATE_EXECUTED, self::STATE_QUEUED));

        return $result;
    }
}

