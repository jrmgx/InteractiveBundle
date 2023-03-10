<?php

declare(strict_types=1);

/*
 * This file is part of the InteractiveBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrmgx\InteractiveBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @private
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('psysh');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('variables')
                    ->info('Define additional variables to be exposed in Psysh')
                    ->useAttributeAsKey('variable_name')
                    ->example([
                        'debug' => '%kernel.debug%',
                        'my_service' => '@my.service',
                        'os' => ['linux', 'macos', 'losedows'],
                    ])
                    ->prototype('variable')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
