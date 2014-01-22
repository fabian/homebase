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

    public function dayAction()
    {
        $logs = $this->log->getLogs(date('Y-m-d H:00', strtotime('-24 hours')));

        $hours = array();
        foreach ($logs as $log) {
            $hours[date('H:00', strtotime($log['created']))][$log['light']] = $log['on'];
        }

        // wrap up
        $data = array('children' => array());
        foreach ($hours as $hour => $lights) {
            $element = array();
            foreach ($lights as $light => $on) {
                $element['name'] = $light;
                $element['hour'] = $hour;
                $element['size'] = 1;
                $element['on'] = $on;
                $element = array('children' => array($element));
            }
            $data['children'][] = $element;
        }

        return new JsonResponse($data);
    }
}
