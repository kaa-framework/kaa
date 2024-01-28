<?php

namespace Kaa\Component\Security\Voter;

use Kaa\Component\Security\UserInterface;
use Kaa\Component\Security\VoterInterface;

class IsAuthenticatedFullyVoter implements VoterInterface
{
    /**
     * @param string[] $subject
     */
    public function vote(array $subject, ?UserInterface $user): bool
    {
        return $user !== null;
    }
}
