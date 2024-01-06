<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\LessThanOrEqual;
use Kaa\Component\Validator\Assert\NegativeOrZero;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class NegativeOrZeroGenerator extends BaseGenerator
{
    /**
     * @param NegativeOrZero $assert
     * @param Environment $twig
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        return (new LessThanOrEqualGenerator())->generateAssert(
            new LessThanOrEqual(
                value: 0,
                message: $assert->message,
            ),
            $reflectionProperty,
            $className,
            $twig,
        );
    }
}