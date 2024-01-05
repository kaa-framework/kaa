<?php

declare(strict_types=1);

namespace Kaa\Component\HttpKernel\Event;

use Kaa\Component\EventDispatcher\AbstractEvent;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;

class ResponseEvent extends AbstractEvent
{
    private Request $request;

    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
