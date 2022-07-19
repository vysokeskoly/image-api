<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class NamespaceService
{
    private ?string $namespace;

    public function __construct(private RequestStack $requestStack, private string $defaultNamespace)
    {
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

        $request = $this->requestStack->getCurrentRequest() ?? $this->requestStack->getMainRequest();

        $namespace = $request?->query->get('namespace', $this->defaultNamespace) ?? $this->defaultNamespace;

        return rtrim((string) $namespace, ' /');
    }
}
