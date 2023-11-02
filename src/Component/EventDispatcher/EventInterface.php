<?php

declare(strict_types=1);

namespace Kaa\Component\EventDispatcher;

interface EventInterface
{
    public function stopPropagation(): void;

    public function isPropagationStopped(): bool;
}
