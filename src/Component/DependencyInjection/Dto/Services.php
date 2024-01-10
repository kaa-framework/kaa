<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto;

use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\ServiceDoesNotExistException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class Services
{
    public function __construct(
        private ServiceCollection $serviceCollection,
        private AliasCollection $aliasCollection,
    ) {
    }

    /**
     * @throws ServiceDoesNotExistException
     */
    public function getClass(string $nameOrAlias): string
    {
        $serviceName = $this->aliasCollection->getServiceName($nameOrAlias) ?? $nameOrAlias;

        return $this->serviceCollection->get($serviceName)->class->getName();
    }
}
