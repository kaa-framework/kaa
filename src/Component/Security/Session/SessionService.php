<?php

namespace Kaa\Component\Security\Session;

use Kaa\Component\HttpMessage\Cookie;
use Kaa\Component\HttpMessage\Exception\BadRequestException;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\Security\Exception\SessionException;
use Kaa\Component\Security\UserInterface;
use Kaa\Component\Security\UserProviderInterface;
use Random\RandomException;

class SessionService
{
    private string $sessionsDirectory;
    private int $lifetimeSeconds;
    private string $cookieName;
    private UserProviderInterface $userProvider;

    public function __construct(
        int $lifetimeSeconds,
        string $cookieName,
        UserProviderInterface $userProvider,
        string $sessionsDirectory = '/tmp/kaa_session'
    ) {
        $this->sessionsDirectory = $sessionsDirectory;
        $this->lifetimeSeconds = $lifetimeSeconds;
        $this->cookieName = $cookieName;
        $this->userProvider = $userProvider;
    }

    /**
     * @throws RandomException|SessionException
     */
    public function writeSession(UserInterface $user): Cookie
    {
        $sessionId = hash('sha256', json_encode($user->getRoles()) . microtime() . mt_rand());
        $fileName = $this->sessionsDirectory . '/' . $sessionId;

        while (file_exists($fileName)) {
            $sessionId = hash('sha256', json_encode($user->getRoles()) . microtime() . mt_rand());
            $fileName = $this->sessionsDirectory . '/' . $sessionId;
        }

        if (
            !is_dir($this->sessionsDirectory)
            && !mkdir($this->sessionsDirectory, 077, true)
            && !is_dir($this->sessionsDirectory)
        ) {
            throw new SessionException("Directory {$this->sessionsDirectory} was not created");
        }

        file_put_contents($fileName, $user->getIdentifier() . '###' . (time() + $this->lifetimeSeconds));

        return Cookie::create(
            $this->cookieName,
            $sessionId,
            time() + $this->lifetimeSeconds,
        );
    }

    /**
     * @throws BadRequestException
     */
    public function getUserFromRequest(Request $request): ?UserInterface
    {
        $cookie = $request->cookie->get($this->cookieName);
        if ($cookie === null) {
            return null;
        }

        $fileName = $this->sessionsDirectory . '/' . $cookie;
        if (!file_exists($fileName)) {
            return null;
        }

        $content = file_get_contents($fileName);
        if ($content === false) {
            return null;
        }

        $parts = explode('###', $content);
        if (time() > (int) $parts[1]) {
            unlink($fileName);

            return null;
        }

        return $this->userProvider->getUser($parts[0]);
    }
}
