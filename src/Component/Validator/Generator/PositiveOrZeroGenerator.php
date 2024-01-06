<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\GreaterThanOrEqual;
use Kaa\Component\Validator\Assert\PositiveOrZero;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class PositiveOrZeroGenerator extends BaseGenerator
{
    /**
     * @param PositiveOrZero $assert
     * @param Environment $twig
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        return (new GreaterThanOrEqualGenerator())->generateAssert(
            new GreaterThanOrEqual(
                value: 0,
                message: $assert->message,
            ),
            $reflectionProperty,
            $className,
            $twig,
        );
    }
}
