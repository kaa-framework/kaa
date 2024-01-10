<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\Blank;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class BlankGenerator extends BaseGenerator
{
    /**
     * @param Blank $assert
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
            'Blank.php.twig', [
                'allowNull' => $assert->allowNull,
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
