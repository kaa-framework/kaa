<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Exception;

use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class InvalidServiceDefinitionException extends DependencyInjectionGeneratorException
{
}
