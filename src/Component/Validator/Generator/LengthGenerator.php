<?php

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\Length;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LengthGenerator extends AbstractGenerator
{
    /**
     * @param Length $assert
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
        bool $useArrayAccess = false,
    ): string {
        $code = '';

        if ($assert->exactly !== null) {
            /** @var string $message */
            $message = preg_replace('/{{ limit }}/', (string) $assert->exactly, $assert->exactlyMessage);

            $code .= $twig->render('length/exactly.php.twig', [
                'getProperty' => $this->getPropertyCode($reflectionProperty, $useArrayAccess),
                'limit' => $assert->exactly,
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $message,
                'useArrayAccess' => $useArrayAccess,
            ]);

            $code .= "\n\n";
        }

        if ($assert->max !== null) {
            /** @var string $message */
            $message = preg_replace('/{{ limit }}/', (string) $assert->max, $assert->maxMessage);

            $code .= $twig->render('length/max.php.twig', [
                'getProperty' => $this->getPropertyCode($reflectionProperty, $useArrayAccess),
                'limit' => $assert->max,
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $message,
                'useArrayAccess' => $useArrayAccess,
            ]);

            $code .= "\n\n";
        }

        if ($assert->min !== null) {
            /** @var string $message */
            $message = preg_replace('/{{ limit }}/', (string) $assert->min, $assert->minMessage);

            $code .= $twig->render('length/min.php.twig', [
                'getProperty' => $this->getPropertyCode($reflectionProperty, $useArrayAccess),
                'limit' => $assert->min,
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $message,
                'useArrayAccess' => $useArrayAccess,
            ]);

            $code .= "\n\n";
        }

        return $code;
    }
}
