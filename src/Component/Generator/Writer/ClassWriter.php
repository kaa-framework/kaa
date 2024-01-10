<?php

namespace Kaa\Component\Generator\Writer;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

#[PhpOnly]
readonly class ClassWriter
{
    private PhpFile $file;
    private ClassType $class;

    /**
     * @param string $namespaceName Часть namespace, которая идёт после Kaa\Generated\
     * @param class-string[] $implements
     */
    public function __construct(
        private string $namespaceName,
        private string $className,
        ?string $extends = null,
        array $implements = [],
    ) {
        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\' . $namespaceName);
        $this->class = $namespace->addClass($this->className);

        $this->class->setExtends($extends);
        foreach ($implements as $implement) {
            $this->class->addImplement($implement);
        }
    }

    public function addVariable(
        Visibility $visibility,
        string $type,
        string $name,
        bool $nullable = false,
        mixed $value = None::None,
        bool $isStatic = false,
        ?string $comment = null,
    ): self {
        $var = $this->class->addProperty($name);

        $var->setVisibility($visibility->value);
        $var->setType($type);
        $var->setNullable($nullable);
        $var->setStatic($isStatic);
        $var->setComment($comment);

        if ($value !== None::None) {
            $var->setValue($value);
        }

        return $this;
    }

    /**
     * @param Parameter[] $parameters
     */
    public function addMethod(
        Visibility $visibility,
        string $name,
        string $returnType,
        string $code,
        array $parameters = [],
        bool $isStatic = false,
        ?string $comment = null,
    ): self {
        $method = $this->class->addMethod($name);

        $method->setVisibility($visibility->value);
        $method->setReturnType($returnType);
        $method->setBody($code);
        $method->setStatic($isStatic);
        $method->setComment($comment);

        foreach ($parameters as $parameter) {
            $param = $method->addParameter($parameter->name);

            $param->setType($parameter->type);
            if ($parameter->defaultValue !== None::None) {
                $param->setDefaultValue($parameter->defaultValue);
            }
        }

        return $this;
    }

    /**
     * @throws WriterException
     */
    public function writeFile(string $exportDirectory): void
    {
        $directory = $exportDirectory . '/' . str_replace('\\', '/', $this->namespaceName);

        if (!is_dir($directory) && !mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new WriterException("Directory {$directory} was not created");
        }

        file_put_contents(
            $directory . '/' . $this->className . '.php',
            (new KaaPrinter())->printFile($this->file),
        );
    }
}
