<?php

namespace VysokeSkoly\ImageApi\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use VysokeSkoly\ImageApi\Facade\StorageFacade;

class ImageController extends Controller
{
    /**
     * @Route("/image")
     * @Method("POST")
     */
    public function postImageAction(Request $request): JsonResponse
    {
        $storage = $this->get(StorageFacade::class);
        $storage->saveFiles($request->files);

        $status = $storage->getStatus();

        return $this->json($status, $status->isSuccess() ? 200 : 500);
    }

    /**
     * @Route("/image")
     * @Method("GET")
     */
    public function getImageAction(Request $request): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
        ]);
    }

    /**
     * @Route("/image")
     * @Method("DELETE")
     */
    public function deleteImageAction(Request $request): JsonResponse
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }
}
