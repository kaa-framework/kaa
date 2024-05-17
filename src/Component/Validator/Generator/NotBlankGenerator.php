<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\Reflection;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\NotBlank;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class NotBlankGenerator extends AbstractGenerator
{
    /**
     * @param NotBlank $assert
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException|BadTypeException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
        bool $useArrayAccess = false,
    ): string {
        $propertyType = Reflection::namedType($reflectionProperty->getType())->getName();
        $twigTemplate = 'not_blank/not_blank_' . $propertyType . '.php.twig';

        return $twig->render(
            $twigTemplate, [
                'allowNull' => $assert->allowNull,
                'getProperty' => $this->getPropertyCode($reflectionProperty, $useArrayAccess),
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $assert->message,
                'useArrayAccess' => $useArrayAccess,
            ]
        );
    }
}
