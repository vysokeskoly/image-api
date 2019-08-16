<?php

namespace VysokeSkoly\ImageApi\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    public function postImageAction(Request $request): JsonResponse
    {
        $storage = $this->get(StorageFacade::class);
        $storage->saveFiles($request->files);

        $status = $storage->getStatus();

        return $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/image/{fileName}", methods={"GET"})
     */
    public function getImageAction(Request $request, string $fileName): Response
    {
        $storage = $this->get(StorageFacade::class);
        $image = $storage->getImage($fileName);

        $status = $storage->getStatus();

        return $status->isSuccess()
            ? new Response($image)
            : $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/image/{fileName}", methods={"DELETE"})
     */
    public function deleteImageAction(Request $request, string $fileName): JsonResponse
    {
        $storage = $this->get(StorageFacade::class);
        $storage->delete($fileName);

        $status = $storage->getStatus();

        return $this->json($status->toArray(), $status->getStatusCode());
    }

    /**
     * @Route("/list/", methods={"GET"})
     */
    public function getListAction()
    {
        return $this->json($this->get(StorageFacade::class)->listAll());
    }
}
