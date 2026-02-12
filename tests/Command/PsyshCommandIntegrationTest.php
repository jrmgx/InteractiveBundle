<?php

/*
 * This file is part of the InteractiveBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrmgx\InteractiveBundle\Command;

use Psy\Shell;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @coversNothing
 */
class PsyshCommandIntegrationTest extends KernelTestCase
{
    private Shell $shell;

    private PsyshCommand $command;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->shell = static::getContainer()->get('psysh.shell');
        $this->command = static::getContainer()->get('psysh.command.shell_command');
    }

    public function testScopeVariables(): void
    {
        $this->assertEqualsCanonicalizing(
            [
//                'container',
//                'kernel',
//                'parameters',
                '_',
                // 'self',
            ],
            array_keys($this->shell->getScopeVariables()),
            'Expected shell service to have scope variables'
        );
    }

    public function testFindShell(): void
    {
        $application = new Application(self::$kernel);
        $application->addCommand($this->command);
        $application->find('interactive');

        $this->addToAssertionCount(1);
    }
}
