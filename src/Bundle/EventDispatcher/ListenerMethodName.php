<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher;

use Kaa\Component\GeneratorContract\PhpOnly;

#[PhpOnly]
class ListenerMethodName
{
    /**
     * @param mixed[] $listener
     */
    public static function get(array $listener): string
    {
        return str_replace(['\\', '.'], '_', $listener['service']) . '__' . $listener['method'];
    }
}
