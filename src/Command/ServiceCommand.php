<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrmgx\InteractiveBundle\Command;

use Jrmgx\InteractiveBundle\Interactive;
use Psr\Container\ContainerInterface;
use Psy\Command\Command;
use Psy\Shell;
use Symfony\Bundle\FrameworkBundle\Command\BuildDebugContainerTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class ServiceCommand extends Command
{
    use BuildDebugContainerTrait;

    private static KernelInterface $kernel;
    /** @var array<int, string> */
    private array $servicesIds = [];

    public static function resolvedService(string $identifier): object
    {
        /** @var ContainerInterface $testContainer */
        $testContainer = self::$kernel->getContainer()->get('test.service_container');

        return $testContainer->get($identifier);
    }

    public function __construct(KernelInterface $kernel)
    {
        self::$kernel = $kernel;

        $this->computeServiceIds();

        parent::__construct('service');
    }

    protected function configure(): void
    {
        $this
            ->setName('service')
            ->setDefinition([
                new InputArgument('variable', InputArgument::REQUIRED, 'desc'),
                new InputArgument('equal', InputArgument::OPTIONAL, 'desc'),
                new InputArgument('serviceIdentifier', InputArgument::OPTIONAL, 'desc'),
            ])
            ->setDescription('Service command.')
            ->setHelp(
                <<<'HELP'
...
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Shell $shell */
        $shell = $this->getApplication();

        $variable = trim($input->getArgument('variable'), '$');
        $identifier = $input->getArgument('serviceIdentifier')
            ?? $input->getArgument('equal');
        $result = $this->findServiceId($identifier, new SymfonyStyle($input, $output));

        if ($result) {
            $shell->addInput('$' . $variable . ' = resolved_service(\'' . $result . '\');');
        }

        return 0;
    }

    protected function computeServiceIds(): void
    {
        $containerBuilder = $this->getContainerBuilder(self::$kernel);
        $this->servicesIds = array_filter(array_merge(
            array_keys($containerBuilder->getDefinitions()),
            array_keys($containerBuilder->getAliases()),
        ), fn (string $id) => !str_starts_with($id, '.'));
        unset($containerBuilder);
    }

    public function findServiceId(?string $identifier, SymfonyStyle $symfonyStyle): ?string
    {
        /** @var ContainerInterface $container */
        $container = self::$kernel->getContainer()->get('test.service_container');

        // Perfect match
        if ($identifier && $container->has($identifier)) {
            Interactive::outputSuccess(sprintf('Service "%s" found for identifier: %s', $identifier, $identifier), 'Found');

            return $identifier;
        }

        $found = Interactive::find($this->servicesIds, $identifier, fn (string $identifier) => $container->has($identifier));
        if (null === $found) {
            Interactive::outputError(sprintf('No service found for identifier: %s', $identifier), 'Not Found');

            return null;
        }

        if (1 === \count($found)) {
            Interactive::outputSuccess(sprintf('Service "%s" found for identifier: %s', $found[0], $identifier), 'Found');

            return $found[0];
        }

        Interactive::outputInfo(sprintf('Found multiple services for identifier: %s', $identifier), 'Multiple');
        $found[] = 'cancel';
        $candidate = $symfonyStyle->choice('Select the service you want', $found);
        if ('cancel' === $candidate) {
            Interactive::outputInfo('Cancelled');

            return null;
        }

        Interactive::outputSuccess(sprintf('Service "%s" found for identifier: %s', $candidate, $identifier), 'Found');

        return $candidate;
    }
}
