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
use Psy\Command\Command;
use Psy\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class InstanceCommand extends Command
{
    /**
     * @param class-string $className
     */
    public static function resolvedClassName($className): object
    {
        $reflexion = new \ReflectionClass($className);
        $construct = $reflexion->getConstructor();
        if ($construct && $construct->getNumberOfRequiredParameters() > 0) {
            $args = self::methodToString($construct);
            Interactive::outputWarning('This class need some non-optional parameters to be instanced: ' . implode(', ', $args));
            Interactive::outputInfo('Returning the class name instead.');
            throw new \Exception('');
        }

        return new $className();
    }

    public function __construct()
    {
        $this->forceLoadAllProjectClasses();

        parent::__construct('instance');
    }

    protected function configure(): void
    {
        $this
            ->setName('instance')
            ->setDefinition([
                new InputArgument('variable', InputArgument::REQUIRED, 'desc'),
                new InputArgument('equal', InputArgument::OPTIONAL, 'desc'),
                new InputArgument('className', InputArgument::OPTIONAL, 'desc'),
            ])
            ->setDescription('Instance command.')
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
        $identifier = $input->getArgument('className')
            ?? $input->getArgument('equal');
        $result = $this->findFullyQualifiedClassName($identifier, new SymfonyStyle($input, $output));

        if ($result) {
            try {
                $this->resolvedClassName($result);
                $shell->addInput('$' . $variable . ' = resolved_class(\'' . $result . '\');');
            } catch (\Exception $e) {
                $shell->addInput('$' . $variable . '_class = \'' . $result . '\';');
            }
        }

        return 0;
    }

    /**
     * @return array<string>
     */
    private static function methodToString(\ReflectionMethod $method): array
    {
        return array_map(fn (\ReflectionParameter $parameter) => trim(
            ($parameter->getType() ?? '') .
            ' $' . $parameter->getName() .
            ($parameter->isOptional() ? ' = ' . $parameter->getDefaultValue() : '')
        ), $method->getParameters());
    }

    /**
     * @return ?class-string
     */
    public function findFullyQualifiedClassName(?string $identifier, SymfonyStyle $symfonyStyle)
    {
        /** @var array<class-string> $classes */
        $classes = array_map(fn (string $name) => '\\' . trim($name, '\\'), get_declared_classes());
        /** @var ?array<class-string> $found */
        $found = Interactive::find(
            $classes,
            $identifier,
            fn (string $identifier) => class_exists($identifier),
        );

        if (null === $found) {
            Interactive::outputError(sprintf('No class found for identifier: %s', $identifier), 'Not Found');

            return null;
        }

        if (1 === \count($found)) {
            Interactive::outputSuccess(sprintf('Class "%s" found for identifier: %s', $found[0], $identifier), 'Found');

            return $found[0];
        }

        Interactive::outputInfo(sprintf('Found multiple classes for identifier: %s', $identifier), 'Multiple');
        $found[] = 'cancel';
        $candidate = $symfonyStyle->choice('Select the class you want', $found);
        if ('cancel' === $candidate) {
            Interactive::outputInfo('Cancelled');

            return null;
        }

        Interactive::outputSuccess(sprintf('Class "%s" found for identifier: %s', $candidate, $identifier), 'Found');

        return $candidate;
    }

    private function forceLoadAllProjectClasses(): void
    {
        $composerFile = $this->findProjectDir();
        if (!$composerFile) {
            Interactive::outputWarning('composer.json file not found.');

            return;
        }

        $composerPath = \dirname($composerFile->getRealPath());
        $composerData = json_decode((string) file_get_contents($composerFile->getRealPath()), true);
        if (false === $composerData) {
            Interactive::outputError('composer.json data corrupted.');

            return;
        }

        $autoloadPsr4 = $composerData['autoload']['psr-4'] ?? [];

        foreach ($autoloadPsr4 as $namespace => $src) {
            $currentPath = $composerPath . '/' . $src;
            foreach ((new Finder())->in($currentPath)->files()->name('*.php')->reverseSorting() as $file) {
                $relativePath = (string) preg_replace('@^' . $currentPath . '@', '', $file->getRealPath());
                $relativeDirectory = trim(\dirname($relativePath), \DIRECTORY_SEPARATOR);
                $currentNamespace = str_replace(\DIRECTORY_SEPARATOR, '\\', $relativeDirectory);
                $currentNamespace = mb_strlen($currentNamespace) > 0 ? $currentNamespace . '\\' : '';
                $fullClassName = trim($namespace, '\\') . '\\' . $currentNamespace . $file->getFilenameWithoutExtension();
                try {
                    class_exists($fullClassName);
                } catch (\Exception $e) {
                    // No need user feedback here: var_dump($e->getMessage());
                }
            }
        }
    }

    /**
     * @return false|SplFileInfo
     */
    protected function findProjectDir()
    {
        // __DIR__ = vendor/jrmgx/interactive-bundle/src/Command
        $dir = (string) realpath(__DIR__ . '/../../../../'); // vendor
        while ($dir !== \dirname($dir)) {
            $files = (new Finder())->depth('== 0')->in($dir)->files()->name('composer.json');
            if ($files->count() > 0) {
                $results = iterator_to_array($files->getIterator());

                return current($results);
            }
            $dir = \dirname($dir); // Move up one level
        }

        return false;
    }
}
