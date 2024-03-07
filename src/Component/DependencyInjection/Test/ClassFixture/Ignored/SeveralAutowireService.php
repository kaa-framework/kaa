<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored;

use Kaa\Component\DependencyInjection\Attribute\Autowire;

class SeveralAutowireService
{
    public function __construct(
        #[Autowire(service: 'app.test', parameter: 'app.param')] int $test
    ) {
    }
}
