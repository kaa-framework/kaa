<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ServiceLocator;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ArgumentType;
use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\DependencyInjection\Exception\ServiceAlreadyExistsException;
use Kaa\Component\DependencyInjection\Exception\ServiceDoesNotExistException;
use Kaa\Component\DependencyInjection\ServiceLocator\ConfigServiceLocator;
use Kaa\Component\DependencyInjection\Test\ClassFixture\JustService;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class ConfigServiceLocatorTest extends TestCase
{
    private AliasCollection $aliasCollection;

    private ParameterCollection $parameterCollection;

    private ServiceCollection $serviceCollection;

    protected function setUp(): void
    {
        $this->aliasCollection = new AliasCollection();
        $this->parameterCollection = new ParameterCollection();
        $this->serviceCollection = new ServiceCollection();
    }

    /**
     * @throws ServiceAlreadyExistsException
     */
    public function testWillThrowOnServiceWithoutClass(): void
    {
        $this->expectException(InvalidServiceDefinitionException::class);

        $this->locate([
            'services' => [
                'app.service_with_no_class' => [],
            ],
        ]);
    }

    /**
     * @throws ServiceAlreadyExistsException
     */
    public function testWillThrowIfClassDoesNotExits(): void
    {
        $this->expectException(InvalidServiceDefinitionException::class);

        $this->locate(['services' => ['Not\\Kaa\\Service' => []]]);
    }

    /**
     * @throws ServiceAlreadyExistsException
     */
    public function testWillThrowIfThereAreBothArgumentsAndFactory(): void
    {
        $this->expectException(InvalidServiceDefinitionException::class);

        $this->locate([
            'services' => [
                JustService::class => [
                    'factory' => [

                    ],

                    'arguments' => [

                    ],
                ],
            ],
        ]);
    }

    /**
     * @throws ServiceDoesNotExistException|InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    public function testWillUseClassAsServiceName(): void
    {
        $this->locate([
            'services' => [
                JustService::class => [
                ],
            ],
        ]);

        assertTrue($this->serviceCollection->has(JustService::class));
        $service = $this->serviceCollection->get(JustService::class);

        assertEquals(JustService::class, $service->name);
    }

    /**
     * @throws ServiceDoesNotExistException|InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    public function testWillUseExplicitServiceName(): void
    {
        $this->locate([
            'services' => [
                'app.just_service' => [
                    'class' => JustService::class,
                ],
            ],
        ]);

        assertTrue($this->serviceCollection->has('app.just_service'));
        $service = $this->serviceCollection->get('app.just_service');

        assertEquals('app.just_service', $service->name);
    }

    /**
     * @throws ServiceDoesNotExistException|InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    public function testWillParseFactoryDefinitionAndUseInvokeAsDefault(): void
    {
        $this->locate([
            'services' => [
                JustService::class => [
                    'factory' => [
                        'service' => JustService::class,
                    ],
                ],
            ],
        ]);

        assertTrue($this->serviceCollection->has(JustService::class));
        $service = $this->serviceCollection->get(JustService::class);

        assertEquals(ConstructionType::Factory, $service->constructionType);
        assertEquals(JustService::class, $service->factory->serviceName);
        assertEquals('invoke', $service->factory->method);
        assertFalse($service->factory->isStatic);
    }

    /**
     * @throws ServiceDoesNotExistException|InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    public function testWillReplaceArgumentsWithConfigArguments(): void
    {
        $this->locate([
            'parameters' => [
                'app.int' => 10,
            ],

            'services' => [
                'app.just_service' => [
                    'class' => JustService::class,
                    'arguments' => [
                        'parameter' => '%app.int',
                        'justService2' => '@' . JustService::class,
                    ],
                ],
            ],
        ]);

        assertTrue($this->serviceCollection->has('app.just_service'));
        $service = $this->serviceCollection->get('app.just_service');

        assertEquals(ConstructionType::Constructor, $service->constructionType);

        assertEquals(ArgumentType::Parameter, $service->arguments[0]->type);
        assertEquals('app.int', $service->arguments[0]->name);

        assertEquals(ArgumentType::Service, $service->arguments[1]->type);
        assertEquals(JustService::class, $service->arguments[1]->name);
    }

    /**
     * @param mixed[] $config
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    private function locate(array $config): void
    {
        (new ConfigServiceLocator(
            $config,
            $this->serviceCollection,
            $this->parameterCollection,
            $this->aliasCollection,
        ))
            ->locate();
    }
}
