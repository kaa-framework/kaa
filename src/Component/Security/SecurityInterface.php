<?php

namespace Kaa\Component\Security;

use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;

interface SecurityInterface
{
    public function run(Request $request): ?Response;

    /**
     * Возвращает пользователя. Если до этого не был вызван метод run(), то выкинет исключение
     */
    public function getUser(): ?UserInterface;

    /**
     * Вызывает воутер, чей аттрибут совпадает с переданным и возвращает true, если доступ разрешён
     *
     * @param string[] $subject
     */
    public function isGranted(string $attribute, array $subject = []): bool;
}
