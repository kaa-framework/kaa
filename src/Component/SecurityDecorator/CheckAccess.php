<?php

namespace Kaa\Component\SecurityDecorator;

use Attribute;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use ReflectionMethod;
use ReflectionParameter;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class CheckAccess implements DecoratorInterface
{
    public function __construct(
        public string $accessCheckerClass,
        public string $serviceName,
        /** @var string[] */
        public array $arguments = [],
        public string $method = 'invoke',
        public ?string $serviceClass = null,
    ) {
    }

    public function getType(): DecoratorType
    {
        return DecoratorType::Pre;
    }

    public function getPriority(): int
    {
        return -500;
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
            $this->serviceName,
            $this->serviceClass ?? $this->serviceName,
        );

        return $twig->render('check_access.php.twig', [
            'service' => $service,
            'method' => $this->method,
            'arguments' => $this->arguments,
            'serviceName' => $this->serviceName,
        ]);
    }
}
