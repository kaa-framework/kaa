<?php

namespace Kaa\Util\Exception;

use Exception;
use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class BadParameterTypeException extends Exception
{
}
