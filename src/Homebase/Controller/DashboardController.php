<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


class DashboardController
{
    protected $twig;

    protected $log;

    public function __construct($twig, $log) {
        $this->twig = $twig;
        $this->log = $log;
    }

    public function indexAction()
    {
        return $this->twig->render('dashboard.twig');
    }

    public function weekAction()
    {
        $from = new \DateTime('-30 days');
        $to = new \DateTime('today');

        $logs = $this->log->getLogs($from->format('Y-m-d 00:00:00'), $to->format('Y-m-d H:i:s'));

        $to->modify( '+1 day' ); // include today
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

                $hours[$light][$date][$hour] = $log['on'];
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
