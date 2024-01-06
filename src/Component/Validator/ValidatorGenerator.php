<?php

declare(strict_types=1);

namespace Kaa\Component\Validator;

use Exception;
use Kaa\Component\GeneratorContract\GeneratorInterface;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\Validator\ValidatorLocator\AttributesParser;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;

#[PhpOnly]
readonly class ValidatorGenerator implements GeneratorInterface
{
    /**
     * @param mixed[] $config
     * @throws Exception
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        $reflectionClasses = (new ModelLocator($config))->locate();
        $attributes = (new AttributesParser($reflectionClasses))->parseAttributes();
        (new ValidatorWriter($sharedConfig, $attributes))->write();
    }
}
