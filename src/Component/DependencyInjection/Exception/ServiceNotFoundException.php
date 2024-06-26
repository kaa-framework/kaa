<?php

namespace Kaa\Component\DependencyInjection\Exception;

use Exception;

class ServiceNotFoundException extends Exception
{
    public function __construct(string $name, string $class)
    {
        parent::__construct("Service with name = {$name} and class = {$class} was not found");
    }
}
