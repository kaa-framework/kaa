<?php

namespace Kaa\Component\RequestMapperDecorator;

use Attribute;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\Reflection;
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

    /**
     * @throws BadTypeException
     */
    public function decorate(
        ReflectionMethod $decoratedMethod,
        ?ReflectionParameter $parameter,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string {
        $variables->addVariable(JsonResponse::class, 'kaaDecoratorResponse');

        $isReturnTypeBuiltin = Reflection::namedType($decoratedMethod->getReturnType())->isBuiltin();

        if ($isReturnTypeBuiltin) {
            $code = sprintf(
                '$kaaDecoratorResponse = new \Kaa\Component\HttpMessage\Response\JsonResponse(not_false(json_encode($%s)));',
                $variables->getActualReturnValueName(),
            );
        } else {
            $code = sprintf(
                '$kaaDecoratorResponse = \Kaa\Component\HttpMessage\Response\JsonResponse::fromObject($%s);',
                $variables->getActualReturnValueName(),
            );
        }

        $variables->setActualReturnValueName('kaaDecoratorResponse');

        return $code;
    }
}
