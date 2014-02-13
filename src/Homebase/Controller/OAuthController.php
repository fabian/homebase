<?php

namespace Homebase\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Homebase\Service\RedirectUri;

class OAuthController
{
    const RESPONSE_TYPE_TOKEN = 'token'; // implicit grant

    protected $twig;

    protected $oauth;

    public function __construct($twig, $oauth)
    {
        $this->twig = $twig;
        $this->oauth = $oauth;
    }

    public function authorizeAction(Request $request)
    {
        $clientId = $request->query->get('client_id');
        $responseType = $request->query->get('response_type');
        $state = $request->query->get('state');

        $client = $this->getClient($clientId);

        return $this->twig->render('authorize.twig', array(
            'client_id' => $clientId,
            'client' => $client,
            'response_type' => $responseType,
            'state' => $state,
        ));
    }

    public function authorizePostAction(Request $request)
    {
        $allow = $request->request->has('allow');
        $clientId = $request->request->get('client_id');
        $responseType = $request->request->get('response_type');
        $state = $request->request->get('state');
        $user = $request->getUser();

        $client = $this->getClient($clientId);

        if (!$allow) {

            // user clicked deny, redirect back to client with error
            $uri = RedirectUri::create($client['redirect_uri'], array('error' => 'user_denied'));

            return new RedirectResponse($uri);
        }

        if ($responseType != self::RESPONSE_TYPE_TOKEN) {

            // unsupported response type, redirect back to client with error
            $uri = RedirectUri::create($client['redirect_uri'], array('error' => 'only response type token is supported'));

            return new RedirectResponse($uri);
        }

        // create access token
        $accessToken = $this->oauth->createAccessToken($client['id'], $user);

        // send back to client with access token
        $params = array(
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'state' => $state,
        );
        $uri = RedirectUri::create($client['redirect_uri'], $params, '#');

        return new RedirectResponse($uri);
    }

    protected function getClient($id)
    {
        $client = $this->oauth->getClient($id);
        if (!$client) {
            throw new NotFoundHttpException('Client not found');
        }

        return $client;
    }
}
