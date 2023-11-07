<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Exception;

use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class ServiceDoesNotExistException extends DependencyInjectionGeneratorException
{
    public function __construct(string $serviceName)
    {
        parent::__construct("Service '{$serviceName}' does not exist");
    }
}
