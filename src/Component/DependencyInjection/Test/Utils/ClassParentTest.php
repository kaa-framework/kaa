<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Utils;

use Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored\IgnoredService;
use Kaa\Component\DependencyInjection\Util\ClassParents;
use ReflectionClass;

test('test', function () {
    $classes = ClassParents::getClassParents(new ReflectionClass(IgnoredService::class));
    expect(in_array(IgnoredService::class, $classes))->toBe(true);
});
