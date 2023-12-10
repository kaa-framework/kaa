<?php

declare(strict_types=1);

namespace Kaa\Component\Router;

use Kaa\Component\HttpMessage\Request;

interface RouterInterface
{
    public function findAction(Request $request): callable;
}
