<?php

namespace Kaa\Component\RequestMapperDecorator;

use Attribute;
use Kaa\Component\GeneratorContract\NewInstanceGeneratorInterface;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\HttpMessage\Response\JsonResponse;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use ReflectionMethod;
use ReflectionParameter;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_METHOD),
]
class AsJsonResponse implements DecoratorInterface
{
    public function getType(): DecoratorType
    {
        return DecoratorType::Post;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function decorate(
        ReflectionMethod $decoratedMethod,
        ?ReflectionParameter $parameter,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string {
        $variables->addVariable(JsonResponse::class, 'kaaDecoratorResponse');

        $code = sprintf(
            '$kaaDecoratorResponse = \Kaa\HttpKernel\Response\JsonResponse::fromObject($%s);',
            $variables->getActualReturnValueName(),
        );

        $variables->setActualReturnValueName('kaaDecoratorResponse');

        return $code;
    }
}
