<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class ListenerMethodName
{
    /**
     * @param mixed[] $listener
     */
    public static function name(array $listener): string
    {
        return str_replace(['\\', '.'], '_', $listener['service']) . '__' . $listener['method'];
    }
}
