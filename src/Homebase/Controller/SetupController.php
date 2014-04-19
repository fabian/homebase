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

    protected $lights;

    protected $url;

    public function __construct($twig, $beacons, $lights, $url)
    {
        $this->twig = $twig;
        $this->beacons = $beacons;
        $this->lights = $lights;
        $this->url = $url;
    }

    public function indexAction(Request $request)
    {
        $user = $request->getUser();
        $message = $request->query->get('message');

        $beacons = $this->beacons->getBeacons();
        $lights = $this->lights->getLights();
        $mapping = $this->beacons->getUserMappings($user);

        $mappingGrouped = array();
        foreach ($mapping as $map) {
            if (!isset($mappingGrouped[$map['beacon']])) {
                $mappingGrouped[$map['beacon']] = array();
            }
            $mappingGrouped[$map['beacon']][$map['light']] = true;
        }

        return $this->twig->render('setup.twig', array(
            'message' => $message,
            'beacons' => $beacons,
            'lights' => $lights,
            'mapping' => $mappingGrouped,
        ));
    }

    public function saveAction(Request $request)
    {
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

    public function addBeaconAction(Request $request)
    {
        return $this->twig->render('beacon.twig');
    }

    public function addBeaconPostAction(Request $request)
    {
        $name = $request->request->get('name');
        $uuid = $request->request->get('uuid');
        $major = $request->request->get('major');
        $minor = $request->request->get('minor');
        $active = $request->request->get('active');

        $duplicate = $this->beacons->isDuplicate($uuid, $major, $minor);

        if ($duplicate) {

            return $this->twig->render('beacon.twig', array(
                'name' => $name,
                'uuid' => $uuid,
                'major' => $major,
                'minor' => $minor,
                'active' => $active,
                'message' => 'There is already another Beacon with the same UUID, major and minor.',
            ));
    
        } else {
    
            $this->beacons->addBeacon($uuid, $major, $minor, $name, $active);
    
            return new RedirectResponse($this->url->generate('setup', array('message' => 'Beacon was saved successfully.')));
        }
    }

    public function editBeaconAction($id, Request $request)
    {
        $beacon = $this->beacons->getBeaconById($id);

        return $this->twig->render('beacon.twig', array(
            'beacon' => $beacon,
        ));
    }

    public function editBeaconPostAction($id, Request $request)
    {
        $name = $request->request->get('name');
        $uuid = $request->request->get('uuid');
        $major = $request->request->get('major');
        $minor = $request->request->get('minor');
        $active = $request->request->get('active');

        $duplicate = $this->beacons->isDuplicate($uuid, $major, $minor, $id);

        if ($duplicate) {

            $beacon = array(
                'id' => $id,
                'name' => $name,
                'uuid' => $uuid,
                'major' => $major,
                'minor' => $minor,
                'active' => $active,
            );

            return $this->twig->render('beacon.twig', array(
                'message' => 'There is already another Beacon with the same UUID, major and minor.',
                'beacon' => $beacon,
            ));

        } else {

            $this->beacons->saveBeacon($id, $name, $uuid, $major, $minor, $active);

            return new RedirectResponse($this->url->generate('setup', array('message' => 'Beacon was saved successfully.')));
        }
    }
}
