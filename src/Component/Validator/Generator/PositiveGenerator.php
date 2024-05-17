<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\GreaterThan;
use Kaa\Component\Validator\Assert\Positive;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class PositiveGenerator extends AbstractGenerator
{
    /**
     * @param Positive $assert
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
        bool $useArrayAccess = false,
    ): string {
        return (new GreaterThanGenerator())->generateAssert(
            new GreaterThan(
                value: 0,
                message: $assert->message,
            ),
            $reflectionProperty,
            $className,
            $twig,
            $useArrayAccess,
        );
    }
}
