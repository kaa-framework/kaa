<?php

namespace Kaa\Component\Security;

interface UserInterface
{
    public function getIdentifier(): string;

    /**
     * @return string[]
     */
    public function getRoles(): array;
}
