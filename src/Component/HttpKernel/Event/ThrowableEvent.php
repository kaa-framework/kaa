<?php

declare(strict_types=1);

namespace Kaa\Component\HttpKernel\Event;

use Kaa\Component\EventDispatcher\AbstractEvent;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Throwable;

class ThrowableEvent extends AbstractEvent
{
    private Throwable $throwable;

    private Request $request;

    private ?Response $response = null;

    public function __construct(Throwable $throwable, Request $request)
    {
        $this->throwable = $throwable;
        $this->request = $request;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}
