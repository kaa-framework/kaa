<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ServiceLocator;

use Exception;
use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ArgumentType;
use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\DependencyInjection\Exception\ServiceAlreadyExistsException;
use Kaa\Component\DependencyInjection\Exception\ServiceDoesNotExistException;
use Kaa\Component\DependencyInjection\ServiceLocator\AttributesToConfigParser;
use Kaa\Component\DependencyInjection\ServiceLocator\ConfigServiceLocator;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored\IgnoredService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\JustService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Scanned\ScannedService;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class AttributesToConfigParserTest extends TestCase
{
    private AliasCollection $aliasCollection;

    private ServiceCollection $serviceCollection;

    private ParameterCollection $parameterCollection;

    protected function setUp(): void
    {
        $this->aliasCollection = new AliasCollection();
        $this->serviceCollection = new ServiceCollection();
        $this->parameterCollection = new ParameterCollection();
    }

    /**
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    public function testIgnoresIgnored(): void
    {
        $this->locate([
            'scan' => [
                '\\Kaa\\Component\\DependencyInjection\\Test\\ClassFixture',
            ],
            'ignore' => [
                JustService::class,
                'Kaa\\Component\\DependencyInjection\\Test\\ClassFixture\\Ignored',
            ],
        ]);

        assertTrue($this->serviceCollection->has(ScannedService::class));
        assertFalse($this->serviceCollection->has(JustService::class));
        assertFalse($this->serviceCollection->has(IgnoredService::class));
    }

    /**
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException|ServiceDoesNotExistException
     */
    public function testParsesAttributes(): void
    {
        $this->locate([
            'scan' => [
                '\\Kaa\\Component\\DependencyInjection\\Test\\ClassFixture',
            ],
            'ignore' => [
                'Kaa\\Component\\DependencyInjection\\Test\\ClassFixture\\Ignored',
            ],
        ]);

        assertTrue($this->serviceCollection->has(ScannedService::class));
        $scannedService = $this->serviceCollection->get(ScannedService::class);

        assertEquals(ConstructionType::Constructor, $scannedService->constructionType);

        assertEquals(ArgumentType::Service, $scannedService->arguments[0]->type);
        assertEquals('app.service', $scannedService->arguments[0]->name);

        assertEquals(ArgumentType::Parameter, $scannedService->arguments[1]->type);
        assertEquals('app.parameter', $scannedService->arguments[1]->name);

        assertTrue($this->serviceCollection->has(JustService::class));
        $justService = $this->serviceCollection->get(JustService::class);

        assertEquals(ConstructionType::Factory, $justService->constructionType);

        assertEquals(JustService::class, $justService->factory->serviceName);
        assertFalse($justService->factory->isStatic);
        assertEquals('invoke', $justService->factory->method);
    }

    /**
     * @param mixed[] $config
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException|Exception
     */
    private function locate(array $config): void
    {
        $config = (new AttributesToConfigParser(
            $config,
        ))
            ->getConfig();

        (new ConfigServiceLocator(
            $config,
            $this->serviceCollection,
            $this->parameterCollection,
            $this->aliasCollection,
        ))
            ->locate();
    }
}
