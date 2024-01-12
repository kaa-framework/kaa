<?php

namespace Kaa\Component\Security\VoterLocator;

use Exception;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\ClassFinder;
use Kaa\Component\Security\Attribute\Voter;
use Kaa\Component\Security\Voter\RoleVoter;
use ReflectionClass;

#[PhpOnly]
readonly class VoterLocator
{
    /**
     * @param mixed[] $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    /**
     * @return mixed[]
     * @throws Exception
     */
    public function find(): array
    {
        $voters = ClassFinder::find(
            scan: $this->config['scan'],
            predicate: static fn (ReflectionClass $c) => $c->isInstantiable()
                && $c->getAttributes(Voter::class) !== [],
        );

        $votersArray = $this->config['voters'];
        $votersArray['ROLE'] = ['service' => RoleVoter::class];

        foreach ($voters as $voterClass) {
            /** @var Voter $voter */
            $voter = $voterClass->getAttributes(Voter::class)[0]->newInstance();

            if (array_key_exists($voter->attribute, $votersArray)) {
                continue;
            }

            $votersArray[$voter->attribute] = ['service' => $voterClass->getName()];
        }

        return $votersArray;
    }
}
