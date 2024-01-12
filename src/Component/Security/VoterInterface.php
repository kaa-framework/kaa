<?php

namespace Kaa\Component\Security;

interface VoterInterface
{
    /**
     * @param string[] $subject
     */
    public function vote(array $subject, ?UserInterface $user): bool;
}
