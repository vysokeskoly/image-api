<?php

namespace VysokeSkoly\ImageApi\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->json([
            'app' => 'VysokeSkoly/ImageApi',
        ]);
    }

    /**
     * @Route("/auth", name="api.auth")
     */
    public function authAction()
    {
        return $this->json([
            'auth' => 'OK',
        ]);
    }
}
