<?php

namespace Kaa\Bundle\Framework;

use Kaa\Component\Generator\DefaultNewInstanceGenerator;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Throwable;

#[PhpOnly]
class FrameworkGenerator
{
    public function generate(string $pathToConfig, string $pathToGenerated): void
    {
        try {
            $generatorsConfig = require $pathToConfig . '/bundles.php';
            $newInstanceGenerator = $this->getNewInstanceGenerator($generatorsConfig);
            $generators = $this->getGenerators($generatorsConfig);

            $sharedConfig = new SharedConfig($pathToGenerated, $newInstanceGenerator);
            $config = $this->parseConfig($pathToConfig);

            $processor = new Processor();
            foreach ($generators as $generator) {
                $generatorConfig = [];
                if ($generator->getConfiguration() !== null) {
                    $generatorConfig = $processor->process(
                        $generator->getConfiguration()->buildTree(),
                        [$generator->getRootConfigurationKey() => $config[$generator->getRootConfigurationKey()] ?? []],
                    );
                }

                $generator->generate($sharedConfig, $generatorConfig);

                $configArray = $generator->getConfigArray($generatorConfig);
                $config = array_merge_recursive($config, $configArray);
            }
        } catch (Throwable $throwable) {
            echo "\nGeneration error: {$throwable->getMessage()}\n\n";
            echo get_class($throwable) . "\n\n";
            echo $throwable->getTraceAsString() . "\n\n";
            exit;
        }
    }

    /**
     * @param mixed[] $generatorsConfig
     */
    private function getNewInstanceGenerator(array $generatorsConfig): NewInstanceGeneratorInterface
    {
        if (array_key_exists('instanceGenerator', $generatorsConfig)) {
            return new $generatorsConfig['instanceGenerator']();
        }

        return new DefaultNewInstanceGenerator();
    }

    /**
     * @param mixed[] $generatorsConfig
     * @return BundleGeneratorInterface[]
     */
    private function getGenerators(array $generatorsConfig): array
    {
        /** @var BundleGeneratorInterface[] $generators */
        $generators = [];
        foreach ($generatorsConfig as $key => $generatorClass) {
            if ($key === 'instanceGenerator') {
                continue;
            }

            $generators[] = new $generatorClass();
        }

        return $this->sortByPriority($generators);
    }

    /**
     * @param BundleGeneratorInterface[] $generators
     * @return BundleGeneratorInterface[]
     */
    private function sortByPriority(array $generators): array
    {
        usort(
            $generators,
            static fn (BundleGeneratorInterface $left, BundleGeneratorInterface $right) => -(
                $left->getPriority() <=> $right->getPriority()
            ),
        );

        return $generators;
    }

    /**
     * @return mixed[]
     */
    private function parseConfig(string $pathToConfig): array
    {
        $config = [];

        $finder = (new Finder())
            ->files()
            ->in($pathToConfig)
            ->name(['*.yaml', '*.yml']);

        foreach ($finder as $file) {
            $config[] = Yaml::parseFile($file->getRealPath());
        }

        return array_merge_recursive(...$config);
    }
}
