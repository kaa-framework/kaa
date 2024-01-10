<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Router\RouteLocator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Router\Exception\ValidationException;

#[PhpOnly]
class ConfigValidator
{
    /**
     * @param mixed[] $config
     * @throws ValidationException
     */
    public static function validate(array $config): void
    {
        $troubleRoutes = [];
        foreach ($config['routes'] as $route) {
            $index = $route['method'] . $route['route'];
            if (array_key_exists($index, $troubleRoutes)) {
                $troubleRoutes[$index]++;
            } else {
                $troubleRoutes[$index] = 1;
            }
        }

        $troubleRoutes = array_filter($troubleRoutes, static fn ($v) => $v > 1);
        $listTroubles = implode(' ', array_keys($troubleRoutes));
        if ($troubleRoutes !== []) {
            throw new ValidationException("Duplicate routes: {$listTroubles}");
        }
    }
}
