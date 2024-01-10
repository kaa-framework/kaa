<?php

declare(strict_types=1);

namespace Kaa\Component\Generator;

use Kaa\Component\Generator\Writer\BootstrapWriter;

#[PhpOnly]
readonly class SharedConfig
{
    public BootstrapWriter $bootstrapWriter;

    /**
     * @param string $exportDirectory Папка, в которую будут помещены сгенерированные классы. Autoloader должен настроить namespace Kaa\\Generated\\ в эту папку
     * @param NewInstanceGeneratorInterface $newInstanceGenerator Может заменяться, если используете контейнер инъекции зависимостей
     */
    public function __construct(
        public string $exportDirectory,
        public NewInstanceGeneratorInterface $newInstanceGenerator = new DefaultNewInstanceGenerator(),
    ) {
        $this->bootstrapWriter = new BootstrapWriter($this->exportDirectory);
    }
}
