<?php

namespace VysokeSkoly\ImageApi\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use VysokeSkoly\ImageApi\Facade\StorageFacade;

class StorageController extends Controller
{
    /**
     * @Route("/save")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request): JsonResponse
    {
        $storage = $this->get(StorageFacade::class);
        $storage->saveFiles($request->files);

        $status = $storage->getStatus();

        return $this->json($status, $status->isSuccess() ? 200 : 500);
    }

    /**
     * @Route("/get")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
        ]);
    }
}
