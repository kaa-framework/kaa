<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use DateTime;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\DateRange;
use Kaa\Component\Validator\Exception\InvalidArgumentException;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class DateRangeGenerator extends AbstractGenerator
{
    /**
     * @param DateRange $assert
     * @throws InvalidArgumentException|LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        if ($assert->after === null && $assert->before === null) {
            throw new InvalidArgumentException(
                'At least one value from "before" or "after" must be specified.',
            );
        }

        $code = '';
        if ($assert->before === null && $assert->after !== null) {
            $message = preg_replace(
                '/{{ date }}/',
                $assert->after->format($assert->format),
                $assert->message_after
            );

            $code .= $twig->render(
                'date_range/after.php.twig', [
                    'getMethod' => $this->getAccessMethod(
                        $reflectionProperty,
                    ),
                    'after' => DateTime::createFromInterface($assert->after)->format($assert->format),
                    'format' => $assert->format,
                    'class' => $className,
                    'property' => $reflectionProperty->name,
                    'message' => $message,
                ]
            );

            $code .= "\n\n";
        }

        if ($assert->after === null && $assert->before !== null) {
            $message = preg_replace(
                '/{{ date }}/',
                $assert->before->format($assert->format),
                $assert->message_before
            );

            $code .= $twig->render(
                'date_range/before.php.twig', [
                    'getMethod' => $this->getAccessMethod(
                        $reflectionProperty,
                    ),
                    'before' => DateTime::createFromInterface($assert->before)->format($assert->format),
                    'format' => $assert->format,
                    'class' => $className,
                    'property' => $reflectionProperty->name,
                    'message' => $message,
                ]
            );

            $code .= "\n\n";
        }

        if ($assert->after !== null && $assert->before !== null) {
            $message = preg_replace(
                [
                    '/{{ after }}/',
                    '/{{ before }}/',
                ],
                [
                    $assert->after->format($assert->format),
                    $assert->before->format($assert->format),
                ],
                $assert->message_between
            );

            $code .= $twig->render(
                'date_range/between.php.twig', [
                    'getMethod' => $this->getAccessMethod(
                        $reflectionProperty,
                    ),
                    'before' => DateTime::createFromInterface($assert->before)->format($assert->format),
                    'after' => DateTime::createFromInterface($assert->after)->format($assert->format),
                    'format' => $assert->format,
                    'class' => $className,
                    'property' => $reflectionProperty->name,
                    'message' => $message,
                ]
            );

            $code .= "\n\n";
        }

        return $code;
    }
}
