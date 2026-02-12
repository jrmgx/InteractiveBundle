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

namespace Jrmgx\InteractiveBundle;

use Psy\Shell;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class PsyshFacade
{
    private static ?Shell $shell = null;

    private static ?ContainerInterface $container = null;

    public function __construct(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function init(): void
    {
        if (self::$shell !== null) {
            return;
        }

        if (self::$container === null) {
            throw new \RuntimeException('Cannot initialize the facade without a container.');
        }

        self::$shell = self::$container->get('psysh.shell');
    }

    public static function debug(array $variables = [], $bind = null): void
    {
        self::init();

        $_variables = array_merge(self::$shell->getScopeVariables(), $variables);

        self::$shell::debug($_variables, $bind);
    }
}
