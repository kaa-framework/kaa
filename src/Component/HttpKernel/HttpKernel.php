<?php

declare(strict_types=1);

namespace Kaa\HttpKernel;

use Kaa\EventDispatcher\EventDispatcherInterface;
use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;
use Kaa\HttpKernel\Event\FindActionEvent;
use Kaa\HttpKernel\Event\RequestEvent;
use Kaa\HttpKernel\Event\ResponseEvent;
use Kaa\HttpKernel\Event\ThrowableEvent;
use Kaa\HttpKernel\Exception\ActionNotFoundException;
use Kaa\HttpKernel\Exception\ResponseNotReachedException;
use Throwable;

class HttpKernel
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws ResponseNotReachedException
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->handleRequest($request);
        } catch (Throwable $throwable) {
            $event = new ThrowableEvent($throwable, $request);
            $this->eventDispatcher->dispatch($event, HttpKernelEvents::THROWABLE);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }
        }

        throw new ResponseNotReachedException();
    }

    /**
     * @throws ActionNotFoundException
     */
    private function handleRequest(Request $request): Response
    {
        $requestEvent = new RequestEvent($request);
        $this->eventDispatcher->dispatch($requestEvent, HttpKernelEvents::REQUEST);

        if ($requestEvent->hasResponse()) {
            return $requestEvent->getResponse();
        }

        $findActionEvent = new FindActionEvent($request);
        $this->eventDispatcher->dispatch($findActionEvent, HttpKernelEvents::FIND_ACTION);

        if (!$findActionEvent->hasAction()) {
            throw new ActionNotFoundException(
                'Probably no listener was attached to the ' . HttpKernelEvents::FIND_ACTION
                . ' event or non of them was able to find an action'
            );
        }

        $response = $findActionEvent->getAction()($request);

        $responseEvent = new ResponseEvent($request, $response);
        $this->eventDispatcher->dispatch($responseEvent, HttpKernelEvents::RESPONSE);

        return $responseEvent->getResponse();
    }
}
