<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\IsTrue;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class IsTrueGenerator extends BaseGenerator
{
    /**
     * @param IsTrue $assert
     * @param Environment $twig
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        return $twig->render(
            'IsTrue.php.twig', [
                'getMethod' => $this->getAccessMethod(
                    $reflectionProperty,
                ),
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $assert->message,
            ]
        );
    }
}