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

    public function addAction($light, $on)
    {
        $sql = 'INSERT INTO `lights_actions` (`light`, `on`, `state`, `created`) VALUES (?, ?, ?, NOW())';

        $result = $this->database->executeUpdate($sql, array($light, $on, self::STATE_QUEUED));
    }

    public function updateActions($light, $on, $state, $newState)
    {
        $sql = 'UPDATE `lights_actions` SET `state` = ?, `executed` = NOW() WHERE `light` = ? AND `on` = ? AND `state` = ?';

        $result = $this->database->executeUpdate($sql, array($newState, $light, $on, $state));
    }

    public function getQueuedActions($on, $to)
    {
        $sql = 'SELECT * FROM `lights_actions` WHERE `on` = ? AND `state` = ? AND `created` < ? GROUP BY `light`';

        $result = $this->database->fetchAll($sql, array($on, self::STATE_QUEUED, $to));

        return $result;
    }

    public function getLatestActions()
    {
        $sql = 'SELECT a1.*
            FROM lights_actions a1
            LEFT JOIN lights_actions a2
            ON a1.light = a2.light AND a1.created < a2.created
            WHERE a1.state IN (?, ?) AND a2.id IS NULL';

        $result = $this->database->fetchAll($sql, array(self::STATE_EXECUTED, self::STATE_QUEUED));

        return $result;
    }
}

