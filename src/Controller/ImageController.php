<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VysokeSkoly\ImageApi\Facade\StorageFacade;

class ImageController extends AbstractController
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
