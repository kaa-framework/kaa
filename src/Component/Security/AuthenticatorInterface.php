<?php

namespace Kaa\Component\Security;

use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Throwable;

interface AuthenticatorInterface
{
    /**
     * Поддерживает ли аутентификатор обработку этого запроса
     */
    public function supports(Request $request): bool;

    /**
     * Проводит аутентификацию и возвращает функцию, которую нужно вызвать, чтобы получить пользователя
     * @return callable(): (UserInterface|null);
     */
    public function authenticate(Request $request): callable;

    /**
     * Будет вызвана, если authenticate отработала без ошибок.
     * Может вернуть Response
     */
    public function onAuthenticationSuccess(Request $request, callable $getUser): ?Response;

    /**
     * Будет вызвана, если authenticate выбросила исключение.
     * Может вернуть Response
     */
    public function onAuthenticationFailure(Request $request, Throwable $throwable): ?Response;
}
