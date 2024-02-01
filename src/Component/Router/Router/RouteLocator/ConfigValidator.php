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
            $possiblePrefixes = [];
            foreach (array_keys($config['prefixes']) as $key) {
                if (str_starts_with($route['service'], (string) $key)) {
                    $possiblePrefixes[$key] = $config['prefixes'][$key];
                }
            }
            $keys = array_map('strlen', array_keys($possiblePrefixes));
            array_multisort($keys, SORT_REGULAR, $possiblePrefixes);
            $index = $route['method'] . implode('', $possiblePrefixes) . $route['route'];
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
