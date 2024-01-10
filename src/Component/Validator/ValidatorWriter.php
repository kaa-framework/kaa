<?php

declare(strict_types=1);

namespace Kaa\Component\Validator;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Exception\UnsupportedAssertException;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ValidatorWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    public function __construct(
        private SharedConfig $config,
        /** @var array<class-string<object>, array<int, array<string, array{attribute: AssertInterface, reflectionProperty: ReflectionProperty}>>> */
        private array $asserts,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'Validator',
            className: 'Validator',
            implements: [ValidatorInterface::class],
        );

        $this->twig = TwigFactory::create(__DIR__ . '/templates');
    }

    /**
     * @throws SyntaxError|ValidatorGeneratorException|RuntimeError|LoaderError|WriterException
     */
    public function write(): void
    {
        $code = $this->getAssertCodes();
        $methodNames = $this->generateValidateMethods($code);

        $code = $this->twig->render('switch.php.twig', [
            'methodNames' => $methodNames,
        ],
        );

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'validate',
            returnType: 'array',
            code: $code,
            parameters: [
                new Parameter(type: 'object', name: 'model'),
            ],
            comment: '@return \Kaa\Component\Validator\Violation[]'
        );

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @return string[]
     * @throws UnsupportedAssertException
     */
    private function getAssertCodes(): array
    {
        $code = [];
        foreach ($this->asserts as $class => $attributes) {
            foreach ($attributes as $assert) {
                if (!$assert['reflectionProperty']->hasType()) {
                    throw new UnsupportedAssertException(
                        "The type of {$class}::{$assert['reflectionProperty']->name} must be specified."
                    );
                }
                $propertyType = $assert['reflectionProperty']->getType()->getName();
                if (!in_array($propertyType, $assert['attribute']->getAllowedTypes(), true)) {
                    $allowedTypes = implode(', ', $assert['attribute']->getAllowedTypes());
                    throw new UnsupportedAssertException(
                        "The type of {$class}::{$assert['reflectionProperty']->name} must match these types: {$allowedTypes}."
                    );
                }

                $generator = $assert['attribute']->getGenerator();
                $code[$class][] = $generator->generateAssert(
                    $assert['attribute'],
                    $assert['reflectionProperty'],
                    $class,
                    $this->twig,
                );
            }

            array_unshift($code[$class], '$violationsList = [];');
            $code[$class][] = 'return $violationsList;';
            $code[$class] = implode("\n", $code[$class]);
        }

        return $code;
    }

    /**
     * @param string[] $code
     * @return string[]
     */
    private function generateValidateMethods(array $code): array
    {
        $methodNames = [];
        foreach ($code as $class => $value) {
            $methodNames[$class] = 'validate_' . str_replace('\\', '_', $class);

            $this->classWriter->addMethod(
                visibility: Visibility::Private,
                name: $methodNames[$class],
                returnType: 'array',
                code: $value,
                parameters: [
                    new Parameter(type: $class, name: 'model'),
                ],
                comment: '@return \Kaa\Component\Validator\Violation[]',
            );
        }

        return $methodNames;
    }
}
