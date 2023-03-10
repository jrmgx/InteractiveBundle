<?php

declare(strict_types=1);

/*
 * This file is part of the InteractiveBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Jrmgx\InteractiveBundle\Command\InstanceCommand;
use Jrmgx\InteractiveBundle\Command\ServiceCommand;
use Jrmgx\InteractiveBundle\PsyshFacade;

if (!function_exists('psysh')) {
    function psysh(array $variables = [], $bind = null): void
    {
        PsyshFacade::debug($variables, $bind);
    }
}

if (!function_exists('resolved_service')) {
    function resolved_service(string $identifier): object
    {
        return ServiceCommand::resolvedService($identifier);
    }
}

if (!function_exists('resolved_class')) {
    /**
     * @param class-string $className
     */
    function resolved_class($className): object
    {
        return InstanceCommand::resolvedClassName($className);
    }
}
