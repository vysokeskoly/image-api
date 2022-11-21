<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(): JsonResponse
    {
        return $this->json([
            'app' => 'VysokeSkoly/ImageApi',
        ]);
    }

    /**
     * @Route("/auth", name="api.auth")
     */
    public function authAction(): JsonResponse
    {
        return $this->json([
            'auth' => 'OK',
            'host' => \Safe\gethostname(),
        ]);
    }
}
