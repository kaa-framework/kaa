<?php

use Kaa\Bundle\Router\RouterBundle;
use Kaa\Component\DependencyInjection\InstanceProvider;

return [
    RouterBundle::class,
    'instanceGenerator' => InstanceProvider::class,
];
