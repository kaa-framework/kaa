<?php

namespace Kaa\Component\Security;

use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Throwable;

abstract class AbstractSecurity implements SecurityInterface
{
    /** @var (callable(): (UserInterface|null))|null */
    private $getUserCallable = null;

    private ?UserInterface $user = null;

    /**
     * @throws Throwable
     */
    public function run(Request $request): ?Response
    {
        $response = null;

        foreach ($this->getAuthenticators($request->getPathInfo()) as $authenticator) {
            if (!$authenticator->supports($request)) {
                continue;
            }

            try {
                $this->getUserCallable = $authenticator->authenticate($request);

                $response = $authenticator->onAuthenticationSuccess($request, $this->getUserCallable);
            } catch (Throwable $e) {
                $response = $authenticator->onAuthenticationFailure($request, $e);

                if ($response === null) {
                    throw $e;
                }
            }

            break;
        }

        if ($response !== null) {
            return $response;
        }

        foreach ($this->getAccessControl() as $path => $roles) {
            if (preg_match('#' . $path . '#', $request->getPathInfo()) !== 1) {
                continue;
            }

            if ($roles === ['PUBLIC_ACCESS']) {
                return null;
            }

            if ($this->getUser() === null) {
                return new ForbiddenResponse();
            }

            if (array_intersect($roles, $this->getUser()->getRoles()) === []) {
                return new ForbiddenResponse();
            }

            break;
        }

        return null;
    }

    public function getUser(): ?UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($this->getUserCallable === null) {
            return null;
        }

        $getUser = $this->getUserCallable;

        return $getUser();
    }

    /**
     * @return AuthenticatorInterface[]
     */
    abstract protected function getAuthenticators(string $route): array;

    /**
     * @return array<string, string[]>
     */
    abstract protected function getAccessControl(): array;
}
