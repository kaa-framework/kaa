<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Dto;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Router\Dto\RoutesCollection;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

#[PhpOnly]
class RoutesCollectionTest extends TestCase
{
    private const CONFIG = [
        'scan' => [
            '\\Kaa\\Component\\Router\\Test\\Handlers\\Scan\\',
        ],
        'routes' => [
            [
                'route' => '/external-api',
                'method' => 'GET',
                'service' => 'TestHandler',
                'classMethod' => 'callExternalApi',
            ],
            [
                'route' => '/api/get',
                'method' => 'POST',
                'service' => ['class' => 'TestHandler',
                    'name' => 'app.service'],
                'classMethod' => 'callExternalApi',
            ],
        ],
    ];

    public function testHas()
    {
        $routesCollection = new RoutesCollection(self::CONFIG);
        assertTrue($routesCollection->has('/external-api'));
        assertFalse($routesCollection->has('/test'));
        assertTrue($routesCollection->has('/api/get'));
        assertTrue($routesCollection->has('/api/get', 'POST'));
        assertFalse($routesCollection->has('/external-api', 'POST'));
        assertTrue($routesCollection->has('/external-api', 'GET'));
    }

    public function testEmpty()
    {
        $routesCollection = new RoutesCollection(self::CONFIG);
        assertFalse($routesCollection->has(''));
    }
}
