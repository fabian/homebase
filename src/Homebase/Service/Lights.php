<?php

namespace Homebase\Service;

class Lights
{
    const STATE_EXECUTED = 'executed';

    const STATE_QUEUED = 'queued';

    const STATE_CANCELED = 'canceled';
    
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

    public function getLogs($from, $to)
    {
        $sql = 'SELECT * FROM `lights_log` WHERE `created` >= ? AND `created` < ? ORDER BY `created`';

        $result = $this->database->fetchAll($sql, array($from, $to));

        return $result;
    }

    public function getLightsOn($on)
    {
        $sql = 'SELECT `id`,
            (SELECT `on`
                FROM `lights_log` ll
                WHERE ll.light = l.id
                ORDER BY `created` DESC
                LIMIT 1) AS `on`
            FROM `lights` l
            HAVING `on` = ?';

        $result = $this->database->fetchAll($sql, array($on));

        return $result;
    }

    public function getSummedLogs($from, $to)
    {
        $sql = 'SELECT l.light, DATE_FORMAT(l.created, "%Y-%m-%d") AS `date`, SUM(l.on) AS `hours` 
            FROM `lights_log` l 
            WHERE l.created >= ? AND l.created < ? 
            GROUP BY l.light, `date` 
            ORDER BY l.created, l.light';

        $result = $this->database->fetchAll($sql, array($from, $to));

        return $result;
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

    public function getActions($limit = 10)
    {
        $sql = 'SELECT a.id, a.on, a.state, a.scheduled, a.executed, l.number, l.name, \'action\' AS `type`
            FROM `lights_actions` a 
            INNER JOIN `lights` l ON l.id = a.light 
            ORDER BY `scheduled` DESC 
            LIMIT ?';

        $result = $this->database->executeQuery($sql, array($limit), array(\PDO::PARAM_INT));

        return $result->fetchAll();
    }

    public function getQueuedActions()
    {
        $sql = 'SELECT a.id, a.on, l.number FROM `lights_actions` a INNER JOIN `lights` l ON l.id = a.light WHERE a.state = ? AND `scheduled` <= NOW()';

        $result = $this->database->fetchAll($sql, array(self::STATE_QUEUED));

        return $result;
    }

    public function getLatestActions()
    {
        $sql = 'SELECT `id`,
            (SELECT `on`
                FROM `lights_actions` la
                WHERE la.light = l.id AND la.state IN (?, ?)
                ORDER BY `scheduled` DESC
                LIMIT 1) AS `on`
            FROM `lights` l';

        $result = $this->database->fetchAll($sql, array(self::STATE_EXECUTED, self::STATE_QUEUED));

        return $result;
    }
}

