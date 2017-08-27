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
     * @Route("/image/")
     * @Method("POST")
     */
    public function postImageAction(Request $request): JsonResponse
    {
        $storage = $this->get(StorageFacade::class);
        $storage->saveFiles($request->files);

        $status = $storage->getStatus();

        return $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/image/{fileName}")
     * @Method("GET")
     */
    public function getImageAction(Request $request, string $fileName): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
        ]);
    }

    /**
     * @Route("/image/{fileName}")
     * @Method("DELETE")
     */
    public function deleteImageAction(Request $request, string $fileName): JsonResponse
    {
        $storage = $this->get(StorageFacade::class);
        $storage->delete($fileName);
        
        $status = $storage->getStatus();

        return $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/list/")
     * @Method("GET")
     */
    public function getListAction()
    {
        return $this->json($this->get(StorageFacade::class)->listAll(), 200);
    }
}
