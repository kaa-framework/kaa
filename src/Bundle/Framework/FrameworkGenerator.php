<?php

namespace Kaa\Bundle\Framework;

use Kaa\Component\GeneratorContract\DefaultNewInstanceGenerator;
use Kaa\Component\GeneratorContract\NewInstanceGeneratorInterface;
use Kaa\Component\GeneratorContract\SharedConfig;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class FrameworkGenerator
{
    public function generate(string $pathToConfig, string $pathToGenerated): void
    {
        $newInstanceGenerator = new DefaultNewInstanceGenerator();

        $generatorsConfig = require $pathToConfig . '/bundles.php';

        /** @var NewInstanceGeneratorInterface $newInstanceGenerator */
        $newInstanceGenerator = array_key_exists('instanceGenerator', $generatorsConfig)
            ? new $generatorsConfig['instanceGenerator']()
            : new DefaultNewInstanceGenerator();

        /** @var BundleGeneratorInterface[] $generators */
        $generators = [];
        foreach ($generatorsConfig as $key => $generatorClass) {
            if ($key === 'instanceGenerator') {
                continue;
            }

            $generators[] = new $generatorClass();
        }

        $generators = $this->sortByPriority($generators);

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

            $configArray = $generator->getConfigArray();
            $config = array_merge_recursive($config, $configArray);
        }
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
