<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\RoutesLocator;

use Exception;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Router\Dto\RoutesCollection;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Component\Router\RoutesLocator\AttributesToConfigParser;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

#[PhpOnly]
class AttributesToConfigParserTest extends TestCase
{
    private const CONFIG = [
        'scan' => [
            '\\Kaa\\Component\\Router\\Test\\Handlers\\Scan\\',
        ],
    ];

    /**
     * @throws ReflectionException|RouterGeneratorException|Exception
     */
    public function testIgnoring(): void
    {
        $config = (new AttributesToConfigParser(self::CONFIG))->getConfig();
        $routeCollection = new RoutesCollection($config);
        assertTrue($routeCollection->has('/test/healthcheck'));
        assertFalse($routeCollection->has('/test/ignore'));
        assertTrue($routeCollection->has('/test/posthealthcheck', 'POST'));
    }

    /**
     * @throws ReflectionException
     */
    public function testRouteException(): void
    {
        $config = self::CONFIG;
        $config['scan'][] = '\\Kaa\\Component\\Router\\Test\\Handlers\\Ignore\\';
        $this->expectException(RouterGeneratorException::class);
        (new AttributesToConfigParser($config))->getConfig();
    }
}
