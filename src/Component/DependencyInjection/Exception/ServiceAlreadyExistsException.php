<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Exception;

use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class ServiceAlreadyExistsException extends DependencyInjectionGeneratorException
{
    public function __construct(string $serviceName)
    {
        parent::__construct("Cannot redefine already defined service '{$serviceName}'");
    }
}
