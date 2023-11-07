<?php

declare(strict_types=1);

namespace Kaa\Component\GeneratorContract;

#[PhpOnly]
final class DefaultNewInstanceGenerator implements NewInstanceGeneratorInterface
{
    /**
     * Возвращает код, который вызовет конструктор переданного класса без параметров
     */
    public function generate(string $nameOrAlias, string $class): string
    {
        if (!str_starts_with($class, '\\')) {
            $class = '\\' . $class;
        }

        return "new {$class}()";
    }
}
