<?php

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /** @var bool */
    private $isDebug;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function __construct(bool $isDebug)
    {
        $this->isDebug = $isDebug;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->isDebug) {
            return;
        }

        $e = FlattenException::create($event->getException());

        $response = new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }
}
