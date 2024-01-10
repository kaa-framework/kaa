<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Assert\Email;
use Kaa\Component\Validator\Exception\InvalidArgumentException;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class EmailGenerator extends AbstractGenerator
{
    private const PATTERN_HTML5_ALLOW_NO_TLD = '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';
    private const PATTERN_HTML5 = '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/';
    private const PATTERN_LOOSE = '/^.+\@\S+\.\S+$/';

    private const EMAIL_PATTERNS = [
        'loose' => self::PATTERN_LOOSE,
        'html5' => self::PATTERN_HTML5,
        'html5-allow-no-tld' => self::PATTERN_HTML5_ALLOW_NO_TLD,
    ];

    /**
     * @param Email $assert
     * @throws InvalidArgumentException|LoaderError|RuntimeError|SyntaxError|ValidatorGeneratorException
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string {
        if (!array_key_exists($assert->mode, self::EMAIL_PATTERNS)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "%s::$mode" parameter value is not valid.',
                    $assert->mode,
                )
            );
        }

        return $twig->render(
            'email.php.twig', [
                'getMethod' => $this->getAccessMethod(
                    $reflectionProperty,
                ),
                'email_pattern' => self::EMAIL_PATTERNS[$assert->mode],
                'class' => $className,
                'property' => $reflectionProperty->name,
                'message' => $assert->message,
            ]
        );
    }
}
