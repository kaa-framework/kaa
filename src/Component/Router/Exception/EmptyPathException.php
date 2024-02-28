<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Exception;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class EmptyPathException extends RouterException
{
}
