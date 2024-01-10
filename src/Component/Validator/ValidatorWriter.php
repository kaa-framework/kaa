<?php

declare(strict_types=1);

namespace Kaa\Component\Validator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\Validator\Assert\AssertInterface;
use Kaa\Component\Validator\Exception\UnsupportedAssertException;
use Kaa\Component\Validator\Exception\ValidatorGeneratorException;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use ReflectionProperty;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ValidatorWriter
{
    private PhpFile $file;
    private Twig\Environment $twig;
    private ClassType $class;

    public function __construct(
        private SharedConfig $config,
        /** @var array<class-string<object>, array<int, array<string, array{attribute: AssertInterface, reflectionProperty: ReflectionProperty}>>> */
        private array $asserts,
    ) {
        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\Validator');
        $this->class = $namespace->addClass('Validator');
        $this->class->addImplement(ValidatorInterface::class);

        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new Twig\Environment(
            $loader, [
                'autoescape' => false,
            ]
        );

        return $twig;
    }

    /**
     * @return string[]
     * @throws UnsupportedAssertException
     */
    private function getAssertsCode(): array
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
    private function getMethodNames(array $code): array
    {
        $methodNames = [];
        foreach ($code as $class => $value) {
            $methodNames[$class] = 'validate_' . str_replace('\\', '_', $class);
            $this->class->addMethod($methodNames[$class])
                ->setReturnType('array')
                ->setVisibility(ClassLike::VisibilityPrivate)
                ->setBody(
                    $value,
                )
                ->addParameter('model')
                ->setType('object');
        }

        return $methodNames;
    }

    /**
     * @throws SyntaxError|ValidatorGeneratorException|RuntimeError|LoaderError
     */
    public function write(): void
    {
        $code = $this->getAssertsCode();
        $methodNames = $this->getMethodNames($code);

        $this->class->addMethod('validate')
            ->addComment("@return \Kaa\Component\Validator\Violation[]")
            ->setReturnType('array')
            ->setVisibility(ClassLike::VisibilityPublic)
            ->setBody(
                $this->twig->render(
                    'switch.php.twig', [
                        'methodNames' => $methodNames,
                    ]
                ),
            )
            ->addParameter('model')
            ->setType('object');

        $this->writeFile();
    }

    /**
     * @throws ValidatorGeneratorException
     */
    private function writeFile(): void
    {
        $directory = $this->config->exportDirectory . '/Validator';

        if (!is_dir($directory) && !mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new ValidatorGeneratorException("Directory {$directory} was not created");
        }

        file_put_contents(
            $directory . '/Validator.php',
            (new PsrPrinter())->printFile($this->file),
        );
    }
}
