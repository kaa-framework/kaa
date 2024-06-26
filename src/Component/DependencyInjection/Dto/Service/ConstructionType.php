<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto\Service;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
enum ConstructionType: string
{
    case Constructor = 'constructor';

    case Factory = 'factory';
}
