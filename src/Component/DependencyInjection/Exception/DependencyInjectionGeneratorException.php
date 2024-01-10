<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Exception;

use Exception;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class DependencyInjectionGeneratorException extends Exception
{
}
