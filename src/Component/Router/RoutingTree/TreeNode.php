<?php

declare(strict_types=1);

namespace Kaa\Component\Router\RoutingTree;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Router\Dto\RouteDto;

#[PhpOnly]
class TreeNode
{
    /** @var TreeNode[] */
    private array $next;

    /**
     * @param string[] $keys
     */
    public function __construct(
        private readonly string $data,
        private ?string $handler = null,
        private array $keys = [],
        private ?RouteDto $route = null,
        private bool $isVariable = false,
    ) {
        $this->next = [];
    }

    public function setHandler(string $handler): void
    {
        $this->handler = $handler;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    /**
     * @return ?string[]
     */
    public function getKeys(): ?array
    {
        return $this->keys;
    }

    /**
     * @param string[] $keys
     */
    public function setKeys(array $keys): void
    {
        $this->keys = $keys;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return TreeNode[]
     */
    public function getNext(): array
    {
        return $this->next;
    }

    public function addNext(self $nextNode): void
    {
        if (str_contains($nextNode->data, '{')) {
            $this->next[] = $nextNode;
        } else {
            array_unshift($this->next, $nextNode);
        }
    }

    public function setRoute(RouteDto $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): ?RouteDto
    {
        return $this->route;
    }

    public function isVariable(): bool
    {
        return $this->isVariable;
    }

    public function setVariable(bool $isVar): void
    {
        $this->isVariable = $isVar;
    }
}
