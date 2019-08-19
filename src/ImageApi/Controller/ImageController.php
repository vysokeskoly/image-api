<?php

namespace VysokeSkoly\ImageApi\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VysokeSkoly\ImageApi\Facade\StorageFacade;

class ImageController extends Controller
{
    /**
     * @Route("/image/", methods={"POST"})
     */
    public function postImageAction(StorageFacade $storage, Request $request): JsonResponse
    {
        $storage->saveFiles($request->files);

        $status = $storage->getStatus();

        return $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/image/{fileName}", methods={"GET"})
     */
    public function getImageAction(StorageFacade $storage, Request $request, string $fileName): Response
    {
        $image = $storage->getImage($fileName);

        $status = $storage->getStatus();

        return $status->isSuccess()
            ? new Response($image)
            : $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/image/{fileName}", methods={"DELETE"})
     */
    public function deleteImageAction(StorageFacade $storage, Request $request, string $fileName): JsonResponse
    {
        $storage->delete($fileName);

        $status = $storage->getStatus();

        return $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/list/", methods={"GET"})
     */
    public function getListAction(StorageFacade $storage): JsonResponse
    {
        return $this->json($storage->listAll());
    }
}
