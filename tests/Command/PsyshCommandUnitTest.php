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

namespace Jrmgx\InteractiveBundle\Command;

use PHPUnit\Framework\TestCase;
use Psy\Shell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Jrmgx\InteractiveBundle\Command\PsyshCommand
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PsyshCommandUnitTest extends TestCase
{
    public function testIsASymfonyCommand(): void
    {
        $this->assertTrue(is_a(PsyshCommand::class, Command::class, true));
    }

    public function testConfiguration(): void
    {
        $shell = $this->createMock(Shell::class);
        $shell
            ->expects($this->never())
            ->method($this->anything())
        ;

        $command = new PsyshCommand($shell);

        $this->assertSame('Start PsySH for Symfony', $command->getDescription());
    }

    public function testExecute(): void
    {
        $shell = $this->createMock(Shell::class);
        $shell
            ->expects($this->once())
            ->method('run')
            ->willReturn(1)
        ;

        $input = $this->createMock(InputInterface::class);

        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects($this->once())
            ->method('writeln')
        ;

        $command = new PsyshCommand($shell);
        $command->run($input, $output);
    }
}
