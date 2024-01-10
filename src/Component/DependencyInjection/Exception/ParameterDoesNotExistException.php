<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Exception;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class ParameterDoesNotExistException extends DependencyInjectionGeneratorException
{
    public function __construct(string $parameterName)
    {
        parent::__construct("Parameter '{$parameterName}' does not exist");
    }
}
