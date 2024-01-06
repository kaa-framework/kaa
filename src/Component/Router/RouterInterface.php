<?php

declare(strict_types=1);

namespace Kaa\Component\Router;

use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;

interface RouterInterface
{
    /**
     * @return (callable(Request): Response)|null
     */
    public function findAction(Request $request): ?callable;
}
