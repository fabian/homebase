<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


class ReportController
{
    protected $twig;

    protected $beacons;

    protected $lights;

    public function __construct($twig, $beacons, $lights) {
        $this->twig = $twig;
        $this->beacons = $beacons;
        $this->lights = $lights;
    }

    public function indexAction()
    {
        return $this->twig->render('report.twig');
    }

    public function dayAction($day)
    {
        $day = strtotime($day);
        $from = date('Y-m-d 00:00:00', $day);
        $to = date('Y-m-d 23:59:59', $day);

        $logs = $this->lights->getLogs($from, $to);

        $hours = array();
        foreach ($logs as $log) {
            if ($log['on']) {
                $hours[date('H:i', strtotime($log['created']))][$log['light']] = $log['on'];
            }
        }

        // wrap up
        $data = array();
        for ($i = 0; $i < 24 * 60; $i++) {
            $hour = sprintf('%02d:%02d', ($i / 60), ($i % 60));
            $element = array('hour' => $hour, 'lights' => 0);
            if (isset($hours[$hour])) {
                $element['lights'] = count($hours[$hour]);
            }
            $data[] = $element;
        }

        return new JsonResponse(array('data' => $data));
    }

    public function measurementsAction()
    {
        $beacon = 2;
        $from = date('c', strtotime('-6 hours'));
        $measurements = $this->beacons->getMeasurements($beacon, $from);

        $positions = array();
        foreach ($measurements as $row) {
            $x = $row['position_x'];
            $y = $row['position_y'];
            if (!isset($positions[$x])) {
                $positions[$x] = array();
            }
            if (!isset($positions[$x][$y])) {
                $positions[$x][$y] = array();
            }
            $positions[$x][$y][] = $row['rssi'];
        }

        $data = array();
        foreach ($positions as $x => $position) {
            foreach ($position as $y => $rssi) {

                //average
                $avg = array_sum($rssi) / count($rssi);

                // median
                $median = $rssi[floor(count($rssi)/2)];

                // mode
                $values = array_count_values($rssi); 
                arsort($values);
                foreach ($values as $k => $v) {
                    $mode = $k;
                    break;
                } 

                $data[] = array(
                    'x' => $x,
                    'y' => $y,
                    'rssi' => $median,
                );
            }
        }

        return new JsonResponse(array('data' => $data));
    }
}
