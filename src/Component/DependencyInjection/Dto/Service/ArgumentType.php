<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto\Service;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
enum ArgumentType: string
{
    case Service = 'service';

    case Parameter = 'parameter';
}
