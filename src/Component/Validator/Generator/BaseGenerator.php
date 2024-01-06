<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;

abstract class BaseGenerator implements AssertGeneratorInterface
{
    /**
     * @param Environment $twig
     */
    abstract public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string;

    /**
     * @throws ValidatorGeneratorException
     */
    protected function getAccessMethod(ReflectionProperty $reflectionProperty): string
    {
        if ($reflectionProperty->isPublic()) {
            return $reflectionProperty->name;
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (strcasecmp($reflectionMethod->name, 'get' . $reflectionProperty->name) === 0) {
                return $reflectionMethod->name . '()';
            } elseif (strcasecmp($reflectionMethod->name, 'is' . $reflectionProperty->name) === 0) {
                return $reflectionMethod->name . '()';
            }
        }

        throw new ValidatorGeneratorException(
            sprintf(
                'Property %s::%s is private and has no public getters',
                $reflectionClass->name,
                $reflectionProperty->name
            )
        );
    }
}
