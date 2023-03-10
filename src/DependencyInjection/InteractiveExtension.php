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

use Psy\Command\Command;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @private
 */
final class InteractiveExtension extends Extension
{
    private const CONFIG_DIR = __DIR__ . '/../../resources/config';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(self::CONFIG_DIR),
        );
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config['variables'] as $name => $value) {
            if (\is_string($value) && 0 === mb_strpos($value, '@')) {
                $value = new Reference(mb_substr($value, 1));
            }

            $config['variables'][$name] = $value;
        }

        // $containerId = 'test.service_container';
//
//        $container
//            ->findDefinition('psysh.shell')
//            ->addMethodCall(
//                'setScopeVariables',
//                [array_merge(
//                    $config['variables'],
//                    [
//                        'container' => new Reference($containerId),
//                        'kernel' => new Reference('kernel'),
//                        'self' => new Reference('psysh.shell'),
//                        'parameters' => new Expression(sprintf(
//                            "service('%s').getParameterBag().all()",
//                            $containerId
//                        )),
//                    ]
//                )]
//            )
//        ;

        $container
            ->registerForAutoconfiguration(Command::class)
            ->addTag('psysh.command')
        ;
    }
}
