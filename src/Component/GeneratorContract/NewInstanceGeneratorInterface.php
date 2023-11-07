<?php

declare(strict_types=1);

namespace Kaa\Component\GeneratorContract;

#[PhpOnly]
interface NewInstanceGeneratorInterface
{
    /**
     * Возвращает код, который создаёт новый экземпляр переданного класса
     *
     * @param string $nameOrAlias имя сервиса или алиас. При использовании реализации по умолчанию игнорируется
     * @param string $class Возвращаемый код будет создавать объект этого класса
     * @return string Код, вызов которого создаст экземпляр переданного класса
     */
    public function generate(string $nameOrAlias, string $class): string;
}
