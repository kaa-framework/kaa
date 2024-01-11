<?php

namespace Kaa\Component\Security;

use Exception;
use Kaa\Component\Generator\GeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Security\VoterLocator\VoterLocator;
use Kaa\Component\Security\Writer\SecurityWriter;

#[PhpOnly]
class SecurityGenerator implements GeneratorInterface
{
    /**
     * @param mixed[] $config
     * @throws Exception
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        $voters = (new VoterLocator($config))->find();
        (new SecurityWriter($sharedConfig, $voters, $config['firewalls'], $config['access_control']))->write();
    }
}
