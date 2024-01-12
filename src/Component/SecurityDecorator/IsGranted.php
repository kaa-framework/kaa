<?php

namespace Kaa\Component\SecurityDecorator;

use Attribute;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use Kaa\Component\Security\SecurityInterface;
use ReflectionMethod;
use ReflectionParameter;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE),
]
readonly class IsGranted implements DecoratorInterface
{
    public function __construct(
        private string $attribute,

        /** @var string[] */
        private array $subject,

        /** @var string[] */
        private array $subjectVars,
    ) {
    }

    public function getType(): DecoratorType
    {
        return DecoratorType::Pre;
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function decorate(
        ReflectionMethod $decoratedMethod,
        ?ReflectionParameter $parameter,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string {
        $twig = TwigFactory::create(__DIR__ . '/templates');
        $service = $newInstanceGenerator->generate(
            SecurityInterface::class,
            'Kaa\Generated\Security\Security',
        );

        return $twig->render('is_granted.php.twig', [
            'service' => $service,
            'attribute' => $this->attribute,
            'subject' => $this->subject,
            'subjectVars' => $this->subjectVars,
        ]);
    }
}
