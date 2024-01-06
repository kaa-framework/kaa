<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Exception;

use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class InvalidArgumentException extends ValidatorGeneratorException
{
}
