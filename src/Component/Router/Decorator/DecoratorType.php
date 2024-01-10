<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
enum DecoratorType
{
    case Pre;
    case Post;
}
