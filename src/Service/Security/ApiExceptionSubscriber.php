<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function __construct(private bool $isDebug)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->isDebug) {
            return;
        }

        $e = FlattenException::createFromThrowable($event->getThrowable());

        $response = new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }
}
