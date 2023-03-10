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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversNothing
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class InteractiveBundleTest extends KernelTestCase
{
    /**
     * Test that the bundle loads and compiles.
     */
    public function testServicesLoading(): void
    {
        static::bootKernel();

        $this->assertInstanceOf(
            Shell::class,
            static::getContainer()->get('psysh.shell'),
        );
    }
}
