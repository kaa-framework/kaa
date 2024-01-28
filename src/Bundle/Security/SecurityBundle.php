<?php

namespace Kaa\Bundle\Security;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Bundle\Security\Writer\ListenerWriter;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\HttpKernel\HttpKernelEvents;
use Kaa\Component\Security\SecurityGenerator;
use Kaa\Component\Security\SecurityInterface;
use Kaa\Component\Security\Session\SessionAuthenticator;
use Kaa\Component\Security\Session\SessionService;
use Kaa\Component\Security\Voter\IsAuthenticatedFullyVoter;
use Kaa\Component\Security\Voter\RoleVoter;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
class SecurityBundle extends SecurityGenerator implements BundleGeneratorInterface
{
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        parent::generate($sharedConfig, $config);

        (new ListenerWriter($sharedConfig))->write();
    }

    public function getRootConfigurationKey(): ?string
    {
        return 'security';
    }

    public function getConfiguration(): ?TreeBuilder
    {
        // @formatter:off
        $treeBuilder = new TreeBuilder('security');
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->arrayNode('scan')
                        ->scalarPrototype()
                            ->defaultValue([])
                        ->end()
                    ->end()
                    ->arrayNode('session')
                        ->children()
                            ->scalarNode('cookie_name')
                                ->defaultValue('PHP_SESSION_ID')
                            ->end()
                            ->integerNode('lifetime')
                                ->defaultValue(3600)
                            ->end()
                            ->scalarNode('sessions_directory')
                                ->defaultValue('/tmp/kaa_session')
                            ->end()
                            ->scalarNode('user_provider')->end()
                        ->end()
                    ->end()
                    ->arrayNode('firewalls')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('path')->end()
                                ->arrayNode('authenticators')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('service')
                                                ->isRequired()
                                            ->end()
                                            ->scalarNode('serviceClass')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('voters')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('service')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('serviceClass')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('access_control')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->scalarPrototype()->end()
                        ->scalarPrototype()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getConfigArray(array $config): array
    {
        $services = [
            '\Kaa\Generated\Security\RequestListener' => [
                'class' => '\Kaa\Generated\Security\RequestListener',
            ],

            SecurityInterface::class => [
                'class' => '\Kaa\Generated\Security\Security',
            ],

            RoleVoter::class => [
                'class' => RoleVoter::class,
            ],

            IsAuthenticatedFullyVoter::class => [
                'class' => IsAuthenticatedFullyVoter::class,
            ],
        ];

        $parameters = [];

        if (array_key_exists('session', $config)) {
            $services[SessionService::class] = [
                'arguments' => [
                    'lifetimeSeconds' => '%kaa.security.session.lifetime',
                    'cookieName' => '%kaa.security.session.cookie_name',
                    'userProvider' => $config['session']['user_provider'],
                    'sessionsDirectory' => '%kaa.security.session.sessions_directory',
                ],
            ];

            $services[SessionAuthenticator::class] = [
                'arguments' => [
                    'sessionService' => '@' . SessionService::class,
                ],
            ];

            $parameters = [
                'kaa.security.session.lifetime' => $config['session']['lifetime'],
                'kaa.security.session.cookie_name' => $config['session']['cookie_name'],
                'kaa.security.session.sessions_directory' => $config['session']['sessions_directory'],
            ];
        }

        return [
            'di' => [
                'services' => $services,
                'parameters' => $parameters,
            ],

            'dispatcher' => [
                'listeners' => [
                    [
                        'service' => '\Kaa\Generated\Security\RequestListener',
                        'event' => HttpKernelEvents::REQUEST,
                    ],
                ],
            ],
        ];
    }
}
