<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class NamespaceService
{
    private RequestStack $requestStack;
    private string $defaultNamespace;
    private ?string $namespace;

    public function __construct(RequestStack $requestStack, string $defaultNamespace)
    {
        $this->defaultNamespace = $defaultNamespace;
        $this->requestStack = $requestStack;
    }

    public function useNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        if (!empty($this->namespace)) {
            return $this->namespace;
        }

        $request = $this->requestStack->getCurrentRequest() ?? $this->requestStack->getMasterRequest();

        $namespace = $request !== null
            ? $request->query->get('namespace', $this->defaultNamespace)
            : $this->defaultNamespace;

        return rtrim((string) $namespace, ' /');
    }
}
