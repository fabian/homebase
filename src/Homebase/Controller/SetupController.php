<?php

namespace Homebase\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


class SetupController
{
    protected $twig;

    protected $beacons;

    protected $remoteHue;

    protected $url;

    public function __construct($twig, $beacons, $remoteHue, $url)
    {
        $this->twig = $twig;
        $this->beacons = $beacons;
        $this->remoteHue = $remoteHue;
        $this->url = $url;
    }

    public function indexAction(Request $request)
    {
        $user = $request->getUser();
        $message = $request->query->get('message');

        $beacons = $this->beacons->getBeacons();
        $info = $this->remoteHue->getBridgeInfo();
        $mapping = $this->beacons->getUserMappings($user);

        $mappingGrouped = array();
        foreach ($mapping as $map) {
            if (!isset($mappingGrouped[$map['beacon']])) {
                $mappingGrouped[$map['beacon']] = array();
            }
            $mappingGrouped[$map['beacon']][$map['light']] = true;
        }

        $lights = $info['lights'];

        return $this->twig->render('setup.twig', array(
            'message' => $message,
            'beacons' => $beacons,
            'lights' => $lights,
            'mapping' => $mappingGrouped,
        ));
    }

    public function saveAction(Request $request)
    {
        $hide = $request->request->get('hide');
        $show = $request->request->get('show');

        if ($hide) {

            foreach ($hide as $beacon => $empty) {
                $this->beacons->saveBeaconActive($beacon, false);
            }

            return new RedirectResponse($this->url->generate('setup', array('message' => 'Beacon is now hidden.')));

        } else if ($show) {

            foreach ($show as $beacon => $empty) {
                $this->beacons->saveBeaconActive($beacon, true);
            }

            return new RedirectResponse($this->url->generate('setup', array('message' => 'Beacon is now shown again.')));

        } else {

            $mapping = $request->request->get('mapping');
            $user = $request->getUser();

            // clear old values
            $this->beacons->deleteMappings($user);

            // save new values
            foreach ($mapping as $beacon => $lights) {
                foreach ($lights as $light => $true) {
                    $this->beacons->saveMapping($beacon, $light, $user);
                }
            }

            return new RedirectResponse($this->url->generate('setup', array('message' => 'Setup was saved successfully.')));
        }
    }
}
