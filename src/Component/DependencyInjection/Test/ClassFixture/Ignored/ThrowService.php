<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored;

use Kaa\Component\DependencyInjection\Attribute\Service;

#[Service(aliases: 'app.throw')]
class ThrowService
{
}
