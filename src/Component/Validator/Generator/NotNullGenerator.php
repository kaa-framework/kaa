<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\NotNull;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class NotNullGenerator extends AbstractGenerator
{
    /**
     * @param NotNull $assert
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
        bool $useArrayAccess = false,
    ): string {
        return $twig->render(
            'not_null.php.twig', [
                'getProperty' => $this->getPropertyCode($reflectionProperty, $useArrayAccess),
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $assert->message,
                'useArrayAccess' => $useArrayAccess,
            ]
        );
    }
}
