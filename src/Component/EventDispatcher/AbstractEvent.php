<?php

declare(strict_types=1);

namespace Kaa\Component\EventDispatcher;

class AbstractEvent implements EventInterface
{
    private bool $isPropagationStopped = false;

    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
