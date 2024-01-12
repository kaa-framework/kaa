<?php

namespace Kaa\Component\Security;

interface UserProviderInterface
{
    public function getUser(string $identifier): UserInterface;
}
