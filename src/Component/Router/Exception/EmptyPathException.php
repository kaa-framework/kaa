<?php


declare(strict_types=1);

namespace Kaa\Component\Router\Exception;

use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class EmptyPathException extends RouterException
{
}
