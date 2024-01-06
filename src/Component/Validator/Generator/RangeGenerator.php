<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\Range;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class RangeGenerator extends BaseGenerator
{
    /**
     * @param Range $assert
     * @param Environment $twig
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        /** @var string $message */
        $message = preg_replace('/{{ min }}/', (string) $assert->min, $assert->message);
        $message = preg_replace('/{{ max }}/', (string) $assert->max, $message);

        return $twig->render(
            'Range.php.twig', [
                'getMethod' => $this->getAccessMethod(
                    $reflectionProperty,
                ),
                'min' => $assert->min,
                'max' => $assert->max,
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $message,
            ]
        );
    }
}
