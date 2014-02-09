<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


class SetupController
{
    protected $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function indexAction()
    {
        return $this->twig->render('setup.twig');
    }
}
