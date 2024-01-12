<?php

namespace Kaa\Component\Security\Session;

use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\Security\AuthenticatorInterface;
use Kaa\Component\Security\UserInterface;
use Throwable;

class SessionAuthenticator implements AuthenticatorInterface
{
    private SessionService $sessionService;

    public function __construct(
        SessionService $sessionService
    ) {
        $this->sessionService = $sessionService;
    }

    public function supports(Request $request): bool
    {
        return true;
    }

    /**
     * @return callable(): (UserInterface|null);
     */
    public function authenticate(Request $request): callable
    {
        return fn () => $this->sessionService->getUserFromRequest($request);
    }

    public function onAuthenticationSuccess(Request $request, callable $getUser): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, Throwable $throwable): ?Response
    {
        return null;
    }
}
