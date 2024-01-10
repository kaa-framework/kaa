<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Exception;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class InvalidServiceDefinitionException extends DependencyInjectionGeneratorException
{
}
