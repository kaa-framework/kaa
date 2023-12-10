<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\RoutesLocator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Router\Exception\ValidationException;
use Kaa\Component\Router\RoutesLocator\ConfigValidator;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

#[PhpOnly]
class ConfigValidatorTest extends TestCase
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

    /**
     * @throws ValidationException
     */
    public function testNoChanges()
    {
        $old = self::CONFIG;
        ConfigValidator::validate(self::CONFIG);
        assertEquals($old, $old);
    }

    /**
     * @throws ValidationException
     */
    public function testEmpty()
    {
        $config = [];
        ConfigValidator::validate($config);
        assertEquals([], $config);
    }

    public function testThrow()
    {
        $config = self::CONFIG;
        $config['routes'][] = [
            'route' => '/external-api',
            'method' => 'GET',
            'service' => 'TestHandler',
            'classMethod' => 'callExternalApi',
        ];
        $this->expectException(ValidationException::class);
        ConfigValidator::validate($config);
    }

    /**
     * @throws ValidationException
     */
    public function testNormal()
    {
        $config = self::CONFIG;
        $config['routes'][] = [
            'route' => '/external-api',
            'method' => 'POST',
            'service' => 'TestHandler',
            'classMethod' => 'callExternalApi',
        ];
        ConfigValidator::validate($config);
        assertTrue(true);
    }
}
