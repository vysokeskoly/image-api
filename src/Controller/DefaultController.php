<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function indexAction(): JsonResponse
    {
        return $this->json([
            'app' => 'VysokeSkoly/ImageApi',
        ]);
    }

    #[Route(path: '/auth', name: 'api.auth')]
    public function authAction(): JsonResponse
    {
        return $this->json([
            'auth' => 'OK',
            'host' => \Safe\gethostname(),
        ]);
    }

    #[Route(path: '/status', name: 'api.status')]
    public function statusAction(): JsonResponse
    {
        $status = ['appStatus' => 'unknown'];

        $filename = __DIR__ . '/../../var/buildinfo.xml';
        if (file_exists($filename) && is_readable($filename)) {
            $content = \Safe\file_get_contents($filename);
            $content = str_replace('__HOSTNAME__', \Safe\gethostname(), $content);

            $xml = \Safe\simplexml_load_string($content, \SimpleXMLElement::class, LIBXML_NOCDATA);
            $json = \Safe\json_encode($xml);
            $status = \Safe\json_decode($json, true);
        }

        return $this->json($status);
    }
}
