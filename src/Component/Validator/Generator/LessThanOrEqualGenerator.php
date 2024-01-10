<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\LessThanOrEqual;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class LessThanOrEqualGenerator extends BaseGenerator
{
    /**
     * @param LessThanOrEqual $assert
     * @param Environment $twig
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        $message = preg_replace('/{{ compared_value }}/', (string) $assert->value, $assert->message);

        return $twig->render(
            'LessThanOrEqual.php.twig', [
                'getMethod' => $this->getAccessMethod(
                    $reflectionProperty,
                ),
                'compared_value' => $assert->value,
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $message,
            ]
        );
    }
}
