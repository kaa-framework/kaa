<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\Choice;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class ChoiceGenerator extends AbstractGenerator
{
    /**
     * @param Choice $assert
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        return $twig->render(
            'choice.php.twig', [
                'getMethod' => $this->getAccessMethod(
                    $reflectionProperty
                ),
                'choices' => var_export($assert->choices, true),
                'strict' => $assert->strict === true ? 'true' : 'false',
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $assert->message,
            ]
        );
    }
}