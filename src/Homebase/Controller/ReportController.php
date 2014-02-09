<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


class ReportController
{
    protected $twig;

    protected $log;

    public function __construct($twig, $log) {
        $this->twig = $twig;
        $this->log = $log;
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

        $logs = $this->log->getLogs($from, $to);

        $hours = array();
        foreach ($logs as $log) {
            if ($log['on']) {
                $hours[date('H:00', strtotime($log['created']))][$log['light']] = $log['on'];
            }
        }

        // wrap up
        $data = array();
        for ($i = 0; $i < 24; $i++) {
            $hour = sprintf('%02d:00', $i);
            $element = array('hour' => $hour, 'lights' => 0);
            if (isset($hours[$hour])) {
                $element['lights'] = count($hours[$hour]);
            }
            $data[] = $element;
        }

        return new JsonResponse(array('data' => $data));
    }
}
