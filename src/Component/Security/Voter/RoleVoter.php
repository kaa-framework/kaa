<?php

namespace Kaa\Component\Security\Voter;

use Kaa\Component\Security\UserInterface;
use Kaa\Component\Security\VoterInterface;

class RoleVoter implements VoterInterface
{
    public function vote(array $subject, ?UserInterface $user): bool
    {
        return $user !== null
            && in_array($subject[0], $user->getRoles(), true);
    }
}
