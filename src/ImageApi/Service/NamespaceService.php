<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class NamespaceService
{
    /** @var RequestStack */
    private $requestStack;
    /** @var string */
    private $defaultNamespace;

    public function __construct(RequestStack $requestStack, string $defaultNamespace)
    {
        $this->defaultNamespace = $defaultNamespace;
        $this->requestStack = $requestStack;
    }

    public function getNamespace(): string
    {
        $request = $this->requestStack->getCurrentRequest() ?? $this->requestStack->getMasterRequest();

        $namespace = $request !== null
            ? $request->query->get('namespace', $this->defaultNamespace)
            : $this->defaultNamespace;

        return rtrim($namespace, ' /');
    }
}
