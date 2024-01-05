<?php

declare(strict_types=1);

namespace Kaa\Component\HttpKernel\Event;

use Kaa\Component\EventDispatcher\AbstractEvent;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;

class FindActionEvent extends AbstractEvent
{
    private Request $request;

    /** @var (callable(Request): Response)|null */
    private $action = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function hasAction(): bool
    {
        return $this->action !== null;
    }

    /**
     * @return callable(Request): Response $action
     */
    public function getAction(): callable
    {
        return $this->action;
    }

    /**
     * @param callable(Request): Response $action
     */
    public function setAction(callable $action): void
    {
        $this->action = $action;
    }
}
