<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\All;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class AllGenerator extends AbstractGenerator
{
    /**
     * @param All $assert
     * @throws LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
        bool $useArrayAccess = false,
    ): string {
        $code = $twig->render(
            'all/all_foreach_open.php.twig', [
                'getProperty' => $this->getPropertyCode($reflectionProperty, $useArrayAccess),
                'elemName' => self::ARRAY_VARIABLE_NAME,
            ]
        );

        foreach ($assert->asserts as $a) {
            $code .= "\n" . $a->getGenerator()->generateAssert(
                $a,
                $reflectionProperty,
                $className,
                $twig,
                true,
            );
        }

        $code .= "\n" . $twig->render('all/all_foreach_close.php.twig');

        return $code;
    }
}
