<?php

namespace Homebase\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

use Homebase\Service\Config;

class DashboardController
{
    protected $twig;

    protected $beacons;

    protected $config;

    protected $lights;

    protected $url;

    public function __construct($twig, $beacons, $config, $lights, $url)
    {
        $this->twig = $twig;
        $this->beacons = $beacons;
        $this->config = $config;
        $this->lights = $lights;
        $this->url = $url;
    }

    public function indexAction()
    {
        $from = new \DateTime('-24 hour');
        $to = new \DateTime('now');

        $states = $this->beacons->getStates();
        $actions = $this->lights->getActions();

        $events = array_merge($states, $actions);
        uasort($events, function ($a, $b) {

            if ($a['type'] == 'action') {
                $secondsA = strtotime($a['scheduled']);
            } else { // state
                $secondsA = strtotime($a['occurred']);
                $secondsA += $a['occurred_micro'] / 1000000;
            }

            if ($b['type'] == 'action') {
                $secondsB = strtotime($b['scheduled']);
            } else { // state
                $secondsB = strtotime($b['occurred']);
                $secondsB += $b['occurred_micro'] / 1000000;
            }

            return $secondsB - $secondsA;
        });

        $mode = $this->config->get(Config::ENGINE_MODE);

        return $this->twig->render('dashboard.twig', array(
            'states' => $states,
            'actions' => $actions,
            'events' => $events,
            'mode' => $mode,
        ));
    }

    public function modeAction(Request $request)
    {
        $mode = $request->get('mode');

        $this->config->set(Config::ENGINE_MODE, $mode);

        return new RedirectResponse($this->url->generate('dashboard'));
    }

    public function eventsAction()
    {
        $from = new \DateTime('-19 minutes 59 seconds');
        $to = new \DateTime('now');
        $limit = 5000;

        $data = array();

        $proximities = $this->beacons->getProximities($limit);
        foreach ($proximities as $proximity) {
            $data[] = array('recorded' => $proximity['recorded'], 'type' => 'proximity', 'value' => $proximity['proximity']);
        }

        $states = $this->beacons->getStates($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $limit);
        foreach ($states as $state) {
            $data[] = array('recorded' => $state['recorded'], 'type' => 'state', 'value' => $state['state']);
        }

        return new JsonResponse(array('data' => $data));
    }

    public function proximitiesAction()
    {
        $from = new \DateTime('-59 minutes');
        $to = new \DateTime('now');

        $beacons = $this->beacons->getProximities($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'));

        $minutes = array();
        foreach(range(0, 59) as $minute) {
            $minutes[$minute] = 1;
        }

        $grouped = array();
        foreach ($beacons as $beacon) {

            $beaconId = $beacon['beacon'];
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
        $from = new \DateTime('-21 days');
        $to = new \DateTime('today');

        $logs = $this->lights->getSummedLogs($from->format('Y-m-d 00:00:00'), $to->format('Y-m-d 23:59:59'));

        $period = new \DatePeriod($from, new \DateInterval('P1D'), $to);
        $days = array();
        foreach($period as $date) {
            $days[$date->format('Y-m-d')] = array();
        }

        $hours = array();
        foreach ($logs as $log) {

            $light = $log['light'];
            $date = $log['date'];

            if (!isset($hours[$light])) {
                $hours[$light] = $days;
            }

            $hours[$light][$date] = (int) $log['hours'];
        }

        $data = array();
        foreach ($hours as $light => $child) {
            foreach ($child as $date => $hours) {
                $data[] = array('light' => (int) $light, 'date' => $date, 'hours' => $hours);
            }
        }

        return new JsonResponse(array('data' => $data));
    }
}
