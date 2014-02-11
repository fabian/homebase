<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


class DashboardController
{
    protected $twig;

    protected $log;

    protected $beacons;

    protected $regions;

    public function __construct($twig, $log, $beacons, $regions) {
        $this->twig = $twig;
        $this->log = $log;
        $this->beacons = $beacons;
        $this->regions = $regions;
    }

    public function indexAction()
    {
        $from = new \DateTime('-24 hour');
        $to = new \DateTime('now');

        $regions = $this->regions->getRegions($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'));

        return $this->twig->render('dashboard.twig', array('regions' => $regions));
    }

    public function beaconsAction()
    {
        $from = new \DateTime('-59 minutes');
        $to = new \DateTime('now');

        $beacons = $this->beacons->getBeacons($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'));

        $minutes = array();
        foreach(range(0, 59) as $minute) {
            $minutes[$minute] = 1;
        }

        $grouped = array();
        foreach ($beacons as $beacon) {

            $beaconId = $beacon['uuid'] . '.' . $beacon['major'] . '.' . $beacon['minor'];
            $minute = (int) date('i', strtotime($beacon['recorded']));

            if (!isset($grouped[$beaconId])) {
                $grouped[$beaconId] = $minutes;
            }

            if ($beacon['rssi']) {
                $grouped[$beaconId][$minute] = (int) $beacon['rssi'];
            }
        }

        $data = array();
        foreach ($grouped as $beacon => $minutes) {
            foreach ($minutes as $minute => $rssi) {
                $data[] = array('beacon' => $beacon, 'minute' => $minute, 'rssi' => $rssi);
            }
        }
    
        return new JsonResponse(array('data' => $data));
    }

    public function logsAction()
    {
        $from = new \DateTime('-30 days');
        $to = new \DateTime('today');

        $logs = $this->log->getLogs($from->format('Y-m-d 00:00:00'), $to->format('Y-m-d 23:59:59'));

        $period = new \DatePeriod($from, new \DateInterval('P1D'), $to);
        $days = array();
        foreach( $period as $date) {
            $days[$date->format('Y-m-d')] = array();
        }

        $hours = array();
        foreach ($logs as $log) {

            if ($log['on']) {

                $light = $log['light'];
                $date = date('Y-m-d', strtotime($log['created']));
                $hour = date('H:00', strtotime($log['created']));

                if (!isset($hours[$light])) {
                    $hours[$light] = $days;
                }

                if (isset($hours[$light][$date])) {
                    $hours[$light][$date][$hour] = $log['on'];
                }
            }
        }

        $data = array();
        foreach ($hours as $light => $child) {
            foreach ($child as $date => $logs) {
                $data[] = array('light' => $light, 'date' => $date, 'hours' => count($logs));
            }
        }

        return new JsonResponse(array('data' => $data));
    }
}
